<?php

namespace App\Http\Controllers\Api;

use App\Models\Book;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class BookController extends Controller
{
    public function index(Request $request)
    {
        $query = Book::query();

        if ($request->has('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->has('search')) {
            $query->where('title', 'like', '%' . $request->search . '%');
        }

        return $query->with('category')->get();
    }

    public function popular()
    {
        return Book::orderBy('views_count', 'desc')->take(10)->get();
    }

    public function newArrivals()
    {
        return Book::latest()->take(10)->get();
    }

    public function stats()
    {
        return response()->json([
            'total_books' => Book::count(),
            'most_viewed' => Book::orderBy('views_count', 'desc')->first(),
            'degraded_count' => Book::sum('degraded_count'),
            'available_count' => Book::where('is_available', true)->count(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'author' => 'required|string|max:255',
            'year' => 'nullable|integer',
            'category_id' => 'required|exists:categories,id',
            'total_count' => 'integer|min:0',
            'degraded_count' => 'integer|min:0',
        ]);

        $book = Book::create($validated);
        return response()->json($book, 201);
    }

    public function show($id)
    {
        $book = Book::with('category')->findOrFail($id);
        $book->increment('views_count');
        return $book;
    }

    public function update(Request $request, $id)
    {
        $book = Book::findOrFail($id);

        $validated = $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'author' => 'sometimes|required|string|max:255',
            'year' => 'nullable|integer',
            'category_id' => 'sometimes|required|exists:categories,id',
            'total_count' => 'integer|min:0',
            'degraded_count' => 'integer|min:0',
            'is_available' => 'boolean',
        ]);

        $book->update($validated);
        return $book;
    }

    public function destroy($id)
    {
        Book::destroy($id);
        return response()->json(null, 204);
    }
}
