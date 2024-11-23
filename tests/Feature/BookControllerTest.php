<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\Test;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;
use App\Models\Store\Book;
use App\Models\Store\Store;
use Illuminate\Support\Str;
use Laravel\Sanctum\Sanctum;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Database\Seeders\RolePermissionSeeder;

class BookControllerTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Setup the test environment.
     *
     * @return void
     */
    protected function setup(): void
    {
        parent::setUp();


        $this->seed(RolePermissionSeeder::class);
        $user = User::factory()->create();
        $adminRole = Role::where(['name' => 'admin'])->first();
        $user->assignRole($adminRole);
        $this->actingAs($user);
    }

    #[Test]
    public function itListsBooks()
    {
        $user = User::factory()->create();
        $secondUser = User::factory()->create();
        $store = Store::factory()->create();

        $book = Book::factory()->create(
            [
                'store_id' => $store->id,
                'user_id' => $secondUser->id,
                'name' => 'My first book name',
                'barcode' => "some-barcode",
                'pages_number' => 400,
                'published' => true,
            ]
        );

        $books = Book::factory()->count(1)->create(
            [
                'store_id' => $store->id,
                'user_id' => $user->id,
                'name' => 'Other My book name',
                'barcode' => "second-barcode",
                'pages_number' => 500,
                'published' => false,
            ]
        );

        $this->getJson(route('books.index'))->assertSee(
            [
                $book->id,
                ...$books->pluck('id')->toArray()
            ]
        );

        $this->assertCount(
            1,
            $this->getJson(route('books.index') . '?filter[name]=first')
                ->json('data')
        );
        $this->assertCount(
            1,
            $this->getJson(route('books.index') . '?filter[user_id]=' . $secondUser->id)
                ->json('data')
        );

        $this->assertCount(
            1,
            $this->getJson(route('books.index') . '?filter[is_published]=true')
                ->json('data')
        );
    }

    #[Test]
    public function itDoesntIncludeRelationshipsByDefault()
    {
        $user = User::factory()->create();
        $store = Store::factory()->create();

        $book = Book::factory()->create(
            [
                'user_id' => $user->id,
                'store_id' => $store->id
            ]
        );

        $response = $this->getJson(route('books.index'));

        $this->assertArrayNotHasKey('user', $response->json('data')[0]);
        $this->assertArrayNotHasKey('store', $response->json('data')[0]);
    }

    #[Test]
    public function itIncludesRelationships()
    {
        $user = User::factory()->create();
        $store = Store::factory()->create();

        $book = Book::factory()->create(
            [
                'user_id' => $user->id,
                'store_id' => $store->id
            ]
        );

        $response = $this->getJson(route('books.index') . '?include=store,user');

        $responseBook = collect($response->json('data'))->first(fn($i) => $i['id'] === $book->id);

        $this->assertEquals($user->id, $responseBook['user']['id']);
        $this->assertEquals($store->id, $responseBook['store']['id']);
    }

    #[Test]
    public function itBooksListFilterSoftDeletion()
    {
        $books = Book::factory(2)->create();

        $books[0]->delete();

        $this->getJson(route('books.index'))->assertSee($books->pluck('id')->toArray(), false);

        $this->getJson(route('books.index') . '?filter[is_deleted]=0')->assertDontSee($books[0]->id);
    }

    #[Test]
    public function itShowsBook()
    {
        $user = User::factory()->create();
        $store = Store::factory()->create();

        $book = Book::factory()->create(
            [
                'user_id' => $user->id,
                'store_id' => $store->id
            ]
        );
        $response = $this->getJson(route('books.show', ['book' => $book]));

        $this->assertEquals($user->id, $response->json('data')['user']['id']);
        $this->assertEquals($store->id, $response->json('data')['store']['id']);
    }

    #[Test]
    public function itShows404IfBookNotFound()
    {
        $this->putJson(route('books.show', 'incorrect'))->assertStatus(404);
    }

    #[Test]
    public function itShows404IfBookToUpdateNotFound()
    {
        $this->putJson(route('books.update', ['book' => '-122']))->assertStatus(404);
    }

    #[Test]
    public function itCreatesNewBook()
    {

        Storage::fake('public');

        Book::factory()->create();
        $user = user::factory()->create();

        $store = Store::factory()->create();

        $response = $this->postJson(
            route('books.create'),
            [
                'name' => 'my_book_name',
                'store_id' => $store->id,
                'user_id' => $user->id,
                'barcode' => "123",
                'pages_number' => "12",
                'published' => true,
                'book_cover_img' => UploadedFile::fake()->image('book_cover.jpg')
            ]
        );

        $response->assertSuccessful();

        $this->assertArrayHasKey('id', $response->json('data'));
        $this->assertTrue(Str::isUuid($response->json('data')['id']));

        $showResponse = $this->getJson(route('books.show', ['book' => $response->json('data')['id']]));

        $this->assertEquals($user->id, $showResponse->json('data')['user']['id']);
        $this->assertEquals($store->id, $showResponse->json('data')['store']['id']);
        $this->assertEquals('my_book_name', $showResponse->json('data')['name']);
        $this->assertStringContainsString('.jpg', $showResponse->json('data')['book_cover_img']);

        Storage::disk('public')->assertExists($showResponse->json('data')['book_cover_img']);
    }

    #[Test]
    public function itUploadBookImage()
    {

        Storage::fake('public');

        $user = user::factory()->create();
        $store = Store::factory()->create();

        $response = $this->postJson(
            route('books.create'),
            [
                'name' => 'my_book_name',
                'store_id' => $store->id,
                'user_id' => $user->id,
                'barcode' => "123",
                'pages_number' => "12",
                'published' => true,
                'book_cover_img' => UploadedFile::fake()->image('book_cover.jpg')
            ]
        );

        $response->assertSuccessful();

        $book = Book::first();
        $this->assertNotNull($book->book_cover_img);
        Storage::disk('public')->assertExists($book->book_cover_img);
    }

    #[Test]
    public function itValidatesCreateBookFields()
    {
        $response = $this->postJson(
            route('books.create'),
            [
                'name' => 'my_book_name',
                'store_id' => 1,
                'user_id' => 33,
                'barcode' => "",
                'pages_number' => "12",
                'published' => true,
            ]
        );
        $response->assertStatus(422);

        $this->assertArrayHasKey('store_id', $response->json('errors'));
        $this->assertArrayHasKey('user_id', $response->json('errors'));
        $this->assertCount(2, $response->json('errors'));
    }

    #[Test]
    public function itUpdatesBook()
    {

        $user = user::factory()->create();
        $store = Store::factory()->create();

        $book = Book::factory()->create(['name' => 'Name to update']);

        $response = $this->putJson(
            route('books.update', ['book' => $book]),
            [
                'name' => 'my_book_name',
                'store_id' => $store->id,
                'user_id' => $user->id,
                'barcode' => "123",
                'pages_number' => "12",
                'published' => true,
            ]
        );

        $response->assertSuccessful();

        $showResponse = $this->getJson(route('books.show', ['book' => $book]));

        $this->assertEquals($user->id, $showResponse->json('data')['user']['id']);
        $this->assertEquals($store->id, $showResponse->json('data')['store']['id']);
        $this->assertEquals('my_book_name', $showResponse->json('data')['name']);
    }

    #[Test]
    public function itDeletesBook()
    {
        $book = Book::factory()->create();

        $this->delete(route('books.delete', ['book' => $book]));

        $book->refresh();

        $this->assertNotNull($book->deleted_at);
    }

    #[Test]
    public function itCanAccessViewIfAuthorized()
    {
        $user = User::factory()->create();
        $permission = Permission::where(['name' => 'book.view', 'guard_name' => 'api'])->first(); // Replace with your permission name

        $this->actingAs($user);
        $user = User::factory()->create();
        $store = Store::factory()->create();

        $book = Book::factory()->create(
            [
                'user_id' => $user->id,
                'store_id' => $store->id
            ]
        );

        $response = $this->getJson(route('books.show', ['book' => $book]));

        $response->assertStatus(403);
        $this->actingAs($user);

        $user->givePermissionTo($permission);

        $response = $this->getJson(route('books.show', ['book' => $book]));

        $response->assertSuccessful();
    }
}
