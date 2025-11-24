<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Recipe;
use App\Models\Tag;
use App\Models\Direction;
use Illuminate\Support\Facades\DB;

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

    public function show(Recipe $recipe)
    {
        return view('recipes.show', ['recipe' => $recipe]);
    }

    public function create()
    {
        $tags = \App\Models\Tag::orderBy('sort_order')->orderBy('name')->get();
        return view('recipes.create', ['tags' => $tags]);
    }

    public function edit(Recipe $recipe)
    {
        $recipe->load('tags');
        $tags = \App\Models\Tag::orderBy('sort_order')->orderBy('name')->get();
        return view('recipes.edit', compact('recipe', 'tags'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'excerpt' => 'required|string',
            'image' => 'nullable|url|max:255',
            'time' => 'nullable|string|max:50',
            'rating' => 'nullable|numeric|min:0|max:5',
            'tags' => 'required|array|min:1',
            'directions' => 'nullable|array',
            'directions.*.body' => 'required|string',
            'directions.*.sort_order' => 'nullable|integer',
        ]);

        $recipe = null;

        DB::transaction(function () use ($data, &$recipe) {
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

            // Create directions if provided. Use recipe's created_at for direction timestamps.
            if (!empty($data['directions']) && is_array($data['directions'])) {
                $ts = $recipe->created_at;
                foreach ($data['directions'] as $d) {
                    $dir = $recipe->directions()->create([
                        'body' => $d['body'],
                        'sort_order' => isset($d['sort_order']) ? (int)$d['sort_order'] : 0,
                    ]);
                    // Force timestamps to match recipe
                    $dir->timestamps = false;
                    $dir->created_at = $ts;
                    $dir->updated_at = $ts;
                    $dir->save();
                }
            }
        });

        /** @var \App\Models\Recipe $recipe */
        return redirect()->route('recipes.show', $recipe->id)->with('status', 'Recipe created.');
    }

    public function update(Request $request, Recipe $recipe)
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'excerpt' => 'required|string',
            'image' => 'nullable|url|max:255',
            'time' => 'nullable|string|max:50',
            'rating' => 'nullable|numeric|min:0|max:5',
            'tags' => 'required|array|min:1',
            'tags.*' => 'exists:tags,id',
            'directions' => 'nullable|array',
            'directions.*.id' => 'nullable|integer|exists:directions,id',
            'directions.*.body' => 'required|string',
            'directions.*.sort_order' => 'nullable|integer',
        ]);

        DB::transaction(function () use ($data, $recipe) {
            $recipe->update([
                'title' => $data['title'],
                'excerpt' => $data['excerpt'] ?? null,
                'image' => $data['image'] ?? null,
                'time' => $data['time'] ?? null,
                'rating' => isset($data['rating']) ? number_format((float)$data['rating'], 1) : null,
            ]);

            // Sync tags via pivot
            $recipe->tags()->sync($data['tags']);

            // Process directions: create, update, reorder, and delete missing ones.
            $incoming = collect($data['directions'] ?? []);
            $incomingIds = $incoming->pluck('id')->filter()->all();

            // Delete directions not present in incoming payload
            if (!empty($incomingIds)) {
                $recipe->directions()->whereNotIn('id', $incomingIds)->delete();
            } else {
                // If no incoming directions, remove all
                $recipe->directions()->delete();
            }

            $ts = $recipe->updated_at;

            foreach ($incoming as $d) {
                if (!empty($d['id'])) {
                    $dir = Direction::where('id', $d['id'])->where('recipe_id', $recipe->id)->first();
                    if ($dir) {
                        $dir->body = $d['body'];
                        $dir->sort_order = isset($d['sort_order']) ? (int)$d['sort_order'] : 0;
                        $dir->timestamps = false;
                        $dir->created_at = $ts;
                        $dir->updated_at = $ts;
                        $dir->save();
                    }
                } else {
                    $dir = $recipe->directions()->create([
                        'body' => $d['body'],
                        'sort_order' => isset($d['sort_order']) ? (int)$d['sort_order'] : 0,
                    ]);
                    $dir->timestamps = false;
                    $dir->created_at = $ts;
                    $dir->updated_at = $ts;
                    $dir->save();
                }
            }
        });

        return redirect()->route('recipes.show', $recipe->id)->with('status', 'Recipe updated.');
    }

    public function destroy(Recipe $recipe)
    {
        // Detach all tags first (pivot cleanup), then delete the recipe
        $recipe->tags()->detach();
        $recipe->delete();

        return redirect()->route('recipes.index')->with('status', 'Recipe deleted.');
    }
}
