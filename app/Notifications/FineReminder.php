<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class FineReminder extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public $fine,
        public $totalPending
    ) {}

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Fine Reminder - Payment Pending')
            ->greeting('Hello ' . $notifiable->name . '!')
            ->line('You have pending fines that need to be paid.')
            ->line('**Book:** ' . $this->fine->borrow->book->title)
            ->line('**Fine Amount:** ₹' . number_format($this->fine->amount, 2))
            ->line('**Reason:** ' . $this->fine->reason)
            ->line('**Total Pending Fines:** ₹' . number_format($this->totalPending, 2))
            ->action('View Fines', url('/student/fines'))
            ->line('Please pay your fines at the library counter to avoid any restrictions.');
    }
}
