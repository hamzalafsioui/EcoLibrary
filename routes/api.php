<?php

use App\Http\Controllers\Api\AuthController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\BookController;
use Symfony\Component\HttpFoundation\Request;

// Route::post('/tokens/create', function(Request $request){
//     $request->validate([
//         'token_name' => 'required|string|max:255'
//     ]);

//     $token = $request->user()->createToken($request->token_name);

//     return ['token' => $token->plainTextToken];
// });

Route::post("login", [AuthController::class, "login"]);
Route::post("register", [AuthController::class, "register"]);
Route::post("logout", [AuthController::class, "logout"]);

// Protect book routes with Sanctum
Route::middleware('auth:sanctum')->group(function () {
    Route::apiResource('books', BookController::class);
});
