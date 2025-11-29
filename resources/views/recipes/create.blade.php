<x-app-layout>
    <x-slot name="title">Create Recipe</x-slot>

    <div class="container py-5">
        <nav aria-label="breadcrumb" class="mb-3">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ url('/') }}">Home</a></li>
                <li class="breadcrumb-item"><a href="{{ url('/recipes') }}">Recipes</a></li>
                <li class="breadcrumb-item active" aria-current="page">Create</li>
            </ol>
        </nav>

        <div class="mb-4">
            <h1 class="h3 mb-2">
                <i class="fa-solid fa-plus-circle me-2 text-primary"></i>Create New Recipe
            </h1>
            <p class="text-muted mb-0">Share your culinary creation with the community</p>
        </div>

        <div class="card shadow-sm">
            <div class="card-body p-4">
                @include('recipes._form', [
                    'action' => route('recipes.store'),
                    'method' => 'POST',
                    'buttonText' => 'Create Recipe',
                    'recipe' => null,
                    'tags' => $tags,
                ])
            </div>
        </div>
    </div>

</x-app-layout>
