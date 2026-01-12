<?php

namespace App\Console\Commands;

use App\Models\Borrow;
use App\Notifications\DueDateReminder;
use Illuminate\Console\Command;

class SendDueDateReminders extends Command
{
    protected $signature = 'library:send-due-reminders';
    protected $description = 'Send due date reminders to students';

    public function handle()
    {
        $this->info('Sending due date reminders...');

        // Get borrows due in 3 days or overdue
        $borrows = Borrow::where('status', 'borrowed')
            ->whereDate('due_date', '<=', now()->addDays(3))
            ->whereDate('due_date', '>=', now()->subDays(1))
            ->with(['user', 'book'])
            ->get();

        $sent = 0;
        foreach ($borrows as $borrow) {
            $daysRemaining = now()->diffInDays($borrow->due_date, false);
            
            $borrow->user->notify(new DueDateReminder($borrow, $daysRemaining));
            $sent++;
        }

        $this->info("Sent {$sent} due date reminders.");
        return 0;
    }
}
