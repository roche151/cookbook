<x-app-layout>
    <x-slot name="title">Edit Recipe</x-slot>

    <div class="container py-md-5">
        <nav aria-label="breadcrumb" class="mb-3">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ url('/') }}">Home</a></li>
                <li class="breadcrumb-item"><a href="{{ url('/recipes') }}">Recipes</a></li>
                <li class="breadcrumb-item"><a href="{{ route('recipes.show', $recipe) }}">{{ data_get($recipe, 'title') }}</a></li>
                <li class="breadcrumb-item active" aria-current="page">Edit</li>
            </ol>
        </nav>

        <div class="mb-4">
            <h1 class="h3 mb-2">
                <i class="fa-solid fa-pen-to-square me-2 text-primary"></i>Edit Recipe
            </h1>
            <p class="text-muted mb-0">Update your recipe details and save your changes</p>
        </div>

        <div class="card shadow-sm">
            <div class="card-body p-4">
                @include('recipes._form', [
                    'action' => route('recipes.update', $recipe->slug),
                    'method' => 'PATCH',
                    'buttonText' => 'Update Recipe',
                    'recipe' => $recipe,
                    'tags' => $tags,
                    'confirmOnSubmit' => true,
                ])
            </div>
        </div>
    </div>

</x-app-layout>
