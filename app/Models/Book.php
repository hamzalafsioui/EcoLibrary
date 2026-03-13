<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @OA\Schema(
 *     schema="Book",
 *     title="Book",
 *     description="Book model",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="title", type="string", example="Clean Architecture"),
 *     @OA\Property(property="author", type="string", example="Robert C. Martin"),
 *     @OA\Property(property="year", type="integer", example=2008),
 *     @OA\Property(property="category_id", type="integer", example=2),
 *     @OA\Property(property="views_count", type="integer", example=150),
 *     @OA\Property(property="is_available", type="boolean", example=true),
 *     @OA\Property(property="total_count", type="integer", example=5),
 *     @OA\Property(property="degraded_count", type="integer", example=1),
 *     @OA\Property(property="created_at", type="string", format="date-time", example="2023-01-01T00:00:00.000000Z"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", example="2023-01-01T00:00:00.000000Z"),
 *     @OA\Property(property="category", ref="#/components/schemas/Category")
 * )
 */
class Book extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'author',
        'year',
        'category_id',
        'views_count',
        'is_available',
        'total_count',
        'degraded_count',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }
}
