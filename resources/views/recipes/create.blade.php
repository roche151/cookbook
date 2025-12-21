<x-app-layout>
    <x-slot name="title">Create Recipe</x-slot>

    <div class="container py-5">
        <nav aria-label="breadcrumb" class="mb-3">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ url('/') }}">Home</a></li>
                <li class="breadcrumb-item"><a href="{{ url('/recipes') }}">Recipes</a></li>
                <li class="breadcrumb-item active" aria-current="page">Create</li>
            </ol>
        </nav>

        <div class="mb-4">
            <h1 class="h3 mb-2">
                <i class="fa-solid fa-plus-circle me-2 text-primary"></i>Create New Recipe
            </h1>
            <p class="text-muted mb-0">Share your culinary creation with the community</p>
        </div>

        <!-- URL Import Card -->
        <div class="card shadow-sm mb-4">
            <div class="card-body p-4">
                <h6 class="card-title mb-3 fw-semibold">
                    <i class="fa-solid fa-download me-2 text-primary"></i>Import from URL
                </h6>
                <p class="text-muted small mb-3">Paste a recipe URL from popular cooking sites (BBC Food, AllRecipes, etc.) to automatically populate the form below.</p>
                <div class="input-group">
                    <input type="url" class="form-control" id="import-url">
                    <button class="btn btn-primary" type="button" id="import-btn" onclick="importRecipe()">
                        <i class="fa-solid fa-download me-1"></i>Import
                    </button>
                </div>
                <div id="import-status" class="mt-2"></div>
            </div>
        </div>

        <div class="card shadow-sm">
            <div class="card-body p-4">
                @include('recipes._form', [
                    'action' => route('recipes.store'),
                    'method' => 'POST',
                    'buttonText' => 'Create Recipe',
                    'recipe' => null,
                    'tags' => $tags,
                ])
            </div>
        </div>
    </div>

    <script>
        async function importRecipe() {
            const url = document.getElementById('import-url').value;
            const btn = document.getElementById('import-btn');
            const status = document.getElementById('import-status');
            
            if (!url) {
                status.innerHTML = '<div class="alert alert-warning alert-dismissible fade show" role="alert">Please enter a URL<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>';
                return;
            }
            
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Importing...';
            status.innerHTML = '<div class="alert alert-info">Fetching recipe data...</div>';
            
            try {
                const response = await fetch('{{ route('recipes.import') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({ url })
                });
                
                const data = await response.json();
                
                if (response.ok) {
                    try {
                        populateForm(data);
                        status.innerHTML = '<div class="alert alert-success alert-dismissible fade show" role="alert">Recipe imported successfully! Review and edit as needed.<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>';
                    } catch (formError) {
                        console.error('Form population error:', formError);
                        status.innerHTML = `<div class="alert alert-danger alert-dismissible fade show" role="alert">Error populating form: ${formError.message}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>`;
                    }
                } else {
                    status.innerHTML = `<div class="alert alert-danger alert-dismissible fade show" role="alert">${data.message || 'Failed to import recipe'}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>`;
                }
            } catch (error) {
                console.error('Import error:', error);
                status.innerHTML = '<div class="alert alert-danger alert-dismissible fade show" role="alert">Error importing recipe. Please try again.<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>';
            } finally {
                btn.disabled = false;
                btn.innerHTML = '<i class="fa-solid fa-download me-1"></i>Import';
            }
        }
        
        function populateForm(data) {
            console.log('Populating form with data:', data);
            
            // Helper function to find input by name or id
            const getInput = (id, name) => {
                return document.getElementById(id) || document.querySelector(`[name="${name}"]`);
            };
            
            // Populate title
            if (data.title) {
                const titleEl = getInput('title', 'title');
                if (titleEl) titleEl.value = data.title;
            }
            
            // Populate description
            console.log('Description from API:', data.description, 'Length:', data.description ? data.description.length : 0);
            if (data.description) {
                const descEl = getInput('description', 'description');
                console.log('Description element found:', !!descEl);
                if (descEl) {
                    descEl.value = data.description;
                    console.log('Description populated, new value:', descEl.value.substring(0, 50));
                }
            }
            
            // Populate time (it's already in minutes from backend)
            if (data.time !== null && data.time !== undefined) {
                const totalMinutes = parseInt(data.time);
                const hours = Math.floor(totalMinutes / 60);
                const minutes = totalMinutes % 60;
                const hoursEl = getInput('time_hours', 'time_hours');
                const minsEl = getInput('time_minutes', 'time_minutes');
                if (hoursEl) hoursEl.value = hours;
                if (minsEl) minsEl.value = minutes;
            }
            
            // Populate difficulty if exists
            const diffEl = getInput('difficulty', 'difficulty');
            if (data.difficulty && diffEl) {
                diffEl.value = data.difficulty;
            }
            
            // Populate image if URL provided
            if (data.imageUrl) {
                const imagePreview = document.querySelector('.image-preview');
                const uploadPlaceholder = document.querySelector('.upload-placeholder');
                const removeBtn = document.querySelector('.remove-image-btn');
                
                if (imagePreview && uploadPlaceholder && removeBtn) {
                    imagePreview.src = data.imageUrl;
                    imagePreview.style.display = 'block';
                    uploadPlaceholder.style.display = 'none';
                    removeBtn.style.display = 'block';
                }
                const importedImageInput = document.querySelector('[name="imported_image_url"]');
                if (importedImageInput) importedImageInput.value = data.imageUrl;
            }

            // Persist source URL if provided
            if (data.sourceUrl) {
                const srcEl = getInput('source_url', 'source_url');
                if (srcEl) srcEl.value = data.sourceUrl;
            }
            
            // Populate ingredients
            if (data.ingredients && data.ingredients.length > 0) {
                const ingredientsContainer = document.getElementById('ingredients-container');
                if (ingredientsContainer) {
                    ingredientsContainer.innerHTML = '';
                    ingredientsContainer.setAttribute('data-next-index', '0');
                    
                    data.ingredients.forEach((ing, index) => {
                        addIngredientToForm(index, ing.amount || '', ing.name);
                    });
                    
                    ingredientsContainer.setAttribute('data-next-index', data.ingredients.length);
                }
            }
            
            // Populate directions
            if (data.directions && data.directions.length > 0) {
                const directionsContainer = document.getElementById('directions-container');
                if (directionsContainer) {
                    directionsContainer.innerHTML = '';
                    directionsContainer.setAttribute('data-next-index', '0');
                    
                    data.directions.forEach((dir, index) => {
                        addDirectionToForm(index, dir);
                    });
                    
                    directionsContainer.setAttribute('data-next-index', data.directions.length);
                }
            }
            
            // Scroll to form
            const formCard = document.querySelector('.card.shadow-sm:last-of-type');
            if (formCard) {
                formCard.scrollIntoView({ behavior: 'smooth' });
            }
        }
        
        function addIngredientToForm(index, amount, name) {
            const container = document.getElementById('ingredients-container');
            const html = `
                <div class="card mb-2 ingredient-item" data-index="${index}">
                    <div class="card-body p-2 d-flex gap-2 align-items-start">
                        <div class="flex-grow-1 d-flex gap-2 flex-column">
                            <div class="d-flex gap-2">
                                <input type="hidden" name="ingredients[${index}][id]" value="">
                                <input type="hidden" name="ingredients[${index}][sort_order]" class="ingredient-sort-order" value="${index}">
                                <input type="text" name="ingredients[${index}][amount]" class="form-control ingredient-amount" placeholder="e.g. 100g" value="${amount}" style="width:140px" required>
                                <input type="text" name="ingredients[${index}][name]" class="form-control ingredient-name" placeholder="Ingredient" value="${name}" required>
                            </div>
                        </div>
                        <div class="d-flex flex-column gap-1">
                            <button type="button" class="btn btn-sm btn-outline-secondary js-ing-up" title="Move up">↑</button>
                            <button type="button" class="btn btn-sm btn-outline-secondary js-ing-down" title="Move down">↓</button>
                            <button type="button" class="btn btn-sm btn-outline-danger js-ing-remove" title="Remove">✕</button>
                        </div>
                    </div>
                </div>
            `;
            container.insertAdjacentHTML('beforeend', html);
        }
        
        function addDirectionToForm(index, body) {
            const container = document.getElementById('directions-container');
            const html = `
                <div class="card mb-2 direction-item" data-index="${index}">
                    <div class="card-body p-2 d-flex gap-2 align-items-start">
                        <div class="flex-grow-1 d-flex flex-column gap-2">
                            <div>
                                <input type="hidden" name="directions[${index}][id]" value="">
                                <input type="hidden" name="directions[${index}][sort_order]" class="direction-sort-order" value="${index}">
                                <textarea name="directions[${index}][body]" class="form-control direction-body" rows="2">${body}</textarea>
                            </div>
                        </div>
                        <div class="d-flex flex-column gap-1">
                            <button type="button" class="btn btn-sm btn-outline-secondary js-dir-up" title="Move up">↑</button>
                            <button type="button" class="btn btn-sm btn-outline-secondary js-dir-down" title="Move down">↓</button>
                            <button type="button" class="btn btn-sm btn-outline-danger js-dir-remove" title="Remove">✕</button>
                        </div>
                    </div>
                </div>
            `;
            container.insertAdjacentHTML('beforeend', html);
        }
    </script>

</x-app-layout>
