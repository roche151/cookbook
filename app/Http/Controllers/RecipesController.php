<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Recipe;
use App\Models\Tag;
use App\Models\Direction;
use App\Models\Ingredient;
use App\Models\RecipeRating;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Barryvdh\DomPDF\Facade\Pdf;

class RecipesController extends Controller
{
    public function index(Request $request)
    {
        return $this->listRecipes($request, 'all');
    }

    public function myRecipes(Request $request)
    {
        return $this->listRecipes($request, 'my');
    }

    public function myFavorites(Request $request)
    {
        return $this->listRecipes($request, 'favorites');
    }

    private function listRecipes(Request $request, string $context = 'all')
    {
        $q = $request->query('q');
        $tags = $request->query('tags', []);
        $sort = $request->query('sort', 'date_desc');

        // Support legacy ?tag=name parameter - convert to tags[] array
        if ($request->has('tag') && !empty($request->query('tag'))) {
            $legacyTag = $request->query('tag');
            $normalized = mb_strtolower($legacyTag);
            $foundTag = Tag::whereRaw('LOWER(name) = ?', [$normalized])
                ->orWhere('slug', $legacyTag)
                ->orWhere('id', $legacyTag)
                ->first();
            
            if ($foundTag && !in_array($foundTag->id, $tags)) {
                $tags[] = $foundTag->id;
            }
        }

        // Initialize query based on context
        if ($context === 'my') {
            $query = Recipe::where('user_id', Auth::id());
            $title = 'My Recipes';
            $subtitle = 'View and manage all your created recipes';
            $emptyMessage = 'You haven\'t created any recipes yet.';
        } elseif ($context === 'favorites') {
            $user = Auth::user();
            if (!$user) {
                abort(403, 'Unauthorized action.');
            }
            $query = $user->favoriteRecipes();
            $title = 'My Favorite Recipes';
            $subtitle = 'Your collection of saved and loved recipes';
            $emptyMessage = 'You haven\'t favorited any recipes yet.';
        } else {
            $query = Recipe::query();
            // Only show public recipes or recipes owned by the authenticated user
            if (Auth::check()) {
                $query->where(function ($qb) {
                    $qb->where('is_public', true)
                        ->orWhere('user_id', Auth::id());
                });
            } else {
                $query->where('is_public', true);
            }
            $title = 'All Recipes';
            $subtitle = 'Discover and explore delicious recipes';
            $emptyMessage = 'No recipes found.';
        }

        // Filter by tags (support multiple)
        if (!empty($tags) && is_array($tags)) {
            foreach ($tags as $tagId) {
                $query->whereHas('tags', function ($qb) use ($tagId) {
                    $qb->where('tags.id', $tagId);
                });
            }
        }

        // Search across multiple fields
        if ($q) {
            $query->where(function ($qbuilder) use ($q) {
                $qbuilder->where('title', 'like', "%{$q}%")
                    ->orWhere('description', 'like', "%{$q}%")
                    ->orWhereHas('ingredients', function ($iq) use ($q) {
                        $iq->where('name', 'like', "%{$q}%");
                    });
            });
            $emptyMessage = 'No recipes found matching "' . $q . '".';
        }

        // Apply sorting
        switch ($sort) {
            case 'date_asc':
                $query->orderBy('created_at', 'asc');
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
                $query->orderBy('created_at', 'desc');
                break;
        }

        $recipes = $query->with('tags')->paginate(12)->withQueryString();

        // Get all tags for filter
        $allTags = Tag::orderBy('sort_order')->orderBy('name')->get();

        return view('recipes.index', [
            'recipes' => $recipes,
            'q' => $q,
            'selectedTags' => $tags,
            'allTags' => $allTags,
            'sort' => $sort,
            'title' => $title,
            'subtitle' => $subtitle ?? 'Discover and explore delicious recipes',
            'emptyMessage' => $emptyMessage,
        ]);
    }

    public function toggleFavorite(Recipe $recipe)
    {
        /** @var \App\Models\User|null $user */
        $user = Auth::user();
        if (! $user) {
            abort(403, 'Unauthorized action.');
        }
        $favorites = $user->favoriteRecipes();
        $isFavorited = $favorites->where('recipe_id', $recipe->id)->exists();
        
        if ($isFavorited) {
            $favorites->detach($recipe->id);
            $message = 'Recipe removed from favorites';
            $favorited = false;
        } else {
            $favorites->attach($recipe->id);
            $message = 'Recipe added to favorites';
            $favorited = true;
        }

        if (request()->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => $message,
                'favorited' => $favorited
            ]);
        }

        return back()->with('success', $message);
    }

    public function storeRating(Request $request, Recipe $recipe)
    {
        $user = auth()->user();

        // Prevent owner from rating their own recipe
        if ($recipe->user_id === $user->id) {
            return back()->with('error', 'You cannot rate your own recipe.');
        }

        $validated = $request->validate([
            'rating' => 'required|integer|min:1|max:5',
        ]);

        // Update or create the rating
        RecipeRating::updateOrCreate(
            [
                'user_id' => $user->id,
                'recipe_id' => $recipe->id,
            ],
            [
                'rating' => $validated['rating'],
            ]
        );

        return back()->with('success', 'Rating submitted successfully!');
    }

    public function show(Recipe $recipe)
    {
        // Check if the recipe is private and user is not the owner
        if (!$recipe->is_public && (!Auth::check() || $recipe->user_id !== Auth::id())) {
            abort(403, 'This recipe is private.');
        }

        return view('recipes.show', ['recipe' => $recipe]);
    }

    public function downloadPdf(Recipe $recipe)
    {
        // Check if the recipe is private and user is not the owner
        if (!$recipe->is_public && (!Auth::check() || $recipe->user_id !== Auth::id())) {
            abort(403, 'This recipe is private.');
        }

        $pdf = Pdf::loadView('recipes.pdf', ['recipe' => $recipe])
            ->setPaper('a4', 'portrait');
        
        $filename = \Illuminate\Support\Str::slug($recipe->title) . '-recipe.pdf';
        
        return $pdf->stream($filename);
    }

    public function create()
    {
        $tags = \App\Models\Tag::orderBy('sort_order')->orderBy('name')->get();
        return view('recipes.create', ['tags' => $tags]);
    }

    public function importFromUrl(Request $request)
    {
        $request->validate([
            'url' => 'required|url'
        ]);

        try {
            $url = $request->input('url');
            $response = Http::timeout(10)->get($url);
            $html = $response->body();
            
            // Parse JSON-LD structured data
            $recipe = $this->parseJsonLD($html);
            
            if (!$recipe) {
                // Log failed import to database
                \App\Models\FailedRecipeImport::create([
                    'user_id' => auth()->id(),
                    'url' => $url,
                    'error_message' => 'Could not find recipe data on this page',
                    'http_status' => $response->status(),
                    'details' => null,
                ]);
                
                return response()->json(['message' => 'Could not find recipe data on this page'], 422);
            }
            
            if ($recipe) {
                $recipe['sourceUrl'] = $url;
            }
            return response()->json($recipe);
            
        } catch (\Exception $e) {
            // Log failed import to database
            \App\Models\FailedRecipeImport::create([
                'user_id' => auth()->id(),
                'url' => $request->input('url'),
                'error_message' => $e->getMessage(),
                'http_status' => null,
                'details' => $e->getTraceAsString(),
            ]);
            
            return response()->json(['message' => 'Failed to fetch recipe: ' . $e->getMessage()], 500);
        }
    }

    private function parseJsonLD($html)
    {
        // Look for JSON-LD scripts (with or without type attribute)
        preg_match_all('/<script[^>]*>(.*?)<\/script>/is', $html, $allScripts);
        
        $jsonLDScripts = [];
        
        // First, check for proper application/ld+json scripts
        preg_match_all('/<script type="application\/ld\+json">(.*?)<\/script>/is', $html, $matches);
        if (!empty($matches[1])) {
            $jsonLDScripts = array_merge($jsonLDScripts, $matches[1]);
        }
        
        // Also check regular script tags that contain Schema.org Recipe data
        foreach ($allScripts[1] as $idx => $script) {
            $trimmed = trim($script);
            if (empty($trimmed)) continue;
            
            // Check if it looks like JSON and contains Recipe type
            if (preg_match('/^\s*[\[\{]/', $trimmed) && 
                (strpos($trimmed, '"@type":"Recipe"') !== false || 
                 strpos($trimmed, '"@type": "Recipe"') !== false)) {
                $jsonLDScripts[] = $trimmed;
            }
        }
        
        if (empty($jsonLDScripts)) {
            return null;
        }
        
        // Try each JSON block
        foreach ($jsonLDScripts as $jsonString) {
            try {
                $json = json_decode($jsonString, true);
                
                if (!$json) continue;
                
                // Try to find a Recipe object
                $recipe = $this->findRecipeInJson($json);
                
                if ($recipe) {
                    return $this->extractRecipeData($recipe);
                }
            } catch (\Exception $e) {
                continue;
            }
        }
        
        return null;
    }
    
    private function findRecipeInJson($json)
    {
        // Case 1: Direct Recipe object
        if (isset($json['@type']) && $json['@type'] === 'Recipe') {
            return $json;
        }
        
        // Case 2: @graph array at root
        if (isset($json['@graph']) && is_array($json['@graph'])) {
            foreach ($json['@graph'] as $item) {
                if (is_array($item) && isset($item['@type']) && $item['@type'] === 'Recipe') {
                    return $item;
                }
            }
        }
        
        // Case 3: Array of objects (check each object)
        if (is_array($json)) {
            foreach ($json as $item) {
                if (!is_array($item)) continue;
                
                // Check if item is a Recipe directly
                if (isset($item['@type']) && $item['@type'] === 'Recipe') {
                    return $item;
                }
                
                // Check if item has @graph with Recipe
                if (isset($item['@graph']) && is_array($item['@graph'])) {
                    foreach ($item['@graph'] as $graphItem) {
                        if (is_array($graphItem) && isset($graphItem['@type']) && $graphItem['@type'] === 'Recipe') {
                            return $graphItem;
                        }
                    }
                }
            }
        }
        
        return null;
    }
    
    private function extractRecipeData($json)
    {
        // Extract ingredients
        $ingredients = [];
        if (isset($json['recipeIngredient'])) {
            foreach ((array)$json['recipeIngredient'] as $ing) {
                $ing = trim($ing);
                // Try to separate amount from name using regex
                if (preg_match('/^([\d\/\.\s]+(?:g|kg|ml|l|cup|cups|tbsp|tsp|oz|lb|lbs|handful|pinch|clove|cloves)?)\s+(.+)$/i', $ing, $parts)) {
                    $ingredients[] = [
                        'amount' => trim($parts[1]),
                        'name' => trim($parts[2])
                    ];
                } else {
                    $ingredients[] = [
                        'amount' => '',
                        'name' => $ing
                    ];
                }
            }
        }
        
        // Extract directions
        $directions = [];
        if (isset($json['recipeInstructions'])) {
            $instructions = $json['recipeInstructions'];
            
            // Handle different instruction formats
            if (is_string($instructions)) {
                $steps = preg_split('/\n+|\d+\.\s*/', $instructions);
                foreach ($steps as $step) {
                    $step = trim($step);
                    if (!empty($step)) {
                        $directions[] = $step;
                    }
                }
            } elseif (is_array($instructions)) {
                foreach ($instructions as $instruction) {
                    if (is_string($instruction)) {
                        $directions[] = trim($instruction);
                    } elseif (isset($instruction['text'])) {
                        $directions[] = trim($instruction['text']);
                    } elseif (isset($instruction['itemListElement'])) {
                        foreach ((array)$instruction['itemListElement'] as $step) {
                            if (isset($step['text'])) {
                                $directions[] = trim($step['text']);
                            }
                        }
                    }
                }
            }
        }
        
        // Extract time (convert ISO 8601 duration to minutes)
        $time = null;
        if (isset($json['totalTime'])) {
            $time = $this->parseDuration($json['totalTime']);
        } elseif (isset($json['cookTime'])) {
            $time = $this->parseDuration($json['cookTime']);
        } elseif (isset($json['prepTime'])) {
            $time = $this->parseDuration($json['prepTime']);
        }
        
        // Extract image URL
        $imageUrl = null;
        if (isset($json['image'])) {
            $image = $json['image'];
            // Image can be a string URL or an object with url property
            if (is_string($image)) {
                $imageUrl = $image;
            } elseif (is_array($image) && isset($image['url'])) {
                $imageUrl = $image['url'];
            } elseif (is_array($image) && !empty($image)) {
                // Could be array of images, take first one
                $firstImage = reset($image);
                if (is_array($firstImage) && isset($firstImage['url'])) {
                    $imageUrl = $firstImage['url'];
                } elseif (is_string($firstImage)) {
                    $imageUrl = $firstImage;
                }
            }
        }
        
        return [
            'title' => $json['name'] ?? '',
            'description' => $json['description'] ?? '',
            'time' => $time,
            'ingredients' => $ingredients,
            'directions' => $directions,
            'imageUrl' => $imageUrl,
        ];
    }

    private function parseDuration($duration)
    {
        // Parse ISO 8601 duration (e.g., PT30M, PT1H30M)
        if (!preg_match('/PT(?:(\d+)H)?(?:(\d+)M)?/', $duration, $matches)) {
            return null;
        }
        
        $hours = isset($matches[1]) ? (int)$matches[1] : 0;
        $minutes = isset($matches[2]) ? (int)$matches[2] : 0;
        
        return ($hours * 60) + $minutes;
    }

    public function edit(Recipe $recipe)
    {
        // Check if user owns the recipe
        if ($recipe->user_id !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }

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
            'image' => 'nullable|image|mimes:jpeg,jpg,png,gif,webp|max:5120',
            // individual fields nullable numeric; combined time error added below
            'time_hours' => 'nullable|integer|min:0',
            'time_minutes' => 'nullable|integer|min:0|max:59',
            'tags' => 'required|array|min:1',
            'directions' => 'required|array|min:1',
            'ingredients' => 'required|array|min:1',
            'ingredients.*.name' => 'required|string',
            'ingredients.*.amount' => 'nullable|string',
            'ingredients.*.sort_order' => 'required|integer',
            'directions.*.body' => 'required|string',
            'directions.*.sort_order' => 'required|integer',
            'is_public' => 'nullable|boolean',
            'source_url' => 'nullable|url',
            'imported_image_url' => 'nullable|url',
        ];

        // Custom messages and attribute names
        $messages = [
            'required' => ':attribute is required',
            'tags.required' => 'At least one Tag is required',
            'directions.required' => 'At least one Direction is required',
            'directions.*.body.required' => 'Direction cannot be empty',
            'ingredients.*.name.required' => 'Ingredient cannot be empty',
            'ingredients.*.amount.required' => 'Ingredient amount cannot be empty',
            'ingredients.*.amount.min' => 'Ingredient amount cannot be empty',
        ];

        $attributes = [
            'title' => 'Title',
            'description' => 'Description',
            'image' => 'Image',
            'time_hours' => 'Time',
            'time_minutes' => 'Time',
            'tags' => 'Tags',
            'directions' => 'Directions',
            'directions.*.body' => 'Direction',
            'ingredients' => 'Ingredients',
            'ingredients.*.name' => 'Ingredient',
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
                if ($key === 'tags' || $key === 'directions' || $key === 'ingredients') {
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

            // Store uploaded image temporarily if validation fails
            if ($request->hasFile('image')) {
                $tempPath = $request->file('image')->store('temp', 'public');
                session()->flash('temp_image', $tempPath);
            }

            return redirect()->back()->withErrors($ordered)->withInput();
        }

        $data = $validator->validated();

        // Save total minutes into the existing `time` column (as integer)
        $hours = isset($data['time_hours']) ? (int)$data['time_hours'] : 0;
        $minutes = isset($data['time_minutes']) ? (int)$data['time_minutes'] : 0;
        $totalMinutes = ($hours * 60) + $minutes;
        $data['time'] = $totalMinutes > 0 ? $totalMinutes : null;

        // Handle image upload
        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('recipes', 'public');
        } elseif ($request->input('existing_temp_image')) {
            // Use temp image from validation failure
            $tempPath = $request->input('existing_temp_image');
            if (Storage::disk('public')->exists($tempPath)) {
                $newPath = str_replace('temp/', 'recipes/', $tempPath);
                Storage::disk('public')->move($tempPath, $newPath);
                $data['image'] = $newPath;
            }
        }

        // If no uploaded image but we have an imported image URL, fetch and store it
        if (empty($data['image']) && !empty($data['imported_image_url'])) {
            try {
                $imageUrl = $data['imported_image_url'];
                $response = Http::timeout(10)->get($imageUrl);
                if ($response->successful()) {
                    $mime = $response->header('Content-Type');
                    if ($mime && str_starts_with($mime, 'image/')) {
                        $extMap = [
                            'image/jpeg' => 'jpg',
                            'image/jpg' => 'jpg',
                            'image/png' => 'png',
                            'image/gif' => 'gif',
                            'image/webp' => 'webp',
                        ];
                        $ext = $extMap[$mime] ?? 'jpg';
                        $filename = Str::slug($data['title'] ?? 'recipe') . '-' . Str::random(6) . '.' . $ext;
                        $path = 'recipes/' . $filename;
                        Storage::disk('public')->put($path, $response->body());
                        $data['image'] = $path;
                    }
                }
            } catch (\Exception $e) {
                // swallow fetch errors; image stays null
            }
        }

        $recipe = null;

        DB::transaction(function () use ($data, &$recipe) {
            $recipe = Recipe::create([
                'title' => $data['title'],
                'slug' => \Illuminate\Support\Str::slug($data['title']) . '-' . \Illuminate\Support\Str::random(5),
                'description' => $data['description'] ?? null,
                'time' => $data['time'] ?? null,
                'image' => $data['image'] ?? null,
                'user_id' => Auth::id(),
                'is_public' => $data['is_public'] ?? false,
                'source_url' => $data['source_url'] ?? null,
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

            // Create ingredients if provided.
            if (!empty($data['ingredients']) && is_array($data['ingredients'])) {
                $ts = $recipe->created_at;
                foreach ($data['ingredients'] as $ing) {
                    $it = $recipe->ingredients()->create([
                        'name' => $ing['name'],
                        'amount' => $ing['amount'] ?? null,
                        'sort_order' => isset($ing['sort_order']) ? (int)$ing['sort_order'] : 0,
                    ]);
                    $it->timestamps = false;
                    $it->created_at = $ts;
                    $it->updated_at = $ts;
                    $it->save();
                }
            }
        });

        /** @var \App\Models\Recipe $recipe */
        return redirect()->route('recipes.show', $recipe)->with('status', 'Recipe created.');
    }

    public function update(Request $request, Recipe $recipe)
    {
        // Check if user owns the recipe
        if ($recipe->user_id !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }

        // Validation rules
        $rules = [
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'image' => 'nullable|image|mimes:jpeg,jpg,png,gif,webp|max:5120',
            // individual fields nullable numeric; combined time error added below
            'time_hours' => 'nullable|integer|min:0',
            'time_minutes' => 'nullable|integer|min:0|max:59',
            'tags' => 'required|array|min:1',
            'tags.*' => 'exists:tags,id',
            'directions' => 'required|array|min:1',
            'ingredients' => 'required|array|min:1',
            'ingredients.*.id' => 'nullable|integer|exists:ingredients,id',
            'ingredients.*.name' => 'required|string',
            'ingredients.*.amount' => 'nullable|string',
            'ingredients.*.sort_order' => 'required|integer',
            'directions.*.id' => 'nullable|integer|exists:directions,id',
            'directions.*.body' => 'required|string',
            'directions.*.sort_order' => 'required|integer',
            'is_public' => 'nullable|boolean',
            'source_url' => 'nullable|url',
            'remove_image' => 'nullable|boolean',
        ];

        $messages = [
            'required' => ':attribute is required.',
            'tags.required' => 'At least 1 Tag is required.',
            'directions.required' => 'At least 1 Direction is required.',
            'directions.*.body.required' => 'Direction cannot be empty.',
            'ingredients.required' => 'At least 1 Ingredient is required.',
            'ingredients.*.name.required' => 'Ingredient cannot be empty.',
            'ingredients.*.amount.required' => 'Ingredient amount cannot be empty.',
            'ingredients.*.amount.min' => 'Ingredient amount cannot be empty.',
        ];

        $attributes = [
            'title' => 'Title',
            'description' => 'Description',
            'image' => 'Image',
            'time_hours' => 'Time',
            'time_minutes' => 'Time',
            'tags' => 'Tags',
            'directions' => 'Directions',
            'directions.*.body' => 'Direction',
            'ingredients' => 'Ingredients',
            'ingredients.*.name' => 'Ingredient',
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

                if ($key === 'tags' || $key === 'directions' || $key === 'ingredients') {
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

            // Store uploaded image temporarily if validation fails
            if ($request->hasFile('image')) {
                $tempPath = $request->file('image')->store('temp', 'public');
                session()->flash('temp_image', $tempPath);
            }
            
            return redirect()->back()->withErrors($ordered)->withInput();
        }

        $data = $validator->validated();

        // Save total minutes into the existing `time` column (as integer)
        $hours = isset($data['time_hours']) ? (int)$data['time_hours'] : 0;
        $minutes = isset($data['time_minutes']) ? (int)$data['time_minutes'] : 0;
        $totalMinutes = ($hours * 60) + $minutes;
        $data['time'] = $totalMinutes > 0 ? $totalMinutes : null;

        // Handle image upload
        if ($request->hasFile('image')) {
            // Delete old image if exists
            if ($recipe->image && Storage::disk('public')->exists($recipe->image)) {
                Storage::disk('public')->delete($recipe->image);
            }
            $data['image'] = $request->file('image')->store('recipes', 'public');
        } elseif ($request->input('existing_temp_image')) {
            // Move temp image from validation failure to recipes folder
            $tempPath = $request->input('existing_temp_image');
            if (Storage::disk('public')->exists($tempPath)) {
                $newPath = str_replace('temp/', 'recipes/', $tempPath);
                Storage::disk('public')->move($tempPath, $newPath);
                // Delete old image if exists
                if ($recipe->image && Storage::disk('public')->exists($recipe->image)) {
                    Storage::disk('public')->delete($recipe->image);
                }
                $data['image'] = $newPath;
            }
        } elseif ($request->boolean('remove_image')) {
            // Explicit remove
            if ($recipe->image && Storage::disk('public')->exists($recipe->image)) {
                Storage::disk('public')->delete($recipe->image);
            }
            $data['image'] = null;
        } else {
            // Keep existing image if no new upload or removal
            unset($data['image']);
        }

        DB::transaction(function () use ($data, $recipe) {
            $updatePayload = [
                'title' => $data['title'],
                'description' => $data['description'] ?? null,
                'time' => $data['time'] ?? null,
                'rating' => isset($data['rating']) ? number_format((float)$data['rating'], 1) : null,
                'is_public' => $data['is_public'] ?? false,
            ];

            // Only update image if explicitly provided (new upload)
            if (array_key_exists('image', $data)) {
                $updatePayload['image'] = $data['image'];
            }

            // Update source_url if provided
            if (array_key_exists('source_url', $data)) {
                $updatePayload['source_url'] = $data['source_url'];
            }

            $recipe->update($updatePayload);

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

            // Process ingredients: create, update, reorder, and delete missing ones.
            $incomingIng = collect($data['ingredients'] ?? []);
            $incomingIngIds = $incomingIng->pluck('id')->filter()->all();

            if (!empty($incomingIngIds)) {
                $recipe->ingredients()->whereNotIn('id', $incomingIngIds)->delete();
            } else {
                $recipe->ingredients()->delete();
            }

            foreach ($incomingIng as $ing) {
                if (!empty($ing['id'])) {
                    $it = Ingredient::where('id', $ing['id'])->where('recipe_id', $recipe->id)->first();
                    if ($it) {
                        $it->name = $ing['name'];
                        $it->amount = $ing['amount'] ?? null;
                        $it->sort_order = isset($ing['sort_order']) ? (int)$ing['sort_order'] : 0;
                        $it->timestamps = false;
                        $it->created_at = $ts;
                        $it->updated_at = $ts;
                        $it->save();
                    }
                } else {
                    $it = $recipe->ingredients()->create([
                        'name' => $ing['name'],
                        'amount' => $ing['amount'] ?? null,
                        'sort_order' => isset($ing['sort_order']) ? (int)$ing['sort_order'] : 0,
                    ]);
                    $it->timestamps = false;
                    $it->created_at = $ts;
                    $it->updated_at = $ts;
                    $it->save();
                }
            }
        });

        return redirect()->route('recipes.show', $recipe)->with('status', 'Recipe updated.');
    }

    public function destroy(Recipe $recipe)
    {
        // Check if user owns the recipe
        if ($recipe->user_id !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }

        // Remove stored image file if present
        if ($recipe->image && Storage::disk('public')->exists($recipe->image)) {
            Storage::disk('public')->delete($recipe->image);
        }

        // Detach all tags first (pivot cleanup), then delete the recipe
        $recipe->tags()->detach();
        $recipe->delete();

        return redirect()->route('recipes.index')->with('status', 'Recipe deleted.');
    }
}
