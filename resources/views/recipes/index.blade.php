<x-app-layout>
    <x-slot name="title">Recipes</x-slot>

    <div class="container py-5">
        <h1 class="h3 mb-3">Recipes</h1>

        @if(session('status'))
            <div class="alert alert-success">{{ session('status') }}</div>
        @endif

        <form action="{{ url('/recipes') }}" method="GET" class="mb-4">
            <div class="input-group">
                <input name="q" value="{{ $q ?? '' }}" type="search" class="form-control" placeholder="Search recipes, ingredients, or tags">
                <button class="btn btn-outline-secondary" type="submit">Search</button>
            </div>
        </form>

        @if(isset($tag) && $tag)
            <div class="mb-3">Filtered by tag: <strong>{{ $tag }}</strong></div>
        @elseif(isset($category) && $category)
            <div class="mb-3">Filtered by category: <strong>{{ $category }}</strong></div>
        @endif

        <div class="row gy-3">
            @forelse($recipes as $recipe)
                <div class="col-md-6 col-lg-4">
                    @include('recipes._card', ['recipe' => $recipe])
                </div>
            @empty
                <div class="col-12">
                    <div class="alert alert-info">No recipes found.</div>
                </div>
            @endforelse
        </div>

        <div class="mt-4">
            {{ $recipes->links() ?? '' }}
        </div>
    </div>

</x-app-layout>
