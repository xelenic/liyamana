<?php

namespace App\Http\Controllers;

use App\Models\SeoPage;
use App\Models\Setting;
use App\Services\SeoService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminSeoController extends Controller
{
    public function __construct(
        private SeoService $seoService
    ) {}

    public function index(Request $request): View
    {
        $q = trim((string) $request->get('q', ''));

        $pages = SeoPage::query()
            ->when($q !== '', function ($query) use ($q) {
                $query->where(function ($sub) use ($q) {
                    $sub->where('label', 'like', '%'.$q.'%')
                        ->orWhere('page_key', 'like', '%'.$q.'%')
                        ->orWhere('path_hint', 'like', '%'.$q.'%');
                });
            })
            ->orderBy('label')
            ->paginate(25)
            ->withQueryString();

        $pages->getCollection()->transform(function (SeoPage $p) {
            $p->setAttribute('seo_score_value', $this->seoService->score([
                'meta_title' => $p->meta_title,
                'meta_description' => $p->meta_description,
                'meta_keywords' => $p->meta_keywords,
                'og_image' => $p->og_image,
                'canonical_url' => $p->canonical_url,
                'robots' => $p->robots,
                'focus_keyword' => $p->focus_keyword,
            ])['score']);

            return $p;
        });

        $global = [
            'seo_site_title_suffix' => Setting::get('seo_site_title_suffix', ' - '.site_name()),
            'seo_default_meta_description' => Setting::get('seo_default_meta_description', ''),
            'seo_default_og_image' => Setting::get('seo_default_og_image', ''),
            'seo_default_robots' => Setting::get('seo_default_robots', 'index, follow'),
            'seo_twitter_handle' => Setting::get('seo_twitter_handle', ''),
            'seo_google_site_verification' => Setting::get('seo_google_site_verification', ''),
            'seo_bing_site_verification' => Setting::get('seo_bing_site_verification', ''),
            'seo_organization_json_ld' => Setting::get('seo_organization_json_ld', ''),
        ];

        return view('admin.seo.index', compact('pages', 'global', 'q'));
    }

    public function edit(SeoPage $seoPage): View
    {
        $preview = $this->seoService->score([
            'meta_title' => $seoPage->meta_title,
            'meta_description' => $seoPage->meta_description,
            'meta_keywords' => $seoPage->meta_keywords,
            'og_image' => $seoPage->og_image,
            'canonical_url' => $seoPage->canonical_url,
            'robots' => $seoPage->robots,
            'focus_keyword' => $seoPage->focus_keyword,
        ]);

        return view('admin.seo.edit', ['page' => $seoPage, 'preview' => $preview]);
    }

    public function update(Request $request, SeoPage $seoPage): RedirectResponse
    {
        $data = $request->validate([
            'meta_title' => ['nullable', 'string', 'max:255'],
            'meta_description' => ['nullable', 'string', 'max:2000'],
            'meta_keywords' => ['nullable', 'string', 'max:500'],
            'og_title' => ['nullable', 'string', 'max:255'],
            'og_description' => ['nullable', 'string', 'max:2000'],
            'og_image' => ['nullable', 'string', 'max:2048'],
            'twitter_card' => ['nullable', 'in:summary,summary_large_image'],
            'canonical_url' => ['nullable', 'string', 'max:2048'],
            'robots' => ['nullable', 'string', 'max:120'],
            'focus_keyword' => ['nullable', 'string', 'max:120'],
        ]);

        $seoPage->update($data);

        return redirect()->route('admin.seo.edit', $seoPage)->with('success', 'SEO settings saved.');
    }

    public function updateGlobal(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'seo_site_title_suffix' => ['nullable', 'string', 'max:120'],
            'seo_default_meta_description' => ['nullable', 'string', 'max:2000'],
            'seo_default_og_image' => ['nullable', 'string', 'max:2048'],
            'seo_default_robots' => ['nullable', 'string', 'max:120'],
            'seo_twitter_handle' => ['nullable', 'string', 'max:80'],
            'seo_google_site_verification' => ['nullable', 'string', 'max:120'],
            'seo_bing_site_verification' => ['nullable', 'string', 'max:120'],
            'seo_organization_json_ld' => ['nullable', 'string', 'max:10000'],
        ]);

        if (! empty($data['seo_organization_json_ld'])) {
            $decoded = json_decode($data['seo_organization_json_ld'], true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                return redirect()->route('admin.seo.index')->with('error', 'Organization JSON-LD must be valid JSON.');
            }
            if (! is_array($decoded)) {
                return redirect()->route('admin.seo.index')->with('error', 'Organization JSON-LD must be a JSON object or array.');
            }
        }

        foreach ($data as $key => $value) {
            Setting::set($key, $value ?? '', 'seo');
        }

        return redirect()->route('admin.seo.index')->with('success', 'Global SEO settings saved.');
    }

    public function scorePreview(Request $request): JsonResponse
    {
        $input = $request->validate([
            'meta_title' => ['nullable', 'string', 'max:255'],
            'meta_description' => ['nullable', 'string', 'max:2000'],
            'meta_keywords' => ['nullable', 'string', 'max:500'],
            'og_image' => ['nullable', 'string', 'max:2048'],
            'canonical_url' => ['nullable', 'string', 'max:2048'],
            'robots' => ['nullable', 'string', 'max:120'],
            'focus_keyword' => ['nullable', 'string', 'max:120'],
        ]);

        return response()->json($this->seoService->score($input));
    }

    public function syncRegistry(): RedirectResponse
    {
        SeoPage::syncRegistry();

        return redirect()->route('admin.seo.index')->with('success', 'SEO page list synced from config/seo.php (new routes added).');
    }
}
