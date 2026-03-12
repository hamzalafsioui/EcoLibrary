<?php

namespace Database\Factories;

use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;

class BookFactory extends Factory
{
    public function definition(): array
    {
        $total    = $this->faker->numberBetween(1, 10);
        $degraded = $this->faker->numberBetween(0, $total);

        return [
            'title'          => $this->faker->sentence(rand(3, 6), false),
            'author'         => $this->faker->name(),
            'year'           => $this->faker->numberBetween(1990, 2024),
            'category_id'    => Category::inRandomOrder()->first()?->id ?? Category::factory(),
            'total_count'    => $total,
            'degraded_count' => $degraded,
            'is_available'   => $degraded < $total,
            'views_count'    => $this->faker->numberBetween(0, 500),
        ];
    }
}
