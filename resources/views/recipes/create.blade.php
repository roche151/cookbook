<x-app-layout>
    <x-slot name="title">Create Recipe</x-slot>

    <div class="container py-5">
        <h1 class="h4 mb-3">Create Recipe</h1>

        <form action="{{ route('recipes.store') }}" method="POST" class="row g-3">
            @csrf
            <div class="col-12">
                <label class="form-label">Title</label>
                <input name="title" class="form-control" value="{{ old('title') }}">
            </div>
            <div class="col-md-6">
                <label class="form-label">Category</label>
                <input name="category" class="form-control" value="{{ old('category') }}" placeholder="breakfast, lunch, dinner, dessert">
            </div>
            <div class="col-12">
                <label class="form-label">Excerpt</label>
                <textarea name="excerpt" class="form-control" rows="4">{{ old('excerpt') }}</textarea>
            </div>

            <div class="col-12">
                <button class="btn btn-primary" type="submit">Create (stub)</button>
                <a href="{{ url('/recipes') }}" class="btn btn-link">Cancel</a>
            </div>
        </form>
    </div>

</x-app-layout>
