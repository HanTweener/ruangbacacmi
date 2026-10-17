<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class ArticleFactory extends Factory
{
    public function definition(): array
    {
        return ['user_id' => User::factory(), 'judul' => fake()->sentence(6), 'slug' => Str::uuid()->toString(), 'isi' => fake()->paragraphs(3, true), 'status' => false, 'kategori' => 'Kesehatan'];
    }
}
