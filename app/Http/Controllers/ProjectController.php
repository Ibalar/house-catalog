<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\ProjectCategory;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProjectController extends Controller
{
    public function index(Request $request): View
    {
        $projects = Project::where('is_published', true)
            ->with('category', 'images')
            ->orderBy('sort_order')
            ->orderBy('created_at', 'desc')
            ->paginate(12);

        $categories = ProjectCategory::orderBy('name')->get();

        return view('projects.index', compact('projects', 'categories'));
    }

    public function show(string $slug): View
    {
        $project = Project::where('slug', $slug)
            ->where('is_published', true)
            ->with('category', 'images')
            ->firstOrFail();

        return view('projects.show', compact('project'));
    }
}