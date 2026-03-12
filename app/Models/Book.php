<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
