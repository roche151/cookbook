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
                    <input type="text" name="title" class="form-control" placeholder="Add an item (e.g., 100g of oats)" required>
                    <button class="btn btn-primary" type="submit">Add</button>
                </form>

                <div class="row g-4">
                    <div class="col-lg-6">
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <h6 class="fw-semibold mb-0">To Buy <small class="badge bg-secondary">{{ $items->where('is_checked', false)->count() }}</small></h6>
                            @if($items->where('is_checked', false)->count() > 0)
                                <form action="{{ route('shopping-list.items.mark-all-checked') }}" method="POST" style="display: inline;">
                                    @csrf
                                    @method('PATCH')
                                    <button class="btn btn-sm btn-outline-secondary" type="submit" title="Mark all as bought">Mark all done</button>
                                </form>
                            @endif
                        </div>
                        @if($items->where('is_checked', false)->count() > 0)
                            <ul class="list-unstyled mb-0" id="uncheckedList">
                                @foreach($items->where('is_checked', false) as $item)
                                    <li class="mb-2 shopping-item" data-item-id="{{ $item->id }}">
                                        <div class="d-flex align-items-center gap-2">
                                            <form action="{{ route('shopping-list.items.toggle', $item) }}" method="POST" class="toggle-form">
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
                        @else
                            <div class="alert alert-info small mb-0">
                                <i class="fa-solid fa-check-circle me-2"></i>All done! Great work.
                            </div>
                        @endif
                    </div>

                    <div class="col-lg-6">
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <h6 class="fw-semibold mb-0">Checked <small class="badge bg-secondary">{{ $items->where('is_checked', true)->count() }}</small></h6>
                        </div>
                        @if($items->where('is_checked', true)->count() > 0)
                            <ul class="list-unstyled mb-3 text-muted" id="checkedList">
                                @foreach($items->where('is_checked', true) as $item)
                                    <li class="mb-2 shopping-item" data-item-id="{{ $item->id }}">
                                        <div class="d-flex align-items-center gap-2">
                                            <form action="{{ route('shopping-list.items.toggle', $item) }}" method="POST" class="toggle-form">
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
                                <button class="btn btn-sm btn-outline-secondary w-100" type="submit">Clear All</button>
                            </form>
                        @else
                            <div class="alert alert-info small mb-0">
                                <i class="fa-solid fa-inbox me-2"></i>No items checked yet.
                            </div>
                        @endif
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
        
        .shopping-item {
            transition: opacity 0.3s ease, transform 0.3s ease;
        }
        
        .shopping-item.moving-out {
            opacity: 0;
            transform: scale(0.95);
        }
        
        .toast-notification {
            position: fixed;
            bottom: 20px;
            right: 20px;
            background: var(--bs-success);
            color: white;
            padding: 12px 20px;
            border-radius: 0.5rem;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            animation: slideInUp 0.3s ease;
            z-index: 1050;
        }
        
        @keyframes slideInUp {
            from {
                transform: translateY(100%);
                opacity: 0;
            }
            to {
                transform: translateY(0);
                opacity: 1;
            }
        }
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
            
            // Toast notification helper
            function showToast(message, type = 'success') {
                const toast = document.createElement('div');
                toast.className = 'toast-notification';
                toast.style.background = type === 'success' ? 'var(--bs-success)' : 'var(--bs-info)';
                toast.innerHTML = message;
                document.body.appendChild(toast);
                
                setTimeout(() => {
                    toast.style.animation = 'slideInUp 0.3s ease reverse';
                    setTimeout(() => toast.remove(), 300);
                }, 2000);
            }
            
            // Handle toggle forms with animation and notification
            document.querySelectorAll('.toggle-form').forEach(form => {
                form.addEventListener('submit', function(e) {
                    e.preventDefault();
                    const item = form.closest('.shopping-item');
                    const isChecked = item.parentElement.id === 'checkedList';
                    
                    item.classList.add('moving-out');
                    showToast(isChecked ? '✓ Item restored' : '✓ Item marked as bought');
                    
                    setTimeout(() => {
                        form.submit();
                    }, 300);
                });
            });
        });
    </script>
</x-app-layout>
