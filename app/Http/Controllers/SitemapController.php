<?php

namespace App\Http\Controllers;

use Illuminate\Http\Response;
use Illuminate\Support\Facades\URL;
use App\Models\Recipe;

class SitemapController extends Controller
{
    public function index()
    {
        $urls = [];
        $urls[] = [
            'loc' => url('/'),
            'lastmod' => now()->toDateString(),
            'changefreq' => 'daily',
            'priority' => '1.0',
        ];
        $urls[] = [
            'loc' => url('/recipes'),
            'lastmod' => now()->toDateString(),
            'changefreq' => 'daily',
            'priority' => '0.9',
        ];
        // Add all public recipes
        $recipes = Recipe::where('is_public', true)->where('status', 'approved')->get();
        foreach ($recipes as $recipe) {
            $urls[] = [
                'loc' => route('recipes.show', $recipe),
                'lastmod' => optional($recipe->updated_at)->toDateString() ?? now()->toDateString(),
                'changefreq' => 'weekly',
                'priority' => '0.8',
            ];
        }
        // Debug: If no URLs, return a message
        if (empty($urls)) {
            return response('No URLs generated for sitemap', 200)->header('Content-Type', 'text/plain');
        }
        $xml = view('sitemap.xml', ['urls' => $urls]);
        return response($xml, 200)->header('Content-Type', 'application/xml');
    }
}
