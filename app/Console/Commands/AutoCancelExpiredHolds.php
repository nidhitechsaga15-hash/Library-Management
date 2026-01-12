<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\BookRequest;
use Illuminate\Support\Facades\DB;
use App\Helpers\NotificationHelper;

class AutoCancelExpiredHolds extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'book-holds:auto-cancel';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Auto-cancel expired book holds and return books to stock';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting auto-cancel expired holds...');
        $this->info('Current time: ' . now()->format('Y-m-d H:i:s'));

        // Get expired holds (hold or approved status with expired hold_expires_at)
        // Check both hold_expires_at and collection_deadline
        $expiredHolds = BookRequest::whereIn('status', ['hold', 'approved'])
            ->where('stock_deducted', true)
            ->where(function($query) {
                $query->where(function($q) {
                    // Primary: Check hold_expires_at (for new hold system)
                    $q->whereNotNull('hold_expires_at')
                      ->where('hold_expires_at', '<=', now());
                })->orWhere(function($q) {
                    // Fallback: check collection_deadline (for older records without hold_expires_at)
                    $q->whereNull('hold_expires_at')
                      ->whereNotNull('collection_deadline')
                      ->where('collection_deadline', '<=', now());
                });
            })
            ->with('book', 'user')
            ->get();
        
        $this->info("Found {$expiredHolds->count()} expired holds to process.");

        $count = 0;
        foreach ($expiredHolds as $request) {
            try {
                DB::transaction(function () use ($request) {
                    // Return book to stock
                    $request->book->increment('available_copies');
                    
                    // Update request status to cancelled
                    $request->update([
                        'status' => 'cancelled',
                        'stock_deducted' => false,
                    ]);

                    // Notify user
                    NotificationHelper::createNotification(
                        $request->user_id,
                        'book_request_expired',
                        'Book Request Cancelled',
                        'You didn\'t pick your book "' . $request->book->title . '" in time. Your request is cancelled and the book has been returned to stock.',
                        route('student.books.index')
                    );
                });
                $count++;
                $this->line("Cancelled hold for: {$request->book->title} (Request #{$request->id})");
            } catch (\Exception $e) {
                $this->error("Error cancelling hold #{$request->id}: " . $e->getMessage());
            }
        }

        $this->info("Completed! Cancelled {$count} expired holds.");
        return Command::SUCCESS;
    }
}
