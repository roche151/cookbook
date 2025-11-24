<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Recipe;
use App\Models\Tag;
use App\Models\Direction;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

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
                    ->orWhere('description', 'like', "%{$q}%");
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
        // Validation rules
        $rules = [
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'image' => 'nullable|url|max:255',
            // individual fields nullable numeric; combined time error added below
            'time_hours' => 'nullable|integer|min:0',
            'time_minutes' => 'nullable|integer|min:0|max:59',
            'tags' => 'required|array|min:1',
            'directions' => 'required|array|min:1',
            'directions.*.body' => 'required|string',
            'directions.*.sort_order' => 'required|integer',
        ];

        // Custom messages and attribute names
        $messages = [
            'required' => ':attribute is required',
            'tags.required' => 'At least one Tag is required',
            'directions.required' => 'At least one Direction is required',
            'directions.*.body.required' => 'Direction cannot be empty',
        ];

        $attributes = [
            'title' => 'Title',
            'description' => 'Description',
            'image' => 'Image URL',
            'time_hours' => 'Time',
            'time_minutes' => 'Time',
            'tags' => 'Tags',
            'directions' => 'Directions',
            'directions.*.body' => 'Direction',
        ];

        $validator = Validator::make($request->all(), $rules, $messages, $attributes);

        // Combined time requirement
        $validator->after(function ($v) use ($request) {
            if (! $request->filled('time_hours') && ! $request->filled('time_minutes')) {
                $v->errors()->add('time', 'Time is required');
            }
        });

        if ($validator->fails()) {
            // Reorder errors to follow the order of $rules, inserting 'time' before 'time_hours'
            $orig = $validator->errors()->getMessages();
            $ordered = new \Illuminate\Support\MessageBag();

            $orderedKeys = array_keys($rules);
            $pos = array_search('time_hours', $orderedKeys, true);
            if ($pos !== false) {
                array_splice($orderedKeys, $pos, 0, ['time']);
            } else {
                array_unshift($orderedKeys, 'time');
            }

            $added = [];
            foreach ($orderedKeys as $key) {
                if (isset($orig[$key])) {
                    foreach ($orig[$key] as $m) {
                        $ordered->add($key, $m);
                    }
                    $added[] = $key;
                }

                // include child keys immediately after their parent (e.g., directions.0.body)
                if ($key === 'tags' || $key === 'directions') {
                    foreach ($orig as $k2 => $msgs2) {
                        if (in_array($k2, $added, true)) continue;
                        if (strpos($k2, $key . '.') === 0) {
                            foreach ($msgs2 as $m2) {
                                $ordered->add($k2, $m2);
                            }
                            $added[] = $k2;
                        }
                    }
                }
            }

            // append any remaining messages
            foreach ($orig as $k => $msgs) {
                if (in_array($k, $added, true)) continue;
                foreach ($msgs as $m) {
                    $ordered->add($k, $m);
                }
            }

            return redirect()->back()->withErrors($ordered)->withInput();
        }

        $data = $validator->validated();

        // Save total minutes into the existing `time` column (as integer)
        $hours = isset($data['time_hours']) ? (int)$data['time_hours'] : 0;
        $minutes = isset($data['time_minutes']) ? (int)$data['time_minutes'] : 0;
        $totalMinutes = ($hours * 60) + $minutes;
        $data['time'] = $totalMinutes > 0 ? $totalMinutes : null;

        $recipe = null;

        DB::transaction(function () use ($data, &$recipe) {
            $recipe = Recipe::create([
                'title' => $data['title'],
                'slug' => \Illuminate\Support\Str::slug($data['title']) . '-' . \Illuminate\Support\Str::random(5),
                'description' => $data['description'] ?? null,
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
        // Validation rules
        $rules = [
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'image' => 'nullable|url|max:255',
            // individual fields nullable numeric; combined time error added below
            'time_hours' => 'nullable|integer|min:0',
            'time_minutes' => 'nullable|integer|min:0|max:59',
            'tags' => 'required|array|min:1',
            'tags.*' => 'exists:tags,id',
            'directions' => 'required|array|min:1',
            'directions.*.id' => 'nullable|integer|exists:directions,id',
            'directions.*.body' => 'required|string',
            'directions.*.sort_order' => 'required|integer',
        ];

        $messages = [
            'required' => ':attribute is required.',
            'tags.required' => 'At least 1 Tag is required.',
            'directions.required' => 'At least 1 Direction is required.',
            'directions.*.body.required' => 'Direction cannot be empty.',
        ];

        $attributes = [
            'title' => 'Title',
            'description' => 'Description',
            'image' => 'Image URL',
            'time_hours' => 'Time',
            'time_minutes' => 'Time',
            'tags' => 'Tags',
            'directions' => 'Directions',
            'directions.*.body' => 'Direction',
        ];

        $validator = Validator::make($request->all(), $rules, $messages, $attributes);
        $validator->after(function ($v) use ($request) {
            if (! $request->filled('time_hours') && ! $request->filled('time_minutes')) {
                $v->errors()->add('time', 'Time is required.');
            }
        });

        if ($validator->fails()) {
            // Reorder errors to follow the order of $rules, inserting 'time' before 'time_hours'
            $orig = $validator->errors()->getMessages();
            $ordered = new \Illuminate\Support\MessageBag();

            $orderedKeys = array_keys($rules);
            $pos = array_search('time_hours', $orderedKeys, true);
            if ($pos !== false) {
                array_splice($orderedKeys, $pos, 0, ['time']);
            } else {
                array_unshift($orderedKeys, 'time');
            }

            $added = [];
            foreach ($orderedKeys as $key) {
                if (isset($orig[$key])) {
                    foreach ($orig[$key] as $m) {
                        $ordered->add($key, $m);
                    }
                    $added[] = $key;
                }

                if ($key === 'tags' || $key === 'directions') {
                    foreach ($orig as $k2 => $msgs2) {
                        if (in_array($k2, $added, true)) continue;
                        if (strpos($k2, $key . '.') === 0) {
                            foreach ($msgs2 as $m2) {
                                $ordered->add($k2, $m2);
                            }
                            $added[] = $k2;
                        }
                    }
                }
            }

            foreach ($orig as $k => $msgs) {
                if (in_array($k, $added, true)) continue;
                foreach ($msgs as $m) {
                    $ordered->add($k, $m);
                }
            }

            return redirect()->back()->withErrors($ordered)->withInput();
        }

        $data = $validator->validated();

        // Save total minutes into the existing `time` column (as integer)
        $hours = isset($data['time_hours']) ? (int)$data['time_hours'] : 0;
        $minutes = isset($data['time_minutes']) ? (int)$data['time_minutes'] : 0;
        $totalMinutes = ($hours * 60) + $minutes;
        $data['time'] = $totalMinutes > 0 ? $totalMinutes : null;

        DB::transaction(function () use ($data, $recipe) {
            $recipe->update([
                'title' => $data['title'],
                'description' => $data['description'] ?? null,
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
