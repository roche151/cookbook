<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Tag;
use App\Models\Recipe;
use Illuminate\Support\Facades\Auth;
use App\Models\RecipeView;

class HomeController extends Controller
{
    public function index()
    {
        $tags = Tag::orderBy('sort_order')->orderBy('name')->get();

        $limit = 3;
        // Trending: most viewed in last 30 days
        $trendingRecipes = Recipe::where('is_public', true)
            ->withCount(['views as recent_views' => function($query) {
                $query->where('viewed_at', '>=', now()->subDays(30));
            }])
            ->orderByDesc('recent_views')
            ->take($limit)
            ->get();

        // Fallback if not enough trending recipes
        if ($trendingRecipes->count() < $limit) {
            $needed = $limit - $trendingRecipes->count();
            $fallback = Recipe::where('is_public', true)
                ->whereNotIn('id', $trendingRecipes->pluck('id'))
                ->inRandomOrder()
                ->take($needed)
                ->get();
            $trendingRecipes = $trendingRecipes->concat($fallback);
        }

        return view('home', [
            'tags' => $tags,
            'trendingRecipes' => $trendingRecipes,
        ]);
    }
}
