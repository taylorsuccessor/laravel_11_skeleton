<?php

namespace App\Repository\Store;

use App\Jobs\PublishBookJob;
use App\Repository\AbstractRepository;
use App\Pay\PendingReviewRecords\PendingReviewRecord;
use Illuminate\Support\Arr;
use Illuminate\Validation\Rule;
use Tymon\JWTAuth\Facades\JWTAuth;

class WishlistRepository extends AbstractRepository
{
    /**
     * @param  Wishlist $wishlist
     * @param  array $attributes
     * @return array
     */
    protected function validate($wishlist, $attributes)
    {
        $attributes = validator(
            $attributes,
            [
                'user_id' => ['required', Rule::exists('users', 'id')], // Ensure the user exists in the `users` table
                'book_id' => [
                    'required',
                    Rule::exists('books', 'id'), // Ensure the book exists in the `books` table
                    Rule::unique('wishlists')->where(function ($query) use ($attributes) {
                        return $query->where('user_id', $attributes['user_id']);
                    })
                ],
            ]

        )->validate();

        return $attributes;
    }

    /**
     * @param  Wishlist $wishlist
     * @param  array $data
     * @return mixed|void
     */
    protected function store($wishlist, $data)
    {
        // if (request()->hasFile('book_cover_img')) {
        //     $data['book_cover_img'] = request()->file('book_cover_img')->store('book_images', 'public');
        // }
        $data['user_id'] = JWTAuth::parseToken()->authenticate()->id;
        $wishlist->fill(Arr::except($data, []))->save();

        return $wishlist;
    }

    /**
     * Draft Update an existing entity.
     *
     * @param Wishlist $wishlist
     * @param $attributes
     */

    /**
     * @param  Wishlist $wishlist
     * @throws \Exception
     */
    public function delete($wishlist)
    {
        $wishlist->delete();
        return "wishlist was deleted successfully";
    }
}
