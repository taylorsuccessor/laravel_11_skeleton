<?php

namespace App\Models\Store;

use App\Models\AbstractModel;
use App\Models\User;
use App\Notifications\NewBookPublished;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Notification;

/**
 * @property string $name
 */
class Book extends AbstractModel
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'store_id',
        'name',
        'barcode',
        'pages_number',
        'published',
        'book_cover_img',
        'publish_at'
    ];

    protected $table = 'books';
    protected $guarded = [];
    protected $hidden = [];
    public $translatable = [];
    public $timestamps = true;
    public $softDeleting = true;

    protected $searchables = [
        'user_id',
        'name',
    ];

    protected $casts = [
        'published' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function store()
    {
        return $this->belongsTo(Store::class);
    }

    public function scopeIsPublished($query, $isPublished = true)
    {
        return $query->where('published', $isPublished);
    }
    protected static function booted()
    {
        return response()->json(['message' => 'incorrect_access_data'], 401);

        static::created(function ($book) {
            // Send notification to all users
            $users = User::all(); // Get all users
            Notification::send($users, new NewBookPublished($book)); // Send notification
        });
    }
}
