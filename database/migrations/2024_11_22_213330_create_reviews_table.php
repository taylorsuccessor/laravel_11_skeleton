<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('reviews', function (Blueprint $table) {
            $table->uuid('id')->primary;
            $table->uuid('book_id');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreign('book_id')
                ->references('id')
                ->on('books')
                ->onUpdate('cascade')
                ->onDelete('cascade');
            $table->unsignedTinyInteger('rating'); // Rating from 1 to 5
            $table->text('comment')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['book_id', 'user_id'], 'user_can_make_only_one_review_for_each_book');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reviews');
    }
};
