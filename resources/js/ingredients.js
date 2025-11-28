// Ingredients UI helper: add/remove/reorder ingredient items.
function qs(selector, ctx = document) {
    return ctx.querySelector(selector);
}

function qsa(selector, ctx = document) {
    return Array.from(ctx.querySelectorAll(selector));
}

function updateIndices(container) {
    const items = qsa('.ingredient-item', container);
    items.forEach((item, idx) => {
        item.setAttribute('data-index', idx);
        let indexElem = qs('.ingredient-index', item);
        if (!indexElem) {
            const cardBody = qs('.card-body', item);
            indexElem = document.createElement('div');
            indexElem.className = 'ingredient-index pe-2';
            if (cardBody) cardBody.insertBefore(indexElem, cardBody.firstChild);
            else item.insertBefore(indexElem, item.firstChild);
        }
        indexElem.textContent = String(idx + 1);

        const idInput = qs('input[name$="[id]"]', item);
        const sortInput = qs('.ingredient-sort-order', item);
        const nameInput = qs('.ingredient-name', item);
        const amountInput = qs('.ingredient-amount', item);
        if (sortInput) sortInput.value = idx;
        if (idInput) idInput.name = `ingredients[${idx}][id]`;
        if (sortInput) sortInput.name = `ingredients[${idx}][sort_order]`;
        if (nameInput) nameInput.name = `ingredients[${idx}][name]`;
        if (amountInput) amountInput.name = `ingredients[${idx}][amount]`;
    });
}

function makeItem(index, data = {}) {
    const wrapper = document.createElement('div');
    wrapper.className = 'card mb-2 ingredient-item';
    wrapper.setAttribute('data-index', index);

    const bodyHtml = `
        <div class="card-body p-2 d-flex gap-2 align-items-start">
            <div class="ingredient-index pe-2">${index + 1}</div>
            <div class="flex-grow-1 d-flex gap-2">
                <input type="hidden" name="ingredients[${index}][id]" value="${data.id || ''}">
                <input type="hidden" name="ingredients[${index}][sort_order]" class="ingredient-sort-order" value="${data.sort_order ?? index}">
                <input type="text" name="ingredients[${index}][amount]" class="form-control ingredient-amount" placeholder="e.g. 100g" value="${data.amount || ''}" style="width:140px">
                <input type="text" name="ingredients[${index}][name]" class="form-control ingredient-name" placeholder="Ingredient" value="${data.name || ''}">
            </div>
            <div class="d-flex flex-column gap-1">
                <button type="button" class="btn btn-sm btn-outline-secondary js-ing-up" title="Move up">↑</button>
                <button type="button" class="btn btn-sm btn-outline-secondary js-ing-down" title="Move down">↓</button>
                <button type="button" class="btn btn-sm btn-outline-danger js-ing-remove" title="Remove">✕</button>
            </div>
        </div>`;

    wrapper.innerHTML = bodyHtml;
    return wrapper;
}

export function initIngredients(containerSelector = '#ingredients-container') {
    const container = document.querySelector(containerSelector);
    if (!container) return;

    const addBtn = document.getElementById('js-add-ingredient');

    function attachHandlers(item) {
        const up = item.querySelector('.js-ing-up');
        const down = item.querySelector('.js-ing-down');
        const rem = item.querySelector('.js-ing-remove');

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

    qsa('.ingredient-item', container).forEach(el => attachHandlers(el));
    updateIndices(container);

    addBtn && addBtn.addEventListener('click', (e) => {
        const nextIndex = container.querySelectorAll('.ingredient-item').length;
        const newItem = makeItem(nextIndex);
        container.appendChild(newItem);
        attachHandlers(newItem);
        updateIndices(container);
        const nameInput = newItem.querySelector('.ingredient-name');
        if (nameInput) nameInput.focus();
    });

    const form = container.closest('form');
    if (form) {
        form.addEventListener('submit', () => updateIndices(container));
    }
}

document.addEventListener('DOMContentLoaded', () => initIngredients());
