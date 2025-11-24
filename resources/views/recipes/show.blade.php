<x-app-layout>
    <x-slot name="title">{{ data_get($recipe, 'title') }}</x-slot>

    <div class="container py-5">
        <div class="row">
            <div class="col-md-8">
                <h1 class="display-6">{{ data_get($recipe, 'title') }}</h1>
                <p class="text-muted">{{ ucfirst(data_get($recipe, 'category')) }} · {{ data_get($recipe, 'time') ?? '' }}</p>
                <p class="lead">{{ data_get($recipe, 'excerpt') }}</p>

                <hr>
                <h5>Directions (stub)</h5>
                <p class="text-muted">This is a placeholder show page. Replace with real recipe content.</p>
            </div>
            <div class="col-md-4">
                <div class="card">
                    <div class="card-body">
                        <h6>Quick info</h6>
                        <p class="mb-1"><strong>Category:</strong> {{ ucfirst(data_get($recipe, 'category')) }}</p>
                        <p class="mb-1"><strong>Prep time:</strong> {{ data_get($recipe, 'time') ?? '—' }}</p>
                        <a href="{{ url('/recipes') }}" class="btn btn-sm btn-link">Back to recipes</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

</x-app-layout>
