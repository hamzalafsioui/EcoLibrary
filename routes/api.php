<?php

use App\Http\Controllers\Api\BookController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\AuthController;
use Illuminate\Support\Facades\Route;

// ======================================== Public auth routes ========================================
Route::post('register', [AuthController::class, 'register']);
Route::post('login',    [AuthController::class, 'login']);

// All authenticated routes
Route::middleware('auth:sanctum')->group(function () {

    // Logout (requires valid token)
    Route::post('logout', [AuthController::class, 'logout']);

    // ======================================== Reader routes (any authenticated user) ========================================
    Route::get('books',             [BookController::class, 'index']);
    Route::get('books/popular',     [BookController::class, 'popular']);
    Route::get('books/new-arrivals', [BookController::class, 'newArrivals']);
    Route::get('books/stats',       [BookController::class, 'stats'])->middleware('admin');
    Route::get('books/{id}',        [BookController::class, 'show']);

    Route::get('categories',        [CategoryController::class, 'index']);
    Route::get('categories/{id}',   [CategoryController::class, 'show']);

    // ======================================== Admin routes ========================================
    Route::middleware('admin')->group(function () {

        // Books management
        Route::post('books',               [BookController::class, 'store']);
        Route::put('books/{id}',           [BookController::class, 'update']);
        Route::patch('books/{id}',         [BookController::class, 'update']);
        Route::delete('books/{id}',        [BookController::class, 'destroy']);

        // Categories management
        Route::post('categories',          [CategoryController::class, 'store']);
        Route::put('categories/{id}',      [CategoryController::class, 'update']);
        Route::patch('categories/{id}',    [CategoryController::class, 'update']);
        Route::delete('categories/{id}',   [CategoryController::class, 'destroy']);
    });
});
