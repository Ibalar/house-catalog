<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Project;
use App\Models\ProjectImage;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProjectImageFactory extends Factory
{
    protected $model = ProjectImage::class;

    public function definition(): array
    {
        return [
            'project_id' => Project::factory(),
            'image_path' => 'projects/' . fake()->uuid() . '.jpg',
            'sort_order' => fake()->numberBetween(0, 10),
        ];
    }
}
