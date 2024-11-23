<?php

namespace App\Models;

use App\Models\Store\Book;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Review extends AbstractModel
{
    use HasFactory;
    protected $fillable = [
        'user_id',
        'book_id',
        'rating',
        'comment',
    ];

    protected $table = 'reviews';
    protected $guarded = [];
    protected $hidden = [];
    public $translatable = [];
    public $timestamps = true;
    public $softDeleting = true;

    // protected $searchables = [
    //     'user_id',
    //     'name',
    // ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function book()
    {
        return $this->belongsTo(Book::class);
    }

    public function scopeRatingGreaterThan($query, $value)
    {
        return $query->where('rating', '>=', $value);
    }
}
