<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Collection;
use App\Models\Recipe;
use Illuminate\Support\Facades\Cache;
use App\Models\Tag;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use App\Rules\NoLinks;

class CollectionsController extends Controller
{
    /**
     * Display a listing of the user's collections.
     */
    public function index()
    {

        $collections = Collection::where('user_id', Auth::id())
            ->with(['recipes' => function ($query) {
                $query->with(['tags', 'user', 'ingredients', 'ratings'])->limit(4);
            }])
            ->withCount('recipes')
            ->orderBy('created_at', 'asc')
            ->get();

        return view('collections.index', compact('collections'));
    }

    /**
     * Show a single collection with its recipes.
     */
    public function show(Request $request, Collection $collection)
    {
        // Verify ownership
        if ($collection->user_id !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }

        $q = $request->query('q');
        $tags = $request->query('tags', []);
        $sort = $request->query('sort', 'date_desc');
        $ratingMin = (int) $request->query('rating_min', 0);

        $query = $collection->recipes();

        // Filter by tags
        if (!empty($tags) && is_array($tags)) {
            foreach ($tags as $tagId) {
                $query->whereHas('tags', function ($qb) use ($tagId) {
                    $qb->where('tags.id', $tagId);
                });
            }
        }

        // Filter by minimum rating
        if ($ratingMin > 0) {
            $query->withAvg('ratings', 'rating')
                ->whereRaw('(
                    select avg("recipe_ratings"."rating")
                    from "recipe_ratings"
                    where "recipes"."id" = "recipe_ratings"."recipe_id"
                ) >= ?', [$ratingMin]);
        }

        // Search
        if ($q) {
            $query->where(function ($qbuilder) use ($q) {
                $qbuilder->where('title', 'like', "%{$q}%")
                    ->orWhere('description', 'like', "%{$q}%")
                    ->orWhereHas('ingredients', function ($iq) use ($q) {
                        $iq->where('name', 'like', "%{$q}%");
                    });
            });
        }

        // Apply sorting
        switch ($sort) {
            case 'date_asc':
                $query->orderBy('collection_recipe.created_at', 'asc');
                break;
            case 'rating_desc':
                $query->withAvg('ratings', 'rating')
                    ->orderBy('ratings_avg_rating', 'desc');
                break;
            case 'time_asc':
                $query->orderBy('time', 'asc');
                break;
            case 'time_desc':
                $query->orderBy('time', 'desc');
                break;
            case 'title_asc':
                $query->orderBy('title', 'asc');
                break;
            case 'title_desc':
                $query->orderBy('title', 'desc');
                break;
            case 'date_desc':
            default:
                $query->orderBy('collection_recipe.created_at', 'desc');
                break;
        }

        $recipes = $query->with(['tags', 'user', 'ingredients', 'ratings'])->paginate(12)->withQueryString();
        $allTags = \Cache::remember('tags.all', 300, function() {
            return Tag::orderBy('sort_order')->orderBy('name')->get();
        });

        return view('collections.show', compact('collection', 'recipes', 'allTags', 'q', 'tags', 'sort', 'ratingMin'));
    }

    /**
     * Store a newly created collection.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:255', new NoLinks],
            'description' => ['nullable', 'string', new NoLinks],
        ]);

        if ($validator->fails()) {
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }
            return back()->withErrors($validator)->withInput();
        }

        $collection = Collection::create([
            'user_id' => Auth::id(),
            'name' => $request->name,
            'description' => $request->description,
        ]);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Collection created successfully',
                'collection' => [
                    'id' => $collection->id,
                    'name' => $collection->name,
                    'slug' => $collection->slug,
                ]
            ]);
        }

        return redirect()->route('collections.show', $collection->slug)
            ->with('success', 'Collection created successfully');
    }

    /**
     * Update the specified collection.
     */
    public function update(Request $request, Collection $collection)
    {
        // Verify ownership
        if ($collection->user_id !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }

        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:255', new NoLinks],
            'description' => ['nullable', 'string', new NoLinks],
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $collection->update([
            'name' => $request->name,
            'description' => $request->description,
        ]);

        return back()->with('success', 'Collection updated successfully');
    }

    /**
     * Remove the specified collection.
     */
    public function destroy(Collection $collection)
    {
        // Verify ownership
        if ($collection->user_id !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }

        $collection->delete();

        if (request()->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Collection deleted successfully'
            ]);
        }

        return redirect()->route('collections.index')
            ->with('success', 'Collection deleted successfully');
    }

    /**
     * Add a recipe to a collection.
     */
    public function addRecipe(Request $request, Recipe $recipe)
    {
        $validator = Validator::make($request->all(), [
            'collection_id' => 'required|exists:collections,id',
        ]);

        if ($validator->fails()) {
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }
            return back()->withErrors($validator);
        }

        $collection = Collection::findOrFail($request->collection_id);

        // Verify ownership
        if ($collection->user_id !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }

        // Check if already in collection
        if ($collection->recipes()->where('recipe_id', $recipe->id)->exists()) {
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Recipe is already in this collection'
                ], 400);
            }
            return back()->with('error', 'Recipe is already in this collection');
        }

        $collection->recipes()->attach($recipe->id);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Recipe added to ' . $collection->name
            ]);
        }

        return back()->with('success', 'Recipe added to ' . $collection->name);
    }

    /**
     * Remove a recipe from a collection.
     */
    public function removeRecipe(Collection $collection, Recipe $recipe)
    {
        // Verify ownership
        if ($collection->user_id !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }

        $collection->recipes()->detach($recipe->id);

        if (request()->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Recipe removed from collection'
            ]);
        }

        return back()->with('success', 'Recipe removed from collection');
    }

    /**
     * Get user's collections for a specific recipe (for modal).
     */
    public function forRecipe(Recipe $recipe)
    {
        $collections = Collection::where('user_id', Auth::id())
            ->orderBy('name')
            ->get()
            ->map(function ($collection) use ($recipe) {
                return [
                    'id' => $collection->id,
                    'name' => $collection->name,
                    'slug' => $collection->slug,
                    'has_recipe' => $collection->recipes()->where('recipe_id', $recipe->id)->exists(),
                ];
            });

        return response()->json([
            'success' => true,
            'collections' => $collections
        ]);
    }
}
