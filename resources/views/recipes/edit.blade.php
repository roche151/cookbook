<x-app-layout>
    <x-slot name="title">Edit Recipe</x-slot>

    <div class="container py-5">
        <nav aria-label="breadcrumb" class="mb-2">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ url('/') }}">Home</a></li>
                <li class="breadcrumb-item"><a href="{{ url('/recipes') }}">Recipes</a></li>
                <li class="breadcrumb-item"><a href="{{ url('/recipes/'.$recipe->id) }}">{{ data_get($recipe, 'title') }}</a></li>
                <li class="breadcrumb-item active" aria-current="page">Edit</li>
            </ol>
        </nav>
        <h1 class="h4 mb-3">Edit Recipe</h1>

        @include('recipes._form', [
            'action' => route('recipes.update', $recipe->slug),
            'method' => 'PATCH',
            'buttonText' => 'Update Recipe',
            'recipe' => $recipe,
            'tags' => $tags,
        ])
    </div>

</x-app-layout>
