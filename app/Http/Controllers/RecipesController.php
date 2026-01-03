<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Recipe;
use App\Models\Collection;
use App\Models\Tag;
use App\Models\Direction;
use App\Models\Ingredient;
use App\Models\RecipeRating;
use App\Models\RecipeRevision;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Rules\NoLinks;
use App\Services\ProfanityChecker;
use App\Models\RecipeView;

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

    private function listRecipes(Request $request, string $context = 'all')
    {
        $q = $request->query('q');
        $tags = $request->query('tags', []);
        $sort = $request->query('sort', 'date_desc');
        $ratingMin = (int) $request->query('rating_min', 0);

        // Support legacy ?tag=name parameter - convert to tags[] array
        if ($request->filled('tag')) {
            $legacyTag = $request->query('tag');
            $normalized = mb_strtolower($legacyTag);

            $foundTag = Tag::whereRaw('LOWER(name) = ?', [$normalized])
                ->orWhereRaw('LOWER(slug) = ?', [$normalized])
                ->when(ctype_digit((string) $legacyTag), function ($q) use ($legacyTag) {
                    // Only compare against id when the value is numeric to avoid PG invalid input syntax
                    $q->orWhere('id', (int) $legacyTag);
                })
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
            $emptyMessage = 'No recipes found.';
        } else {
            $query = Recipe::query();
            // Only show approved public recipes to everyone; include owner's recipes for the logged-in user
            if (Auth::check()) {
                $query->where(function ($qb) {
                    $qb->where(function ($pub) {
                        $pub->where('status', 'approved')
                            ->where('is_public', true);
                    })
                    ->orWhere('user_id', Auth::id());
                });
            } else {
                $query->where('status', 'approved')->where('is_public', true);
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

        // Filter by minimum average rating (4+ style)
        if ($ratingMin > 0) {
            $query->withAvg('ratings', 'rating')
                ->whereRaw('(
                    select avg("recipe_ratings"."rating")
                    from "recipe_ratings"
                    where "recipes"."id" = "recipe_ratings"."recipe_id"
                ) >= ?', [$ratingMin]);
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
            case 'rating_desc':
                $query->withAvg('ratings', 'rating')
                    ->orderBy('ratings_avg_rating', 'desc');
                break;
            case 'time_asc':
                $query->orderByRaw('COALESCE(time_hours * 60, 0) + COALESCE(time_minutes, 0) asc');
                break;
            case 'time_desc':
                $query->orderByRaw('COALESCE(time_hours * 60, 0) + COALESCE(time_minutes, 0) desc');
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


        // Eager load tags, user, ingredients, and ratings for N+1 prevention
        $recipes = $query->with(['tags', 'user', 'ingredients', 'ratings'])->paginate(12)->withQueryString();

        // Cache all tags for filter
        $allTags = Cache::remember('tags.all', 300, function() {
            return Tag::orderBy('sort_order')->orderBy('name')->get();
        });

        return view('recipes.index', [
            'recipes' => $recipes,
            'q' => $q,
            'selectedTags' => $tags,
            'allTags' => $allTags,
            'sort' => $sort,
            'ratingMin' => $ratingMin,
            'title' => $title,
            'subtitle' => $subtitle ?? 'Discover and explore delicious recipes',
            'emptyMessage' => $emptyMessage,
        ]);
    }

    public function storeRating(Request $request, Recipe $recipe)
    {
        /** @var \App\Models\User|null $user */
        $user = Auth::user();

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

    public function show(Request $request, Recipe $recipe)
    {
        // Track a view: 1 per user (or IP) per day
        $userId = Auth::id();
        $ip = $request->ip();
        $viewedRecently = RecipeView::where('recipe_id', $recipe->id)
            ->where(function($q) use ($userId, $ip) {
                if ($userId) {
                    $q->where('user_id', $userId);
                } else {
                    $q->where('ip_address', $ip);
                }
            })
            ->where('viewed_at', '>=', now()->startOfDay())
            ->exists();

        if (!$viewedRecently) {
            RecipeView::create([
                'recipe_id' => $recipe->id,
                'user_id' => $userId,
                'ip_address' => $ip,
                'viewed_at' => now(),
            ]);
        }
        $isOwner = Auth::check() && $recipe->user_id === Auth::id();

        // Block viewing if not owner and recipe is not approved public
        if (!$isOwner && (!$recipe->is_public || $recipe->status !== 'approved')) {
            abort(403, 'This recipe is not available.');
        }

        // If owner has a pending revision, load that version instead
        if ($isOwner && $recipe->status === 'pending') {
            $pendingRevision = $recipe->revisions()->where('status', 'pending')->latest()->first();
            if ($pendingRevision) {
                // Temporarily override recipe data with pending revision
                $proposedData = $pendingRevision->data ?? [];
                $recipe->title = $proposedData['title'] ?? $recipe->title;
                $recipe->description = $proposedData['description'] ?? $recipe->description;
                $recipe->time = $proposedData['time'] ?? $recipe->time;
                $recipe->difficulty = $proposedData['difficulty'] ?? $recipe->difficulty;
                $recipe->is_public = $proposedData['is_public'] ?? $recipe->is_public;
                $recipe->source_url = $proposedData['source_url'] ?? $recipe->source_url;
                $recipe->image = $proposedData['image'] ?? $recipe->image;
                
                // Load tags from proposal
                if (isset($proposedData['tags'])) {
                    $tagObjects = collect($proposedData['tags'])->map(function ($tag) {
                        $tagModel = new \App\Models\Tag();
                        $tagModel->id = $tag['id'] ?? null;
                        $tagModel->name = $tag['name'] ?? null;
                        return $tagModel;
                    });
                    $recipe->setRelation('tags', $tagObjects);
                }
                
                // Load ingredients from proposal
                if (isset($proposedData['ingredients'])) {
                    $ingredients = collect($proposedData['ingredients'])->map(function ($ing) {
                        $ingModel = new \App\Models\Ingredient();
                        $ingModel->name = $ing['name'] ?? null;
                        $ingModel->amount = $ing['amount'] ?? null;
                        $ingModel->sort_order = $ing['sort_order'] ?? 0;
                        return $ingModel;
                    })->sortBy('sort_order');
                    $recipe->setRelation('ingredients', $ingredients);
                }
                
                // Load directions from proposal
                if (isset($proposedData['directions'])) {
                    $directions = collect($proposedData['directions'])->map(function ($dir) {
                        $dirModel = new \App\Models\Direction();
                        $dirModel->body = $dir['body'] ?? null;
                        $dirModel->sort_order = $dir['sort_order'] ?? 0;
                        return $dirModel;
                    })->sortBy('sort_order');
                    $recipe->setRelation('directions', $directions);
                }
            }
        }

        // Load collection context if coming from a collection
        $fromCollection = null;
        if ($request->query('from_collection')) {
            $fromCollection = Collection::where('slug', $request->query('from_collection'))
                ->where('user_id', Auth::id())
                ->first();
        }

        // Load rejection notes if recipe is rejected
        $rejectionNotes = null;
        if ($isOwner && $recipe->status === 'rejected') {
            $rejectedRevision = $recipe->revisions()
                ->where('status', 'rejected')
                ->latest('reviewed_at')
                ->first();
            $rejectionNotes = $rejectedRevision?->notes;
        }

        // SEO meta description
        $metaDescription = Str::limit($recipe->description ?? 'Check out this delicious recipe!', 200);
        $jsonLd = [
            '@context' => 'https://schema.org/',
            '@type' => 'Recipe',
            'name' => $recipe->title,
            'description' => $metaDescription,
            'datePublished' => optional($recipe->created_at)->toIso8601String(),
            'author' => [
                '@type' => 'Person',
                'name' => $recipe->user->name ?? 'Unknown',
            ],
        ];
        if ($recipe->image) {
            $jsonLd['image'] = [url(Storage::url($recipe->image))];
        }
        if ($recipe->time) {
            $jsonLd['totalTime'] = 'PT' . (int) $recipe->time . 'M';
        }
        if ($recipe->serves) {
            $jsonLd['recipeYield'] = $recipe->serves;
        }
        if (!empty($recipe->ingredients)) {
            $jsonLd['recipeIngredient'] = collect($recipe->ingredients)->map(function ($ingredient) {
                return is_string($ingredient) ? $ingredient : ($ingredient->name ?? '');
            })->toArray();
        }
        if (!empty($recipe->directions)) {
            $jsonLd['recipeInstructions'] = collect($recipe->directions)->map(function ($step) {
                return [
                    '@type' => 'HowToStep',
                    'text' => is_string($step) ? $step : ($step->body ?? ($step->text ?? '')),
                ];
            })->toArray();
        }
        return view('recipes.show', [
            'recipe' => $recipe,
            'fromCollection' => $fromCollection,
            'rejectionNotes' => $rejectionNotes,
            'metaDescription' => $metaDescription,
            'canonicalUrl' => route('recipes.show', $recipe),
            'jsonLd' => json_encode($jsonLd, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT),
        ]);
    }

    public function downloadPdf(Recipe $recipe)
    {
        $isOwner = Auth::check() && $recipe->user_id === Auth::id();

        if (!$isOwner && (!$recipe->is_public || $recipe->status !== 'approved')) {
            abort(403, 'This recipe is not available.');
        }

        $pdf = Pdf::loadView('recipes.pdf', ['recipe' => $recipe])
            ->setPaper('a4', 'portrait');
        
        $filename = \Illuminate\Support\Str::slug($recipe->title) . '-recipe.pdf';
        
        return $pdf->stream($filename);
    }

    private function containsProfanity(array $data): bool
    {
        $texts = [
            $data['title'] ?? null,
            $data['description'] ?? null,
        ];

        foreach (($data['ingredients'] ?? []) as $ing) {
            $texts[] = $ing['name'] ?? null;
            $texts[] = $ing['amount'] ?? null;
        }

        foreach (($data['directions'] ?? []) as $dir) {
            $texts[] = $dir['body'] ?? null;
        }

        return collect($texts)
            ->filter(fn($t) => is_string($t) && ProfanityChecker::hasProfanity($t))
            ->isNotEmpty();
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


            // Try to extract difficulty from JSON-LD first
            $difficulty = null;
            if ($recipe && isset($recipe['difficulty'])) {
                $difficulty = strtolower(trim($recipe['difficulty']));
            }

            // If not found, try to extract from HTML for BBC Good Food and similar
            if (!$difficulty) {
                // BBC Good Food: <section class="recipe-details__item--skill-level"> ... <div>Easy</div> ... </section>
                $skillMatch = null;
                if (preg_match('/<section[^>]*class=[\'\"]?[^\'\">]*recipe-details__item--skill-level[^\'\">]*[\'\"]?[^>]*>.*?<div[^>]*>(.*?)<\/div>/is', $html, $skillMatch)) {
                    Log::info('BBC Good Food skill-level match', ['match' => $skillMatch[0] ?? '', 'value' => $skillMatch[1] ?? '']);
                    $difficulty = strtolower(trim(strip_tags($skillMatch[1])));
                } elseif (preg_match('/<meta[^>]+name=["\']?skill-level["\']?[^>]+content=["\']?([^"\'>]+)["\']?/i', $html, $metaMatch)) {
                    Log::info('BBC Good Food meta skill-level match', ['meta' => $metaMatch[0] ?? '', 'value' => $metaMatch[1] ?? '']);
                    $difficulty = strtolower(trim($metaMatch[1]));
                } elseif (preg_match('/\b(easy|medium|hard)\b/i', $html, $textMatch)) {
                    Log::info('BBC Good Food text skill-level match', ['text' => $textMatch[0] ?? '']);
                    $difficulty = strtolower(trim($textMatch[1]));
                } elseif (preg_match('/<span[^>]*>\s*Easy\s*<\/span>/i', $html)) {
                    $difficulty = 'easy';
                } elseif (preg_match('/<span[^>]*>\s*Medium\s*<\/span>/i', $html)) {
                    $difficulty = 'medium';
                } elseif (preg_match('/<span[^>]*>\s*Hard\s*<\/span>/i', $html)) {
                    $difficulty = 'hard';
                } elseif (preg_match('/<span[^>]*class=["\']difficulty["\'][^>]*>(.*?)<\/span>/i', $html, $diffMatch)) {
                    $difficulty = strtolower(trim($diffMatch[1]));
                }
            }

            // Map common synonyms to allowed values
            if ($difficulty) {
                $map = [
                    'beginner' => 'easy',
                    'simple' => 'easy',
                    'basic' => 'easy',
                    'quick' => 'easy',
                    'moderate' => 'medium',
                    'intermediate' => 'medium',
                    'average' => 'medium',
                    'challenging' => 'hard',
                    'advanced' => 'hard',
                    'expert' => 'hard',
                    'difficult' => 'hard',
                ];
                $difficultyNorm = preg_replace('/[^a-z]/', '', strtolower($difficulty));
                if (isset($map[$difficultyNorm])) {
                    $difficulty = $map[$difficultyNorm];
                } elseif (!in_array($difficulty, ['easy', 'medium', 'hard'])) {
                    $difficulty = null;
                }
            }

            if (!$recipe) {
                // Log failed import to database
                \App\Models\FailedRecipeImport::create([
                    'user_id' => Auth::id(),
                    'url' => $url,
                    'error_message' => 'Could not find recipe data on this page',
                    'http_status' => $response->status(),
                    'details' => null,
                ]);
                return response()->json(['message' => 'Could not find recipe data on this page'], 422);
            }

            if ($recipe) {
                $recipe['sourceUrl'] = $url;
                // Always include difficulty for debugging
                $recipe['difficulty'] = $difficulty;
            }
            // Log for debugging
            Log::info('Recipe import debug', ['difficulty' => $difficulty, 'url' => $url]);
            return response()->json($recipe);

        } catch (\Exception $e) {
            // Log failed import to database
            \App\Models\FailedRecipeImport::create([
                'user_id' => Auth::id(),
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
                // Try to separate amount from name (supports mixed units, x-multipliers, unicode fractions, slash combos like 15g/½oz)
                if (preg_match('/^((?:[\d\.,]+|[¼½¾⅓⅔⅛⅜⅝⅞])(?:\s*[xX]\s*[\d\.,]+)?(?:\s*(?:g|kg|ml|l|oz|lb|lbs|tsp|tbsp|cup|cups|clove|cloves|handful|pinch|bunch|tin|tins|can|cans|pack|packs))?(?:\s*[\/\-]\s*(?:[\d\.,]+|[¼½¾⅓⅔⅛⅜⅝⅞])(?:\s*(?:g|kg|ml|l|oz|lb|lbs|tsp|tbsp|cup|cups))?)*)\s+(.+)$/iu', $ing, $parts)) {
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
        
        // Extract serves/yield
        $serves = null;
        if (isset($json['recipeYield'])) {
            // recipeYield can be a string like "Serves 4" or just a number
            $yield = is_array($json['recipeYield']) ? reset($json['recipeYield']) : $json['recipeYield'];
            if (is_numeric($yield)) {
                $serves = (int)$yield;
            } elseif (preg_match('/\d+/', $yield, $matches)) {
                $serves = (int)$matches[0];
            }
        } elseif (isset($json['yield'])) {
            $yield = is_array($json['yield']) ? reset($json['yield']) : $json['yield'];
            if (is_numeric($yield)) {
                $serves = (int)$yield;
            } elseif (preg_match('/\d+/', $yield, $matches)) {
                $serves = (int)$matches[0];
            }
        }

        return [
            'title' => $json['name'] ?? '',
            'description' => $json['description'] ?? '',
            'time' => $time,
            'serves' => $serves,
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
        
        // If there's a pending revision, load its data into the form
        if ($recipe->status === 'pending') {
            $pendingRevision = $recipe->revisions()->where('status', 'pending')->latest()->first();
            if ($pendingRevision) {
                $proposedData = $pendingRevision->data ?? [];
                $recipe->title = $proposedData['title'] ?? $recipe->title;
                $recipe->description = $proposedData['description'] ?? $recipe->description;
                $recipe->time = $proposedData['time'] ?? $recipe->time;
                $recipe->difficulty = $proposedData['difficulty'] ?? $recipe->difficulty;
                $recipe->is_public = $proposedData['is_public'] ?? $recipe->is_public;
                $recipe->source_url = $proposedData['source_url'] ?? $recipe->source_url;
                $recipe->image = $proposedData['image'] ?? $recipe->image;
                
                // Load tags from proposal - fetch real Tag models from database
                if (isset($proposedData['tags'])) {
                    $tagIds = collect($proposedData['tags'])->pluck('id')->filter()->all();
                    if (!empty($tagIds)) {
                        $loadedTags = \App\Models\Tag::whereIn('id', $tagIds)->get();
                        $recipe->setRelation('tags', $loadedTags);
                    }
                }
                
                // Load ingredients from proposal
                if (isset($proposedData['ingredients'])) {
                    $recipe->ingredients = collect($proposedData['ingredients'])->map(function ($ing) {
                        $ingModel = new \App\Models\Ingredient();
                        $ingModel->id = null;
                        $ingModel->name = $ing['name'] ?? null;
                        $ingModel->amount = $ing['amount'] ?? null;
                        $ingModel->sort_order = $ing['sort_order'] ?? 0;
                        return $ingModel;
                    })->sortBy('sort_order')->values();
                }
                
                // Load directions from proposal
                if (isset($proposedData['directions'])) {
                    $recipe->directions = collect($proposedData['directions'])->map(function ($dir) {
                        $dirModel = new \App\Models\Direction();
                        $dirModel->id = null;
                        $dirModel->body = $dir['body'] ?? null;
                        $dirModel->sort_order = $dir['sort_order'] ?? 0;
                        return $dirModel;
                    })->sortBy('sort_order')->values();
                }
            }
        }

        $tags = \App\Models\Tag::orderBy('sort_order')->orderBy('name')->get();
        return view('recipes.edit', compact('recipe', 'tags'));
    }

    public function store(Request $request)
    {
        // Validation rules
        $rules = [
            'title' => ['required','string','max:255', new NoLinks],
            'description' => ['required','string', new NoLinks],
            'image' => 'nullable|image|mimes:jpeg,jpg,png,gif,webp|max:5120',
            // individual fields nullable numeric; combined time error added below
            'time_hours' => 'nullable|integer|min:0',
            'time_minutes' => 'nullable|integer|min:0|max:59',
            'serves' => 'nullable|integer|min:1',
            'difficulty' => 'required|in:easy,medium,hard',
            'tags' => 'nullable|array',
            'directions' => 'required|array|min:1',
            'ingredients' => 'required|array|min:1',
            'ingredients.*.name' => ['required','string', new NoLinks],
            'ingredients.*.amount' => ['nullable','string', new NoLinks],
            'ingredients.*.sort_order' => 'required|integer',
            'directions.*.body' => ['required','string', new NoLinks],
            'directions.*.sort_order' => 'required|integer',
            'is_public' => 'nullable|boolean',
            'source_url' => 'nullable|url',
            'imported_image_url' => 'nullable|url',
        ];

        // Custom messages and attribute names
        $messages = [
            'required' => ':attribute is required',
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

        if ($this->containsProfanity($data)) {
            return back()->withErrors(['profanity' => 'Please remove profanity from your recipe.'])->withInput();
        }

        // Normalize optional tags to an array for downstream sync
        $data['tags'] = $data['tags'] ?? [];

        // Normalize optional tags to an array for downstream sync
        $data['tags'] = $data['tags'] ?? [];

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
            $isPublic = (bool) ($data['is_public'] ?? false);
            $status = $isPublic ? 'pending' : 'approved';
            $approvedAt = $isPublic ? null : now();

            $recipe = Recipe::create([
                'title' => $data['title'],
                'slug' => \Illuminate\Support\Str::slug($data['title']) . '-' . \Illuminate\Support\Str::random(5),
                'description' => $data['description'] ?? null,
                'time' => $data['time'] ?? null,
                'serves' => $data['serves'] ?? null,
                'difficulty' => $data['difficulty'],
                'image' => $data['image'] ?? null,
                'user_id' => Auth::id(),
                'is_public' => $isPublic,
                'status' => $status,
                'approved_at' => $approvedAt,
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

        // Capture snapshot for moderation when requested public visibility
        assert($recipe instanceof Recipe);
        if ($recipe->is_public) {
            $recipe->load(['tags', 'ingredients', 'directions']);
            $this->replacePendingRevision($recipe, $this->snapshotRecipe($recipe));
        }

        /** @var \App\Models\Recipe $recipe */
        $message = $recipe->is_public ? 'Recipe submitted for review. It will go live once approved.' : 'Recipe created.';
        return redirect()->route('recipes.show', $recipe)->with('status', $message);
    }

    public function update(Request $request, Recipe $recipe)
    {
        // Check if user owns the recipe
        if ($recipe->user_id !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }

        // Validation rules
        $rules = [
            'title' => ['required','string','max:255', new NoLinks],
            'description' => ['required','string', new NoLinks],
            'image' => 'nullable|image|mimes:jpeg,jpg,png,gif,webp|max:5120',
            // individual fields nullable numeric; combined time error added below
            'time_hours' => 'nullable|integer|min:0',
            'time_minutes' => 'nullable|integer|min:0|max:59',
            'serves' => 'nullable|integer|min:1',
            'difficulty' => 'required|in:easy,medium,hard',
            'tags' => 'nullable|array',
            'tags.*' => 'exists:tags,id',
            'directions' => 'required|array|min:1',
            'ingredients' => 'required|array|min:1',
            'ingredients.*.id' => 'nullable|integer|exists:ingredients,id',
            'ingredients.*.name' => ['required','string', new NoLinks],
            'ingredients.*.amount' => ['nullable','string', new NoLinks],
            'ingredients.*.sort_order' => 'required|integer',
            'directions.*.id' => 'nullable|integer|exists:directions,id',
            'directions.*.body' => ['required','string', new NoLinks],
            'directions.*.sort_order' => 'required|integer',
            'is_public' => 'nullable|boolean',
            'source_url' => 'nullable|url',
            'remove_image' => 'nullable|boolean',
        ];

        $messages = [
            'required' => ':attribute is required.',
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

        if ($this->containsProfanity($data)) {
            return back()->withErrors(['profanity' => 'Please remove profanity from your recipe.'])->withInput();
        }

        $data['tags'] = $data['tags'] ?? [];
        $isPublic = (bool) ($data['is_public'] ?? false);
        // If requesting public visibility, always go through moderation (don't update live recipe)
        // If requesting private, apply changes immediately
        $applyToLive = !$isPublic;

        // Save total minutes into the existing `time` column (as integer)
        $hours = isset($data['time_hours']) ? (int)$data['time_hours'] : 0;
        $minutes = isset($data['time_minutes']) ? (int)$data['time_minutes'] : 0;
        $totalMinutes = ($hours * 60) + $minutes;
        $data['time'] = $totalMinutes > 0 ? $totalMinutes : null;

        $pendingImagePath = null;

        // Handle image upload differently depending on whether we apply to the live record
        if ($applyToLive) {
            if ($request->hasFile('image')) {
                if ($recipe->image && Storage::disk('public')->exists($recipe->image)) {
                    Storage::disk('public')->delete($recipe->image);
                }
                $data['image'] = $request->file('image')->store('recipes', 'public');
            } elseif ($request->input('existing_temp_image')) {
                $tempPath = $request->input('existing_temp_image');
                if (Storage::disk('public')->exists($tempPath)) {
                    $newPath = str_replace('temp/', 'recipes/', $tempPath);
                    Storage::disk('public')->move($tempPath, $newPath);
                    if ($recipe->image && Storage::disk('public')->exists($recipe->image)) {
                        Storage::disk('public')->delete($recipe->image);
                    }
                    $data['image'] = $newPath;
                }
            } elseif ($request->boolean('remove_image')) {
                if ($recipe->image && Storage::disk('public')->exists($recipe->image)) {
                    Storage::disk('public')->delete($recipe->image);
                }
                $data['image'] = null;
            } else {
                unset($data['image']);
            }
        } else {
            // Pending-only path: keep current public image intact, but store the proposed image path in the revision snapshot
            if ($request->hasFile('image')) {
                $pendingImagePath = $request->file('image')->store('recipes', 'public');
            } elseif ($request->input('existing_temp_image')) {
                $tempPath = $request->input('existing_temp_image');
                if (Storage::disk('public')->exists($tempPath)) {
                    $newPath = str_replace('temp/', 'recipes/', $tempPath);
                    Storage::disk('public')->move($tempPath, $newPath);
                    $pendingImagePath = $newPath;
                }
            } elseif ($request->boolean('remove_image')) {
                $pendingImagePath = null;
            } else {
                $pendingImagePath = $recipe->image;
            }
        }

        if ($applyToLive) {
            DB::transaction(function () use ($data, $recipe, $isPublic) {
                $updatePayload = [
                    'title' => $data['title'],
                    'description' => $data['description'] ?? null,
                    'time' => $data['time'] ?? null,
                    'serves' => $data['serves'] ?? null,
                    'difficulty' => $data['difficulty'],
                    // Don't change is_public to true on live recipe when requesting moderation
                    // It should stay private until approved
                    'status' => $isPublic ? 'pending' : 'approved',
                    'approved_at' => $isPublic ? null : ($recipe->approved_at ?? now()),
                ];
                
                // Allow making public -> private (but not private -> public directly)
                if ($recipe->is_public && !$isPublic) {
                    $updatePayload['is_public'] = false;
                }
                if (array_key_exists('image', $data)) {
                    $updatePayload['image'] = $data['image'];
                }

                if (array_key_exists('source_url', $data)) {
                    $updatePayload['source_url'] = $data['source_url'];
                }

                $recipe->update($updatePayload);

                // Sync tags via pivot
                $recipe->tags()->sync($data['tags']);

                // Directions
                $incoming = collect($data['directions'] ?? []);
                $incomingIds = $incoming->pluck('id')->filter()->all();

                if (!empty($incomingIds)) {
                    $recipe->directions()->whereNotIn('id', $incomingIds)->delete();
                } else {
                    $recipe->directions()->delete();
                }

                $ts = now();

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

                // Ingredients
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

            // When applying to live (private recipes), clear any stale pending revisions
            $recipe->revisions()->where('status', 'pending')->delete();

            return redirect()->route('recipes.show', $recipe)->with('status', 'Recipe updated.');
        }

        // Moderation-only path: keep live recipe untouched, store proposed changes in a revision
        $snapshot = $this->snapshotFromRequest($data, $recipe, $pendingImagePath);
        $this->replacePendingRevision($recipe, $snapshot);
        // Don't update is_public on the recipe - it stays at its current value until approved
        $recipe->update([
            'status' => 'pending',
            'approved_at' => null,
        ]);

        return redirect()->route('recipes.show', $recipe)->with('status', 'Changes submitted for review. Showing last approved version until approval.');
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

    private function snapshotRecipe(Recipe $recipe): array
    {
        $recipe->loadMissing(['tags', 'ingredients', 'directions']);

        return [
            'title' => $recipe->title,
            'description' => $recipe->description,
            'time' => $recipe->time,
            'difficulty' => $recipe->difficulty,
            'is_public' => (bool) $recipe->is_public,
            'source_url' => $recipe->source_url,
            'image' => $recipe->image,
            'tags' => $recipe->tags->map(fn ($t) => [
                'id' => $t->id,
                'name' => $t->name,
            ])->values()->all(),
            'ingredients' => $recipe->ingredients->sortBy('sort_order')->values()->map(fn ($ing) => [
                'name' => $ing->name,
                'amount' => $ing->amount,
                'sort_order' => $ing->sort_order,
            ])->all(),
            'directions' => $recipe->directions->sortBy('sort_order')->values()->map(fn ($dir) => [
                'body' => $dir->body,
                'sort_order' => $dir->sort_order,
            ])->all(),
        ];
    }

    private function snapshotFromRequest(array $data, Recipe $recipe, ?string $imagePath): array
    {
        $tags = collect($data['tags'] ?? []);
        $tagModels = $tags->isNotEmpty() ? Tag::whereIn('id', $tags)->get(['id', 'name']) : collect();

        return [
            'title' => $data['title'] ?? $recipe->title,
            'description' => $data['description'] ?? $recipe->description,
            'time' => $data['time'] ?? $recipe->time,
            'difficulty' => $data['difficulty'] ?? $recipe->difficulty,
            'is_public' => (bool) ($data['is_public'] ?? $recipe->is_public),
            'source_url' => $data['source_url'] ?? $recipe->source_url,
            'image' => $imagePath,
            'tags' => $tagModels->map(fn ($t) => [
                'id' => $t->id,
                'name' => $t->name,
            ])->values()->all(),
            'ingredients' => collect($data['ingredients'] ?? [])->sortBy('sort_order')->values()->map(fn ($ing) => [
                'name' => $ing['name'] ?? null,
                'amount' => $ing['amount'] ?? null,
                'sort_order' => isset($ing['sort_order']) ? (int) $ing['sort_order'] : 0,
            ])->all(),
            'directions' => collect($data['directions'] ?? [])->sortBy('sort_order')->values()->map(fn ($dir) => [
                'body' => $dir['body'] ?? null,
                'sort_order' => isset($dir['sort_order']) ? (int) $dir['sort_order'] : 0,
            ])->all(),
        ];
    }

    private function replacePendingRevision(Recipe $recipe, array $payload): void
    {
        $recipe->revisions()->where('status', 'pending')->delete();

        RecipeRevision::create([
            'recipe_id' => $recipe->id,
            'user_id' => Auth::id(),
            'data' => $payload,
            'status' => 'pending',
        ]);
    }
}
