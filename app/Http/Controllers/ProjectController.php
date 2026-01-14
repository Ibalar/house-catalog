<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Helpers\SeoHelper;
use App\Models\Project;
use App\Models\ProjectCategory;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Support\Facades\Cache;

class ProjectController extends Controller
{
    public function index(Request $request): View
    {
        // Validate filters
        $validated = $request->validate([
            'type' => 'nullable|in:house,sauna',
            'area_min' => 'nullable|numeric|min:0',
            'area_max' => 'nullable|numeric|min:0',
            'bedrooms' => 'nullable|integer|min:1|max:10',
            'bathrooms' => 'nullable|integer|min:1|max:5',
            'floors' => 'nullable|integer|min:1|max:3',
            'has_garage' => 'nullable|boolean',
            'roof_types' => 'nullable|array',
            'roof_types.*' => 'string',
            'styles' => 'nullable|array',
            'styles.*' => 'string',
            'price_min' => 'nullable|numeric|min:0',
            'price_max' => 'nullable|numeric|min:0',
            'sort' => 'nullable|in:featured,newest,price_asc,price_desc,default',
        ]);

        $query = Project::where('is_published', true)
            ->select('id', 'title', 'slug', 'main_image', 'description', 'price_from', 'price_to', 'area', 'bedrooms', 'bathrooms', 'is_featured', 'category_id', 'floors')
            ->with(['category:id,name,type', 'images:id,project_id,image,sort_order']);

        // Apply filters using when() methods
        $query = $query->when($request->get('type'), function ($q, $type) {
            return $q->whereHas('category', function ($c) use ($type) {
                $c->where('type', $type);
            });
        });

        $query = $query->when($request->get('area_min'), function ($q, $areaMin) {
            return $q->where('area', '>=', $areaMin);
        });

        $query = $query->when($request->get('area_max'), function ($q, $areaMax) {
            return $q->where('area', '<=', $areaMax);
        });

        $query = $query->when($request->get('bedrooms'), function ($q, $bedrooms) {
            return $q->where('bedrooms', '>=', $bedrooms);
        });

        $query = $query->when($request->get('bathrooms'), function ($q, $bathrooms) {
            return $q->where('bathrooms', '>=', $bathrooms);
        });

        $query = $query->when($request->get('floors'), function ($q, $floors) {
            return $q->where('floors', $floors);
        });

        $query = $query->when($request->get('has_garage'), function ($q) {
            return $q->where('has_garage', true);
        });

        $query = $query->when($request->get('roof_types'), function ($q, $roofTypes) {
            if (is_array($roofTypes)) {
                return $q->whereIn('roof_type', $roofTypes);
            }
            return $q;
        });

        $query = $query->when($request->get('styles'), function ($q, $styles) {
            if (is_array($styles)) {
                return $q->whereIn('style', $styles);
            }
            return $q;
        });

        $query = $query->when($request->get('price_min'), function ($q, $priceMin) {
            return $q->where('price_to', '>=', $priceMin);
        });

        $query = $query->when($request->get('price_max'), function ($q, $priceMax) {
            return $q->where('price_from', '<=', $priceMax);
        });

        // Apply sorting
        $sort = $request->get('sort', 'default');

        switch ($sort) {
            case 'featured':
                $query = $query->orderBy('is_featured', 'desc')->orderBy('created_at', 'desc');
                break;

            case 'newest':
                $query = $query->orderBy('created_at', 'desc');
                break;

            case 'price_asc':
                $query = $query->orderBy('price_from', 'asc');
                break;

            case 'price_desc':
                $query = $query->orderBy('price_from', 'desc');
                break;

            default:
                $query = $query->orderBy('sort_order')->orderBy('created_at', 'desc');
        }

        $projects = $query->paginate(12)->appends($request->query());

        $categories = Cache::remember('projects_categories', now()->addHours(2), function () {
            return ProjectCategory::select('id', 'name', 'type')->orderBy('name')->get();
        });

        // Get available filter values from database
        $availableValues = Cache::remember('projects_available_values', now()->addHours(2), function () {
            return [
                'roof_types' => Project::whereNotNull('roof_type')
                    ->distinct()
                    ->orderBy('roof_type')
                    ->pluck('roof_type')
                    ->values()
                    ->all(),
                'styles' => Project::whereNotNull('style')
                    ->distinct()
                    ->orderBy('style')
                    ->pluck('style')
                    ->values()
                    ->all(),
            ];
        });

        // Current active filters
        $filters = [
            'type' => $request->get('type'),
            'area_min' => $request->get('area_min'),
            'area_max' => $request->get('area_max'),
            'bedrooms' => $request->get('bedrooms'),
            'bathrooms' => $request->get('bathrooms'),
            'floors' => $request->get('floors'),
            'has_garage' => $request->get('has_garage'),
            'roof_types' => $request->get('roof_types', []),
            'styles' => $request->get('styles', []),
            'price_min' => $request->get('price_min'),
            'price_max' => $request->get('price_max'),
            'sort' => $sort,
        ];

        $breadcrumbs = [
            ['name' => __('messages.home'), 'url' => url('/')],
            ['name' => __('admin.projects')],
        ];

        $seoData = [
            'title' => SeoHelper::pageTitle(__('admin.projects')),
            'description' => SeoHelper::metaDescription(__('admin.projects')),
            'canonical' => route('projects.index'),
            'og_type' => 'website',
            'og_image' => null,
        ];

        return view('projects.index', compact('projects', 'categories', 'filters', 'availableValues', 'breadcrumbs', 'seoData'));
    }

    public function show(string $slug): View
    {
        $project = Project::where('slug', $slug)
            ->where('is_published', true)
            ->select('id', 'title', 'slug', 'main_image', 'description', 'price_from', 'price_to', 'area', 'bedrooms', 'bathrooms', 'is_featured', 'category_id', 'floors', 'has_garage', 'roof_type', 'style', 'meta_title', 'meta_description')
            ->with(['category:id,name,type', 'images:id,project_id,image,sort_order'])
            ->firstOrFail();

        $breadcrumbs = [
            ['name' => __('messages.home'), 'url' => url('/')],
            ['name' => __('admin.projects'), 'url' => route('projects.index')],
            ['name' => $project->title],
        ];

        $seoData = [
            'title' => SeoHelper::pageTitle($project->meta_title ?: $project->title),
            'description' => SeoHelper::metaDescription($project->meta_description ?: $project->description),
            'canonical' => route('projects.show', $project->slug),
            'og_type' => 'product',
            'og_image' => $project->main_image,
        ];

        return view('projects.show', compact('project', 'breadcrumbs', 'seoData'));
    }
}