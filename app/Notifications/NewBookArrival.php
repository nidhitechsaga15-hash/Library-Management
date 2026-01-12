<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewBookArrival extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public $book)
    {}

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('New Book Arrival - ' . $this->book->title)
            ->greeting('Hello ' . $notifiable->name . '!')
            ->line('A new book has been added to the library!')
            ->line('**Title:** ' . $this->book->title)
            ->line('**Author:** ' . $this->book->author->name)
            ->line('**ISBN:** ' . $this->book->isbn)
            ->line('**Available Copies:** ' . $this->book->available_copies)
            ->action('View Book', url('/student/books/' . $this->book->id))
            ->line('Visit the library to borrow this book!');
    }
}
