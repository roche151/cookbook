import axios from 'axios';
window.axios = axios;

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

// Import Bootstrap and expose it globally
import * as bootstrap from 'bootstrap';
window.bootstrap = bootstrap;

// Provide jQuery for bootbox (bootbox expects jQuery & $.fn.modal).
// We import jQuery, expose it as `window.$` / `window.jQuery`, and shim
// a minimal `$.fn.modal` that proxies to Bootstrap 5's Modal class.
import $ from 'jquery';
window.$ = window.jQuery = $;

import { Modal } from 'bootstrap';

if (typeof $.fn.modal === 'undefined') {
	$.fn.modal = function (actionOrOptions) {
		// allow chaining
		return this.each(function () {
			var el = this;
			var instance = $.data(el, 'bs.modal');

			// If we have options object, create an instance
			if (!instance) {
				instance = new Modal(el, (typeof actionOrOptions === 'object') ? actionOrOptions : {});
				$.data(el, 'bs.modal', instance);
			}

			if (typeof actionOrOptions === 'string') {
				switch (actionOrOptions) {
					case 'show':
						instance.show();
						break;
					case 'hide':
						instance.hide();
						break;
					case 'dispose':
						instance.dispose();
						break;
					default:
						// no-op for unknown actions
						break;
				}
			} else if (typeof actionOrOptions === 'object') {
				// allow passing options when creating the modal
				// already handled above by constructing with the options
			}
		});
	};
}

// Expose a Constructor reference and VERSION so libraries that check
// `$.fn.modal.Constructor.VERSION` (like Bootbox) can read it.
try {
	if (typeof $.fn.modal.Constructor === 'undefined') {
		// Bootstrap's Modal class may not include a VERSION property in the
		// ESM build; provide a reasonable fallback.
		if (typeof Modal.VERSION === 'undefined') {
			Modal.VERSION = '5.3.2';
		}

		$.fn.modal.Constructor = Modal;
	}
} catch (e) {
	// noop - if anything goes wrong, Bootbox will handle via fallback
}
