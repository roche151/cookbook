<x-app-layout>
    <x-slot name="title">Edit Recipe</x-slot>

    <div class="container py-md-5">
        <div class="mb-3 no-print">
            <a href="{{ route('recipes.show', $recipe) }}" class="btn btn-outline-secondary btn-sm">
                    <i class="fa-solid fa-arrow-left me-1"></i> Back to Recipe
                </a>
        </div>

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
