<x-app-layout>
    <x-slot name="title">My Collections</x-slot>

    <div class="container py-5">
        <nav aria-label="breadcrumb" class="mb-3">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ url('/') }}">Home</a></li>
                <li class="breadcrumb-item active" aria-current="page">My Collections</li>
            </ol>
        </nav>

        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <div class="d-flex align-items-center gap-2 mb-1">
                    <i class="fa-solid fa-layer-group text-primary"></i>
                    <h1 class="h4 mb-0">My Collections</h1>
                </div>
                <p class="text-muted mb-0">Organize and manage your recipe collections</p>
            </div>
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createCollectionModal">
                <i class="fa-solid fa-plus me-2"></i>New Collection
            </button>
        </div>

        @if($collections->isEmpty())
            <div class="text-center py-5">
                <i class="fa-solid fa-layer-group text-muted mb-3" style="font-size: 4rem; opacity: 0.3;"></i>
                <h4 class="text-muted">No Collections Yet</h4>
                <p class="text-muted mb-4">Create your first collection to start organizing recipes</p>
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createCollectionModal">
                    <i class="fa-solid fa-plus me-2"></i>Create Your First Collection
                </button>
            </div>
        @else
            <div class="row g-4">
                @foreach($collections as $collection)
                    <div class="col-md-6 col-lg-4">
                        <div class="card h-100 shadow-sm collection-card">
                            <!-- Recipe Thumbnail Grid -->
                            @if($collection->recipes_count > 0)
                                <div class="position-relative" style="height: 160px; overflow: hidden; background-color: #f8f9fa;">
                                    @php
                                        $recipes = $collection->recipes->take(4);
                                        $recipeCount = $recipes->count();
                                    @endphp
                                    <div class="h-100 collection-thumbnails collection-thumbnails-{{ $recipeCount }}">
                                        @foreach($recipes as $recipe)
                                            <div class="flex-grow-1 position-relative overflow-hidden thumbnail-item">
                                                @if($recipe->image)
                                                    <img src="{{ Storage::url($recipe->image) }}" alt="{{ $recipe->title }}" class="w-100 h-100" style="object-fit: cover;">
                                                @else
                                                    <div class="w-100 h-100 d-flex align-items-center justify-content-center bg-secondary text-white">
                                                        <i class="fa-solid fa-image" style="font-size: 2rem; opacity: 0.5;"></i>
                                                    </div>
                                                @endif
                                            </div>
                                        @endforeach
                                    </div>
                                    @if($collection->recipes_count > 4)
                                        <div class="position-absolute bottom-0 end-0 bg-dark text-white small px-2 py-1 m-2 rounded">
                                            +{{ $collection->recipes_count - 4 }}
                                        </div>
                                    @endif
                                </div>
                            @else
                                <div style="height: 160px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);" class="d-flex align-items-center justify-content-center">
                                    <div class="text-center text-white">
                                        <i class="fa-solid fa-utensils mb-2" style="font-size: 2.5rem; opacity: 0.9;"></i>
                                        <p class="small mb-0 fw-semibold">Empty Collection</p>
                                        <p class="small mb-0" style="opacity: 0.8; font-size: 0.75rem;">Add recipes to get started</p>
                                    </div>
                                </div>
                            @endif

                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <div class="flex-grow-1" style="overflow: hidden; padding-right: 0.5rem;">
                                        <h5 class="card-title mb-2" style="overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                                            <a href="{{ route('collections.show', $collection->slug) }}" class="text-decoration-none text-body">
                                                {{ $collection->name }}
                                            </a>
                                        </h5>
                                    </div>
                                    <div class="dropdown flex-shrink-0">
                                        <button class="btn btn-sm btn-secondary-outline text-muted" type="button" data-bs-toggle="dropdown">
                                            <i class="fa-solid fa-ellipsis-vertical"></i>
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-end">
                                            <li>
                                                <button class="dropdown-item" type="button" onclick="editCollection('{{ $collection->id }}', '{{ addslashes($collection->name) }}', '{{ addslashes($collection->description ?? '') }}')">
                                                    <i class="fa-solid fa-edit me-2"></i>Edit Collection
                                                </button>
                                            </li>
                                            <li><hr class="dropdown-divider"></li>
                                            <li>
                                                <button class="dropdown-item text-danger" type="button" onclick="deleteCollection('{{ $collection->slug }}', '{{ addslashes($collection->name) }}')">
                                                    <i class="fa-solid fa-trash me-2"></i>Delete Collection
                                                </button>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                                
                                @if($collection->description)
                                    <p class="card-text text-muted small mb-2" style="display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; text-overflow: ellipsis; min-height: 2.5rem;">{{ $collection->description }}</p>
                                @else
                                    <div class="mb-2" style="min-height: 2.5rem;"></div>
                                @endif
                                
                                <div class="d-flex align-items-center text-muted small mb-3">
                                    <i class="fa-solid fa-utensils me-1" style="font-size: 0.75rem;"></i>
                                    <span>{{ $collection->recipes_count }} {{ Str::plural('recipe', $collection->recipes_count) }}</span>
                                </div>
                                
                                <div class="mt-auto pt-2 border-top border-secondary border-opacity-25">
                                    <div class="d-flex justify-content-end gap-2 mt-2">
                                        <a href="{{ route('collections.show', $collection->slug) }}" class="btn btn-primary btn-sm px-3">
                                            <i class="fa-solid fa-eye me-1"></i>View
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    <!-- Create Collection Modal -->
    <div class="modal fade" id="createCollectionModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="{{ route('collections.store') }}" method="POST">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title">Create New Collection</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="collection-name" class="form-label">Collection Name</label>
                            <input type="text" class="form-control" id="collection-name" name="name" required>
                        </div>
                        <div class="mb-3">
                            <label for="collection-description" class="form-label">Description (Optional)</label>
                            <textarea class="form-control" id="collection-description" name="description" rows="3"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Create Collection</button>
                    </div>
                </form>
            </div>
        </div>
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

    <style>
        .collection-thumbnails {
            display: grid;
            gap: 1px;
            background-color: #e9ecef;
        }

        /* 1 image: full size */
        .collection-thumbnails-1 {
            grid-template-columns: 1fr;
        }

        /* 2 images: side by side */
        .collection-thumbnails-2 {
            grid-template-columns: 1fr 1fr;
        }

        /* 3 images: 2 on top, 1 on bottom */
        .collection-thumbnails-3 {
            grid-template-columns: 1fr 1fr;
            grid-template-rows: 1fr 1fr;
        }

        .collection-thumbnails-3 .thumbnail-item:last-child {
            grid-column: 1 / -1; /* span full width */
        }

        /* 4 images: 2x2 grid */
        .collection-thumbnails-4 {
            grid-template-columns: 1fr 1fr;
            grid-template-rows: 1fr 1fr;
        }

        .thumbnail-item {
            position: relative;
            overflow: hidden;
        }

        .thumbnail-item img {
            display: block;
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
    </style>
</x-app-layout>
