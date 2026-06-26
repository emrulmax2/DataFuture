<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\LibraryBook;
use Illuminate\Http\Request;

/**
 * Exposes DataFuture library book copies for external synchronisation.
 *
 * Protected by Passport client_credentials with the `sms.library-books.read` scope.
 */
class LibraryBookSyncController extends Controller
{
    public function index(Request $request)
    {
        $perPage = max((int) $request->integer('per_page', 100), 1);

        $books = LibraryBook::query()
            ->with([
                'abi:id,title,author,publisher,isbn13,isbn10,language,number_of_pages,publication_date,edition,price,image_name,updated_at',
                'location:id,venue_id,name,description',
                'location.venue:id,name',
            ])
            ->select(['id', 'amazon_book_information_id', 'book_location_id', 'book_barcode', 'book_status', 'updated_at'])
            ->orderBy('id')
            ->paginate($perPage);

        $books->getCollection()->transform(function (LibraryBook $book) {
            $title = $book->abi;
            $location = $book->location;

            return [
                'library_book_id' => $book->id,
                'copy_id' => $book->id,
                'book_id' => $book->amazon_book_information_id,
                'amazon_book_information_id' => $book->amazon_book_information_id,
                'title' => $title?->title,
                'author' => $title?->author,
                'publisher' => $title?->publisher,
                'isbn13' => $title?->isbn13,
                'isbn10' => $title?->isbn10,
                'language' => $title?->language,
                'number_of_pages' => $title?->number_of_pages,
                'publication_date' => $title?->getRawOriginal('publication_date'),
                'edition' => $title?->getRawOriginal('edition'),
                'price' => $title?->getRawOriginal('price'),
                'image_name' => $title?->image_name,
                'barcode' => $book->book_barcode,
                'book_barcode' => $book->book_barcode,
                'copy_status' => $book->book_status,
                'book_location_id' => $book->book_location_id,
                'book_location' => $location?->name,
                'book_location_name' => $location?->name,
                'venue_id' => $location?->venue_id,
                'venue_name' => $location?->venue?->name,
                'updated_at' => optional($book->updated_at)->toISOString(),
            ];
        });

        return response()->json([
            'data' => $books->items(),
            'meta' => [
                'current_page' => $books->currentPage(),
                'last_page' => $books->lastPage(),
                'per_page' => $books->perPage(),
                'total' => $books->total(),
            ],
        ]);
    }
}
