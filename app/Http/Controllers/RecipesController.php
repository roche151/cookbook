<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Recipe;
use App\Models\Category;

class RecipesController extends Controller
{
    public function index(Request $request)
    {
        $q = $request->query('q');
        $category = $request->query('category');

        $query = Recipe::query();

        if ($category) {
            // Allow category filter by slug or id
            $cat = Category::where('slug', $category)->orWhere('id', $category)->first();
            if ($cat) {
                $query->whereHas('categories', function ($qb) use ($cat) {
                    $qb->where('categories.id', $cat->id);
                });
            }
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
        $categories = Category::orderBy('name')->get();
        return view('recipes.create', ['categories' => $categories]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'excerpt' => 'nullable|string',
            'image' => 'nullable|url|max:255',
            'time' => 'nullable|string|max:50',
            'rating' => 'nullable|numeric|min:0|max:5',
            'categories' => 'required|array|min:1',
            'categories.*' => 'integer|exists:categories,id',
        ]);

        $recipe = Recipe::create([
            'title' => $data['title'],
            'slug' => \Illuminate\Support\Str::slug($data['title']) . '-' . \Illuminate\Support\Str::random(5),
            'excerpt' => $data['excerpt'] ?? null,
            'image' => $data['image'] ?? null,
            'time' => $data['time'] ?? null,
            'rating' => isset($data['rating']) ? number_format((float)$data['rating'], 1) : null,
        ]);

        // Attach selected categories
        $recipe->categories()->sync($data['categories']);

        return redirect()->route('recipes.show', $recipe->id)->with('status', 'Recipe created.');
    }
}
