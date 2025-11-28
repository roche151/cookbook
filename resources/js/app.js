import './bootstrap';
import 'bootstrap';
import './directions';
import './ingredients';
import './image-upload';

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
        const form = e.target.closest('.js-favorite-form');
        if (form) {
            e.preventDefault();
            
            const button = form.querySelector('button[type="submit"]');
            const icon = button.querySelector('i');
            const url = form.action;
            
            // Disable button during request
            button.disabled = true;
            
            fetch(url, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Update button appearance
                    if (data.favorited) {
                        button.classList.remove('btn-outline-danger');
                        button.classList.add('btn-danger');
                        icon.classList.remove('fa-regular');
                        icon.classList.add('fa-solid');
                        button.title = 'Remove from favorites';
                    } else {
                        button.classList.remove('btn-danger');
                        button.classList.add('btn-outline-danger');
                        icon.classList.remove('fa-solid');
                        icon.classList.add('fa-regular');
                        button.title = 'Add to favorites';
                    }
                    
                    // Show toast notification
                    showToast(data.message, 'success');
                }
                button.disabled = false;
            })
            .catch(error => {
                console.error('Error:', error);
                showToast('An error occurred. Please try again.', 'danger');
                button.disabled = false;
            });
        }
    });
});
