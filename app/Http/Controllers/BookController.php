<?php

namespace App\Http\Controllers;

use App\Models\Book;
use Illuminate\Http\Request;

class BookController extends Controller
{
    public function index()
    {
        return Book::all();
    }

    public function store(Request $request)
    {
        $book = Book::create($request->all());
        return response()->json($book, 201);
    }

    public function show($id)
    {
        return Book::findOrFail($id);
    }

    public function update(Request $request, $id)
    {
        $book = Book::findOrFail($id);
        $book->update($request->all());
        return $book;
    }

    public function destroy($id)
    {
        Book::destroy($id);
        return response()->json(null, 204);
    }
}
