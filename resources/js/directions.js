// Directions UI helper: add/remove/reorder direction items.
function qs(selector, ctx = document) {
    return ctx.querySelector(selector);
}

function qsa(selector, ctx = document) {
    return Array.from(ctx.querySelectorAll(selector));
}

function updateIndices(container) {
    const items = qsa('.direction-item', container);
    items.forEach((item, idx) => {
        item.setAttribute('data-index', idx);
        // show the visible 1-based step number for the current order
        let indexElem = qs('.direction-index', item);
        if (!indexElem) {
            const cardBody = qs('.card-body', item);
            indexElem = document.createElement('div');
            indexElem.className = 'direction-index pe-2';
            // keep it simple: a small label showing the step number
            if (cardBody) cardBody.insertBefore(indexElem, cardBody.firstChild);
            else item.insertBefore(indexElem, item.firstChild);
        }
        indexElem.textContent = String(idx + 1);

        const idInput = qs('input[name$="[id]"]', item);
        const sortInput = qs('.direction-sort-order', item);
        const body = qs('.direction-body', item);
        if (sortInput) sortInput.value = idx;
        // rename inputs to match indices
        if (idInput) idInput.name = `directions[${idx}][id]`;
        if (sortInput) sortInput.name = `directions[${idx}][sort_order]`;
        if (body) body.name = `directions[${idx}][body]`;
    });
}

function makeItem(index, data = {}) {
    const wrapper = document.createElement('div');
    wrapper.className = 'card mb-2 direction-item';
    wrapper.setAttribute('data-index', index);

    const bodyHtml = `
        <div class="card-body p-2 d-flex gap-2 align-items-start">
            <div class="direction-index pe-2">${index + 1}</div>
            <div class="flex-grow-1">
                <input type="hidden" name="directions[${index}][id]" value="${data.id || ''}">
                <input type="hidden" name="directions[${index}][sort_order]" class="direction-sort-order" value="${data.sort_order ?? index}">
                <textarea name="directions[${index}][body]" class="form-control direction-body" rows="2">${data.body || ''}</textarea>
            </div>
            <div class="d-flex flex-column gap-1">
                <button type="button" class="btn btn-sm btn-outline-secondary js-dir-up" title="Move up">↑</button>
                <button type="button" class="btn btn-sm btn-outline-secondary js-dir-down" title="Move down">↓</button>
                <button type="button" class="btn btn-sm btn-outline-danger js-dir-remove" title="Remove">✕</button>
            </div>
        </div>`;

    wrapper.innerHTML = bodyHtml;
    return wrapper;
}

export function initDirections(containerSelector = '#directions-container') {
    const container = document.querySelector(containerSelector);
    if (!container) return;

    const addBtn = document.getElementById('js-add-direction');

    function attachHandlers(item) {
        const up = item.querySelector('.js-dir-up');
        const down = item.querySelector('.js-dir-down');
        const rem = item.querySelector('.js-dir-remove');

        up.addEventListener('click', () => {
            const prev = item.previousElementSibling;
            if (prev) container.insertBefore(item, prev);
            updateIndices(container);
        });

        down.addEventListener('click', () => {
            const next = item.nextElementSibling;
            if (next) container.insertBefore(next, item);
            updateIndices(container);
        });

        rem.addEventListener('click', () => {
            item.remove();
            updateIndices(container);
        });
    }

    // Attach to existing items
    qsa('.direction-item', container).forEach(el => attachHandlers(el));

    // Ensure server-rendered items show their current numbers immediately
    updateIndices(container);

    addBtn && addBtn.addEventListener('click', (e) => {
        const nextIndex = container.querySelectorAll('.direction-item').length;
        const newItem = makeItem(nextIndex);
        container.appendChild(newItem);
        attachHandlers(newItem);
        updateIndices(container);
        // focus textarea
        const ta = newItem.querySelector('.direction-body');
        if (ta) ta.focus();
    });

    // Ensure indices are correct before submit
    const form = container.closest('form');
    if (form) {
        form.addEventListener('submit', () => updateIndices(container));
    }
}

// Auto-init when loaded
document.addEventListener('DOMContentLoaded', () => initDirections());
