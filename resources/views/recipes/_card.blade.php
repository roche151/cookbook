@php use Illuminate\Support\Str; @endphp
<div class="card h-100 shadow-sm">
    @php
        $image = data_get($recipe, 'image');
        $isOwnedByUser = auth()->check() && data_get($recipe, 'user_id') === auth()->id();
        $showCollectionRemove = $showCollectionRemove ?? false;
        $collectionSlug = $collectionSlug ?? null;
    @endphp
    <div style="position: relative; overflow: hidden; border-radius: calc(0.375rem - 1px) calc(0.375rem - 1px) 0 0;">
        @if($image)
              <img src="{{ Str::startsWith($image, ['http://', 'https://']) ? $image : Storage::url($image) }}"
                  class="card-img-top" alt="{{ data_get($recipe, 'title') }}"
                  style="height: 200px; object-fit: cover;"
                  onerror="if(this.parentNode){
                    var fallback = document.createElement('div');
                    fallback.className = 'card-img-top d-flex align-items-center justify-content-center bg-secondary bg-opacity-25';
                    fallback.style.height = '200px';
                    fallback.style.width = '100%';
                    fallback.innerHTML = '<i class=\'fa-regular fa-image text-muted\' style=\'font-size: 3rem; opacity: 0.5;\'></i>';
                    this.parentNode.replaceChild(fallback, this);
                  }">
        @else
            <div class="card-img-top d-flex align-items-center justify-content-center bg-secondary bg-opacity-25" style="height: 200px;">
                <i class="fa-regular fa-image text-muted" style="font-size: 3rem; opacity: 0.5;"></i>
            </div>
        @endif
        
        @if($isOwnedByUser && data_get($recipe, 'status') === 'pending')
            <div class="position-absolute top-0 start-0 m-2" style="z-index: 10;">
                <span class="badge bg-warning text-dark shadow-sm" data-bs-toggle="tooltip" data-bs-title="Awaiting moderation" style="backdrop-filter: blur(8px);">
                    <i class="fa-solid fa-clock me-1"></i> Pending
                </span>
            </div>
        @elseif($isOwnedByUser && !request()->routeIs('recipes.my'))
            <div class="position-absolute top-0 start-0 m-2" style="z-index: 10;">
                <span class="badge bg-primary shadow-sm" data-bs-toggle="tooltip" data-bs-title="Your recipe" style="backdrop-filter: blur(8px); background: rgba(13, 110, 253, 0.9) !important;">
                    <i class="fa-solid fa-user"></i>
                </span>
            </div>
        @endif
        
        @if(data_get($recipe, 'is_public') !== null)
            <div class="position-absolute top-0 end-0 m-2" style="z-index: 10;">
                @php
                    $isPublic = (bool) data_get($recipe, 'is_public');
                    $status = data_get($recipe, 'status');
                    $visibilityLabel = $isPublic
                        ? ($status === 'pending'
                            ? 'Awaiting approval — not visible yet'
                            : 'Public recipe - visible to everyone')
                        : 'Private recipe - only visible to you';
                @endphp
                <span class="badge {{ $isPublic ? 'bg-success' : 'bg-secondary' }} shadow-sm" data-bs-toggle="tooltip" data-bs-title="{{ $visibilityLabel }}" style="backdrop-filter: blur(8px); {{ $isPublic ? 'background: rgba(25, 135, 84, 0.9) !important;' : 'background: rgba(108, 117, 125, 0.9) !important;' }}">
                    <i class="fa-solid fa-{{ data_get($recipe, 'is_public') ? 'globe' : 'lock' }}"></i>
                </span>
            </div>
        @endif
    </div>
    
    <div class="card-body d-flex flex-column position-relative">
        @auth
            @if(isset($showCollectionRemove) && $showCollectionRemove && isset($collectionSlug))
                {{-- Show remove button when in collection view --}}
                <form action="{{ route('collections.remove-recipe', [$collectionSlug, data_get($recipe, 'slug')]) }}" method="POST" class="position-absolute top-0 end-0 m-2 js-remove-from-collection-form">
                    @csrf
                    @method('DELETE')
                    <button type="submit" data-icon-only="true" class="btn btn-sm btn-danger" data-bs-toggle="tooltip" data-bs-title="Remove from collection">
                        <i class="fa-solid fa-times"></i>
                    </button>
                </form>
            @else
                {{-- Show add to collection button --}}
                <button type="button" data-icon-only="true" class="btn btn-sm btn-outline-primary position-absolute top-0 end-0 m-2 js-open-collection-modal" data-recipe-id="{{ data_get($recipe, 'id') }}" data-recipe-slug="{{ data_get($recipe, 'slug') }}" data-bs-toggle="tooltip" data-bs-title="Add to collection">
                    <i class="fa-solid fa-folder-plus"></i>
                </button>
            @endif
        @endauth
        @php
            $recipeUrl = data_get($recipe, 'href') ?? url('/recipes/'.data_get($recipe, 'slug'));
            if (isset($collectionSlug) && $collectionSlug) {
                $recipeUrl .= '?from_collection=' . $collectionSlug;
            }
        @endphp
        <h5 class="card-title mb-2 fw-semibold" style="overflow: hidden; text-overflow: ellipsis; white-space: nowrap; padding-right: 2.5rem; line-height: 1.4;">{{ data_get($recipe, 'title') }}</h5>
        
        <p class="text-muted mb-2 small" style="overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
            @php
                $tags = data_get($recipe, 'tags');
                $tagCount = $tags && is_iterable($tags) ? count($tags) : 0;
                
                $displayTime = '';
                if (is_numeric($recipe->time) && (int)$recipe->time > 0) {
                    $total = (int)$recipe->time;
                    $h = intdiv($total, 60);
                    $m = $total % 60;
                    $parts = [];
                    if ($h > 0) $parts[] = $h . 'h';
                    if ($m > 0) $parts[] = $m . 'm';
                    $displayTime = $parts ? implode(' ', $parts) : '';
                } else {
                    $displayTime = data_get($recipe, 'time') ?? '';
                }
            @endphp
            @if($tagCount > 0)
                @foreach($tags as $t)
                    @if($loop->index < 2)
                        <a href="/recipes?tag={{ urlencode($t->name ?? $t['name'] ?? (string)$t) }}" class="text-decoration-none">{{ $t->name ?? ($t['name'] ?? ucfirst((string)$t)) }}</a>@if(!$loop->last && $loop->index < 1 && $tagCount > 1) · @endif
                    @endif
                @endforeach
                @if($tagCount > 2)
                    <span class="text-muted"> +{{ $tagCount - 2 }}</span>
                @endif
                @if($displayTime || $recipe->difficulty)
                    <span class="mx-1">·</span>
                @endif
            @endif
            @if($displayTime)
                <i class="fa-regular fa-clock" style="font-size: 0.75rem;"></i> {{ $displayTime }}
            @endif
            @if($recipe->serves)
                <span class="mx-1">·</span>
                <span data-bs-toggle="tooltip" title="Serves {{ e($recipe->serves) }}">
                    <i class="fa-solid fa-user-group" style="font-size: 0.75rem;"></i> {{ $recipe->serves }}
                </span>
            @endif
            @if($recipe->difficulty)
                <span class="mx-1">·</span>
                <span style="letter-spacing: 1px;">
                    @if($recipe->difficulty === 'easy')
                        <span style="color: #28a745;" data-bs-toggle="tooltip" data-bs-title="Easy difficulty">
                            <i class="fa-solid fa-circle" style="font-size: 0.45rem;"></i><i class="fa-regular fa-circle" style="font-size: 0.45rem;"></i><i class="fa-regular fa-circle" style="font-size: 0.45rem;"></i>
                        </span>
                    @elseif($recipe->difficulty === 'medium')
                        <span style="color: #ffc107;" data-bs-toggle="tooltip" data-bs-title="Medium difficulty">
                            <i class="fa-solid fa-circle" style="font-size: 0.45rem;"></i><i class="fa-solid fa-circle" style="font-size: 0.45rem;"></i><i class="fa-regular fa-circle" style="font-size: 0.45rem;"></i>
                        </span>
                    @else
                        <span style="color: #dc3545;" data-bs-toggle="tooltip" data-bs-title="Hard difficulty">
                            <i class="fa-solid fa-circle" style="font-size: 0.45rem;"></i><i class="fa-solid fa-circle" style="font-size: 0.45rem;"></i><i class="fa-solid fa-circle" style="font-size: 0.45rem;"></i>
                        </span>
                    @endif
                </span>
            @endif
        </p>

        <p class="card-text mb-3 text-muted" style="overflow: hidden; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; line-height: 1.5; min-height: 3em;">{{ data_get($recipe, 'description') }}</p>
        
        @php
            $avgRating = is_object($recipe) && method_exists($recipe, 'averageRating') ? $recipe->averageRating() : null;
            $ratingsCount = is_object($recipe) && method_exists($recipe, 'ratingsCount') ? $recipe->ratingsCount() : 0;
        @endphp
        <div class="mb-3">
            @if($avgRating)
                <div class="text-warning small d-flex align-items-center gap-1">
                    <span>
                        @for($i = 1; $i <= 5; $i++)
                            <i class="fa-{{ $i <= round($avgRating) ? 'solid' : 'regular' }} fa-star" style="font-size: 0.875rem;"></i>
                        @endfor
                    </span>
                    <span class="text-light fw-semibold">{{ number_format($avgRating, 1) }}</span>
                    <span class="text-muted">({{ $ratingsCount }})</span>
                </div>
            @else
                <div class="text-muted small" style="opacity: 0.7;">
                    <i class="fa-regular fa-star"></i> No ratings yet
                </div>
            @endif
        </div>
        
        <div class="mt-auto pt-2 border-top border-secondary border-opacity-25">
            <div class="d-flex justify-content-end gap-2 mt-2">
                <a href="{{ $recipeUrl }}" class="btn btn-sm btn-primary px-3">
                    <i class="fa-solid fa-eye me-1"></i>View
                </a>
                @auth
                    @if(data_get($recipe, 'user_id') === auth()->id())
                        <a href="{{ route('recipes.edit', data_get($recipe, 'slug')) }}" class="btn btn-sm btn-outline-secondary px-3">
                            <i class="fa-solid fa-pen me-1"></i>Edit
                        </a>
                    @endif
                @endauth
            </div>
        </div>
    </div>
</div>
