<?php

namespace App\Services;

use App\Models\SeoPage;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;

class SeoService
{
    /**
     * @return array{
     *     page_key: string|null,
     *     title: string,
     *     meta_title: string|null,
     *     meta_description: string|null,
     *     meta_keywords: string|null,
     *     og_title: string|null,
     *     og_description: string|null,
     *     og_image: string|null,
     *     twitter_card: string,
     *     canonical_url: string|null,
     *     robots: string,
     *     focus_keyword: string|null,
     *     google_verification: string|null,
     *     bing_verification: string|null,
     *     twitter_handle: string|null,
     *     organization_json_ld: string|null,
     * }
     */
    public function forRequest(Request $request): array
    {
        $routeName = Route::currentRouteName();
        $page = null;
        if (is_string($routeName) && $routeName !== '') {
            $page = SeoPage::query()->where('page_key', $routeName)->first();
        }
        if (! $page) {
            $page = SeoPage::query()->where('page_key', '_default')->first();
        }

        $site = site_name();
        $suffix = trim((string) Setting::get('seo_site_title_suffix', ''));
        if ($suffix === '') {
            $suffix = ' - '.$site;
        }

        $defaultDesc = (string) Setting::get('seo_default_meta_description', '');
        $defaultOg = $this->absoluteUrl((string) Setting::get('seo_default_og_image', ''), $request);

        $metaTitle = $page?->meta_title;
        $title = $metaTitle !== null && $metaTitle !== '' ? $metaTitle.$suffix : $site;

        $metaDescription = $page?->meta_description;
        if ($metaDescription === null || trim($metaDescription) === '') {
            $metaDescription = $defaultDesc !== '' ? $defaultDesc : null;
        }

        $ogTitle = $page?->og_title;
        if ($ogTitle === null || trim($ogTitle) === '') {
            $ogTitle = $metaTitle !== null && $metaTitle !== '' ? $metaTitle : $site;
        }

        $ogDescription = $page?->og_description;
        if ($ogDescription === null || trim($ogDescription) === '') {
            $ogDescription = $metaDescription;
        }

        $ogImage = $page?->og_image;
        if ($ogImage === null || trim($ogImage) === '') {
            $ogImage = $defaultOg !== '' ? $defaultOg : null;
        } else {
            $ogImage = $this->absoluteUrl($ogImage, $request);
        }

        $canonical = $page?->canonical_url;
        if ($canonical !== null && trim($canonical) !== '') {
            $canonical = $this->absoluteUrl($canonical, $request);
        } else {
            $canonical = $request->url();
        }

        $robotsPage = $page?->robots;
        $robotsGlobal = (string) Setting::get('seo_default_robots', 'index, follow');
        $robots = ($robotsPage !== null && trim($robotsPage) !== '') ? trim($robotsPage) : $robotsGlobal;

        return [
            'page_key' => $routeName,
            'title' => $title,
            'meta_title' => $metaTitle,
            'meta_description' => $metaDescription,
            'meta_keywords' => $page?->meta_keywords,
            'og_title' => $ogTitle,
            'og_description' => $ogDescription,
            'og_image' => $ogImage,
            'twitter_card' => $page?->twitter_card ?: 'summary_large_image',
            'canonical_url' => $canonical,
            'robots' => $robots,
            'focus_keyword' => $page?->focus_keyword,
            'google_verification' => Setting::get('seo_google_site_verification'),
            'bing_verification' => Setting::get('seo_bing_site_verification'),
            'twitter_handle' => Setting::get('seo_twitter_handle'),
            'organization_json_ld' => Setting::get('seo_organization_json_ld'),
        ];
    }

    /**
     * Heuristic on-page SEO score (0–100) for admin preview — not a search engine guarantee.
     *
     * @param  array<string, mixed>  $input
     * @return array{score: int, grade: string, checks: array<int, array{ok: bool, label: string, detail: string}>}
     */
    public function score(array $input): array
    {
        $title = trim((string) ($input['meta_title'] ?? ''));
        $desc = trim((string) ($input['meta_description'] ?? ''));
        $kw = trim((string) ($input['meta_keywords'] ?? ''));
        $ogImage = trim((string) ($input['og_image'] ?? ''));
        $canonical = trim((string) ($input['canonical_url'] ?? ''));
        $robots = trim((string) ($input['robots'] ?? ''));
        $focus = strtolower(trim((string) ($input['focus_keyword'] ?? '')));

        $checks = [];
        $points = 0.0;

        $tLen = Str::length($title);
        if ($tLen === 0) {
            $checks[] = ['ok' => false, 'label' => 'Meta title', 'detail' => 'Missing — will use site name only'];
        } elseif ($tLen >= 30 && $tLen <= 65) {
            $points += 20;
            $checks[] = ['ok' => true, 'label' => 'Meta title', 'detail' => "{$tLen} chars — good range (~30–60)"];
        } elseif ($tLen < 30) {
            $points += 10;
            $checks[] = ['ok' => false, 'label' => 'Meta title', 'detail' => "{$tLen} chars — consider expanding toward ~30–60"];
        } else {
            $points += 12;
            $checks[] = ['ok' => false, 'label' => 'Meta title', 'detail' => "{$tLen} chars — may truncate in results"];
        }

        $dLen = Str::length($desc);
        if ($dLen === 0) {
            $checks[] = ['ok' => false, 'label' => 'Meta description', 'detail' => 'Missing — snippets will be auto-generated'];
        } elseif ($dLen >= 120 && $dLen <= 165) {
            $points += 25;
            $checks[] = ['ok' => true, 'label' => 'Meta description', 'detail' => "{$dLen} chars — good range (~120–160)"];
        } elseif ($dLen < 120) {
            $points += 12;
            $checks[] = ['ok' => false, 'label' => 'Meta description', 'detail' => "{$dLen} chars — aim for ~120–160"];
        } else {
            $points += 15;
            $checks[] = ['ok' => false, 'label' => 'Meta description', 'detail' => "{$dLen} chars — may be clipped"];
        }

        $hasOg = filter_var($ogImage, FILTER_VALIDATE_URL) !== false || ($ogImage !== '' && Str::startsWith($ogImage, '/'));
        if ($hasOg) {
            $points += 18;
            $checks[] = ['ok' => true, 'label' => 'Open Graph image', 'detail' => 'URL set for social previews'];
        } else {
            $checks[] = ['ok' => false, 'label' => 'Open Graph image', 'detail' => 'Add an absolute or root-relative image URL'];
        }

        if ($kw !== '') {
            $points += 5;
            $checks[] = ['ok' => true, 'label' => 'Meta keywords', 'detail' => 'Optional; low impact on Google'];
        } else {
            $checks[] = ['ok' => true, 'label' => 'Meta keywords', 'detail' => 'Empty — fine for most sites'];
        }

        if ($canonical !== '') {
            $points += 7;
            $checks[] = ['ok' => true, 'label' => 'Canonical URL', 'detail' => 'Custom canonical set'];
        } else {
            $points += 7;
            $checks[] = ['ok' => true, 'label' => 'Canonical URL', 'detail' => 'Empty — current request URL will be used'];
        }

        if ($robots !== '') {
            $points += 5;
            $checks[] = ['ok' => true, 'label' => 'Robots', 'detail' => $robots];
        } else {
            $checks[] = ['ok' => true, 'label' => 'Robots', 'detail' => 'Using site default from global SEO'];
        }

        if ($focus !== '') {
            $inTitle = $title !== '' && str_contains(strtolower($title), $focus);
            $inDesc = $desc !== '' && str_contains(strtolower($desc), $focus);
            if ($inTitle) {
                $points += 10;
            }
            if ($inDesc) {
                $points += 10;
            }
            $checks[] = ['ok' => $inTitle, 'label' => 'Focus keyword in title', 'detail' => $inTitle ? 'Found' : 'Not found'];
            $checks[] = ['ok' => $inDesc, 'label' => 'Focus keyword in description', 'detail' => $inDesc ? 'Found' : 'Not found'];
        } else {
            $checks[] = ['ok' => true, 'label' => 'Focus keyword', 'detail' => 'Not set — optional hint for this preview'];
        }

        $score = (int) round(max(0, min(100, $points)));

        $grade = match (true) {
            $score >= 85 => 'Strong',
            $score >= 65 => 'Good',
            $score >= 45 => 'Fair',
            default => 'Needs work',
        };

        return [
            'score' => $score,
            'grade' => $grade,
            'checks' => $checks,
        ];
    }

    private function absoluteUrl(string $url, Request $request): string
    {
        $url = trim($url);
        if ($url === '') {
            return '';
        }
        if (Str::startsWith($url, ['http://', 'https://'])) {
            return $url;
        }

        return rtrim($request->getSchemeAndHttpHost(), '/').'/'.ltrim($url, '/');
    }
}
