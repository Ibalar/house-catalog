<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Block;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class BlockFactory extends Factory
{
    protected $model = Block::class;

    public function definition(): array
    {
        $title = fake()->sentence(3);

        return [
            'name' => Str::slug($title),
            'title' => $title,
            'content' => fake()->paragraphs(3, true),
            'image' => null,
            'link' => fake()->boolean(50) ? fake()->url() : null,
            'is_active' => fake()->boolean(90),
        ];
    }
}
