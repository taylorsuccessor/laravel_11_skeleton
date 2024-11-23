<?php
// app/Notifications/NewBookPublished.php

namespace App\Notifications;

use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class NewBookPublished extends Notification
{
    protected $book;

    public function __construct($book)
    {
        $this->book = $book;
    }

    public function via($notifiable)
    {
        return ['mail']; // Only email in this case
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->line('A new book has been published: ' . $this->book->title)
            // ->action('View Book', url('/books/' . $this->book->id))
            ->line('Thank you for subscribing!');
    }
}
