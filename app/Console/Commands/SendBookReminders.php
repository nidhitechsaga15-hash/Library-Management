<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Borrow;
use App\Helpers\NotificationHelper;
use Carbon\Carbon;

class SendBookReminders extends Command
{
    protected $signature = 'books:send-reminders';
    protected $description = 'Send reminders for due dates and overdue books';

    public function handle()
    {
        // Send reminders for books due in 2 days
        $dueSoon = Borrow::where('status', 'borrowed')
            ->where('due_date', '>=', now())
            ->where('due_date', '<=', now()->addDays(2))
            ->whereDoesntHave('user.notifications', function($query) {
                $query->where('type', 'book_due_soon')
                    ->where('created_at', '>=', now()->subDay());
            })
            ->with(['user', 'book'])
            ->get();

        foreach ($dueSoon as $borrow) {
            $daysLeft = now()->diffInDays($borrow->due_date, false);
            NotificationHelper::createNotification(
                $borrow->user_id,
                'book_due_soon',
                'Book Due Soon',
                'The book "' . $borrow->book->title . '" is due in ' . $daysLeft . ' day(s). Please return it on time.',
                route('student.my-books')
            );
        }

        // Send alerts for overdue books
        $overdue = Borrow::where('status', 'borrowed')
            ->where('due_date', '<', now())
            ->whereDoesntHave('user.notifications', function($query) {
                $query->where('type', 'book_overdue')
                    ->where('created_at', '>=', now()->subDay());
            })
            ->with(['user', 'book'])
            ->get();

        foreach ($overdue as $borrow) {
            $daysOverdue = now()->diffInDays($borrow->due_date);
            NotificationHelper::createNotification(
                $borrow->user_id,
                'book_overdue',
                'Book Overdue',
                'The book "' . $borrow->book->title . '" is ' . $daysOverdue . ' day(s) overdue. Please return it immediately to avoid fines.',
                route('student.my-books')
            );
        }

        $this->info('Sent ' . ($dueSoon->count() + $overdue->count()) . ' reminders.');
        return 0;
    }
}
