<?php

namespace App\Repository;

use App\Repository\AbstractRepository;
use App\Pay\PendingReviewRecords\PendingReviewRecord;
use Illuminate\Support\Arr;
use Illuminate\Validation\Rule;
use App\Models\Review;


class ReviewRepository extends AbstractRepository
{
    /**
     * @param  Review $review
     * @param  array $attributes
     * @return array
     */
    protected function validate($review, $attributes)
    {
        $attributes = validator(
            $attributes,
            [
                'book_id' => [Rule::requiredIf(!$review->exists()), Rule::exists('books', 'id')], // Ensure the book exists in the `books` table
                'user_id' => [Rule::requiredIf(!$review->exists()), Rule::exists('users', 'id')], // Ensure the user exists in the `users` table
                'rating' => [
                    Rule::requiredIf(!$review->exists()),
                    'integer',
                    'min:1',
                    'max:5' // Assuming a rating scale from 1 to 5
                ],
                'comment' => ['nullable', 'string', 'max:500'], // Optional comment with a max length
            ]
        )->validate();

        return $attributes;
    }

    /**
     * @param  Review $review
     * @param  array $data
     * @return mixed|void
     */
    protected function store($review, $data)
    {
        $review->fill(Arr::except($data, []))->save();

        return $review;
    }

    /**
     * Draft Update an existing entity.
     *
     * @param Review $review
     * @param $attributes
     */
    // public function draftUpdate($book, $attributes)
    // {
    //     $data = $this->validate($book, $attributes);

    //     $book->pendingReviewRecord()->updateOrCreate(
    //         [
    //             'payload' => $data
    //         ]
    //     );

    //     return $book;
    // }

    /**
     * @param  Review $review
     * @throws \Exception
     */
    public function delete($review)
    {
        $review->delete();
    }
}
