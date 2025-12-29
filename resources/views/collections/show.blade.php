<x-app-layout>
    <x-slot name="title">{{ $collection->name }}</x-slot>

    <div class="container py-md-5">
        <nav aria-label="breadcrumb" class="mb-3">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ url('/') }}">Home</a></li>
                <li class="breadcrumb-item"><a href="{{ route('collections.index') }}">Collections</a></li>
                <li class="breadcrumb-item active" aria-current="page">{{ $collection->name }}</li>
            </ol>
        </nav>

        <div class="d-flex align-items-start gap-2 mb-4">
            <div>
                <div class="d-flex align-items-center gap-2 mb-1">
                    <i class="fa-solid fa-layer-group text-primary"></i>
                    <h1 class="h4 mb-0">{{ $collection->name }}</h1>
                </div>
                @if($collection->description)
                    <p class="text-muted mb-0">{{ $collection->description }}</p>
                @endif
            </div>
            <div class="dropdown">
                <button class="btn btn-outline-secondary btn-sm" type="button" data-bs-toggle="dropdown">
                    <i class="fa-solid fa-ellipsis-vertical"></i>
                </button>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li>
                        <button class="dropdown-item" type="button" onclick="editCollection('{{ $collection->id }}', '{{ addslashes($collection->name) }}', '{{ addslashes($collection->description ?? '') }}', '{{ $collection->slug }}')">
                            <i class="fa-solid fa-edit me-2"></i>Edit Collection
                        </button>
                    </li>
                    <li><hr class="dropdown-divider"></li>
                    <li>
                        <button class="dropdown-item text-danger" type="button" onclick="deleteCollection('{{ $collection->slug }}', '{{ addslashes($collection->name) }}', true)">
                            <i class="fa-solid fa-trash me-2"></i>Delete Collection
                        </button>
                    </li>
                </ul>
            </div>
        </div>

        @if($recipes->isEmpty() && !$q && empty($tags))
            <div class="text-center py-5">
                <i class="fa-solid fa-utensils text-muted mb-3" style="font-size: 4rem; opacity: 0.3;"></i>
                <h4 class="text-muted">No Recipes in This Collection</h4>
                <p class="text-muted mb-4">Browse recipes and add them to this collection</p>
                <a href="{{ route('recipes.index') }}" class="btn btn-primary">
                    <i class="fa-solid fa-magnifying-glass me-2"></i>Browse Recipes
                </a>
            </div>
        @else
            <!-- Recipes Grid -->
            @if($recipes->isEmpty())
                <div class="text-center py-5">
                    <i class="fa-solid fa-utensils text-muted mb-3" style="font-size: 4rem; opacity: 0.3;"></i>
                    <h4 class="text-muted">No Recipes in This Collection</h4>
                    <p class="text-muted mb-4">Browse recipes and add them to this collection</p>
                    <a href="{{ route('recipes.index') }}" class="btn btn-primary">
                        <i class="fa-solid fa-magnifying-glass me-2"></i>Browse Recipes
                    </a>
                </div>
            @else
                <div class="row g-4 mb-4">
                    @foreach($recipes as $recipe)
                        <div class="col-md-6 col-lg-4">
                            @include('recipes._card', ['recipe' => $recipe, 'showCollectionRemove' => true, 'collectionSlug' => $collection->slug])
                        </div>
                    @endforeach
                </div>

                <!-- Pagination -->
                {{ $recipes->links() }}
            @endif
        @endif
    </div>

    <!-- Edit Collection Modal -->
    <div class="modal fade" id="editCollectionModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="editCollectionForm" method="POST">
                    @csrf
                    @method('PATCH')
                    <div class="modal-header">
                        <h5 class="modal-title">Edit Collection</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="edit-collection-name" class="form-label">Collection Name</label>
                            <input type="text" class="form-control" id="edit-collection-name" name="name" required>
                        </div>
                        <div class="mb-3">
                            <label for="edit-collection-description" class="form-label">Description (Optional)</label>
                            <textarea class="form-control" id="edit-collection-description" name="description" rows="3"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
