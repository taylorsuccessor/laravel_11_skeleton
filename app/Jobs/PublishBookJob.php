<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\store\Book;
use App\Models\User;
use App\Notifications\NewBookPublished;
use Illuminate\Support\Facades\Notification;

class PublishBookJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $bookId;

    public function __construct($bookId)
    {
        $this->bookId = $bookId;
    }

    public function handle()
    {
        $book = Book::find($this->bookId);

        if ($book && !$book->published) {
            $book->update(['published' => true]);
            // \Log::info("Book ID {$this->bookId} published successfully.");
            $users = User::all(); // Get all users
            Notification::send($users, new NewBookPublished($book)); // Send notification
        }
    }
}
