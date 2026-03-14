<?php

namespace App\Http\Controllers\Api;

use App\Models\Book;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class BookController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/books",
     *     summary="List all books",
     *     description="List books with optional filters",
     *     tags={"Books"},
     *     security={{"sanctum": {}}},
     *     @OA\Parameter(name="category_id", in="query", required=false, description="Filter by category ID", @OA\Schema(type="integer")),
     *     @OA\Parameter(name="search", in="query", required=false, description="Search by title or category name", @OA\Schema(type="string")),
     *     @OA\Response(
     *         response=200,
     *         description="List of books",
     *         @OA\JsonContent(type="array", @OA\Items(ref="#/components/schemas/Book"))
     *     )
     * )
     */
    public function index(Request $request)
    {
        $query = Book::with('category');

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->filled('search')) {
            $term = $request->search;
            $query->where(function ($q) use ($term) {
                $q->where('title', 'like', '%' . $term . '%')
                    ->orWhereHas('category', function ($q2) use ($term) {
                        $q2->where('name', 'like', '%' . $term . '%');
                    });
            });
        }

        return response()->json($query->get());
    }

    /**
     * @OA\Get(
     *     path="/api/books/popular",
     *     summary="Top 10 most-viewed books",
     *     tags={"Books"},
     *     security={{"sanctum": {}}},
     *     @OA\Response(
     *         response=200,
     *         description="List of popular books",
     *         @OA\JsonContent(type="array", @OA\Items(ref="#/components/schemas/Book"))
     *     )
     * )
     */
    public function popular()
    {
        return response()->json(
            Book::with('category')
                ->orderBy('views_count', 'desc')
                ->take(10)
                ->get()
        );
    }

    /**
     * @OA\Get(
     *     path="/api/books/new-arrivals",
     *     summary="10 most recently added books",
     *     tags={"Books"},
     *     security={{"sanctum": {}}},
     *     @OA\Response(
     *         response=200,
     *         description="List of newest books",
     *         @OA\JsonContent(type="array", @OA\Items(ref="#/components/schemas/Book"))
     *     )
     * )
     */
    public function newArrivals()
    {
        return response()->json(
            Book::with('category')
                ->latest()
                ->take(10)
                ->get()
        );
    }

    /**
     * @OA\Get(
     *     path="/api/books/stats",
     *     summary="Admin stats for books",
     *     tags={"Books"},
     *     security={{"sanctum": {}}},
     *     @OA\Response(
     *         response=200,
     *         description="Books statistics",
     *         @OA\JsonContent(
     *             @OA\Property(property="total_books", type="integer", example=150),
     *             @OA\Property(property="most_viewed", ref="#/components/schemas/Book"),
     *             @OA\Property(property="degraded_count", type="integer", example=25),
     *             @OA\Property(property="available_count", type="integer", example=120)
     *         )
     *     ),
     *     @OA\Response(response=403, description="Unauthorized (Not admin)")
     * )
     */
    public function stats()
    {
        return response()->json([
            'total_books'     => Book::count(),
            'most_viewed'     => Book::with('category')->orderBy('views_count', 'desc')->first(),
            'degraded_count'  => Book::sum('degraded_count'),
            'available_count' => Book::where('is_available', true)->count(),
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/books",
     *     summary="Create a new book (Admin only)",
     *     tags={"Books"},
     *     security={{"sanctum": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"title","author","category_id"},
     *             @OA\Property(property="title", type="string", example="The Alchemist"),
     *             @OA\Property(property="author", type="string", example="Paulo Coelho"),
     *             @OA\Property(property="year", type="integer", example=1988),
     *             @OA\Property(property="category_id", type="integer", example=1),
     *             @OA\Property(property="total_count", type="integer", example=10),
     *             @OA\Property(property="degraded_count", type="integer", example=0)
     *         )
     *     ),
     *     @OA\Response(response=201, description="Book created", @OA\JsonContent(ref="#/components/schemas/Book")),
     *     @OA\Response(response=422, description="Validation errors"),
     *     @OA\Response(response=403, description="Unauthorized (Not admin)")
     * )
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title'          => 'required|string|max:255',
            'author'         => 'required|string|max:255',
            'year'           => 'nullable|integer|min:1000|max:2100',
            'category_id'    => 'required|exists:categories,id',
            'total_count'    => 'integer|min:0',
            'degraded_count' => 'integer|min:0',
        ]);

        // auto-complete
        $total    = $validated['total_count']    ?? 1;
        $degraded = $validated['degraded_count'] ?? 0;
        $validated['is_available'] = $degraded < $total;

        $book = Book::create($validated);
        return response()->json($book->load('category'), 201);
    }

    /**
     * @OA\Get(
     *     path="/api/books/{id}",
     *     summary="Get a specific book",
     *     description="Show a single book and increment its view counter.",
     *     tags={"Books"},
     *     security={{"sanctum": {}}},
     *     @OA\Parameter(name="id", in="path", required=true, description="Book ID", @OA\Schema(type="integer")),
     *     @OA\Response(
     *         response=404,
     *         description="Book not found",
     *         @OA\JsonContent(@OA\Property(property="message", type="string", example="no book found with id 1"))
     *     )
     * )
     */
    public function show($id)
    {
        $book = Book::with('category')->find($id);

        if (!$book) {
            return response()->json(['message' => "no book found with id {$id}"], 404);
        }

        $book->increment('views_count');
        return response()->json($book);
    }

    /**
     * @OA\Put(
     *     path="/api/books/{id}",
     *     summary="Update a book (Admin only)",
     *     tags={"Books"},
     *     security={{"sanctum": {}}},
     *     @OA\Parameter(name="id", in="path", required=true, description="Book ID", @OA\Schema(type="integer")),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="title", type="string", example="The Alchemist - Updated"),
     *             @OA\Property(property="author", type="string", example="Paulo Coelho"),
     *             @OA\Property(property="year", type="integer", example=1990),
     *             @OA\Property(property="category_id", type="integer", example=1),
     *             @OA\Property(property="total_count", type="integer", example=12),
     *             @OA\Property(property="degraded_count", type="integer", example=2)
     *         )
     *     ),
     *     @OA\Response(response=200, description="Book updated", @OA\JsonContent(ref="#/components/schemas/Book")),
     *     @OA\Response(
     *         response=404,
     *         description="Book not found",
     *         @OA\JsonContent(@OA\Property(property="message", type="string", example="no book found with id 1"))
     *     ),
     *     @OA\Response(response=422, description="Validation errors")
     * )
     */
    public function update(Request $request, $id)
    {
        $book = Book::find($id);

        if (!$book) {
            return response()->json(['message' => "no book found with id {$id}"], 404);
        }

        $validated = $request->validate([
            'title'          => 'sometimes|required|string|max:255',
            'author'         => 'sometimes|required|string|max:255',
            'year'           => 'nullable|integer|min:1000|max:2100',
            'category_id'    => 'sometimes|required|exists:categories,id',
            'total_count'    => 'integer|min:0',
            'degraded_count' => 'integer|min:0',
        ]);

        $book->update($validated);

        // is_available
        $total    = $book->fresh()->total_count;
        $degraded = $book->fresh()->degraded_count;
        $book->update(['is_available' => $degraded < $total]);

        return response()->json($book->load('category'));
    }

    /**
     * @OA\Delete(
     *     path="/api/books/{id}",
     *     summary="Delete a book (Admin only)",
     *     tags={"Books"},
     *     security={{"sanctum": {}}},
     *     @OA\Parameter(name="id", in="path", required=true, description="Book ID", @OA\Schema(type="integer")),
     *     @OA\Response(response=204, description="Book deleted"),
     *     @OA\Response(
     *         response=404,
     *         description="Book not found",
     *         @OA\JsonContent(@OA\Property(property="message", type="string", example="no book found with id 1"))
     *     )
     * )
     */
    public function destroy($id)
    {
        $book = Book::find($id);

        if (!$book) {
            return response()->json(['message' => "no book found with id {$id}"], 404);
        }

        $book->delete();
        return response()->json(null, 204);
    }
}
