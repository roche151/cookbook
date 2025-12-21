@props(['recipe'])

<!-- Add to Collection Modal -->
<div class="modal fade" id="addToCollectionModal-{{ $recipe->id ?? 0 }}" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add to Collection</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="collection-list" data-recipe-id="{{ $recipe->id ?? 0 }}">
                    <div class="text-center py-3">
                        <div class="spinner-border spinner-border-sm text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                    </div>
                </div>
                
                <hr>
                
                <div class="create-collection-inline">
                    <h6 class="mb-3">Create New Collection</h6>
                    <form class="js-create-collection-inline" data-recipe-id="{{ $recipe->id ?? 0 }}">
                        <div class="mb-3">
                            <input type="text" class="form-control" name="name" placeholder="Collection name" required>
                        </div>
                        <div class="mb-3">
                            <textarea class="form-control" name="description" rows="2" placeholder="Description (optional)"></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary btn-sm w-100">
                            <i class="fa-solid fa-plus me-2"></i>Create & Add Recipe
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
