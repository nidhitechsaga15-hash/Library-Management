<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\BookController as AdminBookController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\Admin\ReportController as AdminReportController;
use App\Http\Controllers\Staff\DashboardController as StaffDashboardController;
use App\Http\Controllers\Staff\BorrowController as StaffBorrowController;
use App\Http\Controllers\Staff\StudentController as StaffStudentController;
use App\Http\Controllers\Student\DashboardController as StudentDashboardController;
use App\Http\Controllers\Student\BookController as StudentBookController;
use App\Http\Controllers\Student\ProfileController as StudentProfileController;
use Illuminate\Support\Facades\Route;

// Public Routes
Route::get('/', [LoginController::class, 'showLoginForm'])->name('welcome');

// Book Location by QR Code (Public route for scanning)
Route::get('/book-location/{request}', [\App\Http\Controllers\Admin\BookRequestController::class, 'showLocationByQr'])->name('book.location.qr');

Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login']);
Route::get('/admin/login', [LoginController::class, 'showAdminLogin'])->name('admin.login');
Route::get('/student/login', [LoginController::class, 'showStudentLogin'])->name('student.login');
Route::post('/admin/login', [LoginController::class, 'login']);
Route::post('/student/login', [LoginController::class, 'login']);
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');
Route::get('/register', [RegisterController::class, 'showRegistrationForm'])->name('register');
Route::post('/register', [RegisterController::class, 'register']);

// Notification Routes (Authenticated)
Route::middleware('auth')->group(function () {
    Route::get('/notifications', [\App\Http\Controllers\NotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notifications/{notification}/read', [\App\Http\Controllers\NotificationController::class, 'markAsRead'])->name('notifications.read');
    Route::post('/notifications/read-all', [\App\Http\Controllers\NotificationController::class, 'markAllAsRead'])->name('notifications.read-all');
    
    // Chat Routes (Authenticated)
    Route::get('/chat', [\App\Http\Controllers\ChatController::class, 'index'])->name('chat.index');
    Route::get('/chat/users', [\App\Http\Controllers\ChatController::class, 'getChattableUsers'])->name('chat.users');
    Route::get('/chat/conversation/{userId}', [\App\Http\Controllers\ChatController::class, 'getConversation'])->name('chat.conversation');
    Route::get('/chat/messages/{conversationId}', [\App\Http\Controllers\ChatController::class, 'getMessages'])->name('chat.messages');
    Route::post('/chat/messages/{conversationId}', [\App\Http\Controllers\ChatController::class, 'sendMessage'])->name('chat.send');
    Route::put('/chat/messages/{messageId}', [\App\Http\Controllers\ChatController::class, 'editMessage'])->name('chat.edit-message');
    Route::delete('/chat/messages', [\App\Http\Controllers\ChatController::class, 'deleteMessages'])->name('chat.delete-messages');
    Route::post('/chat/messages/{messageId}/mark-delivered', [\App\Http\Controllers\ChatController::class, 'markAsDelivered'])->name('chat.mark-delivered');
    Route::get('/chat/unread-count', [\App\Http\Controllers\ChatController::class, 'getUnreadCount'])->name('chat.unread-count');
});

// Admin Routes
Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');
    
    // Libraries Management
    Route::resource('libraries', \App\Http\Controllers\Admin\LibraryController::class);
    Route::get('/libraries/{library}/settings', [\App\Http\Controllers\Admin\LibraryController::class, 'settings'])->name('libraries.settings');
    Route::post('/libraries/{library}/settings', [\App\Http\Controllers\Admin\LibraryController::class, 'updateSettings'])->name('libraries.settings.update');
    
    // Books Management
    Route::resource('books', AdminBookController::class);
    
    // Authors Management
    Route::resource('authors', \App\Http\Controllers\Admin\AuthorController::class);
    
    // Categories Management
    Route::resource('categories', \App\Http\Controllers\Admin\CategoryController::class);
    
    // Publishers Management
    Route::resource('publishers', \App\Http\Controllers\Admin\PublisherController::class);
    
    // Users Management
    Route::resource('users', AdminUserController::class);
    Route::get('/profile/change-password', [AdminUserController::class, 'showChangePassword'])->name('profile.change-password');
    Route::post('/profile/change-password', [AdminUserController::class, 'updatePassword'])->name('profile.update-password');
    
    // Borrows Management
    Route::get('/borrows', [\App\Http\Controllers\Admin\BorrowController::class, 'index'])->name('borrows.index');
    Route::get('/borrows/create', [\App\Http\Controllers\Admin\BorrowController::class, 'create'])->name('borrows.create');
    Route::post('/borrows/issue', [\App\Http\Controllers\Admin\BorrowController::class, 'issue'])->name('borrows.issue');
    Route::get('/borrows/overdue', [\App\Http\Controllers\Admin\BorrowController::class, 'overdue'])->name('borrows.overdue');
    Route::get('/borrows/{borrow}/return', [\App\Http\Controllers\Admin\BorrowController::class, 'showReturn'])->name('borrows.return.show');
    Route::post('/borrows/{borrow}/return', [\App\Http\Controllers\Admin\BorrowController::class, 'return'])->name('borrows.return');
    Route::post('/borrows/{borrow}/extend', [\App\Http\Controllers\Admin\BorrowController::class, 'extend'])->name('borrows.extend');
    
    // Fine Management
    Route::get('/fines', [\App\Http\Controllers\Admin\FineController::class, 'index'])->name('fines.index');
    // Specific routes must come before parameterized routes
    Route::get('/fines/payment-logs', [\App\Http\Controllers\Admin\FineController::class, 'paymentLogs'])->name('fines.payment-logs');
    Route::get('/fines/live-payments', [\App\Http\Controllers\Admin\FineController::class, 'livePayments'])->name('fines.live-payments');
    Route::get('/fines/settings', [\App\Http\Controllers\Admin\FineController::class, 'settings'])->name('fines.settings');
    Route::post('/fines/settings', [\App\Http\Controllers\Admin\FineController::class, 'updateSettings'])->name('fines.settings.update');
    // Parameterized routes come after specific routes
    Route::get('/fines/{fine}', [\App\Http\Controllers\Admin\FineController::class, 'show'])->name('fines.show');
    Route::put('/fines/{fine}/payment-status', [\App\Http\Controllers\Admin\FineController::class, 'updatePaymentStatus'])->name('fines.update-payment');
    Route::post('/fines/{fine}/partial-payment', [\App\Http\Controllers\Admin\FineController::class, 'recordPartialPayment'])->name('fines.partial-payment');
    Route::post('/fines/{fine}/waive', [\App\Http\Controllers\Admin\FineController::class, 'waiveFine'])->name('fines.waive');
    Route::post('/fines/{fine}/adjust', [\App\Http\Controllers\Admin\FineController::class, 'adjustFine'])->name('fines.adjust');
    
    // Book Requests
    Route::get('/book-requests', [\App\Http\Controllers\Admin\BookRequestController::class, 'index'])->name('book-requests.index');
    Route::post('/book-requests/{request}/approve', [\App\Http\Controllers\Admin\BookRequestController::class, 'approve'])->name('book-requests.approve');
    Route::post('/book-requests/{request}/reject', [\App\Http\Controllers\Admin\BookRequestController::class, 'reject'])->name('book-requests.reject');
    Route::post('/book-requests/{request}/issue', [\App\Http\Controllers\Admin\BookRequestController::class, 'issue'])->name('book-requests.issue');
    Route::get('/book-requests/scan', [\App\Http\Controllers\Admin\BookRequestController::class, 'scanQr'])->name('book-requests.scan');
    Route::get('/book-requests/scan/{request}', [\App\Http\Controllers\Admin\BookRequestController::class, 'showScanResult'])->name('book-requests.scan-result');
    
    // Book Conditions (Damaged/Lost Books)
    Route::resource('book-conditions', \App\Http\Controllers\Admin\BookConditionController::class);
    Route::post('/book-conditions/{bookCondition}/resolve', [\App\Http\Controllers\Admin\BookConditionController::class, 'resolve'])->name('book-conditions.resolve');
    
    // Inventory Management
    Route::get('/inventory', [\App\Http\Controllers\Admin\InventoryController::class, 'index'])->name('inventory.index');
    Route::get('/inventory/alerts', [\App\Http\Controllers\Admin\InventoryController::class, 'alerts'])->name('inventory.alerts');
    
    // Settings/Configuration
    Route::get('/settings', [\App\Http\Controllers\Admin\SettingsController::class, 'index'])->name('settings.index');
    Route::post('/settings', [\App\Http\Controllers\Admin\SettingsController::class, 'update'])->name('settings.update');
    Route::post('/settings/fine-rules', [\App\Http\Controllers\Admin\SettingsController::class, 'updateFineRules'])->name('settings.fine-rules.update');
    Route::post('/settings/opening-hours', [\App\Http\Controllers\Admin\SettingsController::class, 'updateOpeningHours'])->name('settings.opening-hours.update');
    Route::get('/settings/member-types', [\App\Http\Controllers\Admin\SettingsController::class, 'memberTypes'])->name('settings.member-types');
    Route::post('/settings/member-types', [\App\Http\Controllers\Admin\SettingsController::class, 'updateMemberTypes'])->name('settings.member-types.update');
    Route::post('/settings/holidays', [\App\Http\Controllers\Admin\SettingsController::class, 'storeHoliday'])->name('settings.holidays.store');
    Route::put('/settings/holidays/{holiday}', [\App\Http\Controllers\Admin\SettingsController::class, 'updateHoliday'])->name('settings.holidays.update');
    Route::delete('/settings/holidays/{holiday}', [\App\Http\Controllers\Admin\SettingsController::class, 'deleteHoliday'])->name('settings.holidays.delete');
    
    // Audit Logs
    Route::get('/audit-logs', [\App\Http\Controllers\Admin\AuditLogController::class, 'index'])->name('audit-logs.index');
    Route::get('/audit-logs/{auditLog}', [\App\Http\Controllers\Admin\AuditLogController::class, 'show'])->name('audit-logs.show');
    
    // Digital Library / E-Resources
    Route::resource('e-resources', \App\Http\Controllers\Admin\EResourceController::class);
    Route::post('/e-resources/{eResource}/upload', [\App\Http\Controllers\Admin\EResourceController::class, 'upload'])->name('e-resources.upload');
    
    // Reports
    Route::get('/reports', [AdminReportController::class, 'index'])->name('reports');
    Route::get('/reports/total-books', [AdminReportController::class, 'totalBooks'])->name('reports.total-books');
    Route::get('/reports/book-issue', [AdminReportController::class, 'bookIssue'])->name('reports.book-issue');
    Route::get('/reports/overdue', [AdminReportController::class, 'overdue'])->name('reports.overdue');
    Route::get('/reports/fines', [AdminReportController::class, 'fines'])->name('reports.fines');
    Route::get('/reports/popular-books', [AdminReportController::class, 'popularBooks'])->name('reports.popular-books');
    Route::get('/reports/member-activity', [AdminReportController::class, 'memberActivity'])->name('reports.member-activity');
    Route::get('/reports/student-wise', [AdminReportController::class, 'studentWise'])->name('reports.student-wise');
    Route::get('/reports/student/{user}', [AdminReportController::class, 'studentDetail'])->name('reports.student-detail');
    Route::get('/reports/user/{user}/issue-history', [AdminReportController::class, 'userIssueHistory'])->name('reports.user-issue-history');
    
    // Library Cards
    Route::get('/library-cards', [\App\Http\Controllers\Admin\LibraryCardController::class, 'index'])->name('library-cards.index');
    Route::get('/library-cards/create', [\App\Http\Controllers\Admin\LibraryCardController::class, 'create'])->name('library-cards.create');
    Route::post('/library-cards', [\App\Http\Controllers\Admin\LibraryCardController::class, 'store'])->name('library-cards.store');
    Route::get('/library-cards/{libraryCard}', [\App\Http\Controllers\Admin\LibraryCardController::class, 'show'])->name('library-cards.show');
    Route::post('/library-cards/{libraryCard}/block', [\App\Http\Controllers\Admin\LibraryCardController::class, 'block'])->name('library-cards.block');
    Route::post('/library-cards/{libraryCard}/unblock', [\App\Http\Controllers\Admin\LibraryCardController::class, 'unblock'])->name('library-cards.unblock');
    Route::post('/library-cards/{libraryCard}/renew', [\App\Http\Controllers\Admin\LibraryCardController::class, 'renew'])->name('library-cards.renew');
    Route::get('/library-cards/{libraryCard}/print', [\App\Http\Controllers\Admin\LibraryCardController::class, 'print'])->name('library-cards.print');
    
    // Library Chatbot
    Route::get('/chatbot', [\App\Http\Controllers\Admin\ChatbotController::class, 'index'])->name('chatbot.index');
    Route::post('/chatbot/query', [\App\Http\Controllers\Admin\ChatbotController::class, 'query'])->name('chatbot.query');
});

// Staff Routes
Route::middleware(['auth', 'role:staff'])->prefix('staff')->name('staff.')->group(function () {
    Route::get('/dashboard', [StaffDashboardController::class, 'index'])->name('dashboard');
    
    // Books (View Only)
    Route::get('/books', [\App\Http\Controllers\Staff\BookController::class, 'index'])->name('books.index');
    Route::get('/books/search', [\App\Http\Controllers\Staff\BookController::class, 'search'])->name('books.search');
    Route::get('/books/{book}', [\App\Http\Controllers\Staff\BookController::class, 'show'])->name('books.show');
    
    // Students (View Only)
    Route::get('/students', [StaffStudentController::class, 'index'])->name('students.index');
    Route::get('/students/{user}', [StaffStudentController::class, 'show'])->name('students.show');
    Route::get('/students/{user}/issue-history', [StaffStudentController::class, 'issueHistory'])->name('students.issue-history');
    
    // Borrow Management (Issue/Return)
    Route::get('/borrows', [StaffBorrowController::class, 'index'])->name('borrows.index');
    Route::get('/borrows/create', [StaffBorrowController::class, 'create'])->name('borrows.create');
    Route::post('/borrows/issue', [StaffBorrowController::class, 'issue'])->name('borrows.issue');
    Route::get('/borrows/overdue', [StaffBorrowController::class, 'overdue'])->name('borrows.overdue');
    Route::get('/borrows/{borrow}/return', [StaffBorrowController::class, 'showReturn'])->name('borrows.return.show');
    Route::post('/borrows/{borrow}/return', [StaffBorrowController::class, 'return'])->name('borrows.return');
    Route::post('/borrows/{borrow}/extend', [\App\Http\Controllers\Admin\BorrowController::class, 'extend'])->name('borrows.extend');
    
    // Fine Management (View/Update Payment Status)
    Route::get('/fines', [\App\Http\Controllers\Staff\FineController::class, 'index'])->name('fines.index');
    Route::get('/fines/failed-payments', [\App\Http\Controllers\Staff\FineController::class, 'failedPayments'])->name('fines.failed-payments');
    Route::get('/fines/{fine}', [\App\Http\Controllers\Staff\FineController::class, 'show'])->name('fines.show');
    Route::get('/fines/{fine}/verify-payment', [\App\Http\Controllers\Staff\FineController::class, 'verifyPayment'])->name('fines.verify-payment');
    Route::get('/fines/assist/{payment}', [\App\Http\Controllers\Staff\FineController::class, 'assistStudent'])->name('fines.assist-student');
    Route::put('/fines/{fine}/payment-status', [\App\Http\Controllers\Staff\FineController::class, 'updatePaymentStatus'])->name('fines.update-payment');
    
    // Library Chatbot
    Route::get('/chatbot', [\App\Http\Controllers\Staff\ChatbotController::class, 'index'])->name('chatbot.index');
    Route::post('/chatbot/query', [\App\Http\Controllers\Staff\ChatbotController::class, 'query'])->name('chatbot.query');
    
    // Book Requests
    Route::get('/book-requests', [\App\Http\Controllers\Staff\BookRequestController::class, 'index'])->name('book-requests.index');
    Route::post('/book-requests/{request}/approve', [\App\Http\Controllers\Staff\BookRequestController::class, 'approve'])->name('book-requests.approve');
    Route::post('/book-requests/{request}/reject', [\App\Http\Controllers\Staff\BookRequestController::class, 'reject'])->name('book-requests.reject');
    Route::post('/book-requests/{request}/issue', [\App\Http\Controllers\Staff\BookRequestController::class, 'issue'])->name('book-requests.issue');
    
    // Scanner
    Route::get('/scanner', [\App\Http\Controllers\Staff\ScannerController::class, 'index'])->name('scanner.index');
    Route::post('/scanner/scan', [\App\Http\Controllers\Staff\ScannerController::class, 'scan'])->name('scanner.scan');
    Route::post('/scanner/issue', [\App\Http\Controllers\Staff\ScannerController::class, 'issueByScan'])->name('scanner.issue');
    Route::post('/scanner/return', [\App\Http\Controllers\Staff\ScannerController::class, 'returnByScan'])->name('scanner.return');
    
    // Profile
    Route::get('/profile', [\App\Http\Controllers\Staff\ProfileController::class, 'show'])->name('profile.show');
    Route::put('/profile', [\App\Http\Controllers\Staff\ProfileController::class, 'update'])->name('profile.update');
    Route::get('/profile/change-password', [\App\Http\Controllers\Staff\ProfileController::class, 'showChangePassword'])->name('profile.change-password');
    Route::put('/profile/change-password', [\App\Http\Controllers\Staff\ProfileController::class, 'updatePassword'])->name('profile.update-password');
    
    // Library Cards
    Route::get('/library-cards', [\App\Http\Controllers\Staff\LibraryCardController::class, 'index'])->name('library-cards.index');
    Route::get('/library-cards/create', [\App\Http\Controllers\Staff\LibraryCardController::class, 'create'])->name('library-cards.create');
    Route::post('/library-cards', [\App\Http\Controllers\Staff\LibraryCardController::class, 'store'])->name('library-cards.store');
    Route::get('/library-cards/{libraryCard}', [\App\Http\Controllers\Staff\LibraryCardController::class, 'show'])->name('library-cards.show');
    Route::post('/library-cards/{libraryCard}/block', [\App\Http\Controllers\Staff\LibraryCardController::class, 'block'])->name('library-cards.block');
    Route::post('/library-cards/{libraryCard}/unblock', [\App\Http\Controllers\Staff\LibraryCardController::class, 'unblock'])->name('library-cards.unblock');
    Route::post('/library-cards/{libraryCard}/renew', [\App\Http\Controllers\Staff\LibraryCardController::class, 'renew'])->name('library-cards.renew');
    Route::get('/library-cards/{libraryCard}/print', [\App\Http\Controllers\Staff\LibraryCardController::class, 'print'])->name('library-cards.print');
});

// Student Routes
Route::middleware(['auth', 'role:student'])->prefix('student')->name('student.')->group(function () {
    Route::get('/dashboard', [StudentDashboardController::class, 'index'])->name('dashboard');
    
    // Books
    Route::get('/books', [StudentBookController::class, 'index'])->name('books.index');
    Route::get('/books/search', [StudentBookController::class, 'search'])->name('books.search');
    Route::get('/books/{book}', [StudentBookController::class, 'show'])->name('books.show');
    Route::post('/books/{book}/request', [StudentBookController::class, 'request'])->name('books.request');
    
    // My Books
    Route::get('/my-books', [StudentDashboardController::class, 'myBooks'])->name('my-books');
    
    // Fine History
    Route::get('/fines', [\App\Http\Controllers\Student\FineController::class, 'index'])->name('fines.index');
    Route::get('/fines/{fine}/qr', [\App\Http\Controllers\Student\FineController::class, 'generateQR'])->name('fines.qr');
    Route::get('/fines/{fine}/check-payment', [\App\Http\Controllers\Student\FineController::class, 'checkPaymentStatus'])->name('fines.check-payment');
    Route::post('/fines/{fine}/simulate-payment', [\App\Http\Controllers\Student\FineController::class, 'simulatePayment'])->name('fines.simulate-payment');
    Route::get('/fines/{fine}/receipt', [\App\Http\Controllers\Student\FineController::class, 'downloadReceipt'])->name('fines.receipt');
    
    // Profile
    Route::get('/profile', [StudentProfileController::class, 'show'])->name('profile.show');
    Route::put('/profile', [StudentProfileController::class, 'update'])->name('profile.update');
    Route::get('/profile/change-password', [StudentProfileController::class, 'showChangePassword'])->name('profile.change-password');
    Route::put('/profile/change-password', [StudentProfileController::class, 'updatePassword'])->name('profile.update-password');
    
    // Library Card
    Route::get('/library-card', [\App\Http\Controllers\Student\LibraryCardController::class, 'show'])->name('library-card.show');
    Route::post('/library-card/request', [\App\Http\Controllers\Student\LibraryCardController::class, 'request'])->name('library-card.request');
    Route::post('/library-card/report-lost', [\App\Http\Controllers\Student\LibraryCardController::class, 'reportLost'])->name('library-card.report-lost');
    Route::get('/library-card/print', [\App\Http\Controllers\Student\LibraryCardController::class, 'print'])->name('library-card.print');
    
    // Book Reservations
    Route::get('/reservations', [\App\Http\Controllers\Student\ReservationController::class, 'index'])->name('reservations.index');
    Route::post('/books/{book}/reserve', [\App\Http\Controllers\Student\ReservationController::class, 'store'])->name('books.reserve');
    Route::post('/reservations/{reservation}/cancel', [\App\Http\Controllers\Student\ReservationController::class, 'cancel'])->name('reservations.cancel');
    
    // Library Chatbot
    Route::get('/chatbot', [\App\Http\Controllers\Student\ChatbotController::class, 'index'])->name('chatbot.index');
    Route::post('/chatbot/query', [\App\Http\Controllers\Student\ChatbotController::class, 'query'])->name('chatbot.query');
    
    // Online Payments
    Route::get('/payments', [\App\Http\Controllers\Student\PaymentController::class, 'index'])->name('payments.index');
    Route::get('/payments/{payment}', [\App\Http\Controllers\Student\PaymentController::class, 'show'])->name('payments.show');
    Route::get('/fines/{fine}/pay', [\App\Http\Controllers\Student\PaymentController::class, 'payFine'])->name('fines.pay');
    Route::post('/payments/create-order', [\App\Http\Controllers\Student\PaymentController::class, 'createOrder'])->name('payments.create-order');
    Route::post('/payments/success', [\App\Http\Controllers\Student\PaymentController::class, 'success'])->name('payments.success');
    Route::post('/payments/failure', [\App\Http\Controllers\Student\PaymentController::class, 'failure'])->name('payments.failure');
    
    // LMS Integration - Course-specific Recommendations
    Route::get('/lms/recommendations', [\App\Http\Controllers\Student\LMSController::class, 'index'])->name('lms.recommendations');
    Route::get('/lms/recommendations/api', [\App\Http\Controllers\Student\LMSController::class, 'getRecommendations'])->name('lms.recommendations.api');
    Route::get('/lms/course-books', [\App\Http\Controllers\Student\LMSController::class, 'getCourseBooks'])->name('lms.course-books');
});
