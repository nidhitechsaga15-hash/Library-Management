<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class DueDateReminder extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public $borrow,
        public $daysRemaining
    ) {}

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        $message = (new MailMessage)
            ->subject('Book Due Date Reminder - ' . $this->borrow->book->title)
            ->greeting('Hello ' . $notifiable->name . '!')
            ->line('This is a reminder that your borrowed book is due soon.')
            ->line('**Book:** ' . $this->borrow->book->title)
            ->line('**Due Date:** ' . $this->borrow->due_date->format('F d, Y'));

        if ($this->daysRemaining > 0) {
            $message->line('**Days Remaining:** ' . $this->daysRemaining . ' day(s)');
        } else {
            $message->line('**Status:** OVERDUE - Please return immediately!');
        }

        return $message
            ->action('View My Books', url('/student/my-books'))
            ->line('Please return the book on or before the due date to avoid fines.');
    }
}
