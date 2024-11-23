<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\store\Book;
use Carbon\Carbon;

class PublishScheduledBooks extends Command
{
    protected $signature = 'books:publish-scheduled';
    protected $description = 'Publish books scheduled for today';

    public function handle()
    {
        $today = Carbon::today();

        // Update books scheduled for publishing today
        $booksToPublish = Book::where('publish_at', $today)
            ->where('published', false)
            ->update(['published' => true]);

        $this->info("$booksToPublish book(s) published successfully!");
    }
}
