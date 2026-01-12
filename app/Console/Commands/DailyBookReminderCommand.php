<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Borrow;
use App\Models\User;
use App\Helpers\NotificationHelper;
use Carbon\Carbon;

class DailyBookReminderCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'books:daily-reminders';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send daily reminders for books due tomorrow and due today. Notify students, admin, and staff.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $today = now();
        $tomorrow = now()->addDay();
        
        $remindersSent = 0;
        $alertsSent = 0;

        // Reminder 1: Books due tomorrow (14th day reminder - 1 day before due)
        $dueTomorrow = Borrow::where('status', 'borrowed')
            ->whereDate('due_date', $tomorrow)
            ->whereNull('return_date')
            ->with(['user', 'book'])
            ->get();

        foreach ($dueTomorrow as $borrow) {
            // Notify Student
            NotificationHelper::createNotification(
                $borrow->user_id,
                'book_due_tomorrow',
                'Book Due Tomorrow',
                'Reminder: Your book "' . $borrow->book->title . '" is due tomorrow. Please return it to avoid fine.',
                route('student.my-books')
            );

            // Notify Admin & Staff
            $admins = User::where('role', 'admin')->where('is_active', true)->get();
            $staff = User::where('role', 'staff')->where('is_active', true)->get();
            
            foreach ($admins as $admin) {
                NotificationHelper::createNotification(
                    $admin->id,
                    'book_due_tomorrow_alert',
                    'Book Due Tomorrow Alert',
                    'Student ' . $borrow->user->name . '\'s book "' . $borrow->book->title . '" is due tomorrow.',
                    route('admin.borrows.index')
                );
            }
            
            foreach ($staff as $staffMember) {
                NotificationHelper::createNotification(
                    $staffMember->id,
                    'book_due_tomorrow_alert',
                    'Book Due Tomorrow Alert',
                    'Student ' . $borrow->user->name . '\'s book "' . $borrow->book->title . '" is due tomorrow.',
                    route('staff.borrows.index')
                );
            }
            
            $remindersSent++;
        }

        // Reminder 2: Books due today (Last day reminder)
        $dueToday = Borrow::where('status', 'borrowed')
            ->whereDate('due_date', $today)
            ->whereNull('return_date')
            ->with(['user', 'book'])
            ->get();

        foreach ($dueToday as $borrow) {
            // Notify Student
            NotificationHelper::createNotification(
                $borrow->user_id,
                'book_due_today',
                'Book Due Today',
                'Today is the last day to return "' . $borrow->book->title . '". Fine will be applied from tomorrow.',
                route('student.my-books')
            );

            // Notify Admin & Staff
            $admins = User::where('role', 'admin')->where('is_active', true)->get();
            $staff = User::where('role', 'staff')->where('is_active', true)->get();
            
            foreach ($admins as $admin) {
                NotificationHelper::createNotification(
                    $admin->id,
                    'book_due_today_alert',
                    'Book Return Deadline Today',
                    'Book return deadline today for Student ' . $borrow->user->name . ' - "' . $borrow->book->title . '".',
                    route('admin.borrows.index')
                );
            }
            
            foreach ($staff as $staffMember) {
                NotificationHelper::createNotification(
                    $staffMember->id,
                    'book_due_today_alert',
                    'Book Return Deadline Today',
                    'Book return deadline today for Student ' . $borrow->user->name . ' - "' . $borrow->book->title . '".',
                    route('staff.borrows.index')
                );
            }
            
            $alertsSent++;
        }

        $this->info("Sent {$remindersSent} 'due tomorrow' reminders and {$alertsSent} 'due today' alerts.");
        return 0;
    }
}
