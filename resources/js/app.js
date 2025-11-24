// Font Awesome (bundled via Vite)
import '@fortawesome/fontawesome-free/css/all.css';

import './bootstrap';
// No dialog handler here — Bootbox will be loaded and handled in the layout when not bundling.
// Bootbox 6 (bundled, no jQuery)
import bootbox from 'bootbox';

// Directions UI
import './directions';

// Delegate click handler for delete buttons (bundled via Vite)
document.addEventListener('click', function (ev) {
	var btn = ev.target.closest && ev.target.closest('.js-delete-btn');
	if (!btn) return;
	ev.preventDefault();

	var form = btn.closest('form');
	var message = btn.getAttribute('data-confirm') || 'Are you sure?';

	if (bootbox && typeof bootbox.confirm === 'function') {
		bootbox.confirm({
			message: message,
			buttons: {
				cancel: { label: 'Cancel', className: 'btn-secondary' },
				confirm: { label: 'Delete', className: 'btn-danger' }
			},
			callback: function (result) {
				if (result && form) form.submit();
			}
		});
		return;
	}

	// Fallback to native confirm
	if (confirm(message) && form) {
		form.submit();
	}
}, false);
