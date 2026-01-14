<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Service;
use App\Models\Project;
use App\Models\Setting;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function index(): View
    {
        $topServices = Service::where('parent_id', null)
            ->where('is_published', true)
            ->orderBy('sort_order')
            ->limit(4)
            ->get();

        $featuredProjects = Project::where('is_published', true)
            ->where('is_featured', true)
            ->with('category', 'images')
            ->limit(6)
            ->get();

        return view('home', compact(['topServices', 'featuredProjects']));
    }
}