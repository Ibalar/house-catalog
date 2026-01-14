<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ServiceController extends Controller
{
    public function index(): View
    {
        $services = Service::where('parent_id', null)
            ->with('children')
            ->where('is_published', true)
            ->orderBy('sort_order')
            ->get();

        return view('services.index', compact('services'));
    }

    public function show(string $slug): View
    {
        $service = Service::where('slug', $slug)
            ->where('is_published', true)
            ->with('children')
            ->firstOrFail();

        return view('services.show', compact('service'));
    }
}