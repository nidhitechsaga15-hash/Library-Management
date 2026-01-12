<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BookRequest;
use App\Models\Borrow;
use App\Models\Book;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use App\Notifications\BookRequestApproved;

class BookRequestController extends Controller
{
    public function index()
    {
        $requests = BookRequest::with(['user', 'book', 'approvedBy'])
            ->latest()
            ->get();
        
        $pendingCount = BookRequest::where('status', 'pending')->count();
        $approvedCount = BookRequest::where('status', 'approved')->count();
        $totalRequests = BookRequest::count();

        return view('admin.book-requests.index', compact('requests', 'pendingCount', 'approvedCount', 'totalRequests'));
    }

    public function approve(BookRequest $request)
    {
        // Allow approval from pending or hold status
        if (!in_array($request->status, ['pending', 'hold'])) {
            return redirect()->back()
                ->with('error', 'Request cannot be approved from current status!');
        }

        $book = $request->book;
        
        // Get library settings for collection deadline
        $library = $book->library;
        $collectionDeadlineDays = 2; // Default
        if ($library && $library->settings) {
            $collectionDeadlineDays = $library->settings->book_collection_deadline_days ?? 2;
        }

        DB::transaction(function () use ($request, $book, $collectionDeadlineDays) {
            // Always deduct stock when approving (whether status is pending or hold)
            if (!$request->stock_deducted) {
                if ($book->available_copies <= 0) {
                    throw new \Exception('Book is not available! No stock left.');
                }
                $book->decrement('available_copies');
                $stockDeducted = true;
                $stockDeductedAt = now();
            } else {
                // Stock already deducted (shouldn't happen, but handle it)
                $stockDeducted = true;
                $stockDeductedAt = $request->stock_deducted_at ?? now();
            }
            
            // Update hold expiration if not set
            $holdExpiresAt = $request->hold_expires_at;
            if (!$holdExpiresAt) {
                $holdExpiresAt = now()->addDays($collectionDeadlineDays);
            }
            
            $request->update([
                'status' => 'approved',
                'approved_by' => auth()->id(),
                'approved_at' => now(),
                'collection_deadline' => now()->addDays($collectionDeadlineDays),
                'hold_expires_at' => $holdExpiresAt,
                'stock_deducted' => $stockDeducted,
                'stock_deducted_at' => $stockDeductedAt,
            ]);

            // Create in-app notification
            \App\Helpers\NotificationHelper::createNotification(
                $request->user_id,
                'book_approved',
                'Book Request Approved',
                'Your request for "' . $book->title . '" has been approved. Please collect it within ' . $collectionDeadlineDays . ' days.',
                route('student.my-books')
            );

            // Send email notification if notification class exists
            try {
                $request->user->notify(new BookRequestApproved($book, $request));
            } catch (\Exception $e) {
                // Notification failed, but continue
            }
        });

        return redirect()->back()
            ->with('success', 'Book request approved! Student must collect within ' . $collectionDeadlineDays . ' days.');
    }

    public function reject(BookRequest $request)
    {
        // Allow rejection from pending, hold, or approved status
        if (!in_array($request->status, ['pending', 'hold', 'approved'])) {
            return redirect()->back()
                ->with('error', 'Request cannot be rejected from current status!');
        }

        DB::transaction(function () use ($request) {
            // If stock was deducted (hold or approved status), return it
            if ($request->stock_deducted) {
                $request->book->increment('available_copies');
            }

            $request->update([
                'status' => 'cancelled',
                'approved_by' => auth()->id(),
                'approved_at' => now(),
                'stock_deducted' => false,
            ]);

            // Create in-app notification
            \App\Helpers\NotificationHelper::createNotification(
                $request->user_id,
                'book_rejected',
                'Book Request Cancelled',
                'Your request for "' . $request->book->title . '" has been cancelled. The book has been returned to stock.',
                route('student.books.index')
            );
        });

        return redirect()->back()
            ->with('success', 'Book request cancelled. Stock returned.');
    }

    /**
     * Auto-return books that weren't collected within deadline
     * This should be called via scheduled task or manually
     * Handles both 'approved' and 'hold' status requests
     */
    public function autoReturnExpiredRequests()
    {
        // Get expired holds (hold status with expired hold_expires_at)
        $expiredHolds = BookRequest::whereIn('status', ['hold', 'approved'])
            ->where('stock_deducted', true)
            ->where(function($query) {
                $query->where(function($q) {
                    // Check hold_expires_at for hold/approved status
                    $q->whereNotNull('hold_expires_at')
                      ->where('hold_expires_at', '<', now());
                })->orWhere(function($q) {
                    // Fallback: check collection_deadline for older records
                    $q->whereNull('hold_expires_at')
                      ->whereNotNull('collection_deadline')
                      ->where('collection_deadline', '<', now());
                });
            })
            ->with('book')
            ->get();

        $count = 0;
        foreach ($expiredHolds as $request) {
            DB::transaction(function () use ($request) {
                // Return book to stock
                $request->book->increment('available_copies');
                
                // Update request status to cancelled
                $request->update([
                    'status' => 'cancelled',
                    'stock_deducted' => false,
                ]);

                // Notify user
                \App\Helpers\NotificationHelper::createNotification(
                    $request->user_id,
                    'book_request_expired',
                    'Book Request Cancelled',
                    'You didn\'t pick your book "' . $request->book->title . '" in time. Your request is cancelled and the book has been returned to stock.',
                    route('student.books.index')
                );
            });
            $count++;
        }

        return $count;
    }

    public function issue(BookRequest $request)
    {
        // Allow issue from approved or hold status
        if (!in_array($request->status, ['approved', 'hold'])) {
            return redirect()->back()
                ->with('error', 'Request must be approved or on hold first!');
        }

        $book = $request->book;
        $user = $request->user;

        if (!$book->isAvailable()) {
            return redirect()->back()
                ->with('error', 'Book is not available!');
        }

        // Check if user has valid library card
        if ($user->isStudent() && !$user->hasValidLibraryCard()) {
            return redirect()->back()
                ->with('error', 'Student does not have a valid library card! Please issue a library card first.');
        }

        // Check book limit based on member type
        $maxBooks = $user->getMaxBooksAllowed();
        if (!$user->canBorrowMoreBooks()) {
            return redirect()->back()
                ->with('error', ucfirst($user->member_type ?? 'user') . ' has reached the maximum book limit (' . $maxBooks . ' books). Please return a book before issuing a new one.');
        }

        // Check if user already has this book borrowed
        $existingBorrow = Borrow::where('user_id', $user->id)
            ->where('book_id', $book->id)
            ->where('status', 'borrowed')
            ->first();

        if ($existingBorrow) {
            return redirect()->back()
                ->with('error', 'User already has this book borrowed!');
        }

        // Check if user already has a book with the same subject (only 1 book per subject allowed)
        if ($book->subject) {
            $sameSubjectBorrow = Borrow::where('user_id', $user->id)
                ->where('status', 'borrowed')
                ->whereHas('book', function($query) use ($book) {
                    $query->where('subject', $book->subject);
                })
                ->first();

            if ($sameSubjectBorrow) {
                return redirect()->back()
                    ->with('error', 'Student already has a book with subject "' . $book->subject . '" borrowed. A student can only borrow 1 book per subject. Please return the existing book first.');
            }
        }

        // Get library settings for issue duration
        $library = $book->library;
        $issueDurationDays = 14; // Default
        if ($library && $library->settings) {
            $issueDurationDays = $library->settings->book_issue_duration_days ?? 14;
        }

        DB::transaction(function () use ($request, $book, $user, $issueDurationDays) {
            // Calculate due date: Start counting from next day after issue date
            $borrowDate = now();
            $dueDate = $borrowDate->copy()->addDay()->addDays($issueDurationDays - 1);
            
            // Get fine per day based on issue duration
            $finePerDay = \App\Helpers\FineHelper::getFinePerDayByDuration($issueDurationDays);
            
            // Create borrow record
            $borrow = Borrow::create([
                'user_id' => $user->id,
                'book_id' => $book->id,
                'borrow_date' => $borrowDate,
                'issue_duration_days' => $issueDurationDays,
                'due_date' => $dueDate,
                'fine_per_day' => $finePerDay,
                'status' => 'borrowed',
                'issued_by' => auth()->id(),
            ]);

            // Note: Stock already deducted on hold/approval, so we don't deduct again here

            // Update request status and mark as received
            $request->update([
                'status' => 'issued',
                'received_at' => now(),
            ]);

            // Create in-app notification
            \App\Helpers\NotificationHelper::createNotification(
                $user->id,
                'book_issued',
                'Book Issued',
                'The book "' . $book->title . '" has been issued to you. Due date: ' . $borrow->due_date->format('M d, Y'),
                route('student.my-books')
            );
        });

        return redirect()->route('admin.borrows.index')
            ->with('success', 'Book issued successfully to ' . $user->name . '!');
    }

    /**
     * Show QR scanner page
     */
    public function scanQr()
    {
        return view('admin.book-requests.scan');
    }

    /**
     * Show book details after QR scan
     */
    public function showScanResult(Request $httpRequest, BookRequest $request = null)
    {
        // Handle manual request ID entry (from query parameter)
        if ($httpRequest->has('request_id') && !$request) {
            $requestId = $httpRequest->get('request_id');
            $request = BookRequest::findOrFail($requestId);
        }

        $book = $request->book;
        $user = $request->user;
        $library = $book->library;
        
        // Only show if request is on hold or approved
        if (!in_array($request->status, ['hold', 'approved'])) {
            return redirect()->route('admin.book-requests.scan')
                ->with('error', 'This book request is not available for pickup. Status: ' . $request->status);
        }
        
        return view('admin.book-requests.scan-result', compact('request', 'book', 'user', 'library'));
    }

    /**
     * Show book location by QR code scan (legacy method)
     */
    public function showLocationByQr(BookRequest $request)
    {
        if ($request->status !== 'approved' && $request->status !== 'issued') {
            abort(404, 'Book request not found or not approved');
        }

        $book = $request->book;
        $library = $book->library;
        
        return view('admin.book-requests.location', compact('request', 'book', 'library'));
    }
}
