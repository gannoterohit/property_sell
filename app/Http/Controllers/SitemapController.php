<?php

namespace App\Http\Controllers;

use App\Models\Room;
use App\Models\CmsPage;
use App\Models\Blog;
use App\Models\City;
use App\Models\Setting;
use Illuminate\Http\Request;

class SitemapController extends Controller
{
    public function index()
    {
        $rooms = Room::publicVisible()
            ->orderBy('updated_at', 'desc')
            ->get(['id', 'slug', 'updated_at']);
        $baseUrl = $this->baseUrl();
        $publicUrl = fn (string $path) => $baseUrl . '/' . ltrim($path, '/');

        $urls = [];
        $urls[] = [
            'loc' => $baseUrl,
            'lastmod' => now()->toAtomString(),
            'changefreq' => 'daily',
            'priority' => '1.0'
        ];

        foreach (CmsPage::where('status', 'published')->orderBy('sort_order')->get() as $page) {
            $urls[] = [
                'loc' => $publicUrl($page->slug),
                'lastmod' => optional($page->updated_at)->toAtomString() ?? now()->toAtomString(),
                'changefreq' => 'monthly',
                'priority' => '0.5',
            ];
        }

        $urls[] = [
            'loc' => $publicUrl('rooms'),
            'lastmod' => now()->toAtomString(),
            'changefreq' => 'daily',
            'priority' => '0.9'
        ];

        $urls[] = [
            'loc' => $publicUrl('buy'),
            'lastmod' => now()->toAtomString(),
            'changefreq' => 'daily',
            'priority' => '0.9'
        ];

        $urls[] = [
            'loc' => $publicUrl('rent'),
            'lastmod' => now()->toAtomString(),
            'changefreq' => 'daily',
            'priority' => '0.9'
        ];

        foreach (City::where('is_active', true)->orderBy('sort_order')->orderBy('name')->get(['slug', 'updated_at']) as $city) {
            $urls[] = [
                'loc' => $publicUrl($city->slug),
                'lastmod' => optional($city->updated_at)->toAtomString() ?? now()->toAtomString(),
                'changefreq' => 'daily',
                'priority' => '0.8',
            ];
        }

        $latestBlog = Blog::published()->latest('updated_at')->first();

        $urls[] = [
            'loc' => $publicUrl('blog'),
            'lastmod' => optional($latestBlog?->updated_at)->toAtomString() ?? now()->toAtomString(),
            'changefreq' => 'weekly',
            'priority' => '0.7',
        ];

        foreach (Blog::published()->orderBy('updated_at', 'desc')->get() as $blog) {
            $urls[] = [
                'loc' => $publicUrl('blog/' . $blog->slug),
                'lastmod' => optional($blog->updated_at)->toAtomString() ?? now()->toAtomString(),
                'changefreq' => 'monthly',
                'priority' => '0.6',
            ];
        }

        foreach ($rooms as $room) {
            $urls[] = [
                'loc' => $publicUrl('rooms/' . ($room->slug ?: $room->id)),
                'lastmod' => optional($room->updated_at)->toAtomString() ?? now()->toAtomString(),
                'changefreq' => 'weekly',
                'priority' => '0.7'
            ];
        }

        return response()->view('sitemap.index', compact('urls'))
            ->header('Content-Type', 'application/xml');
    }

    public function robots()
    {
        $content = "User-agent: *\n";
        $content .= "Allow: /\n";
        $content .= "Disallow: /admin/\n";
        $content .= "Disallow: /owner/\n";
        $content .= "Disallow: /profile/\n";
        $content .= "Disallow: /complaints\n";
        $content .= "Disallow: /api/\n\n";
        $content .= "Sitemap: " . $this->baseUrl() . "/sitemap.xml\n";

        return response($content)->header('Content-Type', 'text/plain');
    }

    private function baseUrl(): string
    {
        $configuredUrl = trim((string) Setting::get('website_url', ''));
        return rtrim($configuredUrl !== '' ? $configuredUrl : url('/'), '/');
    }
}

