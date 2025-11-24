<x-app-layout>
    <x-slot name="title">Edit Recipe</x-slot>

    <div class="container py-5">
        <h1 class="h4 mb-3">Edit Recipe</h1>

        @include('recipes._form', [
            'action' => route('recipes.update', $recipe->id),
            'method' => 'PUT',
            'buttonText' => 'Update Recipe',
            'recipe' => $recipe,
            'tags' => $tags,
        ])
    </div>

</x-app-layout>
