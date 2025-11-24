@props(['recipes' => null, 'title' => 'Featured Recipes', 'seeAllUrl' => '/recipes', 'count' => 3])

@php
    // Determine items to render. Support passing arrays/models via the component prop,
    // otherwise load from DB with a static fallback when DB isn't available.
    if (is_null($recipes)) {
        try {
            $items = \App\Models\Recipe::with('tags')->orderBy('created_at', 'desc')->take($count)->get();
        } catch (\Throwable $e) {
            throw $e;
        }
    } else {
        $items = collect($recipes);
    }

    // Determine column class based on count for a consistent grid
    if ($count >= 4) {
        $colClass = 'col-md-3';
    } elseif ($count === 3) {
        $colClass = 'col-md-4';
    } elseif ($count === 2) {
        $colClass = 'col-md-6';
    } else {
        $colClass = 'col-12';
    }
@endphp

<div class="mb-4 d-flex justify-content-between align-items-center">
    <h2 class="h4 mb-0">{{ $title }}</h2>
    <a href="{{ $seeAllUrl }}" class="small">See all</a>
</div>

@if($items->isEmpty())
    <div class="alert alert-secondary">No featured recipes yet.</div>
@else
    <div class="row g-4 mb-5">
        @foreach($items as $r)
            <div class="{{ $colClass }}">
                <x-recipe-card
                    :title="data_get($r, 'title')"
                    :image="data_get($r, 'image')"
                    :excerpt="data_get($r, 'excerpt')"
                    :href="data_get($r, 'href') ?? (data_get($r, 'id') ? url('/recipes/'.data_get($r,'id')) : null)"
                    :rating="data_get($r, 'rating')"
                >
                    @if(isset($r->tags) && is_iterable($r->tags) && count($r->tags))
                        <x-slot name="meta_slot">
                            @foreach($r->tags as $t)
                                <a href="/recipes?tag={{ urlencode($t->name) }}" class="small text-decoration-none me-2">{{ $t->name }}</a>
                            @endforeach
                            @if(data_get($r, 'time'))
                                <span class="text-muted">• {{ data_get($r, 'time') }}</span>
                            @endif
                        </x-slot>
                        @else
                        @if(data_get($r, 'meta') || data_get($r, 'time'))
                            <x-slot name="meta_slot">
                                @if(data_get($r, 'meta'))
                                    {{ data_get($r, 'meta') }}
                                @endif
                                @if(data_get($r, 'time'))
                                    <span class="text-muted"> • {{ data_get($r, 'time') }}</span>
                                @endif
                            </x-slot>
                        @endif
                    @endif
                </x-recipe-card>
            </div>
        @endforeach
    </div>
@endif
