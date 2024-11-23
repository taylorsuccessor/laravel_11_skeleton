<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Resources\ReviewResource;
use App\Models\Review;
use App\Repository\ReviewRepository;
use Illuminate\Http\Request;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class ReviewController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        return ReviewResource::collection(
            QueryBuilder::for(Review::class)
                ->allowedFilters(
                    [
                        AllowedFilter::exact('id'),
                        AllowedFilter::exact('user_id'),
                        AllowedFilter::exact('book_id'),
                        AllowedFilter::partial('comment'),
                        AllowedFilter::scope('rating_greater_than'),
                    ]
                )
                ->allowedSorts(['id'])
                ->allowedIncludes(
                    [
                        'user',
                        'book'
                    ]
                )
                ->paginate(50)
        );
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return ReviewResource::make(
            (new ReviewRepository())
                ->create(new Review(), request()->all())
                ->load([])
        );
    }

    /**
     * Store a newly created resource in storage.
     */

    /**
     * Display the specified resource.
     */
    public function show(Review $review)
    {
        return ReviewResource::make(
            $review->load(
                [
                    'book',
                    'user'
                ]
            )
        );
    }

    /**
     * Show the form for editing the specified resource.
     */

    /**
     * Update the specified resource in storage.
     */
    public function update(Review $review)
    {
        return ReviewResource::make(
            (new ReviewRepository())
                ->update($review, request()->all())
                ->load([])
        );
    }

    /**
     * Remove the specified resource from storage.
     */
    public function delete(Review $review)
    {
        // $this->authorizedFor('books.delete');
        (new ReviewRepository())->delete($review);
    }
}
