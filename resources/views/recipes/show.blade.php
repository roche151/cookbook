<x-app-layout>
    <x-slot name="title">{{ data_get($recipe, 'title') }}</x-slot>

    <div class="container py-5">
        <div class="row">
            <div class="col-md-8">
                <h1 class="display-6">{{ data_get($recipe, 'title') }}</h1>
                @php $tags = data_get($recipe, 'tags'); @endphp
                <p class="text-muted">
                    @if($tags && is_iterable($tags) && count($tags))
                        @foreach($tags as $t)
                            <a href="/recipes?tag={{ urlencode($t->name ?? $t['name'] ?? (string)$t) }}" class="text-decoration-none small me-1">{{ $t->name ?? ($t['name'] ?? ucfirst((string)$t)) }}</a>
                        @endforeach
                    @elseif(data_get($recipe, 'category'))
                        {{ ucfirst(data_get($recipe, 'category')) }}
                    @endif
                    · {{ data_get($recipe, 'time') ?? '' }}
                </p>
                <p class="lead">{{ data_get($recipe, 'excerpt') }}</p>

                <hr>
                <h5>Directions (stub)</h5>
                <p class="text-muted">This is a placeholder show page. Replace with real recipe content.</p>
            </div>
            <div class="col-md-4">
                <div class="card">
                    <div class="card-body">
                        <h6>Quick info</h6>
                        <p class="mb-1"><strong>Tags:</strong>
                            @if($tags && is_iterable($tags) && count($tags))
                                @foreach($tags as $t)
                                    <a href="/recipes?tag={{ urlencode($t->name ?? $t['name'] ?? (string)$t) }}" class="text-decoration-none me-2">{{ $t->name ?? ($t['name'] ?? ucfirst((string)$t)) }}</a>
                                @endforeach
                            @else
                                {{ ucfirst(data_get($recipe, 'category')) }}
                            @endif
                        </p>
                        <p class="mb-1"><strong>Prep time:</strong> {{ data_get($recipe, 'time') ?? '—' }}</p>
                        <div class="d-flex gap-2">
                            <a href="{{ url('/recipes') }}" class="btn btn-sm btn-link">Back to recipes</a>
                            <a href="{{ route('recipes.edit', $recipe->id) }}" class="btn btn-sm btn-outline-secondary">Edit</a>

                            <form action="{{ route('recipes.destroy', $recipe->id) }}" method="POST" onsubmit="return confirm('Delete this recipe?');">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-sm btn-danger" type="submit">Delete</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

</x-app-layout>
