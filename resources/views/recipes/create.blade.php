<x-app-layout>
    <x-slot name="title">Create Recipe</x-slot>

    <div class="container py-5">
        <nav aria-label="breadcrumb" class="mb-2">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ url('/') }}">Home</a></li>
                <li class="breadcrumb-item"><a href="{{ url('/recipes') }}">Recipes</a></li>
                <li class="breadcrumb-item active" aria-current="page">Create</li>
            </ol>
        </nav>
        <h1 class="h4 mb-3">Create Recipe</h1>

        @include('recipes._form', [
            'action' => route('recipes.store'),
            'method' => 'POST',
            'buttonText' => 'Save',
            'recipe' => null,
            'tags' => $tags,
        ])
    </div>

</x-app-layout>
