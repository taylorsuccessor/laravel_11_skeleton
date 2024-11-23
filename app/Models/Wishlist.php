<?php

namespace App\Models;

use App\Models\Store\Book;
use Illuminate\Database\Eloquent\Model;

class Wishlist extends AbstractModel
{
    protected $fillable = ['user_id', 'book_id'];

    protected $table = 'wishlists';
    protected $guarded = [];
    protected $hidden = [];
    public $translatable = [];
    public $timestamps = true;
    public $softDeleting = true;
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function book()
    {
        return $this->belongsTo(Book::class);
    }
}
