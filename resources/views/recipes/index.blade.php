<x-app-layout>
    <x-slot name="title">{{ $title ?? 'Recipes' }}</x-slot>

    <div class="container py-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="h4 mb-0">{{ $title ?? 'Recipes' }}</h1>
            @auth
                <a href="{{ route('recipes.create') }}" class="btn btn-primary">Create Recipe</a>
            @endauth
        </div>

        <form method="GET" class="mb-4">
            <div class="input-group">
                <input type="text" name="q" class="form-control" placeholder="Search recipes..." value="{{ $q ?? '' }}">
                <button class="btn btn-outline-secondary" type="submit">Search</button>
                @if($q)
                    <a href="{{ url()->current() }}" class="btn btn-outline-secondary">Clear</a>
                @endif
            </div>
        </form>

        @if(isset($tag) && $tag)
            <div class="alert alert-info mb-4">
                Filtered by tag: <strong>{{ $tag }}</strong> 
                <a href="{{ url()->current() }}" class="alert-link ms-2">Clear filter</a>
            </div>
        @endif

        @if($recipes->count())
            <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
                @foreach($recipes as $recipe)
                    <div class="col">
                        @include('recipes._card', ['recipe' => $recipe])
                    </div>
                @endforeach
            </div>

            <div class="mt-4">
                {{ $recipes->links() }}
            </div>
        @else
            <div class="alert alert-info">
                {{ $emptyMessage ?? 'No recipes found.' }}
            </div>
        @endif
    </div>

</x-app-layout>