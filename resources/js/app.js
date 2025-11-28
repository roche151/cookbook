import './bootstrap';
import 'bootstrap';
import './directions';
import './ingredients';
import './image-upload';

// Initialize delete confirmation dialogs
import bootbox from 'bootbox';
window.bootbox = bootbox;

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
});
