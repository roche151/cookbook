@props([
    'action',
    'method' => 'POST',
    'buttonText' => 'Save',
    'recipe' => null,
    'tags' => [],
])

@php $confirm = $confirmOnSubmit ?? false; @endphp
<form action="{{ $action }}" method="POST" enctype="multipart/form-data" class="row g-3 {{ $confirm ? 'js-edit-confirm-form' : '' }}" novalidate>
    @csrf
    @if(strtoupper($method) !== 'POST')
        @method($method)
    @endif

    @if($errors->has('profanity'))
        <div class="col-12">
            <div class="alert alert-danger d-flex align-items-start" role="alert" aria-live="assertive">
                <i class="fa-solid fa-ban me-2 mt-1" aria-hidden="true"></i>
                <div>{{ $errors->first('profanity') }}</div>
            </div>
        </div>
    @endif

    <input type="hidden" name="source_url" value="{{ old('source_url', optional($recipe)->source_url) }}">
    <input type="hidden" name="imported_image_url" value="{{ old('imported_image_url') }}">

    @if (false && $errors->any())
        <div class="col-12">
            <div class="alert alert-danger" role="alert" aria-live="assertive">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    @endif

    <div class="col-12">
        <div class="card bg-body-secondary border-0 mb-3" aria-labelledby="visibility-label">
            <div class="card-body p-3">
                <h6 class="card-title mb-3" id="visibility-label">
                    <i class="fa-solid fa-eye me-2" aria-hidden="true"></i>Visibility
                </h6>
                <div class="d-flex gap-4">
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="is_public" id="visibility_private" value="0" {{ old('is_public', optional($recipe)->is_public) ? '' : 'checked' }}>
                        <label class="form-check-label" for="visibility_private">
                            <i class="fa-solid fa-lock me-1" aria-hidden="true"></i>Private
                            <small class="d-block text-muted">Only you can view</small>
                        </label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="is_public" id="visibility_public" value="1" {{ old('is_public', optional($recipe)->is_public) ? 'checked' : '' }}>
                        <label class="form-check-label" for="visibility_public">
                            <i class="fa-solid fa-globe me-1" aria-hidden="true"></i>Public
                            <small class="d-block text-muted">Requires moderation before everyone can see it</small>
                        </label>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-12">
        <label class="form-label fw-semibold">
            <i class="fa-solid fa-utensils me-1 text-primary"></i>Recipe Title
        </label>
        <input name="title" class="form-control form-control-lg" placeholder="e.g. Chicken and Mushroom Risotto" value="{{ old('title', optional($recipe)->title) }}" aria-required="true" @if($errors->has('title')) aria-invalid="true" aria-describedby="error-title" @endif>
        @if($errors->has('title'))
            <div id="error-title" class="text-danger small mt-1">{{ $errors->first('title') }}</div>
        @endif
    </div>

    <div class="col-12">
        <label class="form-label fw-semibold">
            <i class="fa-solid fa-tags me-1 text-primary"></i>Tags
            <span class="text-muted fw-normal">(optional)</span>
        </label>
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
                <div class="form-check ps-0">
                    <input hidden class="form-check-input" type="checkbox" name="tags[]" value="{{ $tag->id }}" id="tag-{{ $tag->id }}" {{ $checked ? 'checked' : '' }}>
                    <label class="form-check-label badge bg-opacity-25 border border-secondary py-2 px-3 {{ $checked ? 'bg-primary text-white' : 'bg-secondary border-secondary-subtle' }}" for="tag-{{ $tag->id }}" style="cursor: pointer; font-weight: 400;">
                        <i class="{{ $tag->icon }} me-1" aria-hidden="true"></i>{{ $tag->name }}
                    </label>
                </div>
            @endforeach
        </div>
        @if($errors->has('tags'))
            <div class="text-danger small mt-1">{{ $errors->first('tags') }}</div>
        @endif
    </div>

    <div class="col-12">
        <label class="form-label fw-semibold">
            <i class="fa-solid fa-image me-1 text-primary"></i>Recipe Image <span class="text-muted fw-normal">(optional)</span>
        </label>
        @php
            $tempImage = session('temp_image');
            $hasTemp = $tempImage && Storage::disk('public')->exists($tempImage);
            $importedImageUrl = old('imported_image_url');
            $showImage = $hasTemp || $importedImageUrl;
            $imageSource = $hasTemp ? Storage::url($tempImage) : $importedImageUrl;
        @endphp
        
        @if($hasTemp)
            <input type="hidden" name="existing_temp_image" value="{{ $tempImage }}">
        @endif
        
        <div class="image-drop-zone border rounded-3 p-4 text-center" tabindex="0" style="position: relative; min-height: 200px; background: var(--bs-body-secondary); border: 2px dashed var(--bs-border-color) !important; outline: none; transition: all 0.2s;">
            <input type="file" name="image" accept="image/*" class="d-none" id="recipe-image">
            <div class="upload-placeholder" style="display: {{ $showImage ? 'none' : 'flex' }}; flex-direction: column; align-items: center; justify-content: center; min-height: 150px; gap: 1rem;">
                <i class="fa-solid fa-cloud-arrow-up fa-3x text-primary opacity-75" aria-hidden="true"></i>
                <div>
                    <p class="mb-1 fw-semibold">Choose a file or drag & drop it here</p>
                    <p class="text-muted small mb-3">JPEG, PNG, GIF, and WebP formats, up to 5MB</p>
                </div>
                <button type="button" class="btn btn-primary btn-sm browse-file-btn">
                    <i class="fa-solid fa-folder-open me-2" aria-hidden="true"></i>Browse Files
                </button>
            </div>
            <img class="image-preview rounded-3" src="{{ $showImage ? $imageSource : '' }}" style="display: {{ $showImage ? 'block' : 'none' }}; max-width: 100%; max-height: 300px; object-fit: contain;" alt="Recipe image preview">
            <button type="button" class="remove-image-btn btn btn-sm btn-danger position-absolute top-0 end-0 m-2" style="display: {{ $showImage ? 'block' : 'none' }};">
                <i class="fa-solid fa-times" aria-hidden="true"></i>
            </button>
        </div>
        @if($recipe && $recipe->image)
            <div class="mt-2">
                <p class="text-muted small mb-1">Current image:</p>
                <img src="{{ Storage::url($recipe->image) }}" alt="Current recipe image" class="img-thumbnail" style="max-height: 150px;">
                <div class="form-check mt-2">
                    <input class="form-check-input" type="checkbox" name="remove_image" value="1" id="remove-image-checkbox">
                    <label class="form-check-label" for="remove-image-checkbox">
                        Remove image
                    </label>
                </div>
            </div>
        @endif
    </div>

    <div class="col-md-6">
        <label class="form-label fw-semibold">
            <i class="fa-solid fa-clock me-1 text-primary"></i>Time
        </label>
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
            <div class="input-group flex-grow-1">
                <input name="time_hours" type="number" min="0" class="form-control" value="{{ $hours }}" placeholder="0" aria-label="Hours" aria-required="true" @if($errors->has('time')) aria-invalid="true" aria-describedby="error-time" @endif>
                <span class="input-group-text">hours</span>
            </div>

            <div class="input-group flex-grow-1">
                <input name="time_minutes" type="number" min="0" max="59" class="form-control" value="{{ $minutes }}" placeholder="0" aria-label="Minutes" aria-required="true" @if($errors->has('time')) aria-invalid="true" aria-describedby="error-time" @endif oninput="if(this.value==='')return; if(Number(this.value) > 59) this.value = 59; if(Number(this.value) < 0) this.value = 0;">
                <span class="input-group-text">minutes</span>
            </div>
        </div>
        @if($errors->has('time'))
            <div id="error-time" class="text-danger small mt-1">{{ $errors->first('time') }}</div>
        @endif
    </div>

    <div class="col-md-6">
        <label class="form-label fw-semibold">
            <i class="fa-solid fa-users me-1 text-primary"></i>Serves <span class="text-muted fw-normal">(optional)</span>
        </label>
        @php
            $servesOld = old('serves');
            $serves = $servesOld !== null ? $servesOld : (optional($recipe)->serves ?? '');
        @endphp
        <input name="serves" type="number" min="1" class="form-control" value="{{ $serves }}" placeholder="4" aria-label="Serves" @if($errors->has('serves')) aria-invalid="true" aria-describedby="error-serves" @endif>
        @if($errors->has('serves'))
            <div id="error-serves" class="text-danger small mt-1">{{ $errors->first('serves') }}</div>
        @endif
    </div>

    <div class="col-md-6">
        <label class="form-label fw-semibold">
            <i class="fa-solid fa-signal me-1 text-primary"></i>Difficulty Level
        </label>
        <select name="difficulty" id="difficulty" class="form-select" required aria-required="true" @if($errors->has('difficulty')) aria-invalid="true" aria-describedby="error-difficulty" @endif>
            <option value="">Select difficulty</option>
            <option value="easy" {{ old('difficulty', optional($recipe)->difficulty) === 'easy' ? 'selected' : '' }}>● Easy - Perfect for beginners</option>
            <option value="medium" {{ old('difficulty', optional($recipe)->difficulty) === 'medium' ? 'selected' : '' }}>●● Medium - Some experience needed</option>
            <option value="hard" {{ old('difficulty', optional($recipe)->difficulty) === 'hard' ? 'selected' : '' }}>●●● Hard - Advanced techniques</option>
        </select>
        @if($errors->has('difficulty'))
            <div id="error-difficulty" class="text-danger small mt-1">{{ $errors->first('difficulty') }}</div>
        @endif
    </div>


    <div class="col-12">
        <label class="form-label fw-semibold">
            <i class="fa-brands fa-youtube me-1 text-danger"></i>Video URL <span class="text-muted fw-normal">(YouTube, Instagram, TikTok, etc. — optional)</span>
        </label>
        <input name="video_url" type="url" class="form-control" placeholder="https://www.youtube.com/watch?v=..." value="{{ old('video_url', optional($recipe)->video_url) }}" aria-label="Video URL" @if($errors->has('video_url')) aria-invalid="true" aria-describedby="error-video-url" @endif>
        @if($errors->has('video_url'))
            <div id="error-video-url" class="text-danger small mt-1">{{ $errors->first('video_url') }}</div>
        @endif
        <div id="video-preview" class="mt-3"></div>
            <script>
            function getVideoEmbedHtml(url) {
                if (!url) return '';
                url = url.trim();
                // YouTube
                let ytMatch = url.match(/(?:youtube\.com\/watch\?v=|youtu\.be\/)([\w-]{11})/i);
                if (ytMatch) {
                    return `<iframe width="100%" height="315" src="https://www.youtube.com/embed/${ytMatch[1]}" frameborder="0" allowfullscreen></iframe>`;
                }
                // Instagram
                let igMatch = url.match(/instagram\.com\/(reel|p)\/([\w-]+)/i);
                if (igMatch) {
                    return `<iframe src="https://www.instagram.com/${igMatch[1]}/${igMatch[2]}/embed" width="400" height="480" frameborder="0" scrolling="no" allowtransparency="true"></iframe>`;
                }
                // TikTok
                let ttMatch = url.match(/tiktok\.com\/@([\w.-]+)\/video\/(\d+)/i);
                if (ttMatch) {
                    return `<iframe src="https://www.tiktok.com/embed/v2/${ttMatch[2]}" width="325" height="575" frameborder="0" allowfullscreen></iframe>`;
                }
                // Facebook
                let fbMatch = url.match(/facebook\.com\/.+\/videos\/(\d+)/i);
                if (fbMatch) {
                    return `<iframe src="https://www.facebook.com/plugins/video.php?href=${encodeURIComponent(url)}" width="500" height="280" style="border:none;overflow:hidden" scrolling="no" frameborder="0" allowfullscreen></iframe>`;
                }
                return '';
            }

            // --- Video thumbnail logic ---
            async function getVideoThumbnailUrl(url) {
                if (!url) return null;
                url = url.trim();
                // YouTube
                let ytMatch = url.match(/(?:youtube\.com\/watch\?v=|youtu\.be\/)([\w-]{11})/i);
                if (ytMatch) {
                    return `https://img.youtube.com/vi/${ytMatch[1]}/hqdefault.jpg`;
                }
                // TikTok (no public API, fallback to og:image)
                let ttMatch = url.match(/tiktok\.com\/@([\w.-]+)\/video\/(\d+)/i);
                if (ttMatch) {
                    try {
                        const resp = await fetch(`https://www.tiktok.com/oembed?url=${encodeURIComponent(url)}`);
                        if (resp.ok) {
                            const data = await resp.json();
                            return data.thumbnail_url;
                        }
                    } catch {}
                }
                // Instagram (no public API, fallback to og:image)
                let igMatch = url.match(/instagram\.com\/(reel|p)\/([\w-]+)/i);
                if (igMatch) {
                    try {
                        const resp = await fetch(`https://www.instagram.com/${igMatch[1]}/${igMatch[2]}/?__a=1`);
                        if (resp.ok) {
                            const data = await resp.json();
                            if (data.graphql && data.graphql.shortcode_media && data.graphql.shortcode_media.display_url) {
                                return data.graphql.shortcode_media.display_url;
                            }
                        }
                    } catch {}
                }
                // Facebook (no public API, fallback to og:image)
                let fbMatch = url.match(/facebook\.com\/.+\/videos\/(\d+)/i);
                if (fbMatch) {
                    // Facebook restricts direct access, so we can't reliably fetch thumbnail client-side
                    return null;
                }
                return null;
            }

            document.addEventListener('DOMContentLoaded', function() {
                const videoInput = document.querySelector('input[name="video_url"]');
                const preview = document.getElementById('video-preview');
                const imageInput = document.querySelector('input[name="image"]');
                const importedImageInput = document.querySelector('input[name="imported_image_url"]');
                const imagePreview = document.querySelector('.image-preview');
                const removeImageBtn = document.querySelector('.remove-image-btn');

                function updatePreview() {
                    const html = getVideoEmbedHtml(videoInput.value);
                    preview.innerHTML = html;
                }

                async function maybeSetThumbnail() {
                    // Only set if no image is set and no preview is shown
                    if (imagePreview && imagePreview.src && imagePreview.src.trim() !== '' && imagePreview.style.display !== 'none') return;
                    if (!importedImageInput) return;
                    const url = videoInput.value;
                    if (!url) return;
                    const thumb = await getVideoThumbnailUrl(url);
                    if (thumb) {
                        imagePreview.src = thumb;
                        imagePreview.style.display = 'block';
                        importedImageInput.value = thumb;
                        if (removeImageBtn) removeImageBtn.style.display = 'block';
                    }
                }

                if (videoInput) {
                    videoInput.addEventListener('input', function() {
                        updatePreview();
                        maybeSetThumbnail();
                    });
                    // Show preview if value exists on load
                    if (videoInput.value) {
                        updatePreview();
                        maybeSetThumbnail();
                    }
                }
            });
            </script>
        <script>
        function getVideoEmbedHtml(url) {
            if (!url) return '';
            url = url.trim();
            // YouTube
            let ytMatch = url.match(/(?:youtube\.com\/watch\?v=|youtu\.be\/)([\w-]{11})/i);
            if (ytMatch) {
                return `<iframe width="100%" height="315" src="https://www.youtube.com/embed/${ytMatch[1]}" frameborder="0" allowfullscreen></iframe>`;
            }
            // Instagram
            let igMatch = url.match(/instagram\.com\/(reel|p)\/([\w-]+)/i);
            if (igMatch) {
                return `<iframe src="https://www.instagram.com/${igMatch[1]}/${igMatch[2]}/embed" width="400" height="480" frameborder="0" scrolling="no" allowtransparency="true"></iframe>`;
            }
            // TikTok
            let ttMatch = url.match(/tiktok\.com\/@([\w.-]+)\/video\/(\d+)/i);
            if (ttMatch) {
                return `<iframe src="https://www.tiktok.com/embed/v2/${ttMatch[2]}" width="325" height="575" frameborder="0" allowfullscreen></iframe>`;
            }
            // Facebook
            let fbMatch = url.match(/facebook\.com\/.+\/videos\/(\d+)/i);
            if (fbMatch) {
                return `<iframe src="https://www.facebook.com/plugins/video.php?href=${encodeURIComponent(url)}" width="500" height="280" style="border:none;overflow:hidden" scrolling="no" frameborder="0" allowfullscreen></iframe>`;
            }
            return '';
        }

        document.addEventListener('DOMContentLoaded', function() {
            const videoInput = document.querySelector('input[name="video_url"]');
            const preview = document.getElementById('video-preview');
            function updatePreview() {
                const html = getVideoEmbedHtml(videoInput.value);
                preview.innerHTML = html;
            }
            if (videoInput) {
                videoInput.addEventListener('input', updatePreview);
                // Show preview if value exists on load
                if (videoInput.value) updatePreview();
            }
        });
        </script>
    </div>

    <div class="col-12">
        <label class="form-label fw-semibold">
            <i class="fa-solid fa-align-left me-1 text-primary"></i>Description
        </label>
        <textarea id="description" name="description" class="form-control" rows="4" placeholder="Describe your recipe, its origins, or what makes it special..." aria-required="true" @if($errors->has('description')) aria-invalid="true" aria-describedby="error-description" @endif>{{ old('description', optional($recipe)->description) }}</textarea>
        @if($errors->has('description'))
            <div id="error-description" class="text-danger small mt-1">{{ $errors->first('description') }}</div>
        @endif
    </div>

    <div class="col-12">
        <div class="mb-4">
            <label class="form-label fw-semibold fs-5 mb-3">
                <i class="fa-solid fa-list-check me-2 text-primary"></i>Ingredients
            </label>

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
                <button type="button" id="js-add-ingredient" class="btn btn-primary mt-2">
                    <i class="fa-solid fa-plus me-2"></i>Add Ingredient
                </button>
            </div>
        </div>

        <div class="mb-4">
            <label class="form-label fw-semibold fs-5 mb-3">
                <i class="fa-solid fa-list-ol me-2 text-primary"></i>Method
            </label>

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
                <button type="button" id="js-add-direction" class="btn btn-primary mt-2">
                    <i class="fa-solid fa-plus me-2"></i>Add Step
                </button>
            </div>
        </div>
        @if($errors->has('directions'))
            <div class="text-danger small mt-1">{{ $errors->first('directions') }}</div>
        @endif
    </div>
    
    <div class="col-12">
        <hr class="my-4">
        <div class="d-flex gap-2">
            <button class="btn btn-primary btn-lg px-4" type="submit">
                <i class="fa-solid fa-save me-2"></i>{{ $buttonText }}
            </button>
            <a href="{{ url('/recipes') }}" class="btn btn-outline-secondary btn-lg px-4">
                <i class="fa-solid fa-times me-2"></i>Cancel
            </a>
        </div>
    </div>
</form>

<script>
    // Image uploader: Browse button, drag & drop, and paste support
    (function initImageDropZones() {
        function bindZones() {
            const zones = document.querySelectorAll('.image-drop-zone');
            zones.forEach(zone => {
                if (zone.dataset.uploadBound) return;
                zone.dataset.uploadBound = 'true';
                
                const input = zone.querySelector('input[type="file"]');
                const preview = zone.querySelector('.image-preview');
                const placeholder = zone.querySelector('.upload-placeholder');
                const browseBtn = zone.querySelector('.browse-file-btn');
                const removeBtn = zone.querySelector('.remove-image-btn');
                if (!input) return;

                // Browse button opens file picker
                if (browseBtn) {
                    browseBtn.addEventListener('click', function(e) {
                        e.stopPropagation();
                        input.click();
                    });
                }

                // File input change handler
                input.addEventListener('change', function(e) {
                    handleFiles(e.target.files);
                });

                // Drag and drop handlers
                zone.addEventListener('dragover', function(e) {
                    e.preventDefault();
                    zone.style.borderColor = 'var(--bs-primary)';
                });
                
                zone.addEventListener('dragleave', function() {
                    zone.style.borderColor = '';
                });
                
                zone.addEventListener('drop', function(e) {
                    e.preventDefault();
                    zone.style.borderColor = '';
                    
                    const files = e.dataTransfer.files;
                    if (files.length > 0) {
                        const dt = new DataTransfer();
                        dt.items.add(files[0]);
                        input.files = dt.files;
                        handleFiles(files);
                    }
                });

                // Paste support when zone is focused
                zone.addEventListener('paste', function(e) {
                    const items = e.clipboardData?.items;
                    if (!items) return;
                    
                    for (let i = 0; i < items.length; i++) {
                        if (items[i].type.indexOf('image') !== -1) {
                            e.preventDefault();
                            const blob = items[i].getAsFile();
                            const file = new File([blob], 'pasted-image-' + Date.now() + '.png', { type: blob.type });
                            
                            const dt = new DataTransfer();
                            dt.items.add(file);
                            input.files = dt.files;
                            handleFiles([file]);
                            break;
                        }
                    }
                });

                // Focus visual feedback
                zone.addEventListener('focus', function() {
                    zone.style.borderColor = 'var(--bs-primary)';
                });
                
                zone.addEventListener('blur', function() {
                    zone.style.borderColor = '';
                });

                // Remove button handler
                if (removeBtn) {
                    removeBtn.addEventListener('click', function(e) {
                        e.stopPropagation();
                        input.value = '';
                        preview.style.display = 'none';
                        placeholder.style.display = 'flex';
                        removeBtn.style.display = 'none';
                    });
                }

                function handleFiles(files) {
                    if (!files || files.length === 0) return;
                    
                    const file = files[0];
                    
                    if (!file.type.match('image.*')) {
                        alert('Please upload an image file');
                        return;
                    }
                    
                    if (file.size > 5 * 1024 * 1024) {
                        alert('Image must be less than 5MB');
                        return;
                    }
                    
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        preview.src = e.target.result;
                        preview.style.display = 'block';
                        placeholder.style.display = 'none';
                        if (removeBtn) removeBtn.style.display = 'block';
                    };
                    reader.readAsDataURL(file);
                }
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
            
            // Handle tag checkbox styling
            const tagCheckboxes = document.querySelectorAll('input[name="tags[]"]');
            tagCheckboxes.forEach(checkbox => {
                checkbox.addEventListener('change', function() {
                    const label = this.nextElementSibling;
                    if (this.checked) {
                        label.classList.remove('bg-secondary', 'border-secondary-subtle');
                        label.classList.add('bg-primary', 'text-white');
                    } else {
                        label.classList.remove('bg-primary', 'text-white');
                        label.classList.add('bg-secondary', 'border-secondary-subtle');
                    }
                });
            });
        });
    </script>
@endif