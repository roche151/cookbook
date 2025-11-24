<div class="card h-100">
    <div class="card-body d-flex flex-column">
        <h5 class="card-title">{{ data_get($recipe, 'title') }}</h5>
        <p class="text-muted mb-2 small">
            @php
                $tags = data_get($recipe, 'tags');
            @endphp
            @if($tags && is_iterable($tags) && count($tags))
                @foreach($tags as $t)
                    <a href="/recipes?tag={{ urlencode($t->name ?? $t['name'] ?? (string)$t) }}" class="text-decoration-none small me-1">{{ $t->name ?? ($t['name'] ?? ucfirst((string)$t)) }}</a> ·
                @endforeach
            @endif
            {{ data_get($recipe, 'time') ?? '' }}</p>
        <p class="card-text grow">{{ data_get($recipe, 'excerpt') }}</p>
        <div class="mt-3 text-end d-flex justify-content-end gap-2">
            <a href="{{ data_get($recipe, 'href') ?? url('/recipes/'.data_get($recipe, 'id')) }}" class="btn btn-sm btn-primary">View</a>
            <a href="{{ route('recipes.edit', data_get($recipe, 'id')) }}" class="btn btn-sm btn-outline-secondary">Edit</a>
        </div>
    </div>
</div>
