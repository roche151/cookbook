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
    
    // Handle favorite button clicks with AJAX
    document.addEventListener('submit', function(e) {
        // Edit form confirmation
        const editForm = e.target.closest('.js-edit-confirm-form');
        if (editForm && !editForm.dataset.confirmed) {
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
            return; // Do not proceed to other handlers
        }

        const form = e.target.closest('.js-favorite-form');
        if (!form) return;
        e.preventDefault();

        const button = form.querySelector('button[type="submit"]');
        const url = form.action;
        if (!button) return;

        button.disabled = true;

        fetch(url, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(r => r.json())
        .then(data => {
            if (data && data.success) {
                const favorited = !!data.favorited;
                const iconOnly = button.hasAttribute('data-icon-only');
                button.classList.toggle('btn-danger', favorited);
                button.classList.toggle('btn-outline-danger', !favorited);
                button.title = favorited ? 'Remove from favorites' : 'Add to favorites';
                if (iconOnly) {
                    // Card variant: icon only
                    button.innerHTML = `<i class=\"fa-${favorited ? 'solid' : 'regular'} fa-heart\"></i>`;
                } else {
                    // Show page variant: icon + text
                    button.innerHTML = `<i class=\"fa-${favorited ? 'solid' : 'regular'} fa-heart me-1\"></i>${favorited ? 'Unfavorite' : 'Favorite'}`;
                }
                showToast(data.message || (favorited ? 'Added to favorites' : 'Removed from favorites'), 'success');
            } else {
                showToast('Unexpected response.', 'danger');
            }
        })
        .catch(err => {
            console.error('Favorite toggle error:', err);
            showToast('An error occurred. Please try again.', 'danger');
        })
        .finally(() => {
            button.disabled = false;
        });
    });
});
