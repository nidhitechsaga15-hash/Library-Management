<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\MobileAuthController;
use App\Http\Controllers\Api\MobileDashboardController;
use App\Http\Controllers\Api\MobileModuleController;

// Mobile API Routes
Route::prefix('mobile')->group(function () {
    // Auth Routes
    Route::post('/login', [MobileAuthController::class, 'login']);
    Route::post('/register', [MobileAuthController::class, 'register']);
    
    // Protected Routes - Using API token authentication
    Route::middleware(['api'])->group(function () {
        Route::get('/profile', [MobileAuthController::class, 'profile']);
        Route::put('/profile', [MobileModuleController::class, 'updateProfile']);
        Route::post('/change-password', [MobileModuleController::class, 'changePassword']);
        
        // Admin Routes
        Route::prefix('admin')->group(function () {
            Route::get('/dashboard', [MobileDashboardController::class, 'adminDashboard']);
            // Books
            Route::get('/books', [MobileDashboardController::class, 'adminBooks']);
            Route::post('/books', [MobileModuleController::class, 'adminCreateBook']);
            Route::get('/books/{id}', [MobileModuleController::class, 'adminGetBook']);
            Route::put('/books/{id}', [MobileModuleController::class, 'adminUpdateBook']);
            Route::delete('/books/{id}', [MobileModuleController::class, 'adminDeleteBook']);
            // Authors
            Route::get('/authors', [MobileModuleController::class, 'adminAuthors']);
            Route::post('/authors', [MobileModuleController::class, 'adminCreateAuthor']);
            Route::get('/authors/{id}', [MobileModuleController::class, 'adminGetAuthor']);
            Route::put('/authors/{id}', [MobileModuleController::class, 'adminUpdateAuthor']);
            Route::delete('/authors/{id}', [MobileModuleController::class, 'adminDeleteAuthor']);
            // Categories
            Route::get('/categories', [MobileModuleController::class, 'adminCategories']);
            Route::post('/categories', [MobileModuleController::class, 'adminCreateCategory']);
            Route::get('/categories/{id}', [MobileModuleController::class, 'adminGetCategory']);
            Route::put('/categories/{id}', [MobileModuleController::class, 'adminUpdateCategory']);
            Route::delete('/categories/{id}', [MobileModuleController::class, 'adminDeleteCategory']);
            // Users
            Route::get('/users', [MobileModuleController::class, 'adminUsers']);
            Route::post('/users', [MobileModuleController::class, 'adminCreateUser']);
            Route::get('/users/{id}', [MobileModuleController::class, 'adminGetUser']);
            Route::put('/users/{id}', [MobileModuleController::class, 'adminUpdateUser']);
            Route::delete('/users/{id}', [MobileModuleController::class, 'adminDeleteUser']);
            // Borrows
            Route::get('/borrows', [MobileModuleController::class, 'adminBorrows']);
            Route::post('/borrows/issue', [MobileModuleController::class, 'adminIssueBook']);
            Route::post('/borrows/{id}/return', [MobileModuleController::class, 'adminReturnBook']);
            Route::post('/borrows/{id}/extend', [MobileModuleController::class, 'adminExtendBorrow']);
            // Fines
            Route::get('/fines', [MobileModuleController::class, 'adminFines']);
            Route::put('/fines/{id}/payment-status', [MobileModuleController::class, 'adminUpdateFineStatus']);
            // Book Requests
            Route::get('/book-requests', [MobileModuleController::class, 'adminBookRequests']);
            Route::post('/book-requests/{id}/approve', [MobileModuleController::class, 'adminApproveRequest']);
            Route::post('/book-requests/{id}/reject', [MobileModuleController::class, 'adminRejectRequest']);
            Route::post('/book-requests/{id}/issue', [MobileModuleController::class, 'adminIssueRequest']);
            // Reports
            Route::get('/reports', [MobileModuleController::class, 'adminReports']);
            Route::get('/reports/total-books', [MobileModuleController::class, 'adminTotalBooksReport']);
            Route::get('/reports/book-issue', [MobileModuleController::class, 'adminBookIssueReport']);
            Route::get('/reports/overdue', [MobileModuleController::class, 'adminOverdueReport']);
            Route::get('/reports/fines', [MobileModuleController::class, 'adminFinesReport']);
            Route::get('/reports/student-wise', [MobileModuleController::class, 'adminStudentWiseReport']);
            Route::get('/reports/student/{id}', [MobileModuleController::class, 'adminStudentDetailReport']);
            // Library Cards
            Route::get('/library-cards', [MobileModuleController::class, 'adminLibraryCards']);
            Route::post('/library-cards', [MobileModuleController::class, 'adminCreateLibraryCard']);
            Route::post('/library-cards/{id}/block', [MobileModuleController::class, 'adminBlockLibraryCard']);
            Route::post('/library-cards/{id}/unblock', [MobileModuleController::class, 'adminUnblockLibraryCard']);
        });
        
        // Staff Routes
        Route::prefix('staff')->group(function () {
            Route::get('/dashboard', [MobileDashboardController::class, 'staffDashboard']);
            Route::get('/students', [MobileDashboardController::class, 'staffStudents']);
            Route::get('/books', [MobileModuleController::class, 'staffBooks']);
            Route::get('/borrows', [MobileModuleController::class, 'staffBorrows']);
            Route::post('/borrows/issue', [MobileModuleController::class, 'staffIssueBook']);
            Route::post('/borrows/{id}/return', [MobileModuleController::class, 'staffReturnBook']);
            Route::post('/borrows/{id}/extend', [MobileModuleController::class, 'staffExtendBorrow']);
            Route::get('/fines', [MobileModuleController::class, 'staffFines']);
            Route::put('/fines/{id}/status', [MobileModuleController::class, 'staffUpdateFineStatus']);
            Route::get('/book-requests', [MobileModuleController::class, 'staffBookRequests']);
            Route::get('/library-cards', [MobileModuleController::class, 'staffLibraryCards']);
        });
        
        // Student Routes
        Route::prefix('student')->group(function () {
            Route::get('/dashboard', [MobileDashboardController::class, 'studentDashboard']);
            Route::get('/books', [MobileDashboardController::class, 'studentBooks']);
            Route::get('/my-books', [MobileModuleController::class, 'studentMyBooks']);
            Route::post('/books/{id}/request', [MobileModuleController::class, 'studentRequestBook']);
            Route::get('/fines', [MobileModuleController::class, 'studentFines']);
            Route::get('/library-card', [MobileModuleController::class, 'studentLibraryCard']);
            Route::get('/reservations', [MobileModuleController::class, 'studentReservations']);
        });
        
        // Shared Routes (Chat, Notifications)
        Route::get('/chat/users', [MobileModuleController::class, 'getChatUsers']);
        Route::post('/chat/conversations', [MobileModuleController::class, 'createConversation']);
        Route::get('/chat/conversations', [MobileModuleController::class, 'getConversations']);
        Route::get('/chat/conversations/{id}/messages', [MobileModuleController::class, 'getMessages']);
        Route::post('/chat/conversations/{id}/messages', [MobileModuleController::class, 'sendMessage']);
        Route::get('/notifications', [MobileModuleController::class, 'getNotifications']);
        Route::post('/notifications/{id}/read', [MobileModuleController::class, 'markNotificationRead']);
    });
});

