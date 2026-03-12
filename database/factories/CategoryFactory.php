<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class CategoryFactory extends Factory
{
    public function definition(): array
    {
        $categories = [
            'Zero Waste',
            'Permaculture',
            'Agroecology',
            'Biodiversity',
            'Renewable Energy',
            'Sustainable Food',
            'Green Mobility',
            'Circular Economy',
            'Natural Gardening',
            'Climate & Environment',
        ];

        return [
            'name'        => $this->faker->unique()->randomElement($categories),
            'description' => $this->faker->sentence(10),
        ];
    }
}
