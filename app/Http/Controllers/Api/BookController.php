<?php

namespace App\Http\Controllers\Api;

use App\Models\Book;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class BookController extends Controller
{
    /**
     * List books with optional filters:
     *   ?category_id=1       => filter by category id
     *   ?search=compost      => search by title OR category name
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
     * Top 10 most-viewed books.
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
     * 10 most recently added books.
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
     * Admin stats: total, most viewed, degraded sum, available count.
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
     * Create a new book (admin only).
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
     * Show a single book and increment its view counter.
     */
    public function show($id)
    {
        $book = Book::with('category')->findOrFail($id);
        $book->increment('views_count');
        return response()->json($book);
    }

    /**
     * Update a book (admin only).
     */
    public function update(Request $request, $id)
    {
        $book = Book::findOrFail($id);

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
     * Delete a book (admin only).
     */
    public function destroy($id)
    {
        Book::destroy($id);
        return response()->json(null, 204);
    }
}
