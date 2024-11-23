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
        Schema::create(
            'books',
            function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->foreignId('user_id')->constrained()->onDelete('cascade');
                $table->uuid('store_id');
                $table->foreign('store_id')
                    ->references('id')
                    ->on('stores')
                    ->onUpdate('cascade')
                    ->onDelete('cascade');
                $table->string('name', 64);
                $table->string('barcode', 64)->nullable()->unique();
                $table->integer('pages_number');
                $table->boolean('published')->default(false);
                $table->string('book_cover_img')->nullable();
                $table->timestamps();
                $table->softDeletes();

                $table->unique(['name', 'user_id'], 'unique_book_name_per_user');
            }
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('books');
    }
};
