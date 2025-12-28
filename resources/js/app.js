import './bootstrap';
import 'bootstrap';
import './directions';
import './ingredients';
import './image-upload';
import './tag-filter';

// Initialize delete confirmation dialogs
import bootbox from 'bootbox';
window.bootbox = bootbox;

// Toast notification helper
function showToast(message, type = 'success') {
    const toastContainer = document.getElementById('toast-container') || createToastContainer();
    const toast = document.createElement('div');
    toast.className = `toast align-items-center text-bg-${type} border-0`;
    toast.setAttribute('role', 'alert');
    toast.setAttribute('aria-live', 'assertive');
    toast.setAttribute('aria-atomic', 'true');
    
    toast.innerHTML = `
        <div class="d-flex">
            <div class="toast-body">${message}</div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
    `;
    
    toastContainer.appendChild(toast);
    const bsToast = new window.bootstrap.Toast(toast, { delay: 3000 });
    bsToast.show();
    
    toast.addEventListener('hidden.bs.toast', () => toast.remove());
}

function createToastContainer() {
    const container = document.createElement('div');
    container.id = 'toast-container';
    container.className = 'toast-container position-fixed top-0 end-0 p-3';
    container.style.zIndex = '9999';
    document.body.appendChild(container);
    return container;
}

// Handle delete button confirmations
document.addEventListener('DOMContentLoaded', function() {
    if (window.bootstrap && window.bootstrap.Tooltip) {
        document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach((el) => {
            new window.bootstrap.Tooltip(el);
        });
    }

    document.addEventListener('click', function(e) {
        const deleteBtn = e.target.closest('.js-delete-btn');
        if (deleteBtn) {
            e.preventDefault();
            const confirmMessage = deleteBtn.dataset.confirm || 'Are you sure?';
            const form = deleteBtn.closest('form');
            
            if (form) {
                bootbox.confirm({
                    message: confirmMessage,
                    buttons: {
                        confirm: {
                            label: 'Yes',
                            className: 'btn-danger'
                        },
                        cancel: {
                            label: 'No',
                            className: 'btn-secondary'
                        }
                    },
                    callback: function(result) {
                        if (result) {
                            form.submit();
                        }
                    }
                });
            }
        }
    });
    
    // Handle collection modal and related actions
    let currentCollectionModal = null;
    let currentRecipeId = null;

    // Open collection modal
    document.addEventListener('click', function(e) {
        const button = e.target.closest('.js-open-collection-modal');
        if (!button) return;
        e.preventDefault();
        
        currentRecipeId = button.dataset.recipeId;
        const recipeSlug = button.dataset.recipeSlug;
        
        // Find or create modal
        let modal = document.getElementById(`addToCollectionModal-${currentRecipeId}`);
        if (!modal) {
            modal = document.getElementById('addToCollectionModal-0');
        }
        if (!modal) return;
        
        currentCollectionModal = new bootstrap.Modal(modal);
        currentCollectionModal.show();
        
        // Load collections
        loadCollectionsForRecipe(recipeSlug);
    });

    function loadCollectionsForRecipe(recipeSlug) {
        const collectionList = document.querySelector('.collection-list[data-recipe-id="' + currentRecipeId + '"]');
        if (!collectionList && currentRecipeId !== '0') {
            // Try with recipe id 0 (generic modal)
            const genericList = document.querySelector('.collection-list[data-recipe-id="0"]');
            if (genericList) {
                genericList.dataset.recipeId = currentRecipeId;
            }
        }
        const list = document.querySelector('.collection-list[data-recipe-id="' + currentRecipeId + '"]');
        if (!list) return;
        
        list.innerHTML = '<div class="text-center py-3"><div class="spinner-border spinner-border-sm text-primary" role="status"><span class="visually-hidden">Loading...</span></div></div>';
        
        fetch(`/recipes/${recipeSlug}/collections`, {
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(r => r.json())
        .then(data => {
            if (data && data.success && data.collections) {
                if (data.collections.length === 0) {
                    list.innerHTML = '<p class="text-muted text-center py-3">No collections yet. Create one below!</p>';
                    // Pre-fill "Favourites" for first collection
                    const nameInput = document.querySelector('.js-create-collection-inline input[name="name"]');
                    if (nameInput && !nameInput.value) {
                        nameInput.value = 'Favourites';
                        nameInput.focus();
                    }
                } else {
                    list.innerHTML = data.collections.map(c => `
                        <div class="form-check mb-2">
                            <input class="form-check-input js-collection-checkbox" type="checkbox" value="${c.id}" id="collection-${c.id}" ${c.has_recipe ? 'checked' : ''} data-recipe-slug="${recipeSlug}">
                            <label class="form-check-label" for="collection-${c.id}">
                                <i class="fa-solid fa-folder me-2"></i>${c.name}
                            </label>
                        </div>
                    `).join('');
                }
            } else {
                list.innerHTML = '<p class="text-danger text-center py-3">Failed to load collections</p>';
            }
        })
        .catch(err => {
            console.error('Load collections error:', err);
            list.innerHTML = '<p class="text-danger text-center py-3">Error loading collections</p>';
        });
    }

    // Handle collection checkbox toggle
    document.addEventListener('change', function(e) {
        const checkbox = e.target.closest('.js-collection-checkbox');
        if (!checkbox) return;
        
        const collectionId = checkbox.value;
        const recipeSlug = checkbox.dataset.recipeSlug;
        const isChecked = checkbox.checked;
        
        checkbox.disabled = true;
        
        if (isChecked) {
            // Add to collection
            fetch(`/recipes/${recipeSlug}/add-to-collection`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({ collection_id: collectionId })
            })
            .then(r => r.json())
            .then(data => {
                if (data && data.success) {
                    showToast(data.message, 'success');
                } else {
                    checkbox.checked = false;
                    showToast(data.message || 'Failed to add recipe', 'danger');
                }
            })
            .catch(err => {
                console.error('Add to collection error:', err);
                checkbox.checked = false;
                showToast('An error occurred', 'danger');
            })
            .finally(() => {
                checkbox.disabled = false;
            });
        } else {
            // Remove from collection
            const label = checkbox.parentElement.querySelector('label');
            const collectionName = label ? label.textContent.trim() : 'collection';
            
            bootbox.confirm({
                message: `Remove this recipe from ${collectionName}?`,
                buttons: {
                    confirm: { label: 'Remove', className: 'btn-danger' },
                    cancel: { label: 'Cancel', className: 'btn-secondary' }
                },
                callback: function(result) {
                    if (result) {
                        // Get collection slug - need to find it
                        fetch(`/recipes/${recipeSlug}/collections`, {
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                                'Accept': 'application/json'
                            }
                        })
                        .then(r => r.json())
                        .then(data => {
                            const collection = data.collections.find(c => c.id == collectionId);
                            if (collection) {
                                return fetch(`/collections/${collection.slug}/recipes/${recipeSlug}`, {
                                    method: 'DELETE',
                                    headers: {
                                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                                        'Accept': 'application/json',
                                        'X-Requested-With': 'XMLHttpRequest'
                                    }
                                });
                            }
                        })
                        .then(r => r.json())
                        .then(data => {
                            if (data && data.success) {
                                showToast(data.message, 'success');
                            } else {
                                checkbox.checked = true;
                                showToast('Failed to remove recipe', 'danger');
                            }
                        })
                        .catch(err => {
                            console.error('Remove from collection error:', err);
                            checkbox.checked = true;
                            showToast('An error occurred', 'danger');
                        })
                        .finally(() => {
                            checkbox.disabled = false;
                        });
                    } else {
                        checkbox.checked = true;
                        checkbox.disabled = false;
                    }
                }
            });
        }
    });

    // Handle inline collection creation
    document.addEventListener('submit', function(e) {
        const form = e.target.closest('.js-create-collection-inline');
        if (!form) return;
        e.preventDefault();
        
        const nameInput = form.querySelector('input[name="name"]');
        const descInput = form.querySelector('textarea[name="description"]');
        const recipeId = form.dataset.recipeId;
        const submitBtn = form.querySelector('button[type="submit"]');
        
        submitBtn.disabled = true;
        
        fetch('/collections', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({
                name: nameInput.value,
                description: descInput.value
            })
        })
        .then(r => r.json())
        .then(data => {
            if (data && data.success && data.collection) {
                // Add recipe to new collection
                const recipeSlug = document.querySelector('.js-collection-checkbox')?.dataset.recipeSlug;
                if (recipeSlug) {
                    return fetch(`/recipes/${recipeSlug}/add-to-collection`, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'Content-Type': 'application/json',
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({ collection_id: data.collection.id })
                    }).then(r => r.json());
                }
                return { success: true, message: 'Collection created' };
            } else {
                throw new Error('Failed to create collection');
            }
        })
        .then(data => {
            showToast('Collection created and recipe added!', 'success');
            nameInput.value = '';
            descInput.value = '';
            // Reload collection list
            const recipeSlug = document.querySelector('.js-collection-checkbox')?.dataset.recipeSlug;
            if (recipeSlug) {
                loadCollectionsForRecipe(recipeSlug);
            }
        })
        .catch(err => {
            console.error('Create collection error:', err);
            showToast('Failed to create collection', 'danger');
        })
        .finally(() => {
            submitBtn.disabled = false;
        });
    });

    // Handle remove from collection on collection show page
    document.addEventListener('submit', function(e) {
        const form = e.target.closest('.js-remove-from-collection-form');
        if (!form) return;
        e.preventDefault();
        
        bootbox.confirm({
            message: 'Remove this recipe from the collection?',
            buttons: {
                confirm: { label: 'Remove', className: 'btn-danger' },
                cancel: { label: 'Cancel', className: 'btn-secondary' }
            },
            callback: function(result) {
                if (result) {
                    form.submit();
                }
            }
        });
    });

    // Edit form confirmation (existing)
    document.addEventListener('submit', function(e) {
        const editForm = e.target.closest('.js-edit-confirm-form');
        if (!editForm || editForm.dataset.confirmed) return;
        e.preventDefault();
        bootbox.confirm({
            message: 'Save changes to this recipe?',
            buttons: {
                confirm: { label: 'Save', className: 'btn-primary' },
                cancel: { label: 'Cancel', className: 'btn-secondary' }
            },
            callback: function(result) {
                if (result) {
                    editForm.dataset.confirmed = 'true';
                    editForm.submit();
                }
            }
        });
    });
});

// Collection management functions (global scope for inline onclick handlers)
window.editCollection = function(collectionId, name, description, slug = null) {
    const modal = document.getElementById('editCollectionModal');
    const form = document.getElementById('editCollectionForm');
    const nameInput = document.getElementById('edit-collection-name');
    const descInput = document.getElementById('edit-collection-description');
    
    if (!form || !nameInput || !descInput) return;
    
    // Determine the action URL
    let actionUrl;
    if (slug) {
        actionUrl = `/collections/${slug}`;
    } else {
        // We're on the index page, need to find the slug from the DOM
        const collectionCard = document.querySelector(`[onclick*="editCollection('${collectionId}'"]`)?.closest('.collection-card');
        const viewLink = collectionCard?.querySelector('a[href*="/my-collections/"]');
        if (viewLink) {
            const urlSlug = viewLink.href.split('/my-collections/')[1];
            actionUrl = `/collections/${urlSlug}`;
        } else {
            console.error('Could not determine collection slug');
            return;
        }
    }
    
    form.action = actionUrl;
    nameInput.value = name;
    descInput.value = description;
    
    const bsModal = new bootstrap.Modal(modal);
    bsModal.show();
};

window.deleteCollection = function(slug, name, redirect = false) {
    bootbox.confirm({
        message: `Delete collection "${name}"? This will not delete the recipes, only the collection.`,
        buttons: {
            confirm: { label: 'Delete', className: 'btn-danger' },
            cancel: { label: 'Cancel', className: 'btn-secondary' }
        },
        callback: function(result) {
            if (result) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = `/collections/${slug}`;
                
                const csrfInput = document.createElement('input');
                csrfInput.type = 'hidden';
                csrfInput.name = '_token';
                csrfInput.value = document.querySelector('meta[name="csrf-token"]').content;
                
                const methodInput = document.createElement('input');
                methodInput.type = 'hidden';
                methodInput.name = '_method';
                methodInput.value = 'DELETE';
                
                form.appendChild(csrfInput);
                form.appendChild(methodInput);
                document.body.appendChild(form);
                form.submit();
            }
        }
    });
};
