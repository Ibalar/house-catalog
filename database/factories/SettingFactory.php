<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Setting;
use Illuminate\Database\Eloquent\Factories\Factory;

class SettingFactory extends Factory
{
    protected $model = Setting::class;

    public function definition(): array
    {
        $keys = [
            'phone' => fake()->phoneNumber(),
            'email' => fake()->companyEmail(),
            'address' => fake()->address(),
            'working_hours' => '9:00 - 18:00',
            'company_name' => fake()->company(),
        ];

        $key = fake()->randomElement(array_keys($keys));

        return [
            'key' => $key . '_' . fake()->unique()->numberBetween(1, 1000),
            'value' => $keys[$key],
            'group' => fake()->randomElement(['general', 'contacts', 'social', 'seo']),
        ];
    }
}
