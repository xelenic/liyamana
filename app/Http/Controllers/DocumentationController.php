<?php

namespace App\Http\Controllers;

use App\Models\Documentation;
use App\Models\DocumentationCategory;

class DocumentationController extends Controller
{
    /**
     * Public documentation index: categories and published docs.
     */
    public function index()
    {
        $categories = DocumentationCategory::active()
            ->ordered()
            ->whereHas('documentations', function ($q) {
                $q->where('is_published', true);
            })
            ->withCount(['documentations' => function ($q) {
                $q->where('is_published', true);
            }])
            ->get();

        $docs = Documentation::published()
            ->ordered()
            ->with('categories')
            ->get();

        return view('docs.index', compact('categories', 'docs'));
    }

    /**
     * Show a single documentation page by slug (published only).
     */
    public function show(string $slug)
    {
        $doc = Documentation::published()
            ->where('slug', $slug)
            ->with('categories')
            ->firstOrFail();

        $categories = DocumentationCategory::active()
            ->ordered()
            ->whereHas('documentations', function ($q) {
                $q->where('is_published', true);
            })
            ->withCount(['documentations' => function ($q) {
                $q->where('is_published', true);
            }])
            ->get();

        $docs = Documentation::published()
            ->ordered()
            ->with('categories')
            ->get();

        return view('docs.show', compact('doc', 'categories', 'docs'));
    }
}
