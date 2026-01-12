<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Borrow;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        
        $issued_books = Borrow::where('user_id', $user->id)
            ->where('status', 'borrowed')
            ->with(['book', 'fine'])
            ->orderBy('due_date')
            ->get();
        
        // Recalculate and update fines for all overdue books
        $totalPendingFines = 0;
        $pendingFines = collect();
        
        foreach ($issued_books as $borrow) {
            if ($borrow->isOverdue()) {
                // Refresh to get latest calculations
                $borrow->refresh();
                
                // Calculate pending fine from last_fine_paid_date
                $pendingFineAmount = $borrow->current_fine_amount;
                $pendingDays = $borrow->pending_fine_days;
                
                if ($pendingFineAmount > 0) {
                    $fine = $borrow->fine;
                    
                    if ($fine) {
                        // Update existing fine record
                        $fine->amount = $pendingFineAmount;
                        $fine->remaining_amount = $pendingFineAmount;
                        $fine->reason = 'Overdue book - ' . $pendingDays . ' day(s) pending from ' . 
                            ($borrow->last_fine_paid_date ? $borrow->last_fine_paid_date->format('Y-m-d') : $borrow->due_date->format('Y-m-d'));
                        $fine->status = 'pending';
                        $fine->save();
                    } else {
                        // Create new fine record
                        $fine = \App\Models\Fine::create([
                            'borrow_id' => $borrow->id,
                            'user_id' => $user->id,
                            'amount' => $pendingFineAmount,
                            'remaining_amount' => $pendingFineAmount,
                            'reason' => 'Overdue book - ' . $pendingDays . ' day(s) pending from ' . 
                                ($borrow->last_fine_paid_date ? $borrow->last_fine_paid_date->format('Y-m-d') : $borrow->due_date->format('Y-m-d')),
                            'status' => 'pending',
                            'days_overdue_at_creation' => $borrow->days_overdue,
                        ]);
                        $borrow->load('fine');
                    }
                    
                    $totalPendingFines += $pendingFineAmount;
                    $pendingFines->push($fine);
                }
            }
        }
        
        // Get all pending fines with relationships
        $pendingFines = \App\Models\Fine::where('user_id', $user->id)
            ->where('status', 'pending')
            ->with(['borrow.book'])
            ->latest()
            ->get();
        
        // Recalculate total pending from actual fine records
        $totalPendingFines = 0;
        foreach ($pendingFines as $fine) {
            $fine->load('borrow');
            if ($fine->borrow && $fine->borrow->isOverdue()) {
                // Refresh borrow to get latest calculations
                $fine->borrow->refresh();
                // Use current pending amount (from last_fine_paid_date)
                $totalPendingFines += $fine->borrow->current_fine_amount;
            } else {
                $totalPendingFines += $fine->remaining_amount ?? $fine->amount;
            }
        }
        
        // Reload issued_books with updated fine relationships
        $issued_books = $issued_books->map(function($borrow) {
            $borrow->load('fine');
            if ($borrow->isOverdue()) {
                $borrow->refresh();
            }
            return $borrow;
        });
        
        $stats = [
            'active_borrows' => $issued_books->count(),
            'total_borrows' => Borrow::where('user_id', $user->id)->count(),
            'overdue_borrows' => $issued_books->filter(function($borrow) {
                return $borrow->isOverdue();
            })->count(),
            'pending_fines' => $totalPendingFines,
        ];

        return view('student.dashboard', compact('stats', 'issued_books', 'pendingFines'));
    }

    public function myBooks()
    {
        $borrows = Borrow::where('user_id', auth()->id())
            ->where('status', 'borrowed')
            ->with(['book', 'fine'])
            ->orderBy('due_date')
            ->paginate(15);
        
        // Recalculate fines for overdue books
        foreach ($borrows as $borrow) {
            if ($borrow->isOverdue()) {
                // Refresh to get latest calculations
                $borrow->refresh();
                
                // Update or create fine record
                $pendingFineAmount = $borrow->current_fine_amount;
                $pendingDays = $borrow->pending_fine_days;
                
                if ($pendingFineAmount > 0) {
                    $fine = $borrow->fine;
                    if ($fine) {
                        // Update existing fine
                        $fine->amount = $pendingFineAmount;
                        $fine->remaining_amount = $pendingFineAmount;
                        $fine->reason = 'Overdue book - ' . $pendingDays . ' day(s) pending from ' . 
                            ($borrow->last_fine_paid_date ? $borrow->last_fine_paid_date->format('Y-m-d') : $borrow->due_date->format('Y-m-d'));
                        $fine->status = 'pending';
                        $fine->save();
                    }
                }
            }
        }

        return view('student.my-books', compact('borrows'));
    }
}
