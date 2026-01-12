<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Borrow;
use App\Models\Fine;
use App\Helpers\FineHelper;
use App\Helpers\NotificationHelper;

class AutoCreateOverdueFines extends Command
{
    protected $signature = 'fines:auto-create-overdue';
    protected $description = 'Automatically create fines for overdue books';

    public function handle()
    {
        $this->info('Checking for overdue books...');

        // Get all overdue books that don't have a fine record yet
        $overdueBorrows = Borrow::where('status', 'borrowed')
            ->where('due_date', '<', now())
            ->whereDoesntHave('fine')
            ->with(['user', 'book'])
            ->get();

        $createdCount = 0;

        foreach ($overdueBorrows as $borrow) {
            // Calculate days overdue
            $daysOverdue = (int) floor(now()->diffInDays($borrow->due_date, false));
            
            if ($daysOverdue <= 0) {
                continue;
            }

            // Calculate fine amount
            $finePerDay = $borrow->fine_per_day ?? FineHelper::getFinePerDayByDuration($borrow->issue_duration_days ?? 15);
            $fineAmount = $daysOverdue * $finePerDay;

            // Create fine record
            $fine = Fine::create([
                'borrow_id' => $borrow->id,
                'user_id' => $borrow->user_id,
                'amount' => $fineAmount,
                'reason' => "Overdue book - {$daysOverdue} day(s) overdue",
                'status' => 'pending',
            ]);

            // Notify student about fine
            NotificationHelper::createNotification(
                $borrow->user_id,
                'fine_added',
                'Overdue Fine Applied',
                'A fine of ₹' . number_format($fineAmount, 2) . ' has been applied for "' . $borrow->book->title . '" which is ' . $daysOverdue . ' day(s) overdue. Please pay via QR code.',
                route('student.fines.index')
            );

            $createdCount++;
            $this->line("Created fine of ₹{$fineAmount} for {$borrow->user->name} - {$borrow->book->title}");
        }

        $this->info("Created {$createdCount} fine(s) for overdue books.");
        return 0;
    }
}

