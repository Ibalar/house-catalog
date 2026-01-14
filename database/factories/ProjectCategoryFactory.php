<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\ProjectCategory;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class ProjectCategoryFactory extends Factory
{
    protected $model = ProjectCategory::class;

    public function definition(): array
    {
        $name = fake()->words(2, true);
        $type = fake()->randomElement(['house', 'sauna']);

        return [
            'name' => ucfirst($name),
            'slug' => Str::slug($name),
            'type' => $type,
        ];
    }
}
