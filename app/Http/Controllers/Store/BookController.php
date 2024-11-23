<?php

namespace App\Http\Controllers\Store;

use App\Http\Controllers\Controller;
use App\Http\Resources\BookResource;
use App\Models\Store\Book;
use App\Repository\Store\BookRepository;
use Illuminate\Http\Request;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

/**
 * @OA\OpenApi(
 *     @OA\Info(
 *         title="Books API",
 *         version="1.0.0"
 *     ),
 *      @OA\Components(
 *          @OA\SecurityScheme(
 *              securityScheme="customAuth",
 *              type="apiKey",
 *              scheme="bearer",
 *              description="Enter token in format  Bearer xx|xxxxxxxx",
 *              name="Authorization",
 *              in="header",
 *              scheme="Bearer"
 *          )
 *      )
 * )
 */
class BookController extends Controller
{

    /**
     * @OA\Get(
     *     path="/api/book",
     *     summary=" Get list of books",
     *     tags={"Books"},
     *     security={{"sanctum":{}},{"customAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="query",
     *         description="Filter by book ID",
     *         required=false,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="user_id",
     *         in="query",
     *         description="Filter by user ID",
     *         required=false,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="name",
     *         in="query",
     *         description="Filter by book name",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="is_published",
     *         in="query",
     *         description="Filter by published status",
     *         required=false,
     *         @OA\Schema(type="boolean")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="A list of books"
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized"
     *     )
     * )
     */
    public function index(Request $request)
    {
        return BookResource::collection(
            QueryBuilder::for(Book::class)
                ->allowedFilters(
                    [
                        AllowedFilter::exact('id'),
                        AllowedFilter::exact('user_id'),
                        AllowedFilter::partial('name'),
                        AllowedFilter::scope('is_published'),
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

    /**
     * @OA\Get(
     *     path="/api/book/{book}",
     *     summary="Get a book by ID",
     *     tags={"Books"},
     *     security={{"sanctum":{}},{"customAuth":{}}},
     *     @OA\Parameter(
     *         name="book",
     *         in="path",
     *         description="ID of the book",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Book details",
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Book not found"
     *     )
     * )
     */
    public function show(Book $book)
    {
        return BookResource::make(
            $book->load(
                [
                    'store',
                    'user'
                ]
            )
        );
    }

    /**
     * @OA\Post(
     *     path="/api/book",
     *     summary="Create a new book",
     *     tags={"Books"},
     *     security={{"sanctum":{}},{"customAuth":{}}},
     *     @OA\RequestBody(
     *          required=true,
     *          @OA\MediaType(
     *              mediaType="multipart/form-data",
     *              @OA\Schema(
     *                  required={"name", "pages_number"},
     *                  @OA\Property(property="store_id", type="integer", nullable=true, description="Must exist in stores table"),
     *                  @OA\Property(property="user_id", type="integer", nullable=true, description="Must exist in users table"),
     *                  @OA\Property(property="name", type="string", maxLength=64, description="Required if book does not exist"),
     *                  @OA\Property(property="barcode", type="string", nullable=true, maxLength=64),
     *                  @OA\Property(property="pages_number", type="integer", description="Required if book does not exist"),
     *                  @OA\Property(property="published", type="boolean", nullable=true),
     *                  @OA\Property(property="book_cover_img", type="string", format="binary", nullable=true, description="Image file in jpeg, jpg, or png format")
     *              )
     *          )
     *      ),
     *     @OA\Response(
     *         response=201,
     *         description="Book created",
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Bad request"
     *     )
     * )
     */
    public function create()
    {
        return BookResource::make(
            (new BookRepository())
                ->create(new Book(), request()->all())
                ->load([])
        );
    }

    /**
     * @OA\Put(
     *     path="/api/book/{book}",
     *     summary="Update a book",
     *     tags={"Books"},
     *     security={{"sanctum":{}},{"customAuth":{}}},
     *     @OA\Parameter(
     *         name="book",
     *         in="path",
     *         description="ID of the book",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\RequestBody(
     *           required=true,
     *           @OA\MediaType(
     *               mediaType="multipart/form-data",
     *               @OA\Schema(
     *                   required={"name", "pages_number"},
     *                   @OA\Property(property="store_id", type="integer", nullable=true, description="Must exist in stores table"),
     *                   @OA\Property(property="user_id", type="integer", nullable=true, description="Must exist in users table"),
     *                   @OA\Property(property="name", type="string", maxLength=64, description="Required if book does not exist"),
     *                   @OA\Property(property="barcode", type="string", nullable=true, maxLength=64),
     *                   @OA\Property(property="pages_number", type="integer", description="Required if book does not exist"),
     *                   @OA\Property(property="published", type="boolean", nullable=true),
     *                   @OA\Property(property="book_cover_img", type="string", format="binary", nullable=true, description="Image file in jpeg, jpg, or png format")
     *               )
     *           )
     *       ),
     *     @OA\Response(
     *         response=200,
     *         description="Book updated"
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Bad request"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Book not found"
     *     )
     * )
     */
    public function update(Book $book)
    {
        return BookResource::make(
            (new BookRepository())
                ->update($book, request()->all())
                ->load([])
        );
    }

    /**
     * @OA\Delete(
     *     path="/api/book/{book}",
     *     summary="Delete a book",
     *     tags={"Books"},
     *     security={{"sanctum":{}},{"customAuth":{}}},
     *     @OA\Parameter(
     *         name="book",
     *         in="path",
     *         description="ID of the book",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Book deleted"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Book not found"
     *     )
     * )
     * @throws \Exception
     */
    public function delete(Book $book)
    {
        // $this->authorizedFor('books.delete');
        (new BookRepository())->delete($book);
    }
}
