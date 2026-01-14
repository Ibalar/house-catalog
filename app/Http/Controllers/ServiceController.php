<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Helpers\SeoHelper;
use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Support\Facades\Cache;

class ServiceController extends Controller
{
    public function index(): View
    {
        $services = Cache::remember('services_root', now()->addHours(2), function () {
            return Service::where('parent_id', null)
                ->where('is_published', true)
                ->select('id', 'title', 'slug', 'description', 'image', 'sort_order')
                ->with(['children' => function ($query) {
                    $query->select('id', 'title', 'slug', 'description', 'parent_id', 'sort_order')
                        ->where('is_published', true)
                        ->orderBy('sort_order');
                }])
                ->orderBy('sort_order')
                ->get();
        });

        $breadcrumbs = [
            ['name' => __('messages.home'), 'url' => url('/')],
            ['name' => __('admin.services')],
        ];

        $seoData = [
            'title' => SeoHelper::pageTitle(__('admin.services')),
            'description' => SeoHelper::metaDescription(__('admin.services')),
            'canonical' => route('services.index'),
            'og_type' => 'website',
            'og_image' => null,
        ];

        return view('services.index', compact('services', 'breadcrumbs', 'seoData'));
    }

    public function show(string $slug): View
    {
        $service = Service::where('slug', $slug)
            ->where('is_published', true)
            ->with(['children' => function ($query) {
                $query->select('id', 'title', 'slug', 'description', 'parent_id', 'sort_order')
                    ->where('is_published', true)
                    ->orderBy('sort_order');
            }])
            ->firstOrFail();

        $breadcrumbs = [
            ['name' => __('messages.home'), 'url' => url('/')],
            ['name' => __('admin.services'), 'url' => route('services.index')],
            ['name' => $service->title],
        ];

        $seoData = [
            'title' => SeoHelper::pageTitle($service->title . ' - ' . __('admin.services')),
            'description' => SeoHelper::metaDescription($service->description ?? $service->title),
            'canonical' => route('services.show', $service->slug),
            'og_type' => 'article',
            'og_image' => $service->image,
        ];

        return view('services.show', compact('service', 'breadcrumbs', 'seoData'));
    }
}