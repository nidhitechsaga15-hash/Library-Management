<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class BookRequestApproved extends Notification
{
    use Queueable;

    public function __construct(
        public $book,
        public $request
    ) {}

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Book Request Approved - ' . $this->book->title)
            ->greeting('Hello ' . $notifiable->name . '!')
            ->line('Your book request has been approved by the library staff.')
            ->line('**Book:** ' . $this->book->title)
            ->line('**ISBN:** ' . $this->book->isbn)
            ->line('Please visit the library to collect your book.')
            ->action('View Request', url('/student/my-books'))
            ->line('Thank you for using our library system!');
    }
}
