<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Direction;
use App\Models\Ingredient;
use App\Models\RecipeRevision;
use App\Notifications\RecipeModerationNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class RecipeModerationController extends Controller
{
    public function index()
    {
        $revisions = RecipeRevision::with(['recipe', 'user'])
            ->where('status', 'pending')
            ->latest()
            ->paginate(20);

        return view('admin.moderation.recipes.index', compact('revisions'));
    }

    public function show(RecipeRevision $revision)
    {
        if ($revision->status !== 'pending') {
            return redirect()
                ->route('admin.moderation.recipes.index')
                ->with('error', 'This revision is not pending.');
        }

        $revision->load(['recipe.tags', 'recipe.ingredients', 'recipe.directions', 'user', 'reviewer']);

        $proposed = $revision->data ?? [];
        $proposedVideoUrl = data_get($proposed, 'video_url');
        return view('admin.moderation.recipes.show', [
            'revision' => $revision,
            'proposed' => $proposed,
            'current' => $revision->recipe,
            'proposedVideoUrl' => $proposedVideoUrl,
        ]);
    }

    public function approve(Request $request, RecipeRevision $revision)
    {
        if ($revision->status !== 'pending') {
            return redirect()->back()->with('error', 'This revision is not pending.');
        }

        $revision->load('recipe');

        DB::transaction(function () use ($revision, $request) {
            $recipe = $revision->recipe()->lockForUpdate()->first();
            $payload = $revision->data ?? [];

            $tagIds = collect(data_get($payload, 'tags', []))
                ->map(fn ($t) => $t['id'] ?? $t)
                ->filter()
                ->all();

            $recipe->update([
                'title' => data_get($payload, 'title', $recipe->title),
                'description' => data_get($payload, 'description', $recipe->description),
                'time' => data_get($payload, 'time', $recipe->time),
                'difficulty' => data_get($payload, 'difficulty', $recipe->difficulty),
                'is_public' => (bool) data_get($payload, 'is_public', true),
                'source_url' => data_get($payload, 'source_url', $recipe->source_url),
                'image' => array_key_exists('image', $payload) ? data_get($payload, 'image') : $recipe->image,
                'status' => 'approved',
                'approved_at' => now(),
            ]);

            // Sync tags
            $recipe->tags()->sync($tagIds);

            // Reset and re-create directions
            $recipe->directions()->delete();
            foreach (collect(data_get($payload, 'directions', []))->sortBy('sort_order') as $dir) {
                $recipe->directions()->create([
                    'body' => $dir['body'] ?? '',
                    'sort_order' => isset($dir['sort_order']) ? (int) $dir['sort_order'] : 0,
                ]);
            }

            // Reset and re-create ingredients
            $recipe->ingredients()->delete();
            foreach (collect(data_get($payload, 'ingredients', []))->sortBy('sort_order') as $ing) {
                $recipe->ingredients()->create([
                    'name' => $ing['name'] ?? '',
                    'amount' => $ing['amount'] ?? null,
                    'sort_order' => isset($ing['sort_order']) ? (int) $ing['sort_order'] : 0,
                ]);
            }

            $revision->update([
                'status' => 'approved',
                'reviewed_by' => Auth::id(),
                'reviewed_at' => now(),
                'notes' => $request->input('notes'),
            ]);

            // Clean up and remove any other pending revisions for this recipe
            $recipe->revisions()
                ->where('status', 'pending')
                ->where('id', '!=', $revision->id)
                ->get()
                ->each(fn($rev) => $this->cleanupRevisionImages($rev));
            
            $recipe->revisions()
                ->where('status', 'pending')
                ->where('id', '!=', $revision->id)
                ->delete();
        });

        // Send notification to recipe owner
        if ($revision->user) {
            $isNewSubmission = $revision->recipe->approved_at === null || 
                               $revision->recipe->approved_at->eq($revision->reviewed_at);
            
            $revision->user->notify(new RecipeModerationNotification(
                recipeId: $revision->recipe->slug,
                recipeTitle: $revision->recipe->title,
                revisionId: $revision->id,
                status: 'approved',
                notes: null,
                reviewedAt: $revision->reviewed_at?->toDateTimeString(),
                isNewSubmission: $isNewSubmission
            ));
        }

        return redirect()->route('admin.moderation.recipes.index')->with('status', 'Revision approved and applied.');
    }

    public function reject(Request $request, RecipeRevision $revision)
    {
        if ($revision->status !== 'pending') {
            return redirect()->back()->with('error', 'This revision is not pending.');
        }

        $data = $request->validate([
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);

        DB::transaction(function () use ($revision, $data) {
            // Clean up images from the revision before marking it rejected
            $this->cleanupRevisionImages($revision);

            $revision->update([
                'status' => 'rejected',
                'reviewed_by' => Auth::id(),
                'reviewed_at' => now(),
                'notes' => $data['notes'] ?? null,
            ]);

            $recipe = $revision->recipe()->lockForUpdate()->first();
            if ($recipe) {
                $isNew = $recipe->approved_at === null;
                if ($isNew) {
                    $recipe->update([
                        'status' => 'rejected',
                        'is_public' => false,
                    ]);
                } else {
                    $recipe->update([
                        'status' => 'approved',
                    ]);
                }
            }
        });

        // Send notification to recipe owner
        if ($revision->user) {
            $isNewSubmission = $revision->recipe->approved_at === null;
            
            $revision->user->notify(new RecipeModerationNotification(
                recipeId: $revision->recipe->slug,
                recipeTitle: $revision->recipe->title,
                revisionId: $revision->id,
                status: 'rejected',
                notes: $revision->notes,
                reviewedAt: $revision->reviewed_at?->toDateTimeString(),
                isNewSubmission: $isNewSubmission
            ));
        }

        return redirect()->route('admin.moderation.recipes.index')->with('status', 'Revision rejected.');
    }

    /**
     * Clean up image files stored in a revision's data.
     * Removes both the revision image and the actual file from storage.
     */
    private function cleanupRevisionImages(RecipeRevision $revision): void
    {
        if (!$revision->data) {
            return;
        }

        $imagePath = $revision->data['image'] ?? null;
        if ($imagePath && is_string($imagePath) && Storage::disk('public')->exists($imagePath)) {
            try {
                Storage::disk('public')->delete($imagePath);
            } catch (\Exception $e) {
                // Log but don't fail if cleanup has issues
                \Illuminate\Support\Facades\Log::warning("Failed to delete image from revision {$revision->id}: " . $e->getMessage());
            }
        }
    }
}
