<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Project;
use App\Models\ProjectCategory;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class ProjectFactory extends Factory
{
    protected $model = Project::class;

    public function definition(): array
    {
        $title = fake()->sentence(3);

        return [
            'title' => $title,
            'slug' => Str::slug($title),
            'description' => fake()->paragraphs(2, true),
            'category_id' => ProjectCategory::factory(),
            'price_from' => fake()->randomFloat(2, 1000000, 5000000),
            'price_to' => fake()->randomFloat(2, 5000000, 10000000),
            'area' => fake()->randomFloat(2, 50, 500),
            'floors' => fake()->numberBetween(1, 3),
            'bedrooms' => fake()->numberBetween(1, 5),
            'bathrooms' => fake()->numberBetween(1, 3),
            'has_garage' => fake()->boolean(50),
            'roof_type' => fake()->randomElement(['плоская', 'двускатная', 'четырехскатная', 'мансардная']),
            'style' => fake()->randomElement(['современный', 'классический', 'скандинавский', 'лофт']),
            'main_image' => null,
            'is_featured' => fake()->boolean(20),
            'is_published' => fake()->boolean(90),
            'sort_order' => fake()->numberBetween(0, 100),
            'meta_title' => $title,
            'meta_description' => fake()->sentence(10),
        ];
    }
}
