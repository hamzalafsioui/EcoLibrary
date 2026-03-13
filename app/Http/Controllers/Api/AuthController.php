<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    /**
     * @OA\Post(
     *     path="/api/register",
     *     summary="Register a new user",
     *     tags={"Auth"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 required={"name","email","password"},
     *                 @OA\Property(property="name", type="string", example="Hamza Lafsioui"),
     *                 @OA\Property(property="email", type="string", format="email", example="hamza@example.com"),
     *                 @OA\Property(property="password", type="string", format="password", example="paswword123")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="User registered successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="user", ref="#/components/schemas/User")
     *         )
     *     ),
     *     @OA\Response(response=422, description="Validation Errors")
     * )
     */
    public function register(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8'
        ]);

        $user = User::create([
            'name' => $validatedData['name'],
            'email' => $validatedData['email'],
            'password' => Hash::make($validatedData['password'])
        ]);

        // create token 
        $token = $user->createToken("personal access token")->plainTextToken;
        $user->token = $token;
        return response()->json(['user' => $user], 201);
    }

    /**
     * @OA\Post(
     *     path="/api/login",
     *     summary="Authenticate user and get token",
     *     tags={"Auth"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 required={"email","password"},
     *                 @OA\Property(property="email", type="string", format="email", example="hamza@example.com"),
     *                 @OA\Property(property="password", type="string", format="password", example="password123")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful login",
     *         @OA\JsonContent(
     *             @OA\Property(property="user", ref="#/components/schemas/User")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Invalid credentials",
     *         @OA\JsonContent(
     *             @OA\Property(property="user", type="string", example="These credentials does not match our records")
     *         )
     *     )
     * )
     */
    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');
        if (Auth::attempt($credentials)) {
            $user = User::where("email", $request->email)->first();
            $token = $user->createToken("personal access token")->plainTextToken;
            $user->token = $token;
            return response()->json(["user" => $user]);
        }
        return response()->json(["user" => "These credentials does not match our records"]);
    }
    /**
     * @OA\Post(
     *     path="/api/logout",
     *     summary="Logout user and invalidate token",
     *     tags={"Auth"},
     *     security={{"sanctum": {}}},
     *     @OA\Response(
     *         response=200,
     *         description="Successfully logged out",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="You have been successfully logged out")
     *         )
     *     ),
     *     @OA\Response(response=401, description="Unauthenticated")
     * )
     */
    public function logout(Request $request)
    {
        if ($request->user()->currentAccessToken()->delete()) {
            return response()->json(["message" => "You have been successfully logged out"]);
        }

        return response()->json(["message" => "something wont wrong"]);
    }
}
