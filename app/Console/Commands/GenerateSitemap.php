<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Page;
use App\Models\Project;
use App\Models\Service;
use Illuminate\Console\Command;
use Spatie\Sitemap\Sitemap;
use Spatie\Sitemap\Tags\Url;

class GenerateSitemap extends Command
{
    protected $signature = 'sitemap:generate';
    protected $description = 'Generate sitemap.xml for the website';

    public function handle(): int
    {
        $this->info('Generating sitemap...');

        $sitemap = Sitemap::create()
            ->add(Url::create('/')
                ->setPriority(1.0)
                ->setChangeFrequency(Url::CHANGE_FREQUENCY_DAILY));

        // Add pages
        Page::where('is_active', true)
            ->whereNotNull('slug')
            ->each(function ($page) use ($sitemap) {
                $sitemap->add(
                    Url::create(route('page.show', $page->slug))
                        ->setPriority(0.7)
                        ->setChangeFrequency(Url::CHANGE_FREQUENCY_WEEKLY)
                        ->setLastModificationDate($page->updated_at)
                );
            });

        // Add services
        Service::where('is_published', true)
            ->whereNotNull('slug')
            ->each(function ($service) use ($sitemap) {
                $sitemap->add(
                    Url::create(route('services.show', $service->slug))
                        ->setPriority(0.8)
                        ->setChangeFrequency(Url::CHANGE_FREQUENCY_WEEKLY)
                        ->setLastModificationDate($service->updated_at)
                );
            });

        $sitemap->add(
            Url::create(route('services.index'))
                ->setPriority(0.8)
                ->setChangeFrequency(Url::CHANGE_FREQUENCY_WEEKLY)
        );

        // Add projects
        Project::where('is_published', true)
            ->whereNotNull('slug')
            ->each(function ($project) use ($sitemap) {
                $sitemap->add(
                    Url::create(route('projects.show', $project->slug))
                        ->setPriority(0.9)
                        ->setChangeFrequency(Url::CHANGE_FREQUENCY_DAILY)
                        ->setLastModificationDate($project->updated_at)
                );
            });

        $sitemap->add(
            Url::create(route('projects.index'))
                ->setPriority(0.9)
                ->setChangeFrequency(Url::CHANGE_FREQUENCY_DAILY)
        );

        $sitemap->writeToFile(public_path('sitemap.xml'));

        $this->info('Sitemap generated successfully!');

        return Command::SUCCESS;
    }
}
