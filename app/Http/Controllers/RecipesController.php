<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Recipe;

class RecipesController extends Controller
{
    public function index(Request $request)
    {
        $q = $request->query('q');
        $category = $request->query('category');

        $query = Recipe::query();

        if ($category) {
            $query->where('category', $category);
        }

        if ($q) {
            $query->where(function ($qbuilder) use ($q) {
                $qbuilder->where('title', 'like', "%{$q}%")
                    ->orWhere('excerpt', 'like', "%{$q}%");
            });
        }

        $recipes = $query->orderBy('created_at', 'desc')->paginate(12)->withQueryString();

        return view('recipes.index', [
            'recipes' => $recipes,
            'q' => $request->query('q'),
            'category' => $category,
        ]);
    }

    public function show($id)
    {
        $recipe = Recipe::findOrFail($id);
        return view('recipes.show', ['recipe' => $recipe]);
    }

    public function create()
    {
        return view('recipes.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'category' => 'required|string|max:50',
            'excerpt' => 'nullable|string',
        ]);

        $recipe = Recipe::create([
            'title' => $data['title'],
            'slug' => \Illuminate\Support\Str::slug($data['title']) . '-' . \Illuminate\Support\Str::random(5),
            'category' => $data['category'],
            'excerpt' => $data['excerpt'] ?? null,
        ]);

        return redirect()->route('recipes.show', $recipe->id)->with('status', 'Recipe created.');
    }
}
