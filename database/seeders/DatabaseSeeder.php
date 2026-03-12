<?php

namespace Database\Seeders;

use App\Models\Book;
use App\Models\Category;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Create admin user
        User::factory()->create([
            'name'     => 'Admin User',
            'email'    => 'admin@ecolibrary.com',
            'password' => Hash::make('password'),
            'role'     => 'admin',
        ]);

        // Create reader user
        User::factory()->create([
            'name'     => 'Reader User',
            'email'    => 'reader@ecolibrary.com',
            'password' => Hash::make('password'),
            'role'     => 'reader',
        ]);

        // Seed 5 categories then 20 books
        Category::factory(5)->create();
        Book::factory(20)->create();
    }
}
