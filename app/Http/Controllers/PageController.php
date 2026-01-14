<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Helpers\SeoHelper;
use App\Models\Page;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PageController extends Controller
{
    public function show(string $slug): View
    {
        $page = Page::where('slug', $slug)
            ->where('is_active', true)
            ->firstOrFail();

        $breadcrumbs = [
            ['name' => 'Главная', 'url' => url('/')],
            ['name' => $page->title],
        ];

        $seoData = [
            'title' => SeoHelper::pageTitle($page->meta_title ?: $page->title),
            'description' => SeoHelper::metaDescription($page->meta_description),
            'canonical' => route('page.show', $page->slug),
            'og_type' => 'website',
            'og_image' => null,
        ];

        return view('pages.show', compact('page', 'breadcrumbs', 'seoData'));
    }
}