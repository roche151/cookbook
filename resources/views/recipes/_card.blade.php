<div class="card h-100">
    <div class="card-body d-flex flex-column">
        <h5 class="card-title">{{ data_get($recipe, 'title') }}</h5>
        <p class="text-muted mb-2 small">{{ ucfirst(data_get($recipe, 'category')) }} · {{ data_get($recipe, 'time') ?? '' }}</p>
        <p class="card-text grow">{{ data_get($recipe, 'excerpt') }}</p>
        <div class="mt-3 text-end">
            <a href="{{ data_get($recipe, 'href') ?? url('/recipes/'.data_get($recipe, 'id')) }}" class="btn btn-sm btn-primary">View</a>
        </div>
    </div>
</div>
