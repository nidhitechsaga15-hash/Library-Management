<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\Book;
use App\Models\Borrow;
use App\Models\BookReservation;
use App\Models\Fine;
use App\Models\BookRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ChatbotController extends Controller
{
    public function index()
    {
        return view('staff.chatbot.index');
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
        // Issue/Return process guidance
        if ($this->isIssueReturnQuery($message)) {
            return $this->handleIssueReturnGuide($message, $user);
        }

        // Fine calculation questions
        if ($this->isFineCalculationQuery($message)) {
            return $this->handleFineCalculation($message, $user);
        }

        // Reservation approvals workflow
        if ($this->isReservationQuery($message)) {
            return $this->handleReservationWorkflow($message, $user);
        }

        // Digital library / e-resource queries
        if ($this->isEResourceQuery($message)) {
            return $this->handleEResourceGuide($message, $user);
        }

        // General FAQs
        if ($this->isFAQQuery($message)) {
            return $this->handleFAQ($message, $user);
        }

        // Default response
        return [
            'message' => "I'm here to help you with library management tasks! You can ask me about:\n\n📖 Issue/Return process\n💰 Fine calculations\n📋 Reservation approvals workflow\n💻 Digital library / e-resources\n❓ FAQs for day-to-day tasks\n\nHow can I assist you today?",
            'type' => 'text',
        ];
    }

    private function isIssueReturnQuery($message)
    {
        $keywords = ['issue', 'return', 'borrow', 'lend', 'give book', 'take book', 'issue process', 'return process', 'how to issue', 'how to return'];
        return $this->containsKeywords($message, $keywords);
    }

    private function isFineCalculationQuery($message)
    {
        $keywords = ['fine', 'calculate', 'penalty', 'overdue', 'late fee', 'fine amount', 'how much fine', 'fine calculation'];
        return $this->containsKeywords($message, $keywords);
    }

    private function isReservationQuery($message)
    {
        $keywords = ['reservation', 'reserve', 'approve reservation', 'reservation approval', 'pending reservation', 'reservation workflow'];
        return $this->containsKeywords($message, $keywords);
    }

    private function isEResourceQuery($message)
    {
        $keywords = ['e-resource', 'e resource', 'electronic resource', 'online resource', 'digital library', 'ebook', 'e-book', 'digital'];
        return $this->containsKeywords($message, $keywords);
    }

    private function isFAQQuery($message)
    {
        $keywords = ['faq', 'help', 'how to', 'what is', 'guide', 'process', 'workflow', 'task', 'daily', 'routine'];
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

    private function handleIssueReturnGuide($message, $user)
    {
        $isReturn = strpos($message, 'return') !== false;
        
        if ($isReturn) {
            $response = "📖 **Book Return Process Guide**\n\n";
            $response .= "**Steps to Return a Book:**\n\n";
            $response .= "1. Go to Borrows section in the menu\n";
            $response .= "2. Find the book record you want to return\n";
            $response .= "3. Click on 'Return' button\n";
            $response .= "4. System will automatically:\n";
            $response .= "   • Calculate fine if overdue\n";
            $response .= "   • Update book availability\n";
            $response .= "   • Check for pending reservations\n";
            $response .= "   • Notify student if reserved\n\n";
            $response .= "**Important Points:**\n";
            $response .= "• Check due date before returning\n";
            $response .= "• System calculates fine automatically\n";
            $response .= "• If book is overdue, fine will be created\n";
            $response .= "• First pending reservation gets notified\n\n";
            $response .= "**Fine Calculation:**\n";
            $response .= "• Fine = Days Overdue × Fine Per Day\n";
            $response .= "• Fine per day varies by issue duration\n";
            $response .= "• 15 days: ₹5/day\n";
            $response .= "• 30 days: ₹10/day\n";
            $response .= "• 60 days: ₹15/day\n";
            
            return [
                'message' => $response,
                'type' => 'text',
            ];
        } else {
            $response = "📖 **Book Issue Process Guide**\n\n";
            $response .= "**Steps to Issue a Book:**\n\n";
            $response .= "1. Go to Borrows section in the menu\n";
            $response .= "2. Click 'Issue New Book' button\n";
            $response .= "3. Select student from the list\n";
            $response .= "4. Select book to issue\n";
            $response .= "5. Choose issue duration (15/30/60 days)\n";
            $response .= "6. System will automatically:\n";
            $response .= "   • Check book availability\n";
            $response .= "   • Verify student's book limit\n";
            $response .= "   • Set due date\n";
            $response .= "   • Deduct available copies\n";
            $response .= "   • Notify student\n\n";
            $response .= "**Important Checks:**\n";
            $response .= "• Student can borrow max 2 books\n";
            $response .= "• Only 1 book per subject allowed\n";
            $response .= "• Book must be available\n";
            $response .= "• Check student's active borrows\n\n";
            $response .= "**Issue Durations:**\n";
            $response .= "• 15 days - Standard issue\n";
            $response .= "• 30 days - Extended issue\n";
            $response .= "• 60 days - Long-term issue\n";
            
            return [
                'message' => $response,
                'type' => 'text',
            ];
        }
    }

    private function handleFineCalculation($message, $user)
    {
        $response = "💰 **Fine Calculation Guide**\n\n";
        $response .= "**Fine Calculation Formula:**\n";
        $response .= "Fine Amount = Days Overdue × Fine Per Day\n\n";
        
        $response .= "**Fine Rates by Issue Duration:**\n\n";
        $response .= "**15 Days Issue:**\n";
        $response .= "• Fine per day: ₹5\n";
        $response .= "• Example: 3 days overdue = ₹15\n\n";
        
        $response .= "**30 Days Issue:**\n";
        $response .= "• Fine per day: ₹10\n";
        $response .= "• Example: 3 days overdue = ₹30\n\n";
        
        $response .= "**60 Days Issue:**\n";
        $response .= "• Fine per day: ₹15\n";
        $response .= "• Example: 3 days overdue = ₹45\n\n";
        
        $response .= "**How System Calculates:**\n";
        $response .= "1. System checks due date\n";
        $response .= "2. Calculates days overdue\n";
        $response .= "3. Uses fine_per_day from borrow record\n";
        $response .= "4. Multiplies: days × fine_per_day\n";
        $response .= "5. Creates fine record automatically\n\n";
        
        $response .= "**Fine Status:**\n";
        $response .= "• Pending - Not paid yet\n";
        $response .= "• Paid - Payment received\n";
        $response .= "• Unpaid fines restrict new issues\n\n";
        
        $response .= "**Managing Fines:**\n";
        $response .= "• View all fines in Fines section\n";
        $response .= "• Update status when payment received\n";
        $response .= "• System tracks fine history\n";
        
        return [
            'message' => $response,
            'type' => 'text',
        ];
    }

    private function handleReservationWorkflow($message, $user)
    {
        $pendingReservations = BookReservation::where('status', 'pending')
            ->with('book.author', 'user')
            ->count();
        
        $availableReservations = BookReservation::where('status', 'available')
            ->with('book.author', 'user')
            ->count();
        
        $response = "📋 **Reservation Approval Workflow**\n\n";
        $response .= "**Current Status:**\n";
        $response .= "• Pending Reservations: {$pendingReservations}\n";
        $response .= "• Available (Ready to Collect): {$availableReservations}\n\n";
        
        $response .= "**Reservation Workflow:**\n\n";
        $response .= "**1. Student Reserves Book:**\n";
        $response .= "• Student reserves unavailable book\n";
        $response .= "• Status: Pending\n";
        $response .= "• Reservation expires in 7 days\n\n";
        
        $response .= "**2. Book Becomes Available:**\n";
        $response .= "• When book is returned\n";
        $response .= "• System checks pending reservations\n";
        $response .= "• First in queue gets notified\n";
        $response .= "• Status changes to 'Available'\n\n";
        
        $response .= "**3. Student Collects Book:**\n";
        $response .= "• Student has 3 days to collect\n";
        $response .= "• Issue book through normal process\n";
        $response .= "• Reservation auto-cancels after issue\n\n";
        
        $response .= "**4. If Not Collected:**\n";
        $response .= "• After 3 days, next in queue notified\n";
        $response .= "• Reservation expires if not collected\n\n";
        
        $response .= "**Managing Reservations:**\n";
        $response .= "• View all in Reservations section\n";
        $response .= "• Filter by status (pending/available)\n";
        $response .= "• System handles notifications automatically\n";
        $response .= "• No manual approval needed\n";
        
        return [
            'message' => $response,
            'type' => 'text',
            'data' => [
                'pending_count' => $pendingReservations,
                'available_count' => $availableReservations,
            ],
        ];
    }

    private function handleEResourceGuide($message, $user)
    {
        $response = "💻 **Digital Library / E-Resource Guide**\n\n";
        $response .= "**Available E-Resources:**\n";
        $response .= "• Digital Library Portal\n";
        $response .= "• Online Journals & Databases\n";
        $response .= "• E-Books Collection\n";
        $response .= "• Research Papers & Articles\n";
        $response .= "• Academic Databases\n\n";
        
        $response .= "**For Students:**\n";
        $response .= "• Access via library website\n";
        $response .= "• Login with student credentials\n";
        $response .= "• Available 24/7 online\n";
        $response .= "• Download/View as needed\n\n";
        
        $response .= "**Staff Assistance:**\n";
        $response .= "• Help students with access issues\n";
        $response .= "• Guide on resource navigation\n";
        $response .= "• Troubleshoot technical problems\n";
        $response .= "• Provide resource recommendations\n\n";
        
        $response .= "**Common Issues:**\n";
        $response .= "• Login problems - Check credentials\n";
        $response .= "• Access denied - Verify membership\n";
        $response .= "• Download issues - Check internet\n";
        $response .= "• Resource not found - Check availability\n\n";
        
        $response .= "**Best Practices:**\n";
        $response .= "• Keep resource links updated\n";
        $response .= "• Guide students to relevant resources\n";
        $response .= "• Report technical issues promptly\n";
        
        return [
            'message' => $response,
            'type' => 'text',
        ];
    }

    private function handleFAQ($message, $user)
    {
        // Daily tasks
        if (strpos($message, 'daily') !== false || strpos($message, 'routine') !== false || strpos($message, 'task') !== false) {
            return [
                'message' => "📋 **Daily Tasks Guide**\n\n**Morning Routine:**\n1. Check pending book requests\n2. Review overdue books\n3. Process new issues\n4. Check reservations\n\n**During Day:**\n• Issue books to students\n• Process returns\n• Calculate and record fines\n• Help students with queries\n• Manage reservations\n\n**End of Day:**\n• Review all transactions\n• Check pending requests\n• Update fine statuses\n• Prepare next day's tasks\n\n**Quick Access:**\n• Borrows - Issue/Return books\n• Fines - Manage fine payments\n• Reservations - View reservations\n• Book Requests - Approve requests",
                'type' => 'text',
            ];
        }
        
        // Book requests
        if (strpos($message, 'request') !== false || strpos($message, 'approve') !== false) {
            $pendingRequests = BookRequest::where('status', 'pending')->count();
            $holdRequests = BookRequest::where('status', 'hold')->count();
            
            return [
                'message' => "📚 **Book Request Management**\n\n**Current Status:**\n• Pending Requests: {$pendingRequests}\n• On Hold: {$holdRequests}\n\n**Request Workflow:**\n1. Student requests book\n2. If available → Status: Hold\n3. If not available → Status: Pending\n4. Staff approves request\n5. Stock deducted on approval\n6. Student notified\n7. Student collects book\n\n**Approval Process:**\n• Go to Book Requests section\n• Review request details\n• Check book availability\n• Approve or reject\n• System handles notifications\n\n**Hold Requests:**\n• Book is available\n• Waiting for approval\n• Stock not deducted yet\n• Approve to reserve stock",
                'type' => 'text',
                'data' => [
                    'pending_requests' => $pendingRequests,
                    'hold_requests' => $holdRequests,
                ],
            ];
        }
        
        // General FAQs
        return [
            'message' => "❓ **Frequently Asked Questions**\n\n**Q: How to issue a book?**\nA: Go to Borrows → Issue New Book → Select student → Select book → Choose duration → Issue\n\n**Q: How to calculate fine?**\nA: System calculates automatically. Fine = Days Overdue × Fine Per Day (based on issue duration)\n\n**Q: What to do with reservations?**\nA: System handles automatically. When book returns, first reservation gets notified. No manual action needed.\n\n**Q: How to process returns?**\nA: Go to Borrows → Find record → Click Return → System calculates fine if overdue → Updates availability\n\n**Q: Student exceeded book limit?**\nA: Students can borrow max 2 books. Check active borrows before issuing new book.\n\n**Q: Book not available?**\nA: Check available_copies. If 0, book is unavailable. Student can reserve it.\n\n**Q: How to update fine status?**\nA: Go to Fines section → Find fine → Update status to 'Paid' when payment received\n\n**Q: Multiple reservations for same book?**\nA: System queues them. First reservation gets priority when book becomes available.",
            'type' => 'text',
        ];
    }
}

