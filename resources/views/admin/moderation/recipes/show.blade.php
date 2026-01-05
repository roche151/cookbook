<x-app-layout>
    <x-slot name="title">Review Recipe</x-slot>

    <div class="container py-4">
        <div class="mb-3 no-print">
            <a href="{{ route('admin.moderation.recipes.index') }}" class="btn btn-outline-secondary btn-sm">
                <i class="fa-solid fa-arrow-left me-1"></i> Back to Moderation
            </a>
        </div>

        <div class="d-flex align-items-center justify-content-between mb-3">
            <div>
                <h1 class="h4 mb-1">Review: {{ data_get($proposed, 'title') ?? data_get($revision, 'recipe.title') }}</h1>
                <p class="text-muted mb-0">Submitted by {{ data_get($revision, 'user.name') ?? 'Unknown user' }} · {{ $revision->created_at->diffForHumans() }}</p>
            </div>
            <div class="d-flex gap-2">
                <form id="approveForm" action="{{ route('admin.moderation.recipes.approve', $revision->id) }}" method="POST" style="display: none;">
                    @csrf
                    <input type="hidden" name="notes" value="">
                </form>
                <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#approveModal"><i class="fa-solid fa-check me-1"></i>Approve</button>
                <button class="btn btn-outline-danger" data-bs-toggle="modal" data-bs-target="#rejectModal"><i class="fa-solid fa-xmark me-1"></i>Reject</button>
            </div>
        </div>

        @php
            $allRevisions = $revision->recipe->revisions()->count();
            $isNew = $allRevisions === 1;
            
            // Format time variables early so they're available in both sections
            $currentTime = $current->time;
            $proposedTime = data_get($proposed, 'time');
            $timeChanged = $currentTime !== $proposedTime;
            
            // Format current time
            $currentTimeDisplay = '';
            if (is_numeric($currentTime) && (int)$currentTime > 0) {
                $total = (int)$currentTime;
                $h = intdiv($total, 60);
                $m = $total % 60;
                $parts = [];
                if ($h > 0) $parts[] = $h . 'h';
                if ($m > 0) $parts[] = $m . 'm';
                $currentTimeDisplay = $parts ? implode(' ', $parts) : '';
            }
            
            // Format proposed time
            $proposedTimeDisplay = '';
            if (is_numeric($proposedTime) && (int)$proposedTime > 0) {
                $total = (int)$proposedTime;
                $h = intdiv($total, 60);
                $m = $total % 60;
                $parts = [];
                if ($h > 0) $parts[] = $h . 'h';
                if ($m > 0) $parts[] = $m . 'm';
                $proposedTimeDisplay = $parts ? implode(' ', $parts) : '';
            }
            
            // Calculate tag IDs early so they're available in both sections
            $currentTagIds = $current->tags->pluck('id')->sort()->values()->toArray();
            $proposedTagIds = collect(data_get($proposed, 'tags', []))->pluck('id')->sort()->values()->toArray();
            $tagsChanged = $currentTagIds !== $proposedTagIds;
            $deletedTagIds = collect($currentTagIds)->diff($proposedTagIds);
            $addedTagIds = collect($proposedTagIds)->diff($currentTagIds);
            
            // Image variables early so they're available in both sections
            $currentImage = $current->image;
            $proposedImage = data_get($proposed, 'image');
            $imageChanged = $currentImage !== $proposedImage;
            
            // Align and compare ingredients and directions by index
            $currentIngs = $current->ingredients->sortBy('sort_order')->values();
            $proposedIngs = collect(data_get($proposed, 'ingredients', []))->sortBy('sort_order')->values();
            $maxIngs = max($currentIngs->count(), $proposedIngs->count());
            
            $currentDirs = $current->directions->sortBy('sort_order')->values();
            $proposedDirs = collect(data_get($proposed, 'directions', []))->sortBy('sort_order')->values();
            $maxDirs = max($currentDirs->count(), $proposedDirs->count());
        @endphp

        <div class="row g-3">
            @unless($isNew)
                <div class="col-lg-6">
                    <div class="card h-100 shadow-sm">
                        <div class="card-header bg-body-secondary border-0">
                            <h5 class="mb-0">Current (approved)</h5>
                        </div>
                        <div class="card-body">
                            <dl class="row mb-0">
                                @php
                                    $currentVis = (bool) $current->is_public;
                                    $proposedVis = (bool) data_get($proposed, 'is_public');
                                    $visChanged = $currentVis !== $proposedVis;
                                @endphp
                                <dt class="col-sm-4">Visibility</dt><dd class="col-sm-8" style="{{ $visChanged ? 'background-color: rgba(220, 53, 69, 0.15); padding: 0.25rem 0.5rem; border-radius: 0.25rem;' : '' }}">
                                    @if($current->is_public)
                                        <i class="fa-solid fa-globe me-1"></i>Public
                                    @else
                                        <i class="fa-solid fa-lock me-1"></i>Private
                                    @endif
                                </dd>
                                
                                @php
                                    $currentTitle = $current->title;
                                    $proposedTitle = data_get($proposed, 'title');
                                    $titleChanged = $currentTitle !== $proposedTitle;
                                    
                                    // Character-level diff for title
                                    $titleDiffCurrent = $currentTitle;
                                    $titleDiffProposed = $proposedTitle;
                                    if ($titleChanged) {
                                        $currentWords = explode(' ', $currentTitle);
                                        $proposedWords = explode(' ', $proposedTitle);
                                        $titleDiffCurrent = '';
                                        $titleDiffProposed = '';
                                        
                                        // Simple word-by-word comparison
                                        $maxLen = max(count($currentWords), count($proposedWords));
                                        for ($i = 0; $i < $maxLen; $i++) {
                                            $currWord = $currentWords[$i] ?? '';
                                            $propWord = $proposedWords[$i] ?? '';
                                            
                                            if ($currWord !== $propWord) {
                                                if ($currWord) {
                                                    $titleDiffCurrent .= '<span style="background-color: rgba(220, 53, 69, 0.3); text-decoration: line-through;">' . e($currWord) . '</span> ';
                                                }
                                                if ($propWord) {
                                                    $titleDiffProposed .= '<span style="background-color: rgba(25, 135, 84, 0.3); font-weight: 500;">' . e($propWord) . '</span> ';
                                                }
                                            } else {
                                                if ($currWord) $titleDiffCurrent .= e($currWord) . ' ';
                                                if ($propWord) $titleDiffProposed .= e($propWord) . ' ';
                                            }
                                        }
                                        $titleDiffCurrent = trim($titleDiffCurrent);
                                        $titleDiffProposed = trim($titleDiffProposed);
                                    }
                                @endphp
                                <dt class="col-sm-4">Title</dt><dd class="col-sm-8" style="{{ $titleChanged ? 'padding: 0.25rem 0.5rem; border-radius: 0.25rem;' : '' }}">{!! $titleChanged ? $titleDiffCurrent : e($current->title) !!}</dd>
                                
                                @php
                                    $currentDesc = $current->description;
                                    $proposedDesc = data_get($proposed, 'description');
                                    $descChanged = $currentDesc !== $proposedDesc;
                                    
                                    // Word-level diff for description
                                    $descDiffCurrent = $currentDesc;
                                    $descDiffProposed = $proposedDesc;
                                    if ($descChanged) {
                                        $currentWords = explode(' ', $currentDesc ?? '');
                                        $proposedWords = explode(' ', $proposedDesc ?? '');
                                        $descDiffCurrent = '';
                                        $descDiffProposed = '';
                                        
                                        $maxLen = max(count($currentWords), count($proposedWords));
                                        for ($i = 0; $i < $maxLen; $i++) {
                                            $currWord = $currentWords[$i] ?? '';
                                            $propWord = $proposedWords[$i] ?? '';
                                            
                                            if ($currWord !== $propWord) {
                                                if ($currWord) {
                                                    $descDiffCurrent .= '<span style="background-color: rgba(220, 53, 69, 0.3); text-decoration: line-through;">' . e($currWord) . '</span> ';
                                                }
                                                if ($propWord) {
                                                    $descDiffProposed .= '<span style="background-color: rgba(25, 135, 84, 0.3); font-weight: 500;">' . e($propWord) . '</span> ';
                                                }
                                            } else {
                                                if ($currWord) $descDiffCurrent .= e($currWord) . ' ';
                                                if ($propWord) $descDiffProposed .= e($propWord) . ' ';
                                            }
                                        }
                                        $descDiffCurrent = trim($descDiffCurrent);
                                        $descDiffProposed = trim($descDiffProposed);
                                    }
                                @endphp
                                <dt class="col-sm-4">Description</dt><dd class="col-sm-8" style="{{ $descChanged ? 'padding: 0.25rem 0.5rem; border-radius: 0.25rem;' : '' }}">{!! $descChanged ? $descDiffCurrent : e($current->description) !!}</dd>
                                
                                @php
                                    $currentDiff = $current->difficulty;
                                    $proposedDiff = data_get($proposed, 'difficulty');
                                    $diffChanged = $currentDiff !== $proposedDiff;
                                @endphp
                                <dt class="col-sm-4">Difficulty</dt><dd class="col-sm-8" style="{{ $diffChanged ? 'background-color: rgba(220, 53, 69, 0.15); padding: 0.25rem 0.5rem; border-radius: 0.25rem;' : '' }}">{{ ucfirst($current->difficulty) }}</dd>
                                
                                <dt class="col-sm-4">Time</dt>
                                <dd class="col-sm-8">
                                    @if($timeChanged)
                                        <span style="background-color: rgba(220, 53, 69, 0.3); text-decoration: line-through; padding: 0.125rem 0.25rem;">{{ $currentTimeDisplay ?: '—' }}</span>
                                    @else
                                        {{ $currentTimeDisplay ?: '—' }}
                                    @endif
                                </dd>
                                
                                <dt class="col-sm-4">Source</dt><dd class="col-sm-8">{{ $current->source_url ?? '—' }}</dd>
                                @php
                                    $currentVideoUrl = $current->video_url;
                                    $proposedVideoUrl = data_get($proposed, 'video_url');
                                    $videoUrlChanged = $currentVideoUrl !== $proposedVideoUrl;
                                @endphp
                                <dt class="col-sm-4">Video URL</dt>
                                <dd class="col-sm-8" style="{{ $videoUrlChanged ? 'padding: 0.25rem 0.5rem; border-radius: 0.25rem;' : '' }}">
                                    @if($currentVideoUrl)
                                        @if($videoUrlChanged)
                                            <a href="{{ $currentVideoUrl }}" target="_blank" rel="noopener" style="background-color: rgba(220, 53, 69, 0.3); text-decoration: line-through;">{{ $currentVideoUrl }}</a>
                                        @else
                                            <a href="{{ $currentVideoUrl }}" target="_blank" rel="noopener">{{ $currentVideoUrl }}</a>
                                        @endif
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </dd>
                                <dt class="col-sm-4">Tags</dt>
                                <dd class="col-sm-8">
                                    @if($current->tags && $current->tags->count())
                                        @foreach($current->tags as $tag)
                                            @php $isDeleted = $deletedTagIds->contains($tag->id); @endphp
                                            <span class="badge me-1 mb-1" style="{{ $isDeleted ? 'background-color: rgba(220, 53, 69, 0.8); text-decoration: line-through;' : 'background-color: #6c757d;' }}">{{ $tag->name }}</span>
                                        @endforeach
                                    @else
                                        <span class="text-muted">None</span>
                                    @endif
                                </dd>
                            </dl>
                            <hr>
                            <div class="mb-3">
                                <h6>Image</h6>
                                @if($currentImage)
                                    <div style="{{ $imageChanged ? 'border: 2px solid rgba(220, 53, 69, 0.5); padding: 0.25rem; border-radius: 0.25rem;' : '' }}">
                                        <img src="{{ Storage::url($currentImage) }}" alt="Current recipe image" class="img-fluid rounded" style="height: 200px; object-fit: cover; width: 100%;">
                                    </div>
                                    <button type="button" class="btn btn-sm btn-outline-secondary mt-2" data-bs-toggle="modal" data-bs-target="#imageModal" data-image="{{ Storage::url($currentImage) }}" data-title="Current Image">
                                        <i class="fa-solid fa-expand me-1"></i>View Full Size
                                    </button>
                                @elseif($proposedImage)
                                    <div class="d-flex align-items-center justify-content-center bg-secondary bg-opacity-10 rounded" style="height: 200px; border: 2px dashed #6c757d;">
                                        <p class="text-muted mb-0">No image</p>
                                    </div>
                                @else
                                    <p class="text-muted">No image</p>
                                @endif
                            </div>
                            <div>
                                <h6>Ingredients</h6>
                                @if($maxIngs > 0)
                                    <ul class="mb-3">
                                        @for($i = 0; $i < $maxIngs; $i++)
                                            @php
                                                $currIng = $currentIngs->get($i);
                                                $propIng = $proposedIngs->get($i);
                                                
                                                $currText = $currIng ? (($currIng->amount ?? '') . ' ' . ($currIng->name ?? '')) : '';
                                                $propText = $propIng ? (($propIng['amount'] ?? '') . ' ' . ($propIng['name'] ?? '')) : '';
                                                $currText = trim($currText);
                                                $propText = trim($propText);
                                                
                                                $ingChanged = $currText !== $propText;
                                                
                                                // Word-level diff
                                                $currDiff = $currText;
                                                if ($ingChanged && $currText) {
                                                    $currWords = explode(' ', $currText);
                                                    $propWords = explode(' ', $propText);
                                                    $currDiff = '';
                                                    
                                                    foreach ($currWords as $idx => $word) {
                                                        $propWord = $propWords[$idx] ?? '';
                                                        if ($word !== $propWord) {
                                                            $currDiff .= '<span style="background-color: rgba(220, 53, 69, 0.3); text-decoration: line-through;">' . e($word) . '</span> ';
                                                        } else {
                                                            $currDiff .= e($word) . ' ';
                                                        }
                                                    }
                                                    $currDiff = trim($currDiff);
                                                }
                                            @endphp
                                            @if($currText)
                                                <li>{!! $ingChanged ? $currDiff : e($currText) !!}</li>
                                            @else
                                                <li style="opacity: 0.3;">—</li>
                                            @endif
                                        @endfor
                                    </ul>
                                @else
                                    <p class="text-muted">No ingredients provided.</p>
                                @endif
                            </div>

                            <div style="margin-top: 0.5rem;">
                                <h6>Directions</h6>
                                @if($maxDirs > 0)
                                    <ol class="mb-0">
                                        @for($i = 0; $i < $maxDirs; $i++)
                                            @php
                                                $currDir = $currentDirs->get($i);
                                                $propDir = $proposedDirs->get($i);
                                                
                                                $currText = $currDir ? trim($currDir->body ?? '') : '';
                                                $propText = $propDir ? trim($propDir['body'] ?? '') : '';
                                                
                                                $dirChanged = $currText !== $propText;
                                                
                                                // Word-level diff
                                                $currDiff = $currText;
                                                if ($dirChanged && $currText) {
                                                    $currWords = explode(' ', $currText);
                                                    $propWords = explode(' ', $propText);
                                                    $currDiff = '';
                                                    
                                                    foreach ($currWords as $idx => $word) {
                                                        $propWord = $propWords[$idx] ?? '';
                                                        if ($word !== $propWord) {
                                                            $currDiff .= '<span style="background-color: rgba(220, 53, 69, 0.3); text-decoration: line-through;">' . e($word) . '</span> ';
                                                        } else {
                                                            $currDiff .= e($word) . ' ';
                                                        }
                                                    }
                                                    $currDiff = trim($currDiff);
                                                }
                                            @endphp
                                            @if($currText)
                                                <li class="mb-2">{!! $dirChanged ? $currDiff : e($currText) !!}</li>
                                            @else
                                                <li class="mb-2" style="opacity: 0.3;">—</li>
                                            @endif
                                        @endfor
                                    </ol>
                                @else
                                    <p class="text-muted mb-0">No directions provided.</p>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            @endunless
            <div class="{{ $isNew ? 'col-12' : 'col-lg-6' }}">
                <div class="card h-100 shadow-sm">
                    <div class="card-header bg-body-secondary border-0 d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">{{ $isNew ? 'Submission' : 'Proposed (pending)' }}</h5>
                        @if($isNew)
                            <span class="badge bg-success">New recipe</span>
                        @endif
                    </div>
                    <div class="card-body">
                        <dl class="row mb-0">
                            <dt class="col-sm-4">Visibility</dt><dd class="col-sm-8" style="{{ !$isNew && $visChanged ? 'background-color: rgba(25, 135, 84, 0.15); padding: 0.25rem 0.5rem; border-radius: 0.25rem; font-weight: 500;' : '' }}">
                                @if(data_get($proposed, 'is_public'))
                                    <i class="fa-solid fa-globe me-1"></i>Public
                                @else
                                    <i class="fa-solid fa-lock me-1"></i>Private
                                @endif
                            </dd>
                            <dt class="col-sm-4">Title</dt><dd class="col-sm-8" style="{{ !$isNew && $titleChanged ? 'padding: 0.25rem 0.5rem; border-radius: 0.25rem;' : '' }}">{!! !$isNew && $titleChanged ? $titleDiffProposed : e(data_get($proposed, 'title')) !!}</dd>
                            <dt class="col-sm-4">Description</dt><dd class="col-sm-8" style="{{ !$isNew && $descChanged ? 'padding: 0.25rem 0.5rem; border-radius: 0.25rem;' : '' }}">{!! !$isNew && $descChanged ? $descDiffProposed : e(data_get($proposed, 'description')) !!}</dd>
                            <dt class="col-sm-4">Difficulty</dt><dd class="col-sm-8" style="{{ !$isNew && $diffChanged ? 'background-color: rgba(25, 135, 84, 0.15); padding: 0.25rem 0.5rem; border-radius: 0.25rem; font-weight: 500;' : '' }}">{{ ucfirst(data_get($proposed, 'difficulty')) }}</dd>
                            <dt class="col-sm-4">Time</dt>
                            <dd class="col-sm-8">
                                @if(!$isNew && $timeChanged)
                                    <span style="background-color: rgba(25, 135, 84, 0.3); padding: 0.125rem 0.25rem; font-weight: 500;">{{ $proposedTimeDisplay ?: '—' }}</span>
                                @else
                                    {{ $proposedTimeDisplay ?: '—' }}
                                @endif
                            </dd>
                            <dt class="col-sm-4">Source</dt><dd class="col-sm-8">{{ data_get($proposed, 'source_url') ?? '—' }}</dd>
                            <dt class="col-sm-4">Video URL</dt>
                            <dd class="col-sm-8" style="{{ !$isNew && $videoUrlChanged ? 'padding: 0.25rem 0.5rem; border-radius: 0.25rem;' : '' }}">
                                @if($proposedVideoUrl)
                                    @if(!$isNew && $videoUrlChanged)
                                        <a href="{{ $proposedVideoUrl }}" target="_blank" rel="noopener" style="background-color: rgba(25, 135, 84, 0.3); font-weight: 500;">{{ $proposedVideoUrl }}</a>
                                    @else
                                        <a href="{{ $proposedVideoUrl }}" target="_blank" rel="noopener">{{ $proposedVideoUrl }}</a>
                                    @endif
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </dd>
                            <dt class="col-sm-4">Tags</dt>
                            <dd class="col-sm-8">
                                @php $ptags = collect(data_get($proposed, 'tags', [])); @endphp
                                @if($ptags->isNotEmpty())
                                    @foreach($ptags as $tag)
                                        @php $isAdded = $addedTagIds->contains($tag['id'] ?? null); @endphp
                                        <span class="badge me-1 mb-1" style="{{ $isAdded ? 'background-color: rgba(25, 135, 84, 0.9); font-weight: 600;' : 'background-color: #6c757d;' }}">{{ $tag['name'] ?? $tag['id'] }}</span>
                                    @endforeach
                                @else
                                    <span class="text-muted">None</span>
                                @endif
                            </dd>
                        </dl>
                        <hr>
                        <div class="mb-3">
                            <h6>Image</h6>
                            @if($proposedImage)
                                <div style="{{ !$isNew && $imageChanged ? 'border: 2px solid rgba(25, 135, 84, 0.5); padding: 0.25rem; border-radius: 0.25rem;' : '' }}">
                                    <img src="{{ Storage::url($proposedImage) }}" alt="Proposed recipe image" class="img-fluid rounded" style="height: 200px; object-fit: cover; width: 100%;">
                                </div>
                                <button type="button" class="btn btn-sm btn-outline-secondary mt-2" data-bs-toggle="modal" data-bs-target="#imageModal" data-image="{{ Storage::url($proposedImage) }}" data-title="Proposed Image">
                                    <i class="fa-solid fa-expand me-1"></i>View Full Size
                                </button>
                            @elseif($currentImage)
                                <div class="d-flex align-items-center justify-content-center bg-secondary bg-opacity-10 rounded" style="height: 200px; border: 2px dashed #6c757d;">
                                    <p class="text-muted mb-0">No image</p>
                                </div>
                            @else
                                <p class="text-muted">No image</p>
                            @endif
                        </div>
                        <div>
                            <h6>Ingredients</h6>
                            @if($maxIngs > 0)
                                <ul class="mb-3">
                                    @for($i = 0; $i < $maxIngs; $i++)
                                        @php
                                            $currIng = $currentIngs->get($i);
                                            $propIng = $proposedIngs->get($i);
                                            
                                            $currText = $currIng ? (($currIng->amount ?? '') . ' ' . ($currIng->name ?? '')) : '';
                                            $propText = $propIng ? (($propIng['amount'] ?? '') . ' ' . ($propIng['name'] ?? '')) : '';
                                            $currText = trim($currText);
                                            $propText = trim($propText);
                                            
                                            $ingChanged = $currText !== $propText;
                                            
                                            // Word-level diff
                                            $propDiff = $propText;
                                            if ($ingChanged && $propText) {
                                                $currWords = explode(' ', $currText);
                                                $propWords = explode(' ', $propText);
                                                $propDiff = '';
                                                
                                                foreach ($propWords as $idx => $word) {
                                                    $currWord = $currWords[$idx] ?? '';
                                                    if ($word !== $currWord) {
                                                        $propDiff .= '<span style="background-color: rgba(25, 135, 84, 0.3); font-weight: 500;">' . e($word) . '</span> ';
                                                    } else {
                                                        $propDiff .= e($word) . ' ';
                                                    }
                                                }
                                                $propDiff = trim($propDiff);
                                            }
                                        @endphp
                                        @if($propText)
                                            <li>{!! $ingChanged ? $propDiff : e($propText) !!}</li>
                                        @else
                                            <li style="opacity: 0.3;">—</li>
                                        @endif
                                    @endfor
                                </ul>
                            @else
                                <p class="text-muted">No ingredients provided.</p>
                            @endif
                        </div>

                        <div style="margin-top: 0.5rem;">
                            <h6>Directions</h6>
                            @if($maxDirs > 0)
                                <ol class="mb-0">
                                    @for($i = 0; $i < $maxDirs; $i++)
                                        @php
                                            $currDir = $currentDirs->get($i);
                                            $propDir = $proposedDirs->get($i);
                                            
                                            $currText = $currDir ? trim($currDir->body ?? '') : '';
                                            $propText = $propDir ? trim($propDir['body'] ?? '') : '';
                                            
                                            $dirChanged = $currText !== $propText;
                                            
                                            // Word-level diff
                                            $propDiff = $propText;
                                            if ($dirChanged && $propText) {
                                                $currWords = explode(' ', $currText);
                                                $propWords = explode(' ', $propText);
                                                $propDiff = '';
                                                
                                                foreach ($propWords as $idx => $word) {
                                                    $currWord = $currWords[$idx] ?? '';
                                                    if ($word !== $currWord) {
                                                        $propDiff .= '<span style="background-color: rgba(25, 135, 84, 0.3); font-weight: 500;">' . e($word) . '</span> ';
                                                    } else {
                                                        $propDiff .= e($word) . ' ';
                                                    }
                                                }
                                                $propDiff = trim($propDiff);
                                            }
                                        @endphp
                                        @if($propText)
                                            <li class="mb-2">{!! $dirChanged ? $propDiff : e($propText) !!}</li>
                                        @else
                                            <li class="mb-2" style="opacity: 0.3;">—</li>
                                        @endif
                                    @endfor
                                </ol>
                            @else
                                <p class="text-muted mb-0">No directions provided.</p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Approve confirmation modal -->
    <div class="modal fade" id="approveModal" tabindex="-1" aria-labelledby="approveModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="approveModalLabel">Approve recipe?</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p class="mb-0">Are you sure you want to approve this recipe and publish it?</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-success" onclick="document.getElementById('approveForm').submit();">Approve</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Reject modal -->
    <div class="modal fade" id="rejectModal" tabindex="-1" aria-labelledby="rejectModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="rejectModalLabel">Reject revision</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('admin.moderation.recipes.reject', $revision->id) }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="notes" class="form-label">Reason / notes (optional)</label>
                            <textarea name="notes" id="notes" class="form-control" rows="3" maxlength="2000"></textarea>
                        </div>
                        <p class="text-muted small mb-0">The author will see this recipe as rejected; it stays private until a new submission is approved.</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-danger">Reject</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Image preview modal -->
    <div class="modal fade" id="imageModal" tabindex="-1" aria-labelledby="imageModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered">
            <div class="modal-content bg-dark">
                <div class="modal-header border-0">
                    <h5 class="modal-title text-white" id="imageModalLabel">Image Preview</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body text-center p-0">
                    <img id="modalImage" src="" alt="Full size image" class="img-fluid" style="max-height: 80vh; width: auto;">
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const imageModal = document.getElementById('imageModal');
            if (imageModal) {
                imageModal.addEventListener('show.bs.modal', function (event) {
                    const trigger = event.relatedTarget;
                    const imageSrc = trigger.getAttribute('data-image');
                    const imageTitle = trigger.getAttribute('data-title');
                    
                    const modalImage = document.getElementById('modalImage');
                    const modalTitle = document.getElementById('imageModalLabel');
                    
                    modalImage.src = imageSrc;
                    modalTitle.textContent = imageTitle;
                });
            }
        });
    </script>
</x-app-layout>
