<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Book;
use App\Models\Borrow;
use App\Models\BookReservation;
use App\Models\Fine;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ChatbotController extends Controller
{
    public function index()
    {
        return view('student.chatbot.index');
    }

    public function query(Request $request)
    {
        $request->validate([
            'message' => 'required|string|max:1000',
        ]);

        $user = Auth::user();
        $message = strtolower(trim($request->message));
        $response = $this->processQuery($message, $user);

        return response()->json([
            'success' => true,
            'response' => $response['message'],
            'type' => $response['type'],
            'data' => $response['data'] ?? null,
        ]);
    }

    private function processQuery($message, $user)
    {
        // My books / Issued books count
        if ($this->isMyBooksQuery($message)) {
            return $this->handleMyBooks($message, $user);
        }

        // Book availability check (by title, author, ISBN)
        if ($this->isBookAvailabilityQuery($message)) {
            return $this->handleBookAvailability($message, $user);
        }

        // Issue/Return process guide
        if ($this->isIssueReturnQuery($message)) {
            return $this->handleIssueReturnGuide($message, $user);
        }

        // Reservation status
        if ($this->isReservationQuery($message)) {
            return $this->handleReservationStatus($message, $user);
        }

        // Overdue fines info
        if ($this->isFineQuery($message)) {
            return $this->handleFineInfo($message, $user);
        }

        // E-resource access
        if ($this->isEResourceQuery($message)) {
            return $this->handleEResourceGuide($message, $user);
        }

        // Payment queries
        if ($this->isPaymentQuery($message)) {
            return $this->handlePaymentGuide($message, $user);
        }

        // LMS / Course recommendations
        if ($this->isLMSQuery($message)) {
            return $this->handleLMSRecommendations($message, $user);
        }

        // General FAQs
        if ($this->isFAQQuery($message)) {
            return $this->handleFAQ($message, $user);
        }

        // Default response
        return [
            'message' => "I'm here to help you with library services! You can ask me about:\n\n📚 Book availability (by title, author, ISBN)\n📖 My issued books\n📋 Issue/Return process\n📋 Reservation status\n💰 Overdue fines & Online payments\n💻 E-resource access\n🎓 Course-specific book recommendations (LMS)\n❓ Library rules, timings, and membership\n\nHow can I assist you today?",
            'type' => 'text',
        ];
    }

    private function isMyBooksQuery($message)
    {
        $keywords = ['my books', 'my issued books', 'kitni book issue', 'how many books', 'issued books', 'borrowed books', 'my borrowed', 'meri kitni book'];
        return $this->containsKeywords($message, $keywords);
    }

    private function isBookAvailabilityQuery($message)
    {
        $keywords = ['book available', 'is book available', 'check book', 'book status', 'find book', 'search book', 'book exist', 'author', 'isbn', 'available', 'is available', 'by author', 'isbn no'];
        return $this->containsKeywords($message, $keywords);
    }

    private function isIssueReturnQuery($message)
    {
        $keywords = ['how to issue', 'how to return', 'issue process', 'return process', 'borrow book', 'return book', 'how do i get', 'how do i return'];
        return $this->containsKeywords($message, $keywords);
    }

    private function isReservationQuery($message)
    {
        $keywords = ['reservation', 'reserved', 'my reservations', 'reservation status', 'book reserved'];
        return $this->containsKeywords($message, $keywords);
    }

    private function isFineQuery($message)
    {
        $keywords = ['fine', 'fines', 'overdue', 'penalty', 'late fee', 'due date', 'my fines'];
        return $this->containsKeywords($message, $keywords);
    }

    private function isEResourceQuery($message)
    {
        $keywords = ['e-resource', 'e resource', 'electronic resource', 'online resource', 'digital resource', 'ebook', 'e-book'];
        return $this->containsKeywords($message, $keywords);
    }

    private function isPaymentQuery($message)
    {
        $keywords = ['payment', 'pay fine', 'online payment', 'pay online', 'payment gateway', 'razorpay', 'pay fine online', 'fine payment', 'membership payment', 'e-resource payment'];
        return $this->containsKeywords($message, $keywords);
    }

    private function isLMSQuery($message)
    {
        $keywords = ['lms', 'course recommendation', 'course books', 'recommended books', 'course specific', 'semester books', 'my course books', 'recommendation', 'course based'];
        return $this->containsKeywords($message, $keywords);
    }

    private function isFAQQuery($message)
    {
        $keywords = ['timing', 'timings', 'hours', 'open', 'close', 'membership', 'rule', 'rules', 'policy', 'policies', 'limit', 'maximum', 'how many books'];
        return $this->containsKeywords($message, $keywords);
    }

    private function containsKeywords($message, $keywords)
    {
        foreach ($keywords as $keyword) {
            if (strpos($message, $keyword) !== false) {
                return true;
            }
        }
        return false;
    }

    private function handleMyBooks($message, $user)
    {
        $activeBorrows = Borrow::where('user_id', $user->id)
            ->where('status', 'borrowed')
            ->with('book.author')
            ->get();
        
        $totalBorrows = $activeBorrows->count();
        $maxBooks = 2;
        
        $response = "📚 **Your Issued Books**\n\n";
        $response .= "**Total Issued: {$totalBorrows} / {$maxBooks} books**\n\n";
        
        if ($totalBorrows > 0) {
            $response .= "**Your Current Books:**\n\n";
            foreach ($activeBorrows as $borrow) {
                $daysLeft = $borrow->days_left;
                $overdue = $borrow->isOverdue();
                $status = $overdue ? "⚠️ OVERDUE ({$borrow->days_overdue} days)" : "✅ Due in {$daysLeft} days";
                
                $response .= "📖 **{$borrow->book->title}**\n";
                if ($borrow->book->author) {
                    $response .= "Author: {$borrow->book->author->name}\n";
                }
                $response .= "Issued: " . $borrow->borrow_date->format('M d, Y') . "\n";
                $response .= "Due Date: " . $borrow->due_date->format('M d, Y') . "\n";
                $response .= "Status: {$status}\n";
                
                if ($overdue) {
                    $estimatedFine = $borrow->current_fine_amount;
                    $response .= "Estimated Fine: ₹{$estimatedFine}\n";
                }
                $response .= "\n";
            }
        } else {
            $response .= "You don't have any books issued currently.\n\n";
            $response .= "💡 You can browse and request books from the Books section!";
        }
        
        if ($totalBorrows >= $maxBooks) {
            $response .= "\n⚠️ **Note:** You've reached the maximum limit. Return a book first to borrow a new one.";
        } else {
            $remaining = $maxBooks - $totalBorrows;
            $response .= "\n✅ You can borrow {$remaining} more book(s).";
        }
        
        return [
            'message' => $response,
            'type' => 'my_books',
            'data' => [
                'total_borrows' => $totalBorrows,
                'max_books' => $maxBooks,
                'borrows' => $activeBorrows->map(function($borrow) {
                    return [
                        'id' => $borrow->id,
                        'book_title' => $borrow->book->title,
                        'author' => $borrow->book->author->name ?? 'N/A',
                        'due_date' => $borrow->due_date->format('Y-m-d'),
                        'days_left' => $borrow->days_left,
                        'is_overdue' => $borrow->isOverdue(),
                    ];
                }),
            ],
        ];
    }

    private function handleBookAvailability($message, $user)
    {
        // Check for ISBN
        $isbn = $this->extractISBN($message);
        if ($isbn) {
            $book = Book::where('isbn', 'like', '%' . $isbn . '%')->first();
            if ($book) {
                return $this->formatBookResponse($book);
            }
            return [
                'message' => "❌ No book found with ISBN: {$isbn}. Please check the ISBN and try again.",
                'type' => 'text',
            ];
        }
        
        // Check for author name
        $authorName = $this->extractAuthorName($message);
        if ($authorName) {
            $books = Book::whereHas('author', function($q) use ($authorName) {
                $q->where('name', 'like', '%' . $authorName . '%');
            })->get();
            
            if ($books->count() > 0) {
                $response = "📚 **Books by Author: {$authorName}**\n\n";
                foreach ($books->take(5) as $book) {
                    $available = $book->isAvailable();
                    $status = $available ? '✅ Available' : '❌ Not Available';
                    $response .= "📖 **{$book->title}**\n";
                    $response .= "Status: {$status}\n";
                    $response .= "Available: {$book->available_copies} / {$book->total_copies}\n";
                    if ($book->isbn) {
                        $response .= "ISBN: {$book->isbn}\n";
                    }
                    $response .= "\n";
                }
                
                if ($books->count() > 5) {
                    $response .= "... and " . ($books->count() - 5) . " more book(s)\n\n";
                }
                
                $response .= "💡 Search for specific book title or ISBN for more details!";
                
                return [
                    'message' => $response,
                    'type' => 'text',
                ];
            } else {
                return [
                    'message' => "❌ No books found by author: {$authorName}. Please check the author name and try again.",
                    'type' => 'text',
                ];
            }
        }
        
        // Try to extract book title from message
        $bookTitle = $this->extractBookTitle($message);
        
        if ($bookTitle) {
            $book = Book::where('title', 'like', '%' . $bookTitle . '%')
                ->orWhere('isbn', 'like', '%' . $bookTitle . '%')
                ->first();
            
            if ($book) {
                return $this->formatBookResponse($book);
            } else {
                return [
                    'message' => "❌ I couldn't find a book matching '{$bookTitle}'. Please try:\n• Exact book title\n• ISBN number\n• Author name\n\nYou can also browse books from the Books section.",
                    'type' => 'text',
                ];
            }
        }
        
        return [
            'message' => "📚 **Book Availability Check**\n\nYou can check book availability by:\n\n1. **Book Title:**\n   \"Is 'Introduction to Programming' available?\"\n\n2. **ISBN Number:**\n   \"Is ISBN 123456789 available?\"\n   \"Check ISBN no 123456789\"\n\n3. **Author Name:**\n   \"Books by John Smith available?\"\n   \"Is author John Smith ki book available?\"\n\n💡 You can also browse all available books from the Books section!",
            'type' => 'text',
        ];
    }

    private function formatBookResponse($book)
    {
        $available = $book->isAvailable();
        $status = $available ? '✅ Available' : '❌ Not Available';
        $copies = $book->available_copies;
        $total = $book->total_copies;
        
        $response = "📚 **Book: {$book->title}**\n\n";
        $response .= "Status: {$status}\n";
        $response .= "Available Copies: {$copies} / {$total}\n";
        
        if ($book->author) {
            $response .= "Author: {$book->author->name}\n";
        }
        
        if ($book->isbn) {
            $response .= "ISBN: {$book->isbn}\n";
        }
        
        if ($book->category) {
            $response .= "Category: {$book->category->name}\n";
        }
        
        if ($book->publisher) {
            $response .= "Publisher: {$book->publisher->name}\n";
        }
        
        if ($available) {
            $response .= "\n✅ You can request this book from the Books section!";
        } else {
            $response .= "\n💡 You can reserve this book and you'll be notified when it becomes available.";
        }
        
        return [
            'message' => $response,
            'type' => 'book_info',
            'data' => [
                'book_id' => $book->id,
                'title' => $book->title,
                'available' => $available,
                'available_copies' => $copies,
            ],
        ];
    }

    private function extractISBN($message)
    {
        // Patterns for ISBN
        $patterns = [
            '/isbn[:\s]+([0-9\-Xx]+)/i',
            '/isbn\s+no[:\s]+([0-9\-Xx]+)/i',
            '/isbn\s+number[:\s]+([0-9\-Xx]+)/i',
            '/isbn\s+([0-9\-Xx]+)/i',
        ];
        
        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $message, $matches)) {
                return trim($matches[1]);
            }
        }
        
        // Check if message is just numbers (might be ISBN)
        if (preg_match('/\b([0-9]{10,13})\b/', $message, $matches)) {
            return trim($matches[1]);
        }
        
        return null;
    }

    private function extractAuthorName($message)
    {
        // Patterns for author
        $patterns = [
            '/author[:\s]+([a-zA-Z\s]+?)(?:available|book|ki|ka)/i',
            '/by\s+author[:\s]+([a-zA-Z\s]+?)(?:available|book)/i',
            '/author\s+([a-zA-Z\s]+?)\s+ki\s+book/i',
            '/author\s+([a-zA-Z\s]+?)\s+available/i',
        ];
        
        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $message, $matches)) {
                $author = trim($matches[1]);
                if (strlen($author) > 2) {
                    return $author;
                }
            }
        }
        
        return null;
    }

    private function extractBookTitle($message)
    {
        // Try to extract book title from common patterns
        $patterns = [
            '/is\s+["\'](.+?)["\']\s+available/i',
            '/check\s+["\'](.+?)["\']\s+available/i',
            '/is\s+(.+?)\s+available/i',
            '/check\s+(.+?)\s+available/i',
            '/book\s+(.+?)$/i',
            '/title\s+(.+?)$/i',
        ];
        
        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $message, $matches)) {
                $title = trim($matches[1]);
                // Skip if it's too short or common words
                if (strlen($title) > 3 && !in_array(strtolower($title), ['the', 'a', 'an', 'is', 'are'])) {
                    return $title;
                }
            }
        }
        
        // If no pattern matches, try to find quoted text
        if (preg_match('/["\'](.+?)["\']/', $message, $matches)) {
            return trim($matches[1]);
        }
        
        return null;
    }

    private function handleIssueReturnGuide($message, $user)
    {
        $isReturn = strpos($message, 'return') !== false;
        
        if ($isReturn) {
            $activeBorrows = Borrow::where('user_id', $user->id)
                ->where('status', 'borrowed')
                ->with('book')
                ->get();
            
            $response = "📖 **Book Return Process**\n\n";
            $response .= "1. Go to the library with the book\n";
            $response .= "2. Present the book to the library staff\n";
            $response .= "3. Staff will process the return\n";
            $response .= "4. You'll receive a confirmation\n\n";
            
            if ($activeBorrows->count() > 0) {
                $response .= "📚 **Your Currently Borrowed Books:**\n\n";
                foreach ($activeBorrows as $borrow) {
                    $daysLeft = $borrow->days_left;
                    $overdue = $borrow->isOverdue();
                    $status = $overdue ? "⚠️ OVERDUE ({$borrow->days_overdue} days)" : "✅ Due in {$daysLeft} days";
                    $response .= "• {$borrow->book->title}\n";
                    $response .= "  Due Date: " . $borrow->due_date->format('M d, Y') . "\n";
                    $response .= "  Status: {$status}\n\n";
                }
            }
            
            $response .= "⚠️ **Important:** Return books on time to avoid fines!";
            
            return [
                'message' => $response,
                'type' => 'return_guide',
                'data' => [
                    'borrows' => $activeBorrows->map(function($borrow) {
                        return [
                            'id' => $borrow->id,
                            'book_title' => $borrow->book->title,
                            'due_date' => $borrow->due_date->format('Y-m-d'),
                            'days_left' => $borrow->days_left,
                            'is_overdue' => $borrow->isOverdue(),
                        ];
                    }),
                ],
            ];
        } else {
            $activeBorrows = $user->getActiveBorrowsCount();
            $maxBooks = 2;
            
            $response = "📖 **Book Issue Process**\n\n";
            $response .= "1. Browse books from the Books section\n";
            $response .= "2. Click on a book you want to borrow\n";
            $response .= "3. Click 'Request Book' button\n";
            $response .= "4. Wait for admin/staff approval\n";
            $response .= "5. Collect the book from library once approved\n\n";
            
            $response .= "📊 **Your Status:**\n";
            $response .= "Currently Borrowed: {$activeBorrows} / {$maxBooks} books\n\n";
            
            if ($activeBorrows >= $maxBooks) {
                $response .= "⚠️ You've reached the maximum limit. Return a book first to borrow a new one.\n\n";
            }
            
            $response .= "📋 **Rules:**\n";
            $response .= "• Maximum {$maxBooks} books at a time\n";
            $response .= "• Only 1 book per subject allowed\n";
            $response .= "• Books must be returned on or before due date\n";
            $response .= "• Late returns will incur fines\n";
            
            return [
                'message' => $response,
                'type' => 'issue_guide',
                'data' => [
                    'active_borrows' => $activeBorrows,
                    'max_books' => $maxBooks,
                ],
            ];
        }
    }

    private function handleReservationStatus($message, $user)
    {
        $reservations = BookReservation::where('user_id', $user->id)
            ->with('book.author')
            ->latest()
            ->get();
        
        if ($reservations->isEmpty()) {
            return [
                'message' => "📋 **Reservation Status**\n\nYou don't have any active reservations.\n\n💡 To reserve a book:\n1. Go to Books section\n2. Find an unavailable book\n3. Click 'Reserve' button\n4. You'll be notified when it becomes available!",
                'type' => 'text',
            ];
        }
        
        $response = "📋 **Your Reservations**\n\n";
        
        foreach ($reservations as $reservation) {
            $status = match($reservation->status) {
                'pending' => '⏳ Pending',
                'available' => '✅ Available - Collect Now!',
                'expired' => '❌ Expired',
                'cancelled' => '❌ Cancelled',
                default => '❓ Unknown',
            };
            
            $response .= "📚 **{$reservation->book->title}**\n";
            $response .= "Status: {$status}\n";
            $response .= "Reserved: " . $reservation->reserved_at->format('M d, Y') . "\n";
            
            if ($reservation->status === 'available') {
                $response .= "⚠️ **Action Required:** Collect within 3 days!\n";
            } elseif ($reservation->status === 'pending') {
                $response .= "⏳ Waiting for book to become available...\n";
            }
            
            $response .= "\n";
        }
        
        return [
            'message' => $response,
            'type' => 'reservations',
            'data' => [
                'reservations' => $reservations->map(function($reservation) {
                    return [
                        'id' => $reservation->id,
                        'book_title' => $reservation->book->title,
                        'status' => $reservation->status,
                        'reserved_at' => $reservation->reserved_at->format('Y-m-d'),
                    ];
                }),
            ],
        ];
    }

    private function handleFineInfo($message, $user)
    {
        $fines = Fine::where('user_id', $user->id)
            ->with('borrow.book')
            ->latest()
            ->get();
        
        $totalPending = Fine::where('user_id', $user->id)
            ->where('status', 'pending')
            ->sum('amount');
        
        $overdueBorrows = Borrow::where('user_id', $user->id)
            ->where('status', 'borrowed')
            ->where('due_date', '<', now())
            ->with('book')
            ->get();
        
        $response = "💰 **Fine Information**\n\n";
        
        if ($totalPending > 0) {
            $response .= "⚠️ **Total Pending Fines: ₹{$totalPending}**\n\n";
        } else {
            $response .= "✅ **No pending fines!**\n\n";
        }
        
        if ($overdueBorrows->count() > 0) {
            $response .= "📚 **Overdue Books (May incur fines):**\n\n";
            foreach ($overdueBorrows as $borrow) {
                $daysOverdue = $borrow->days_overdue;
                $estimatedFine = $borrow->current_fine_amount;
                $response .= "• {$borrow->book->title}\n";
                $response .= "  Overdue: {$daysOverdue} days\n";
                $response .= "  Estimated Fine: ₹{$estimatedFine}\n\n";
            }
        }
        
        if ($fines->count() > 0) {
            $response .= "📋 **Fine History:**\n\n";
            foreach ($fines->take(5) as $fine) {
                $status = $fine->status === 'paid' ? '✅ Paid' : '⚠️ Pending';
                $response .= "• {$fine->borrow->book->title}\n";
                $response .= "  Amount: ₹{$fine->amount}\n";
                $response .= "  Status: {$status}\n";
                $response .= "  Reason: {$fine->reason}\n\n";
            }
        }
        
        $response .= "💡 **Fine Rules:**\n";
        $response .= "• Fines are calculated per day for overdue books\n";
        $response .= "• Pay fines at the library counter\n";
        $response .= "• Unpaid fines may restrict new book issues\n";
        
        return [
            'message' => $response,
            'type' => 'fines',
            'data' => [
                'total_pending' => $totalPending,
                'fines' => $fines->map(function($fine) {
                    return [
                        'id' => $fine->id,
                        'amount' => $fine->amount,
                        'status' => $fine->status,
                        'reason' => $fine->reason,
                        'book_title' => $fine->borrow->book->title,
                    ];
                }),
                'overdue_borrows' => $overdueBorrows->map(function($borrow) {
                    return [
                        'id' => $borrow->id,
                        'book_title' => $borrow->book->title,
                        'days_overdue' => $borrow->days_overdue,
                        'estimated_fine' => $borrow->current_fine_amount,
                    ];
                }),
            ],
        ];
    }

    private function handleEResourceGuide($message, $user)
    {
        $response = "💻 **E-Resource Access Guide**\n\n";
        $response .= "📚 **Available E-Resources:**\n";
        $response .= "• Digital Library Portal\n";
        $response .= "• Online Journals & Databases\n";
        $response .= "• E-Books Collection\n";
        $response .= "• Research Papers & Articles\n\n";
        
        $response .= "🔐 **How to Access:**\n";
        $response .= "1. Visit the library website\n";
        $response .= "2. Login with your student credentials\n";
        $response .= "3. Navigate to E-Resources section\n";
        $response .= "4. Browse or search for resources\n";
        $response .= "5. Access/download as needed\n\n";
        
        $response .= "📋 **Requirements:**\n";
        $response .= "• Valid library membership\n";
        $response .= "• Active student account\n";
        $response .= "• Internet connection\n\n";
        
        $response .= "❓ **Need Help?**\n";
        $response .= "Contact library staff for assistance with e-resource access or technical issues.";
        
        return [
            'message' => $response,
            'type' => 'text',
        ];
    }

    private function handleFAQ($message, $user)
    {
        // Library timings
        if (strpos($message, 'timing') !== false || strpos($message, 'hour') !== false || strpos($message, 'open') !== false || strpos($message, 'close') !== false) {
            return [
                'message' => "🕐 **Library Timings**\n\n**Monday - Friday:**\n9:00 AM - 6:00 PM\n\n**Saturday:**\n9:00 AM - 2:00 PM\n\n**Sunday:**\nClosed\n\n**Holidays:**\nClosed (Check notice board for holiday schedule)\n\n**Note:** Timings may vary during exams. Check notice board for updates.",
                'type' => 'text',
            ];
        }
        
        // Membership
        if (strpos($message, 'membership') !== false || strpos($message, 'member') !== false) {
            return [
                'message' => "🎓 **Library Membership**\n\n**Eligibility:**\n• All enrolled students are automatically members\n• Membership is valid during your course duration\n\n**Benefits:**\n• Borrow up to 2 books at a time\n• Access to e-resources\n• Book reservations\n• Research assistance\n• Library card facility\n\n**Library Card:**\n• View your library card from the menu\n• Report lost cards immediately\n• Card is required for book transactions\n• Request new card if lost\n\n**Membership Renewal:**\n• Automatic renewal during course\n• No manual renewal needed\n• Contact admin if issues",
                'type' => 'text',
            ];
        }
        
        // Book limits
        if (strpos($message, 'limit') !== false || strpos($message, 'maximum') !== false || strpos($message, 'how many') !== false || strpos($message, 'kitni book') !== false) {
            $activeBorrows = $user->getActiveBorrowsCount();
            $maxBooks = 2;
            
            return [
                'message' => "📚 **Book Borrowing Limits**\n\n**Maximum Books:** {$maxBooks} books at a time\n\n**Your Current Status:**\nCurrently Borrowed: {$activeBorrows} / {$maxBooks} books\n\n**Additional Rules:**\n• Only 1 book per subject allowed\n• Books must be returned on or before due date\n• Late returns incur fines\n• Unpaid fines may restrict new issues\n• Cannot borrow if limit reached",
                'type' => 'text',
                'data' => [
                    'active_borrows' => $activeBorrows,
                    'max_books' => $maxBooks,
                ],
            ];
        }
        
        // Library card
        if (strpos($message, 'card') !== false || strpos($message, 'library card') !== false) {
            $card = \App\Models\LibraryCard::where('user_id', $user->id)->latest()->first();
            
            $response = "🪪 **Library Card Information**\n\n";
            if ($card) {
                $status = $card->status === 'active' ? '✅ Active' : ($card->status === 'blocked' ? '❌ Blocked' : '⚠️ Inactive');
                $response .= "**Your Card Status:** {$status}\n";
                $response .= "Card Number: {$card->card_number}\n";
                $response .= "Issued: " . $card->issued_at->format('M d, Y') . "\n";
                if ($card->expires_at) {
                    $response .= "Expires: " . $card->expires_at->format('M d, Y') . "\n";
                }
            } else {
                $response .= "You don't have a library card yet.\n";
                $response .= "💡 Request a library card from the Library Card section!";
            }
            
            $response .= "\n\n**Card Functions:**\n";
            $response .= "• Required for all book transactions\n";
            $response .= "• Report lost cards immediately\n";
            $response .= "• Keep card safe and secure\n";
            $response .= "• Request replacement if lost\n";
            
            return [
                'message' => $response,
                'type' => 'text',
            ];
        }
        
        // Book request
        if (strpos($message, 'request') !== false || strpos($message, 'book request') !== false) {
            $pendingRequests = \App\Models\BookRequest::where('user_id', $user->id)
                ->whereIn('status', ['pending', 'hold', 'approved'])
                ->with('book')
                ->get();
            
            $response = "📝 **Book Request Status**\n\n";
            if ($pendingRequests->count() > 0) {
                $response .= "**Your Active Requests:** {$pendingRequests->count()}\n\n";
                foreach ($pendingRequests as $request) {
                    $status = match($request->status) {
                        'pending' => '⏳ Pending',
                        'hold' => '✅ On Hold',
                        'approved' => '✅ Approved',
                        default => '❓ Unknown',
                    };
                    $response .= "📖 **{$request->book->title}**\n";
                    $response .= "Status: {$status}\n";
                    $response .= "Requested: " . $request->created_at->format('M d, Y') . "\n\n";
                }
            } else {
                $response .= "You don't have any active book requests.\n\n";
            }
            
            $response .= "**How to Request:**\n";
            $response .= "1. Go to Books section\n";
            $response .= "2. Find the book you want\n";
            $response .= "3. Click 'Request Book'\n";
            $response .= "4. Wait for approval\n";
            $response .= "5. Collect when approved\n";
            
            return [
                'message' => $response,
                'type' => 'text',
            ];
        }
        
        // Subject/Subject-wise books
        if (strpos($message, 'subject') !== false) {
            $response = "📚 **Subject-wise Book Rules**\n\n";
            $response .= "**Important Rule:**\n";
            $response .= "• Only 1 book per subject allowed\n";
            $response .= "• Cannot borrow multiple books of same subject\n";
            $response .= "• Return current subject book first\n\n";
            
            $activeBorrows = Borrow::where('user_id', $user->id)
                ->where('status', 'borrowed')
                ->with('book')
                ->get();
            
            if ($activeBorrows->count() > 0) {
                $subjects = $activeBorrows->pluck('book.subject')->filter()->unique();
                if ($subjects->count() > 0) {
                    $response .= "**Your Current Subject Books:**\n";
                    foreach ($subjects as $subject) {
                        $response .= "• {$subject}\n";
                    }
                    $response .= "\n";
                }
            }
            
            $response .= "**Example:**\n";
            $response .= "If you have 'Mathematics' book issued, you cannot request another 'Mathematics' book until you return the current one.";
            
            return [
                'message' => $response,
                'type' => 'text',
            ];
        }
        
        // Course/Semester/Year books
        if (strpos($message, 'course') !== false || strpos($message, 'semester') !== false || strpos($message, 'year') !== false) {
            $response = "📖 **Course/Semester Books**\n\n";
            if ($user->course) {
                $response .= "**Your Course:** {$user->course}\n";
            }
            if ($user->semester) {
                $response .= "**Your Semester:** {$user->semester}\n";
            }
            if ($user->year) {
                $response .= "**Your Year:** {$user->year}\n";
            }
            $response .= "\n";
            
            $response .= "**Book Recommendations:**\n";
            $response .= "• Books are filtered by your course/semester\n";
            $response .= "• Browse Books section shows relevant books\n";
            $response .= "• Search for specific books anytime\n";
            $response .= "• All books available for request\n";
            
            return [
                'message' => $response,
                'type' => 'text',
            ];
        }
        
        // General rules
        return [
            'message' => "📋 **Library Rules & Policies**\n\n**Borrowing Rules:**\n• Maximum 2 books at a time\n• Only 1 book per subject\n• Return books on or before due date\n• Late returns incur fines\n• Subject-wise restriction applies\n\n**Library Card:**\n• Required for all transactions\n• Report lost cards immediately\n• Keep card safe\n• Request replacement if lost\n\n**Behavior:**\n• Maintain silence in library\n• Handle books with care\n• No food or drinks\n• Follow staff instructions\n• Respect library property\n\n**Fines:**\n• Calculated per day for overdue books\n• Fine rate depends on issue duration\n• Pay fines online or at library counter\n• Unpaid fines restrict new issues\n• Check fine history regularly\n\n**Reservations:**\n• Reserve unavailable books\n• Collect within 3 days when notified\n• Maximum 2 active reservations\n• First come, first served\n• Reservation expires in 7 days\n\n**E-Resources:**\n• Access with student credentials\n• Available 24/7 online\n• Contact staff for assistance\n• Download/view as needed\n\n**Book Requests:**\n• Request available books\n• Wait for approval\n• Collect within deadline\n• Check request status regularly\n\n**General:**\n• Follow all library rules\n• Respect staff and other students\n• Keep library clean\n• Report any issues immediately",
            'type' => 'text',
        ];
    }

    private function handlePaymentGuide($message, $user)
    {
        $response = "💳 **Online Payment Guide**\n\n";
        $response .= "**Payment Gateway:** Razorpay\n\n";
        $response .= "**What You Can Pay Online:**\n";
        $response .= "• Overdue fines\n";
        $response .= "• Library membership fees\n";
        $response .= "• Paid e-resources\n\n";
        $response .= "**How to Pay:**\n";
        $response .= "1. Go to Fines section\n";
        $response .= "2. Click 'Pay Online' on any pending fine\n";
        $response .= "3. Enter payment details\n";
        $response .= "4. Complete payment via Razorpay\n";
        $response .= "5. Payment confirmation will be sent\n\n";
        $response .= "**Payment Methods:**\n";
        $response .= "• Credit/Debit Cards\n";
        $response .= "• Net Banking\n";
        $response .= "• UPI\n";
        $response .= "• Wallets\n\n";
        
        // Get user's pending fines
        $pendingFines = Fine::where('user_id', $user->id)
            ->where('status', 'pending')
            ->with('borrow.book')
            ->get();
        
        if ($pendingFines->isNotEmpty()) {
            $totalPending = $pendingFines->sum(function($fine) {
                return $fine->remaining_amount ?? $fine->amount;
            });
            $response .= "**Your Pending Fines:**\n";
            $response .= "• Total Amount: ₹" . number_format($totalPending, 2) . "\n";
            $response .= "• Number of Fines: " . $pendingFines->count() . "\n";
            $response .= "• Pay online from Fines section\n\n";
        }
        
        $response .= "**Payment History:**\n";
        $response .= "• View all payments in Payments section\n";
        $response .= "• Download receipts\n";
        $response .= "• Track payment status\n\n";
        $response .= "**Need Help?**\n";
        $response .= "Contact library staff for payment assistance.";

        return [
            'message' => $response,
            'type' => 'text',
        ];
    }

    private function handleLMSRecommendations($message, $user)
    {
        $response = "🎓 **LMS Course-Specific Book Recommendations**\n\n";
        
        if ($user->course) {
            $response .= "**Your Course Details:**\n";
            $response .= "• Course: " . $user->course . "\n";
            if ($user->semester) {
                $response .= "• Semester: " . $user->semester . "\n";
            }
            if ($user->year) {
                $response .= "• Year: " . $user->year . "\n";
            }
            if ($user->batch) {
                $response .= "• Batch: " . $user->batch . "\n";
            }
            $response .= "\n";
        }
        
        $response .= "**How It Works:**\n";
        $response .= "• Books are automatically matched to your course\n";
        $response .= "• Recommendations based on LMS course data\n";
        $response .= "• Filtered by semester, year, and batch\n";
        $response .= "• Updated in real-time\n\n";
        
        // Get course-specific recommendations
        $recommendedBooks = \App\Models\LMSCourse::getCourseRecommendations($user);
        
        if ($recommendedBooks->isNotEmpty()) {
            $response .= "**Recommended Books for Your Course:**\n\n";
            $count = 0;
            foreach ($recommendedBooks->take(10) as $book) {
                $count++;
                $response .= "{$count}. **{$book->title}**\n";
                if ($book->author) {
                    $response .= "   Author: {$book->author->name}\n";
                }
                $response .= "   Available: {$book->available_copies} copies\n";
                if ($book->isbn) {
                    $response .= "   ISBN: {$book->isbn}\n";
                }
                $response .= "\n";
            }
            
            if ($recommendedBooks->count() > 10) {
                $response .= "... and " . ($recommendedBooks->count() - 10) . " more books\n\n";
            }
        } else {
            $response .= "**No specific recommendations found.**\n";
            $response .= "Browse Books section to see all available books.\n\n";
        }
        
        $response .= "**Access Recommendations:**\n";
        $response .= "• Visit LMS Recommendations section\n";
        $response .= "• Browse course-specific books\n";
        $response .= "• Request books directly\n";
        $response .= "• Get personalized suggestions\n\n";
        $response .= "**Features:**\n";
        $response .= "• Real-time availability\n";
        $response .= "• Course-based filtering\n";
        $response .= "• Subject-wise organization\n";
        $response .= "• Easy book requests\n";

        return [
            'message' => $response,
            'type' => 'text',
        ];
    }
}


