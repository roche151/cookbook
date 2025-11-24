<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Recipe;
use App\Models\Tag;

class RecipesController extends Controller
{
    public function index(Request $request)
    {
        $q = $request->query('q');
        $tag = $request->query('tag');

        $query = Recipe::query();

        // Support ?tag=TagName (case-insensitive), or tag as slug/id
        if ($tag) {
            $normalized = mb_strtolower($tag);
            $tg = \App\Models\Tag::whereRaw('LOWER(name) = ?', [$normalized])
                ->orWhere('slug', $tag)
                ->orWhere('id', $tag)
                ->first();

            if ($tg) {
                $query->whereHas('tags', function ($qb) use ($tg) {
                    $qb->where('tags.id', $tg->id);
                });
            }
        }

        if ($q) {
            $query->where(function ($qbuilder) use ($q) {
                $qbuilder->where('title', 'like', "%{$q}%")
                    ->orWhere('excerpt', 'like', "%{$q}%");
            });
        }

        $recipes = $query->with('tags')->orderBy('created_at', 'desc')->paginate(12)->withQueryString();

        return view('recipes.index', [
            'recipes' => $recipes,
            'q' => $request->query('q'),
            'tag' => $tag,
        ]);
    }

    public function show($id)
    {
        $recipe = Recipe::findOrFail($id);
        return view('recipes.show', ['recipe' => $recipe]);
    }

    public function create()
    {
        $tags = \App\Models\Tag::orderBy('sort_order')->orderBy('name')->get();
        return view('recipes.create', ['tags' => $tags]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'excerpt' => 'nullable|string',
            'image' => 'nullable|url|max:255',
            'time' => 'nullable|string|max:50',
            'rating' => 'nullable|numeric|min:0|max:5',
            'tags' => 'required|array|min:1',
            'tags.*' => 'integer|exists:tags,id',
        ]);

        $recipe = Recipe::create([
            'title' => $data['title'],
            'slug' => \Illuminate\Support\Str::slug($data['title']) . '-' . \Illuminate\Support\Str::random(5),
            'excerpt' => $data['excerpt'] ?? null,
            'image' => $data['image'] ?? null,
            'time' => $data['time'] ?? null,
            'rating' => isset($data['rating']) ? number_format((float)$data['rating'], 1) : null,
        ]);

        // Attach selected tags
        $recipe->tags()->sync($data['tags']);

        return redirect()->route('recipes.show', $recipe->id)->with('status', 'Recipe created.');
    }
}
