<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Block;
use App\Models\Lead;
use App\Models\Page;
use App\Models\Project;
use App\Models\ProjectCategory;
use App\Models\ProjectImage;
use App\Models\Service;
use App\Models\Setting;
use Illuminate\Database\Seeder;

class CatalogSeeder extends Seeder
{
    public function run(): void
    {
        // Создаем страницы
        Page::factory()->count(10)->create();

        // Создаем категории проектов
        $houseCategories = ProjectCategory::factory()->count(3)->create(['type' => 'house']);
        $saunaCategories = ProjectCategory::factory()->count(2)->create(['type' => 'sauna']);

        // Создаем проекты домов
        $houseCategories->each(function ($category) {
            $projects = Project::factory()->count(5)->create([
                'category_id' => $category->id,
            ]);

            // Для каждого проекта создаем изображения
            $projects->each(function ($project) {
                ProjectImage::factory()->count(rand(3, 8))->create([
                    'project_id' => $project->id,
                ]);
            });
        });

        // Создаем проекты бань
        $saunaCategories->each(function ($category) {
            $projects = Project::factory()->count(4)->create([
                'category_id' => $category->id,
            ]);

            // Для каждого проекта создаем изображения
            $projects->each(function ($project) {
                ProjectImage::factory()->count(rand(3, 6))->create([
                    'project_id' => $project->id,
                ]);
            });
        });

        // Создаем родительские услуги
        $parentServices = Service::factory()->count(5)->create(['parent_id' => null]);

        // Создаем дочерние услуги
        $parentServices->each(function ($parent) {
            Service::factory()->count(rand(2, 5))->create([
                'parent_id' => $parent->id,
            ]);
        });

        // Создаем блоки
        Block::factory()->count(8)->create();

        // Создаем настройки
        Setting::create([
            'key' => 'site_name',
            'value' => 'Строительная компания "ДомСтрой"',
            'group' => 'general',
        ]);

        Setting::create([
            'key' => 'phone',
            'value' => '+7 (495) 123-45-67',
            'group' => 'contacts',
        ]);

        Setting::create([
            'key' => 'email',
            'value' => 'info@domstroy.ru',
            'group' => 'contacts',
        ]);

        Setting::create([
            'key' => 'address',
            'value' => 'г. Москва, ул. Строителей, д. 1',
            'group' => 'contacts',
        ]);

        Setting::create([
            'key' => 'working_hours',
            'value' => 'Пн-Пт: 9:00-18:00, Сб-Вс: выходной',
            'group' => 'contacts',
        ]);

        // Создаем заявки
        Lead::factory()->count(30)->create();
    }
}
