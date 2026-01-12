<?php

namespace App\Console\Commands;

use App\Models\Fine;
use App\Notifications\FineReminder;
use Illuminate\Console\Command;

class SendFineReminders extends Command
{
    protected $signature = 'library:send-fine-reminders';
    protected $description = 'Send fine reminders to students with pending fines';

    public function handle()
    {
        $this->info('Sending fine reminders...');

        // Get users with pending fines
        $fines = Fine::where('status', 'pending')
            ->with(['user', 'borrow.book'])
            ->get()
            ->groupBy('user_id');

        $sent = 0;
        foreach ($fines as $userId => $userFines) {
            $user = $userFines->first()->user;
            $totalPending = $userFines->sum('amount');
            
            // Send notification for the latest fine
            $latestFine = $userFines->sortByDesc('created_at')->first();
            $user->notify(new FineReminder($latestFine, $totalPending));
            $sent++;
        }

        $this->info("Sent {$sent} fine reminders.");
        return 0;
    }
}
