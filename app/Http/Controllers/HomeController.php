<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Helpers\SeoHelper;
use App\Models\Service;
use App\Models\Project;
use App\Models\Setting;
use Illuminate\View\View;
use Illuminate\Support\Facades\Cache;

class HomeController extends Controller
{
    public function index(): View
    {
        $topServices = Cache::remember('home_top_services', now()->addHour(), function () {
            return Service::where('parent_id', null)
                ->where('is_published', true)
                ->select('id', 'title', 'slug', 'description', 'image', 'sort_order')
                ->orderBy('sort_order')
                ->limit(4)
                ->get();
        });

        $featuredProjects = Cache::remember('home_featured_projects', now()->addHour(), function () {
            return Project::where('is_published', true)
                ->where('is_featured', true)
                ->select('id', 'title', 'slug', 'main_image', 'description', 'price_from', 'price_to', 'category_id')
                ->with('category:id,name')
                ->orderBy('sort_order')
                ->orderBy('created_at', 'desc')
                ->limit(6)
                ->get();
        });

        $seoData = [
            'title' => SeoHelper::pageTitle(get_setting('site_name', 'Строительная компания')),
            'description' => get_setting('site_description', 'Профессиональное строительство домов и бань'),
            'canonical' => url('/'),
        ];

        return view('home', compact(['topServices', 'featuredProjects', 'seoData']));
    }
}