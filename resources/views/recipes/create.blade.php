<x-app-layout>
    <x-slot name="title">Create Recipe</x-slot>

    <div class="container py-5">
        <h1 class="h4 mb-3">Create Recipe</h1>

        @include('recipes._form', [
            'action' => route('recipes.store'),
            'method' => 'POST',
            'buttonText' => 'Create Recipe',
            'recipe' => null,
            'tags' => $tags,
        ])
    </div>

</x-app-layout>
