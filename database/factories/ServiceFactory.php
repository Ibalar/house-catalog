<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Service;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class ServiceFactory extends Factory
{
    protected $model = Service::class;

    public function definition(): array
    {
        $title = fake()->sentence(3);

        return [
            'title' => $title,
            'slug' => Str::slug($title),
            'description' => fake()->sentence(15),
            'full_text' => fake()->paragraphs(8, true),
            'parent_id' => null,
            'sort_order' => fake()->numberBetween(0, 100),
            'image' => null,
            'is_published' => fake()->boolean(90),
            'meta_fields' => [
                'meta_title' => $title,
                'meta_description' => fake()->sentence(10),
            ],
        ];
    }
}
