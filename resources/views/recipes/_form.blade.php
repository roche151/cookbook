@props([
    'action',
    'method' => 'POST',
    'buttonText' => 'Save',
    'recipe' => null,
    'tags' => [],
])

<form action="{{ $action }}" method="POST" enctype="multipart/form-data" class="row g-3" novalidate>
    @csrf
    @if(strtoupper($method) !== 'POST')
        @method($method)
    @endif

    @if (false && $errors->any())
        <div class="col-12">
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    @endif

    <div class="col-12">
        <label class="form-label">Title</label>
        <input name="title" class="form-control" value="{{ old('title', optional($recipe)->title) }}">
        @if($errors->has('title'))
            <div class="text-danger small mt-1">{{ $errors->first('title') }}</div>
        @endif
    </div>

    <div class="col-12">
        <label class="form-label">Tags</label>
        <div class="d-flex flex-wrap gap-2">
            @foreach($tags as $tag)
                @php
                    $checked = false;
                    if (is_array(old('tags'))) {
                        $checked = in_array($tag->id, old('tags'));
                    } elseif (!is_null($recipe) && $recipe->tags->contains($tag->id)) {
                        $checked = true;
                    }
                @endphp
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="tags[]" value="{{ $tag->id }}" id="tag-{{ $tag->id }}" {{ $checked ? 'checked' : '' }}>
                    <label class="form-check-label" for="tag-{{ $tag->id }}">{{ $tag->name }}</label>
                </div>
            @endforeach
        </div>
        @if($errors->has('tags'))
            <div class="text-danger small mt-1">{{ $errors->first('tags') }}</div>
        @endif
    </div>

    <div class="col-12">
        <label class="form-label">Recipe Image (optional)</label>
        <div class="image-drop-zone border rounded p-4 text-center" style="cursor: pointer; position: relative; min-height: 200px; background: var(--bs-body-bg); border: 2px dashed var(--bs-border-color) !important;">
            <input type="file" name="image" accept="image/*" class="d-none" id="recipe-image">
            <div class="upload-placeholder" style="display: flex; flex-direction: column; align-items: center; justify-content: center; min-height: 150px;">
                <i class="fa-solid fa-cloud-arrow-up fa-3x mb-3 text-muted"></i>
                <p class="mb-1">Drag & drop an image here, paste from clipboard, or click to browse</p>
                <p class="text-muted small">Supports: JPG, PNG, GIF, WebP (max 5MB)</p>
            </div>
            <img class="image-preview rounded" style="display: none; max-width: 100%; max-height: 300px; object-fit: contain;" alt="Preview">
            <button type="button" class="remove-image-btn btn btn-sm btn-danger position-absolute top-0 end-0 m-2" style="display: none;">
                <i class="fa-solid fa-times"></i>
            </button>
        </div>
        @if($recipe && $recipe->image)
            <div class="mt-2">
                <p class="text-muted small mb-1">Current image:</p>
                <img src="{{ Storage::url($recipe->image) }}" alt="Current recipe image" class="img-thumbnail" style="max-height: 150px;">
            </div>
        @endif
    </div>

    <div class="col-md-4">
        <label class="form-label">Time</label>
        @php
            $timeHoursOld = old('time_hours');
            $timeMinutesOld = old('time_minutes');
            $hours = $timeHoursOld !== null ? $timeHoursOld : '';
            $minutes = $timeMinutesOld !== null ? $timeMinutesOld : '';

            if ($hours === '' && $minutes === '' && !is_null($recipe)) {
                // If `time` is numeric (stored minutes), convert to hours/minutes.
                if (is_numeric($recipe->time) && (int)$recipe->time > 0) {
                    $total = (int)$recipe->time;
                    $hours = intdiv($total, 60);
                    $minutes = $total % 60;
                } elseif ($recipe->time) {
                    // Fallback: try to parse legacy human-readable strings like "1 hour 20 minutes".
                    $matches = [];
                    preg_match('/(?:(\d+)\s*(?:hours?|hrs?|h))?\s*(?:(\d+)\s*(?:minutes?|mins?|m))?/i', $recipe->time, $matches);
                    $hours = isset($matches[1]) ? $matches[1] : '';
                    $minutes = isset($matches[2]) ? $matches[2] : '';
                }
            }
        @endphp

        <div class="d-flex gap-2 align-items-center">
            <div class="input-group" style="width:170px">
                <input name="time_hours" type="number" min="0" class="form-control" value="{{ $hours }}" placeholder="0" aria-label="Hours">
                <span class="input-group-text">hours</span>
            </div>

            <div class="input-group" style="width:170px">
                <input name="time_minutes" type="number" min="0" max="59" class="form-control" value="{{ $minutes }}" placeholder="0" aria-label="Minutes" oninput="if(this.value==='')return; if(Number(this.value) > 59) this.value = 59; if(Number(this.value) < 0) this.value = 0;">
                <span class="input-group-text">minutes</span>
            </div>
        </div>
        @if($errors->has('time'))
            <div class="text-danger small mt-1">{{ $errors->first('time') }}</div>
        @endif
    </div>

    <div class="col-12">
        <label class="form-label">Description</label>
        <textarea name="description" class="form-control" rows="4">{{ old('description', optional($recipe)->description) }}</textarea>
        @if($errors->has('description'))
            <div class="text-danger small mt-1">{{ $errors->first('description') }}</div>
        @endif
    </div>

    <div class="col-12">
        <div class="mb-3">
            <label class="form-label">Ingredients</label>

            <div id="ingredients-container" data-next-index="0">
                @php
                    $ingredientsOld = old('ingredients');
                    if (is_array($ingredientsOld)) {
                        $ingredients = $ingredientsOld;
                    } else {
                        $ingredients = [];
                        if (!is_null($recipe) && $recipe->ingredients) {
                            foreach ($recipe->ingredients as $ing) {
                                $ingredients[] = [
                                    'id' => $ing->id,
                                    'name' => $ing->name,
                                    'amount' => $ing->amount,
                                    'sort_order' => $ing->sort_order,
                                ];
                        }
                    }
                    }
                @endphp

                @foreach($ingredients as $i => $ing)
                    <div class="card mb-2 ingredient-item" data-index="{{ $i }}">
                        <div class="card-body p-2 d-flex gap-2 align-items-start">
                            <div class="flex-grow-1 d-flex gap-2 flex-column">
                                <div class="d-flex gap-2">
                                    <input type="hidden" name="ingredients[{{ $i }}][id]" value="{{ $ing['id'] ?? '' }}">
                                    <input type="hidden" name="ingredients[{{ $i }}][sort_order]" class="ingredient-sort-order" value="{{ $ing['sort_order'] ?? $i }}">
                                    <input type="text" name="ingredients[{{ $i }}][amount]" class="form-control ingredient-amount" placeholder="e.g. 100g" value="{{ $ing['amount'] ?? '' }}" style="width:140px" required>
                                    <input type="text" name="ingredients[{{ $i }}][name]" class="form-control ingredient-name" placeholder="Ingredient" value="{{ $ing['name'] ?? '' }}" required>
                                </div>
                                @if($errors->has("ingredients.{$i}.amount") || $errors->has("ingredients.{$i}.name"))
                                    <div class="text-danger small">
                                        @if($errors->has("ingredients.{$i}.amount"))
                                            {{ $errors->first("ingredients.{$i}.amount") }}
                                        @endif
                                        @if($errors->has("ingredients.{$i}.name"))
                                            {{ $errors->first("ingredients.{$i}.name") }}
                                        @endif
                                    </div>
                                @endif
                            </div>
                            <div class="d-flex flex-column gap-1">
                                <button type="button" class="btn btn-sm btn-outline-secondary js-ing-up" title="Move up">↑</button>
                                <button type="button" class="btn btn-sm btn-outline-secondary js-ing-down" title="Move down">↓</button>
                                <button type="button" class="btn btn-sm btn-outline-danger js-ing-remove" title="Remove">✕</button>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <div>
                <button type="button" id="js-add-ingredient" class="btn btn-sm btn-outline-primary mt-2">Add ingredient</button>
            </div>
        </div>

        <div class="mb-3">
            <label class="form-label">Directions</label>

            <div id="directions-container" data-next-index="0">
                @php
                    $directionsOld = old('directions');
                    if (is_array($directionsOld)) {
                        $directions = $directionsOld;
                    } else {
                        $directions = [];
                        if (!is_null($recipe) && $recipe->directions) {
                            foreach ($recipe->directions as $d) {
                                $directions[] = [
                                    'id' => $d->id,
                                    'body' => $d->body,
                                    'sort_order' => $d->sort_order,
                                ];
                            }
                        }
                    }
                @endphp

                @foreach($directions as $i => $d)
                    <div class="card mb-2 direction-item" data-index="{{ $i }}">
                        <div class="card-body p-2 d-flex gap-2 align-items-start">
                            <div class="flex-grow-1 d-flex flex-column gap-2">
                                <div>
                                    <input type="hidden" name="directions[{{ $i }}][id]" value="{{ $d['id'] ?? '' }}">
                                    <input type="hidden" name="directions[{{ $i }}][sort_order]" class="direction-sort-order" value="{{ $d['sort_order'] ?? $i }}">
                                    <textarea name="directions[{{ $i }}][body]" class="form-control direction-body" rows="2">{{ $d['body'] ?? '' }}</textarea>
                                </div>
                                @if($errors->has("directions.{$i}.body"))
                                    <div class="text-danger small">{{ $errors->first("directions.{$i}.body") }}</div>
                                @endif
                            </div>
                            <div class="d-flex flex-column gap-1">
                                <button type="button" class="btn btn-sm btn-outline-secondary js-dir-up" title="Move up">↑</button>
                                <button type="button" class="btn btn-sm btn-outline-secondary js-dir-down" title="Move down">↓</button>
                                <button type="button" class="btn btn-sm btn-outline-danger js-dir-remove" title="Remove">✕</button>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <div>
                <button type="button" id="js-add-direction" class="btn btn-sm btn-outline-primary mt-2">Add direction</button>
            </div>
        </div>
        @if($errors->has('directions'))
            <div class="text-danger small mt-1">{{ $errors->first('directions') }}</div>
        @endif
    </div>
    
    <div class="col-12">
        <button class="btn btn-primary" type="submit">{{ $buttonText }}</button>
        <a href="{{ url('/recipes') }}" class="btn btn-link text-decoration-none">Cancel</a>
    </div>
</form>

<script>
    // Ensure click on the drop zone opens the file picker even if bundled JS isn't loaded
    (function initImageDropZones() {
        function bindZones() {
            const zones = document.querySelectorAll('.image-drop-zone');
            zones.forEach(zone => {
                const input = zone.querySelector('input[type="file"]');
                const removeBtn = zone.querySelector('.remove-image-btn');
                if (!input) return;

                zone.addEventListener('click', function(e) {
                    if (removeBtn && (e.target === removeBtn || removeBtn.contains(e.target))) return;
                    input.click();
                });
            });
        }
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', bindZones);
        } else {
            bindZones();
        }
    })();
</script>

@if($errors->any())
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Find the first input/textarea/select with an error
            const firstErrorField = document.querySelector('.text-danger')?.closest('.card-body, .col-12, .col-md-6, .col-md-4')?.querySelector('input:not([type="hidden"]), textarea, select');
            
            if (firstErrorField) {
                // Scroll the element into view
                firstErrorField.scrollIntoView({ behavior: 'smooth', block: 'center' });
                // Focus the field
                setTimeout(() => {
                    firstErrorField.focus();
                }, 300);
            }
        });
    </script>
@endif