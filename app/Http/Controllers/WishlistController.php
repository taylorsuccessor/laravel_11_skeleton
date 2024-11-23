<?php

namespace App\Http\Controllers;

use App\Http\Resources\wishlistResource;
use App\Models\Wishlist;
use App\Models\store\Book;
use App\Repository\Store\WishlistRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;
use Tymon\JWTAuth\Facades\JWTAuth;

class WishlistController extends Controller
{
    // View user's wishlist

    public function index(Request $request)
    {
        return wishlistResource::collection(
            QueryBuilder::for(JWTAuth::parseToken()->authenticate()->wishlists())
                ->allowedFilters(
                    [
                        AllowedFilter::exact('id'),
                        AllowedFilter::exact('book_id'),
                        // AllowedFilter::partial('name'),
                        // AllowedFilter::scope('is_published'),
                    ]
                )
                ->allowedSorts(['id'])
                ->allowedIncludes(
                    [
                        'user',
                        'store'
                    ]
                )
                ->paginate(50)
        );
    }
    // Add book to wishlist
    public function create()
    {
        // if (Wishlist::where('user_id', $user->id)->where('book_id', $book->id)->exists()) {
        //     return response()->json(['message' => 'This book is already in your wishlist'], 400);
        // }
        return wishlistResource::make(
            (new WishlistRepository())
                ->create(new Wishlist(), attributes: request()->all())
                ->load([])
        );
    }

    // Remove book from wishlist
    public function delete(Wishlist $wishlist)
    {
        // $this->authorizedFor('books.delete');
        (new WishlistRepository())->delete($wishlist);
        return response()->json('Wishlist item was successfully deletd');
    }
}
