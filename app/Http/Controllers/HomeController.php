<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Tag;
use App\Models\Recipe;
use Illuminate\Support\Facades\Auth;
use App\Models\RecipeView;
use Illuminate\Support\Facades\Cache;

class HomeController extends Controller
{
    public function index()
    {

        $tags = Cache::remember('tags.all', 300, function() {
            return Tag::orderBy('sort_order')->orderBy('name')->get();
        });

        $limit = 3;
        $trendingRecipes = Cache::remember('trending_recipes', 300, function() use ($limit) {
            $trending = Recipe::where('is_public', true)
                ->with(['tags', 'user'])
                ->withCount(['views as recent_views' => function($query) {
                    $query->where('viewed_at', '>=', now()->subDays(30));
                }])
                ->orderByDesc('recent_views')
                ->take($limit)
                ->get();
            if ($trending->count() < $limit) {
                $needed = $limit - $trending->count();
                $fallback = Recipe::where('is_public', true)
                    ->whereNotIn('id', $trending->pluck('id'))
                    ->with(['tags', 'user'])
                    ->inRandomOrder()
                    ->take($needed)
                    ->get();
                $trending = $trending->concat($fallback);
            }
            return $trending;
        });

        return view('home', [
            'tags' => $tags,
            'trendingRecipes' => $trendingRecipes,
        ]);
    }
}
