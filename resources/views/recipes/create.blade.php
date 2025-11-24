<x-app-layout>
    <x-slot name="title">Create Recipe</x-slot>

    <div class="container py-5">
        <h1 class="h4 mb-3">Create Recipe</h1>

        <form action="{{ route('recipes.store') }}" method="POST" class="row g-3">
            @csrf
            @if ($errors->any())
                <div class="col-12">
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            @endif
            <div class="col-12">
                <label class="form-label">Title</label>
                <input name="title" class="form-control" value="{{ old('title') }}">
            </div>
            <div class="col-12">
                <label class="form-label">Categories</label>
                <div class="d-flex flex-wrap gap-2">
                    @foreach($categories as $cat)
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="categories[]" value="{{ $cat->id }}" id="cat-{{ $cat->id }}"
                                {{ (is_array(old('categories')) && in_array($cat->id, old('categories'))) ? 'checked' : '' }}>
                            <label class="form-check-label" for="cat-{{ $cat->id }}">{{ $cat->name }}</label>
                        </div>
                    @endforeach
                </div>
            </div>
            <div class="col-md-6">
                <label class="form-label">Image URL (optional)</label>
                <input name="image" class="form-control" value="{{ old('image') }}" placeholder="https://...">
            </div>
            <div class="col-md-4">
                <label class="form-label">Time (optional)</label>
                <input name="time" class="form-control" value="{{ old('time') }}" placeholder="e.g. 20 min">
            </div>
            <div class="col-md-4">
                <label class="form-label">Rating (optional)</label>
                <input name="rating" type="number" step="0.1" min="0" max="5" class="form-control" value="{{ old('rating') }}" placeholder="0.0 - 5.0">
            </div>
            <div class="col-12">
                <label class="form-label">Excerpt</label>
                <textarea name="excerpt" class="form-control" rows="4">{{ old('excerpt') }}</textarea>
            </div>

            <div class="col-12">
                <button class="btn btn-primary" type="submit">Create Recipe</button>
                <a href="{{ url('/recipes') }}" class="btn btn-link">Cancel</a>
            </div>
        </form>
    </div>

</x-app-layout>
