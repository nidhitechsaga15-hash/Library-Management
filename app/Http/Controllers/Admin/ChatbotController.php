<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Book;
use App\Models\Borrow;
use App\Models\BookReservation;
use App\Models\Fine;
use App\Models\BookRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ChatbotController extends Controller
{
    public function index()
    {
        return view('admin.chatbot.index');
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
        // AI Analytics - Popular books prediction
        if ($this->isPopularBooksQuery($message)) {
            return $this->handlePopularBooksPrediction($message, $user);
        }

        // AI Analytics - Inventory forecasting
        if ($this->isInventoryForecastQuery($message)) {
            return $this->handleInventoryForecast($message, $user);
        }

        // System analytics and reports
        if ($this->isAnalyticsQuery($message)) {
            return $this->handleAnalyticsGuide($message, $user);
        }

        // Inventory management
        if ($this->isInventoryQuery($message)) {
            return $this->handleInventoryManagement($message, $user);
        }

        // Fine rules or member policy
        if ($this->isPolicyQuery($message)) {
            return $this->handlePolicyQueries($message, $user);
        }

        // E-resource management
        if ($this->isEResourceQuery($message)) {
            return $this->handleEResourceManagement($message, $user);
        }

        // Admin operations FAQs
        if ($this->isAdminFAQQuery($message)) {
            return $this->handleAdminFAQs($message, $user);
        }

        // Default response
        return [
            'message' => "I'm here to help you with admin operations! You can ask me about:\n\n🤖 **AI Analytics:**\n• Predict popular books\n• Forecast inventory needs\n\n📊 System analytics and reports\n📦 Inventory management\n📋 Fine rules and member policies\n💻 E-resource management\n❓ Admin operations FAQs (audit logs, user management, etc.)\n\nHow can I assist you today?",
            'type' => 'text',
        ];
    }

    private function isPopularBooksQuery($message)
    {
        $keywords = ['popular', 'popular books', 'predict popular', 'trending', 'most borrowed', 'top books', 'best books', 'demand', 'in demand'];
        return $this->containsKeywords($message, $keywords);
    }

    private function isInventoryForecastQuery($message)
    {
        $keywords = ['forecast', 'predict inventory', 'inventory forecast', 'stock forecast', 'inventory needs', 'stock needs', 'predict stock', 'future inventory'];
        return $this->containsKeywords($message, $keywords);
    }

    private function isAnalyticsQuery($message)
    {
        $keywords = ['analytics', 'report', 'statistics', 'stats', 'dashboard', 'data', 'insight', 'overview', 'summary'];
        return $this->containsKeywords($message, $keywords);
    }

    private function isInventoryQuery($message)
    {
        $keywords = ['inventory', 'stock', 'book management', 'add book', 'update book', 'book status', 'available copies', 'total copies'];
        return $this->containsKeywords($message, $keywords);
    }

    private function isPolicyQuery($message)
    {
        $keywords = ['policy', 'rule', 'fine rule', 'member policy', 'regulation', 'guideline', 'fine rate', 'borrowing limit'];
        return $this->containsKeywords($message, $keywords);
    }

    private function isEResourceQuery($message)
    {
        $keywords = ['e-resource', 'e resource', 'electronic resource', 'digital library', 'ebook management', 'digital resource'];
        return $this->containsKeywords($message, $keywords);
    }

    private function isAdminFAQQuery($message)
    {
        $keywords = ['audit', 'log', 'user management', 'admin', 'operation', 'manage user', 'delete', 'edit', 'permission', 'role'];
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

    private function handlePopularBooksPrediction($message, $user)
    {
        // Get books with their borrowing statistics
        $books = Book::withCount([
            'borrows' => function($query) {
                $query->where('created_at', '>=', now()->subMonths(6));
            },
            'reservations' => function($query) {
                $query->where('created_at', '>=', now()->subMonths(6));
            },
            'requests' => function($query) {
                $query->where('created_at', '>=', now()->subMonths(6))
                      ->whereIn('status', ['pending', 'hold', 'approved']);
            }
        ])
        ->with('author', 'category')
        ->get()
        ->map(function($book) {
            // Calculate popularity score
            $borrowScore = $book->borrows_count * 3; // Borrows are most important
            $reservationScore = $book->reservations_count * 2; // Reservations show demand
            $requestScore = $book->requests_count * 1.5; // Requests show interest
            
            // Availability factor (low availability = high demand)
            $availabilityFactor = $book->available_copies > 0 
                ? (1 / max($book->available_copies, 1)) * 2 
                : 3; // Unavailable books are in high demand
            
            $popularityScore = $borrowScore + $reservationScore + $requestScore + $availabilityFactor;
            
            return [
                'book' => $book,
                'popularity_score' => $popularityScore,
                'borrows_count' => $book->borrows_count,
                'reservations_count' => $book->reservations_count,
                'requests_count' => $book->requests_count,
            ];
        })
        ->sortByDesc('popularity_score')
        ->take(10);
        
        $response = "🤖 **AI Prediction: Popular Books**\n\n";
        $response .= "**Analysis Period:** Last 6 months\n";
        $response .= "**Prediction Based On:**\n";
        $response .= "• Borrowing frequency (weight: 3x)\n";
        $response .= "• Reservation count (weight: 2x)\n";
        $response .= "• Request count (weight: 1.5x)\n";
        $response .= "• Availability factor (low stock = high demand)\n\n";
        
        $response .= "**📚 Top 10 Predicted Popular Books:**\n\n";
        
        $rank = 1;
        foreach ($books as $item) {
            $book = $item['book'];
            $score = round($item['popularity_score'], 2);
            $available = $book->isAvailable();
            $status = $available ? '✅ Available' : '❌ Unavailable';
            
            $response .= "**{$rank}. {$book->title}**\n";
            if ($book->author) {
                $response .= "Author: {$book->author->name}\n";
            }
            $response .= "Popularity Score: {$score}\n";
            $response .= "Borrows (6M): {$item['borrows_count']}\n";
            $response .= "Reservations (6M): {$item['reservations_count']}\n";
            $response .= "Requests (6M): {$item['requests_count']}\n";
            $response .= "Status: {$status}\n";
            $response .= "Available: {$book->available_copies} / {$book->total_copies}\n";
            
            // Prediction
            if ($item['popularity_score'] > 20) {
                $response .= "🔮 **Prediction:** Very High Demand - Consider increasing stock\n";
            } elseif ($item['popularity_score'] > 10) {
                $response .= "🔮 **Prediction:** High Demand - Monitor stock levels\n";
            } else {
                $response .= "🔮 **Prediction:** Moderate Demand\n";
            }
            $response .= "\n";
            
            $rank++;
        }
        
        $response .= "**💡 Recommendations:**\n";
        $response .= "• Books with high popularity scores may need more copies\n";
        $response .= "• Monitor unavailable books - they're in high demand\n";
        $response .= "• Consider purchasing popular books in advance\n";
        $response .= "• Track trends monthly for better predictions\n";
        
        return [
            'message' => $response,
            'type' => 'popular_books',
            'data' => [
                'popular_books' => $books->map(function($item) {
                    return [
                        'book_id' => $item['book']->id,
                        'title' => $item['book']->title,
                        'author' => $item['book']->author->name ?? 'N/A',
                        'popularity_score' => round($item['popularity_score'], 2),
                        'borrows_count' => $item['borrows_count'],
                        'reservations_count' => $item['reservations_count'],
                        'requests_count' => $item['requests_count'],
                        'available_copies' => $item['book']->available_copies,
                        'total_copies' => $item['book']->total_copies,
                    ];
                })->values(),
            ],
        ];
    }

    private function handleInventoryForecast($message, $user)
    {
        // Analyze borrowing trends for forecasting
        $last30Days = now()->subDays(30);
        $last60Days = now()->subDays(60);
        $last90Days = now()->subDays(90);
        
        // Get books with borrowing trends
        $books = Book::with(['author', 'category'])
            ->withCount([
                'borrows as borrows_30d' => function($query) use ($last30Days) {
                    $query->where('borrow_date', '>=', $last30Days);
                },
                'borrows as borrows_60d' => function($query) use ($last60Days) {
                    $query->where('borrow_date', '>=', $last60Days);
                },
                'borrows as borrows_90d' => function($query) use ($last90Days) {
                    $query->where('borrow_date', '>=', $last90Days);
                },
                'reservations as reservations_30d' => function($query) use ($last30Days) {
                    $query->where('reserved_at', '>=', $last30Days);
                },
                'requests as requests_30d' => function($query) use ($last30Days) {
                    $query->where('created_at', '>=', $last30Days)
                          ->whereIn('status', ['pending', 'hold']);
                }
            ])
            ->get()
            ->map(function($book) {
                // Calculate demand trend
                $borrows30d = $book->borrows_30d;
                $borrows60d = $book->borrows_60d;
                $borrows90d = $book->borrows_90d;
                
                // Calculate average borrows per month
                $avgMonthlyBorrows = $borrows90d > 0 ? ($borrows90d / 3) : 0;
                
                // Calculate demand growth rate
                $growthRate = 0;
                if ($borrows60d > 0) {
                    $growthRate = (($borrows30d - ($borrows60d - $borrows30d)) / max($borrows60d - $borrows30d, 1)) * 100;
                }
                
                // Forecast next month demand
                $forecastedDemand = $avgMonthlyBorrows * (1 + ($growthRate / 100));
                
                // Calculate recommended stock
                $currentStock = $book->total_copies;
                $availableStock = $book->available_copies;
                $utilizationRate = $currentStock > 0 ? (($currentStock - $availableStock) / $currentStock) * 100 : 0;
                
                // Recommended additional copies
                $recommendedCopies = 0;
                if ($forecastedDemand > $currentStock * 0.8) {
                    $recommendedCopies = max(ceil($forecastedDemand - $currentStock), 1);
                }
                
                // Risk level
                $riskLevel = 'low';
                if ($availableStock == 0 && $forecastedDemand > 0) {
                    $riskLevel = 'critical';
                } elseif ($availableStock <= 1 && $forecastedDemand > 2) {
                    $riskLevel = 'high';
                } elseif ($availableStock <= 2 && $forecastedDemand > 3) {
                    $riskLevel = 'medium';
                }
                
                return [
                    'book' => $book,
                    'forecasted_demand' => round($forecastedDemand, 1),
                    'avg_monthly_borrows' => round($avgMonthlyBorrows, 1),
                    'growth_rate' => round($growthRate, 1),
                    'current_stock' => $currentStock,
                    'available_stock' => $availableStock,
                    'utilization_rate' => round($utilizationRate, 1),
                    'recommended_copies' => $recommendedCopies,
                    'risk_level' => $riskLevel,
                    'reservations_30d' => $book->reservations_30d,
                    'requests_30d' => $book->requests_30d,
                ];
            })
            ->filter(function($item) {
                // Filter books that need attention
                return $item['forecasted_demand'] > 0 || 
                       $item['risk_level'] != 'low' || 
                       $item['available_stock'] == 0;
            })
            ->sortByDesc('forecasted_demand')
            ->take(15);
        
        $criticalBooks = $books->where('risk_level', 'critical')->count();
        $highRiskBooks = $books->where('risk_level', 'high')->count();
        $totalRecommended = $books->sum('recommended_copies');
        
        $response = "🤖 **AI Forecast: Inventory Needs**\n\n";
        $response .= "**Forecast Period:** Next 30 days\n";
        $response .= "**Analysis Based On:**\n";
        $response .= "• Last 30/60/90 days borrowing trends\n";
        $response .= "• Current reservations and requests\n";
        $response .= "• Stock utilization rates\n";
        $response .= "• Demand growth patterns\n\n";
        
        $response .= "**📊 Summary:**\n";
        $response .= "• Critical Risk Books: {$criticalBooks}\n";
        $response .= "• High Risk Books: {$highRiskBooks}\n";
        $response .= "• Recommended Additional Copies: {$totalRecommended}\n\n";
        
        $response .= "**📚 Books Requiring Attention:**\n\n";
        
        $rank = 1;
        foreach ($books as $item) {
            $book = $item['book'];
            $risk = strtoupper($item['risk_level']);
            $riskEmoji = match($item['risk_level']) {
                'critical' => '🔴',
                'high' => '🟠',
                'medium' => '🟡',
                default => '🟢',
            };
            
            $response .= "{$riskEmoji} **{$rank}. {$book->title}**\n";
            if ($book->author) {
                $response .= "Author: {$book->author->name}\n";
            }
            $response .= "Risk Level: {$risk}\n";
            $response .= "Current Stock: {$item['current_stock']} copies\n";
            $response .= "Available: {$item['available_stock']} copies\n";
            $response .= "Utilization: {$item['utilization_rate']}%\n";
            $response .= "Forecasted Demand (Next Month): {$item['forecasted_demand']} borrows\n";
            $response .= "Growth Rate: {$item['growth_rate']}%\n";
            $response .= "Active Reservations: {$item['reservations_30d']}\n";
            $response .= "Pending Requests: {$item['requests_30d']}\n";
            
            if ($item['recommended_copies'] > 0) {
                $response .= "💡 **Recommendation:** Add {$item['recommended_copies']} more copies\n";
            } elseif ($item['risk_level'] == 'critical') {
                $response .= "⚠️ **Urgent:** Book is unavailable with high demand!\n";
            }
            $response .= "\n";
            
            $rank++;
        }
        
        $response .= "**💡 Forecasting Insights:**\n";
        $response .= "• Books with high growth rate need more stock\n";
        $response .= "• Critical risk = Unavailable + High demand\n";
        $response .= "• Monitor utilization rates above 80%\n";
        $response .= "• Consider seasonal patterns (exam periods)\n";
        $response .= "• Review forecasts monthly for accuracy\n";
        
        return [
            'message' => $response,
            'type' => 'inventory_forecast',
            'data' => [
                'critical_books' => $criticalBooks,
                'high_risk_books' => $highRiskBooks,
                'total_recommended' => $totalRecommended,
                'forecasts' => $books->map(function($item) {
                    return [
                        'book_id' => $item['book']->id,
                        'title' => $item['book']->title,
                        'risk_level' => $item['risk_level'],
                        'forecasted_demand' => $item['forecasted_demand'],
                        'recommended_copies' => $item['recommended_copies'],
                        'current_stock' => $item['current_stock'],
                        'available_stock' => $item['available_stock'],
                    ];
                })->values(),
            ],
        ];
    }

    private function handleAnalyticsGuide($message, $user)
    {
        // Get real-time statistics
        $totalBooks = Book::count();
        $totalStudents = User::where('role', 'student')->count();
        $totalStaff = User::where('role', 'staff')->count();
        $activeBorrows = Borrow::where('status', 'borrowed')->count();
        $overdueBorrows = Borrow::where('status', 'borrowed')
            ->where('due_date', '<', now())
            ->count();
        $pendingFines = Fine::where('status', 'pending')->sum('amount');
        $totalFineAmount = Fine::sum('amount');
        
        $response = "📊 **System Analytics & Reports Guide**\n\n";
        $response .= "**Current System Statistics:**\n\n";
        $response .= "📚 **Books:**\n";
        $response .= "• Total Books: {$totalBooks}\n";
        $response .= "• Active Borrows: {$activeBorrows}\n";
        $response .= "• Overdue Books: {$overdueBorrows}\n\n";
        
        $response .= "👥 **Users:**\n";
        $response .= "• Total Students: {$totalStudents}\n";
        $response .= "• Total Staff: {$totalStaff}\n\n";
        
        $response .= "💰 **Fines:**\n";
        $response .= "• Pending Fines: ₹{$pendingFines}\n";
        $response .= "• Total Fine Amount: ₹{$totalFineAmount}\n\n";
        
        $response .= "**Available Reports:**\n\n";
        $response .= "1. **Dashboard Overview**\n";
        $response .= "   • View all key metrics\n";
        $response .= "   • Quick statistics\n";
        $response .= "   • Recent activities\n\n";
        
        $response .= "2. **Book Reports**\n";
        $response .= "   • Book-wise reports\n";
        $response .= "   • Category-wise analysis\n";
        $response .= "   • Popular books\n";
        $response .= "   • Low stock alerts\n\n";
        
        $response .= "3. **Student Reports**\n";
        $response .= "   • Student-wise borrowing\n";
        $response .= "   • Issue history\n";
        $response .= "   • Fine history\n";
        $response .= "   • Activity reports\n\n";
        
        $response .= "4. **Transaction Reports**\n";
        $response .= "   • Daily transactions\n";
        $response .= "   • Monthly summaries\n";
        $response .= "   • Fine collection reports\n\n";
        
        $response .= "**How to Access Reports:**\n";
        $response .= "• Go to Reports section in menu\n";
        $response .= "• Select report type\n";
        $response .= "• Apply filters if needed\n";
        $response .= "• Export or print reports\n";
        
        return [
            'message' => $response,
            'type' => 'analytics',
            'data' => [
                'total_books' => $totalBooks,
                'total_students' => $totalStudents,
                'active_borrows' => $activeBorrows,
                'overdue_borrows' => $overdueBorrows,
                'pending_fines' => $pendingFines,
            ],
        ];
    }

    private function handleInventoryManagement($message, $user)
    {
        $totalBooks = Book::count();
        $availableBooks = Book::where('available_copies', '>', 0)->count();
        $unavailableBooks = Book::where('available_copies', 0)->count();
        $lowStockBooks = Book::where('available_copies', '<=', 2)
            ->where('available_copies', '>', 0)
            ->count();
        
        $response = "📦 **Inventory Management Guide**\n\n";
        $response .= "**Current Inventory Status:**\n\n";
        $response .= "• Total Books: {$totalBooks}\n";
        $response .= "• Available Books: {$availableBooks}\n";
        $response .= "• Unavailable Books: {$unavailableBooks}\n";
        $response .= "• Low Stock Books: {$lowStockBooks}\n\n";
        
        $response .= "**Managing Books:**\n\n";
        $response .= "**1. Adding New Books:**\n";
        $response .= "• Go to Books section\n";
        $response .= "• Click 'Add New Book'\n";
        $response .= "• Fill in book details:\n";
        $response .= "  - Title, ISBN, Author\n";
        $response .= "  - Category, Publisher\n";
        $response .= "  - Total copies\n";
        $response .= "  - Course, Semester, Year\n";
        $response .= "  - Rack number, Location\n";
        $response .= "• Save book\n\n";
        
        $response .= "**2. Updating Book Information:**\n";
        $response .= "• Find book in Books section\n";
        $response .= "• Click 'Edit' button\n";
        $response .= "• Update required fields\n";
        $response .= "• Update available copies if needed\n";
        $response .= "• Save changes\n\n";
        
        $response .= "**3. Managing Stock:**\n";
        $response .= "• Check available_copies field\n";
        $response .= "• Update when books are added/removed\n";
        $response .= "• System auto-updates on issue/return\n";
        $response .= "• Monitor low stock alerts\n\n";
        
        $response .= "**4. Book Status:**\n";
        $response .= "• Available - Can be issued\n";
        $response .= "• Unavailable - All copies issued\n";
        $response .= "• Check condition_status for book condition\n\n";
        
        $response .= "**Best Practices:**\n";
        $response .= "• Keep inventory updated regularly\n";
        $response .= "• Monitor low stock books\n";
        $response .= "• Update book locations accurately\n";
        $response .= "• Maintain proper categorization\n";
        
        return [
            'message' => $response,
            'type' => 'inventory',
            'data' => [
                'total_books' => $totalBooks,
                'available_books' => $availableBooks,
                'unavailable_books' => $unavailableBooks,
                'low_stock' => $lowStockBooks,
            ],
        ];
    }

    private function handlePolicyQueries($message, $user)
    {
        $response = "📋 **Fine Rules & Member Policies**\n\n";
        
        $response .= "**Fine Rules:**\n\n";
        $response .= "**Fine Calculation:**\n";
        $response .= "Fine = Days Overdue × Fine Per Day\n\n";
        
        $response .= "**Fine Rates by Issue Duration:**\n";
        $response .= "• 15 Days Issue: ₹5 per day\n";
        $response .= "• 30 Days Issue: ₹10 per day\n";
        $response .= "• 60 Days Issue: ₹15 per day\n\n";
        
        $response .= "**Fine Status:**\n";
        $response .= "• Pending - Not paid\n";
        $response .= "• Paid - Payment received\n";
        $response .= "• Unpaid fines restrict new issues\n\n";
        
        $response .= "**Member Policies:**\n\n";
        $response .= "**Student Borrowing Limits:**\n";
        $response .= "• Maximum 2 books at a time\n";
        $response .= "• Only 1 book per subject\n";
        $response .= "• Must return before borrowing new\n";
        $response .= "• Unpaid fines block new issues\n\n";
        
        $response .= "**Issue Durations:**\n";
        $response .= "• Standard: 15 days\n";
        $response .= "• Extended: 30 days\n";
        $response .= "• Long-term: 60 days\n";
        $response .= "• Can be extended if needed\n\n";
        
        $response .= "**Reservation Policy:**\n";
        $response .= "• Can reserve unavailable books\n";
        $response .= "• Reservation expires in 7 days\n";
        $response .= "• 3 days to collect when available\n";
        $response .= "• Maximum 2 active reservations\n\n";
        
        $response .= "**Library Card Policy:**\n";
        $response .= "• Required for all transactions\n";
        $response .= "• Report lost cards immediately\n";
        $response .= "• Card can be blocked/unblocked\n";
        $response .= "• Replacement available\n\n";
        
        $response .= "**Updating Policies:**\n";
        $response .= "• Policies are system-wide\n";
        $response .= "• Changes affect all users\n";
        $response .= "• Fine rates stored per borrow\n";
        $response .= "• Review policies regularly\n";
        
        return [
            'message' => $response,
            'type' => 'text',
        ];
    }

    private function handleEResourceManagement($message, $user)
    {
        $response = "💻 **E-Resource Management Guide**\n\n";
        
        $response .= "**E-Resource Features:**\n";
        $response .= "• Digital Library Portal\n";
        $response .= "• Online Journals & Databases\n";
        $response .= "• E-Books Collection\n";
        $response .= "• Research Papers & Articles\n";
        $response .= "• Academic Databases\n\n";
        
        $response .= "**Managing E-Resources:**\n\n";
        $response .= "**1. Adding E-Resources:**\n";
        $response .= "• Access e-resource management\n";
        $response .= "• Add resource details\n";
        $response .= "• Set access permissions\n";
        $response .= "• Configure availability\n\n";
        
        $response .= "**2. Access Control:**\n";
        $response .= "• Set user permissions\n";
        $response .= "• Control by role (student/staff)\n";
        $response .= "• Manage access levels\n";
        $response .= "• Monitor usage\n\n";
        
        $response .= "**3. Resource Updates:**\n";
        $response .= "• Update resource information\n";
        $response .= "• Modify access settings\n";
        $response .= "• Add/remove resources\n";
        $response .= "• Update links and metadata\n\n";
        
        $response .= "**4. Student Access:**\n";
        $response .= "• Students access via library website\n";
        $response .= "• Login with student credentials\n";
        $response .= "• Available 24/7 online\n";
        $response .= "• Download/View as needed\n\n";
        
        $response .= "**5. Staff Support:**\n";
        $response .= "• Help students with access\n";
        $response .= "• Troubleshoot issues\n";
        $response .= "• Guide on resource usage\n";
        $response .= "• Report technical problems\n\n";
        
        $response .= "**Best Practices:**\n";
        $response .= "• Keep resource links updated\n";
        $response .= "• Regular access audits\n";
        $response .= "• Monitor usage statistics\n";
        $response .= "• Update content regularly\n";
        
        return [
            'message' => $response,
            'type' => 'text',
        ];
    }

    private function handleAdminFAQs($message, $user)
    {
        // User management
        if (strpos($message, 'user') !== false || strpos($message, 'student') !== false || strpos($message, 'staff') !== false) {
            $totalUsers = User::count();
            $students = User::where('role', 'student')->count();
            $staff = User::where('role', 'staff')->count();
            $admins = User::where('role', 'admin')->count();
            
            return [
                'message' => "👥 **User Management Guide**\n\n**Current Users:**\n• Total Users: {$totalUsers}\n• Students: {$students}\n• Staff: {$staff}\n• Admins: {$admins}\n\n**Managing Users:**\n\n**1. Adding Users:**\n• Go to Users section\n• Click 'Add New User'\n• Fill user details\n• Set role (Student/Staff/Admin)\n• Set permissions\n• Save user\n\n**2. Editing Users:**\n• Find user in Users section\n• Click 'Edit'\n• Update information\n• Change role if needed\n• Update permissions\n• Save changes\n\n**3. User Roles:**\n• Admin - Full system access\n• Staff - Issue/Return, Fines, Requests\n• Student - Browse, Request, Reserve\n\n**4. User Permissions:**\n• Role-based access control\n• Admins can manage all\n• Staff can manage operations\n• Students have limited access\n\n**5. Deleting Users:**\n• Find user\n• Click 'Delete'\n• Confirm deletion\n• System handles related data\n\n**Best Practices:**\n• Regular user audits\n• Update roles as needed\n• Monitor user activity\n• Keep user data updated",
                'type' => 'text',
                'data' => [
                    'total_users' => $totalUsers,
                    'students' => $students,
                    'staff' => $staff,
                ],
            ];
        }
        
        // Audit logs
        if (strpos($message, 'audit') !== false || strpos($message, 'log') !== false) {
            return [
                'message' => "📝 **Audit Logs & Activity Tracking**\n\n**What are Audit Logs?**\nAudit logs track all system activities and changes for security and compliance.\n\n**Tracked Activities:**\n• User logins/logouts\n• Book additions/updates\n• Issue/Return transactions\n• Fine creation/updates\n• User management actions\n• System configuration changes\n• Data deletions\n\n**Viewing Audit Logs:**\n• Go to Audit Logs section\n• Filter by date range\n• Filter by user\n• Filter by activity type\n• Export logs if needed\n\n**Log Information:**\n• Timestamp of activity\n• User who performed action\n• Action type\n• Details of change\n• IP address (if available)\n\n**Best Practices:**\n• Regular log reviews\n• Monitor suspicious activities\n• Keep logs for compliance\n• Export important logs\n• Set up alerts for critical actions",
                'type' => 'text',
            ];
        }
        
        // General admin FAQs
        return [
            'message' => "❓ **Admin Operations FAQs**\n\n**Q: How to manage system settings?**\nA: Go to Settings section → Configure system-wide settings → Update fine rates, limits, etc.\n\n**Q: How to backup data?**\nA: Use database backup tools → Export data regularly → Store backups securely\n\n**Q: How to restore deleted data?**\nA: Check audit logs → Use backup if available → Contact system administrator\n\n**Q: How to manage permissions?**\nA: Go to Users → Edit user → Change role → Permissions update automatically\n\n**Q: How to view system statistics?**\nA: Dashboard shows key metrics → Reports section for detailed analytics → Export reports\n\n**Q: How to handle system errors?**\nA: Check error logs → Review recent changes → Contact developer if needed\n\n**Q: How to update fine rates?**\nA: Fine rates are set per issue duration → System calculates automatically → Can be adjusted in settings\n\n**Q: How to manage book categories?**\nA: Go to Categories section → Add/Edit/Delete categories → Assign to books\n\n**Q: How to handle bulk operations?**\nA: Use bulk actions in relevant sections → Select multiple items → Apply action\n\n**Q: How to export data?**\nA: Go to Reports → Select report type → Apply filters → Export as CSV/PDF",
            'type' => 'text',
        ];
    }
}

