<?php

namespace App\Http\Controllers;

use App\Models\ExploreSlide;
use App\Models\Template;

class PageController extends Controller
{
    /**
     * Public templates landing page - dynamic templates from database
     */
    public function templates()
    {
        $query = Template::where('is_active', true)
            ->where('is_public', true)
            ->with('creator');

        if ($category = request('category')) {
            $query->where('category', $category);
        }

        if ($price = request('price')) {
            if ($price === 'free') {
                $query->where(function ($q) {
                    $q->whereNull('price')->orWhere('price', 0);
                });
            } elseif ($price === 'paid') {
                $query->where('price', '>', 0);
            }
        }

        if ($pages = request('pages')) {
            if ($pages === '1') {
                $query->where('page_count', 1);
            } elseif ($pages === '2-5') {
                $query->whereBetween('page_count', [2, 5]);
            } elseif ($pages === '6-10') {
                $query->whereBetween('page_count', [6, 10]);
            } elseif ($pages === '11+') {
                $query->where('page_count', '>=', 11);
            }
        }

        if ($q = request('q')) {
            $query->where(function ($qb) use ($q) {
                $qb->where('name', 'like', '%' . $q . '%')
                    ->orWhere('description', 'like', '%' . $q . '%')
                    ->orWhere('short_description', 'like', '%' . $q . '%')
                    ->orWhere('category', 'like', '%' . $q . '%');
            });
        }

        $sort = request('sort', 'newest');
        match ($sort) {
            'oldest' => $query->oldest(),
            'price-low' => $query->orderBy('price', 'asc')->orderBy('id', 'desc'),
            'price-high' => $query->orderBy('price', 'desc')->orderBy('id', 'desc'),
            'name' => $query->orderBy('name', 'asc'),
            default => $query->latest(),
        };

        $templates = $query->limit(48)
            ->get()
            ->map(function ($template) {
                return [
                    'id' => $template->id,
                    'name' => $template->name,
                    'category' => $template->category,
                    'short_description' => $template->short_description ?? $template->description ?? '',
                    'price' => $template->price,
                    'page_count' => $template->page_count,
                    'thumbnail_url' => $template->thumbnail_url,
                ];
            })
            ->values();

        $categories = Template::where('is_active', true)
            ->where('is_public', true)
            ->distinct()
            ->pluck('category')
            ->filter()
            ->sort()
            ->values();

        $slides = ExploreSlide::active()->ordered()->get();

        $filters = [
            'category' => request('category'),
            'price' => request('price', 'all'),
            'pages' => request('pages', 'all'),
            'sort' => request('sort', 'newest'),
            'q' => request('q'),
        ];

        return view('templates', compact('templates', 'categories', 'slides', 'filters'));
    }
}
