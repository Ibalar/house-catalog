<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Lead;
use Illuminate\Database\Eloquent\Factories\Factory;

class LeadFactory extends Factory
{
    protected $model = Lead::class;

    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'phone' => fake()->phoneNumber(),
            'email' => fake()->safeEmail(),
            'source' => fake()->randomElement(['главная', 'услуги', 'проекты', 'контакты']),
            'message' => fake()->boolean(70) ? fake()->sentence(15) : null,
            'status' => fake()->randomElement(['new', 'processed', 'completed']),
        ];
    }
}
