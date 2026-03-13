<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Category;

class CategoryController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/categories",
     *     summary="List all categories",
     *     tags={"Categories"},
     *     security={{"sanctum": {}}},
     *     @OA\Response(
     *         response=200,
     *         description="List of categories with books count",
     *         @OA\JsonContent(type="array", @OA\Items(ref="#/components/schemas/Category"))
     *     )
     * )
     */
    public function index()
    {
        return Category::withCount('books')->get();
    }

    /**
     * @OA\Post(
     *     path="/api/categories",
     *     summary="Create a new category (Admin only)",
     *     tags={"Categories"},
     *     security={{"sanctum": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name"},
     *             @OA\Property(property="name", type="string", example="Science"),
     *             @OA\Property(property="description", type="string", example="Science books")
     *         )
     *     ),
     *     @OA\Response(response=201, description="Category created", @OA\JsonContent(ref="#/components/schemas/Category")),
     *     @OA\Response(response=422, description="Validation errors"),
     *     @OA\Response(response=403, description="Unauthorized (Not admin)")
     * )
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:categories',
            'description' => 'nullable|string',
        ]);

        $category = Category::create($validated);
        return response()->json($category, 201);
    }

    /**
     * @OA\Get(
     *     path="/api/categories/{id}",
     *     summary="Get a specific category",
     *     tags={"Categories"},
     *     security={{"sanctum": {}}},
     *     @OA\Parameter(name="id", in="path", required=true, description="Category ID", @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Category details with books", @OA\JsonContent(ref="#/components/schemas/Category")),
     *     @OA\Response(response=404, description="Category not found")
     * )
     */
    public function show(string $id)
    {
        return Category::with('books')->findOrFail($id);
    }

    /**
     * @OA\Put(
     *     path="/api/categories/{id}",
     *     summary="Update a category (Admin only)",
     *     tags={"Categories"},
     *     security={{"sanctum": {}}},
     *     @OA\Parameter(name="id", in="path", required=true, description="Category ID", @OA\Schema(type="integer")),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="name", type="string", example="Advanced Science"),
     *             @OA\Property(property="description", type="string", example="Advanced Science books")
     *         )
     *     ),
     *     @OA\Response(response=200, description="Category updated", @OA\JsonContent(ref="#/components/schemas/Category")),
     *     @OA\Response(response=404, description="Category not found"),
     *     @OA\Response(response=422, description="Validation errors")
     * )
     */
    public function update(Request $request, string $id)
    {
        $category = Category::findOrFail($id);

        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255|unique:categories,name,' . $id,
            'description' => 'nullable|string',
        ]);

        $category->update($validated);
        return $category;
    }

    /**
     * @OA\Delete(
     *     path="/api/categories/{id}",
     *     summary="Delete a category (Admin only)",
     *     tags={"Categories"},
     *     security={{"sanctum": {}}},
     *     @OA\Parameter(name="id", in="path", required=true, description="Category ID", @OA\Schema(type="integer")),
     *     @OA\Response(response=204, description="Category deleted"),
     *     @OA\Response(response=404, description="Category not found"),
     *     @OA\Response(
     *         response=422,
     *         description="Cannot delete category with books",
     *         @OA\JsonContent(@OA\Property(property="message", type="string", example="Cannot delete a category with associated books."))
     *     )
     * )
     */
    public function destroy(string $id)
    {
        $category = Category::findOrFail($id);

        if ($category->books()->count() > 0) {
            return response()->json([
                'message' => 'Cannot delete a category with associated books.'
            ], 422);
        }

        $category->delete();
        return response()->json(null, 204);
    }
}
