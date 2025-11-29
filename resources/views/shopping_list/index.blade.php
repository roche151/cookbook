<x-app-layout>
    <x-slot name="title">Shopping List</x-slot>

    <div class="container py-5">
        <div class="mb-4">
            <h1 class="h3 mb-2"><i class="fa-solid fa-cart-shopping me-2 text-primary"></i>Shopping List</h1>
            <p class="text-muted mb-0">Add items, mark them done, and manage your list.</p>
        </div>

        {{-- Status flash is rendered globally in layout --}}

        <div class="card shadow-sm border-0 mb-4">
            <div class="card-body p-4">
                <form action="{{ route('shopping-list.items.store') }}" method="POST" class="mb-3 d-flex gap-2">
                    @csrf
                    <input type="text" name="title" class="form-control" placeholder="Add an item (e.g., 2 cups flour)" required>
                    <button class="btn btn-primary" type="submit">Add</button>
                </form>

                <div class="row g-4">
                    <div class="col-md-6">
                        <h6 class="fw-semibold mb-2">To Buy</h6>
                        <ul class="list-unstyled mb-0" id="uncheckedList">
                            @foreach($items->where('is_checked', false) as $item)
                                <li class="mb-2">
                                    <div class="d-flex align-items-center gap-2">
                                        <form action="{{ route('shopping-list.items.toggle', $item) }}" method="POST">
                                            @csrf
                                            @method('PATCH')
                                            <button class="btn btn-sm btn-outline-secondary" title="Mark done" aria-label="Mark done">✓</button>
                                        </form>
                                        <form action="{{ route('shopping-list.items.update', $item) }}" method="POST" class="flex-grow-1 auto-save-form" data-item-id="{{ $item->id }}">
                                            @csrf
                                            @method('PATCH')
                                            <input type="text" name="title" value="{{ $item->title }}" class="form-control form-control-sm auto-save-input">
                                        </form>
                                        <form action="{{ route('shopping-list.items.delete', $item) }}" method="POST">
                                            @csrf
                                            @method('DELETE')
                                            <button class="btn btn-sm btn-outline-secondary" title="Delete" aria-label="Delete">✕</button>
                                        </form>
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                    </div>

                    <div class="col-md-6">
                        <div class="d-flex align-items-center justify-content-between">
                            <h6 class="fw-semibold mb-2">Checked</h6>
                        </div>
                        <ul class="list-unstyled mb-0 text-muted" id="checkedList">
                            @foreach($items->where('is_checked', true) as $item)
                                <li class="mb-2">
                                    <div class="d-flex align-items-center gap-2">
                                        <form action="{{ route('shopping-list.items.toggle', $item) }}" method="POST">
                                            @csrf
                                            @method('PATCH')
                                            <button class="btn btn-sm btn-outline-secondary" title="Uncheck" aria-label="Uncheck">↺</button>
                                        </form>
                                        <span class="flex-grow-1">{{ $item->title }}</span>
                                        <form action="{{ route('shopping-list.items.delete', $item) }}" method="POST">
                                            @csrf
                                            @method('DELETE')
                                            <button class="btn btn-sm btn-outline-secondary" title="Delete" aria-label="Delete">✕</button>
                                        </form>
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                        <form action="{{ route('shopping-list.items.clear-checked') }}" method="POST">
                            @csrf
                            @method('DELETE')
                            <button class="btn btn-sm btn-outline-secondary float-end" type="submit">Clear All</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <div class="alert alert-info">
            Tip: On any recipe page, use "Add ingredients to Shopping List" to autofill items from that recipe.
        </div>
    </div>

    <style>
        #uncheckedList li, #checkedList li { background: var(--bs-body-bg); border: 1px solid var(--bs-border-color); border-radius: .5rem; padding: .5rem; }
        #checkedList .flex-grow-1 { text-decoration: line-through; }
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const inputs = document.querySelectorAll('.auto-save-input');
            
            inputs.forEach(input => {
                const form = input.closest('.auto-save-form');
                let saveTimeout;
                let originalValue = input.value;
                
                // Save on input change (debounced)
                input.addEventListener('input', function() {
                    clearTimeout(saveTimeout);
                    saveTimeout = setTimeout(() => {
                        if (input.value.trim() !== '' && input.value !== originalValue) {
                            originalValue = input.value;
                            form.submit();
                        }
                    }, 500); // Wait 500ms after typing stops
                });
                
                // Save immediately on Enter
                input.addEventListener('keydown', function(e) {
                    if (e.key === 'Enter') {
                        e.preventDefault();
                        clearTimeout(saveTimeout);
                        if (input.value.trim() !== '' && input.value !== originalValue) {
                            originalValue = input.value;
                            form.submit();
                        }
                    }
                });
            });
        });
    </script>
</x-app-layout>
