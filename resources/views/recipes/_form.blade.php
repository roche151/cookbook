@props([
    'action',
    'method' => 'POST',
    'buttonText' => 'Save',
    'recipe' => null,
    'tags' => [],
])

<form action="{{ $action }}" method="POST" class="row g-3">
    @csrf
    @if(strtoupper($method) !== 'POST')
        @method($method)
    @endif

    @if ($errors->any())
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
    </div>

    <div class="col-md-6">
        <label class="form-label">Image URL (optional)</label>
        <input name="image" class="form-control" value="{{ old('image', optional($recipe)->image) }}" placeholder="https://...">
    </div>

    <div class="col-md-4">
        <label class="form-label">Time (optional)</label>
        <input name="time" class="form-control" value="{{ old('time', optional($recipe)->time) }}" placeholder="e.g. 20 min">
    </div>

    <div class="col-12">
        <label class="form-label">Description</label>
        <textarea name="description" class="form-control" rows="4">{{ old('description', optional($recipe)->description) }}</textarea>
    </div>

    <div class="col-12">
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
                            <div class="flex-grow-1">
                                <input type="hidden" name="directions[{{ $i }}][id]" value="{{ $d['id'] ?? '' }}">
                                <input type="hidden" name="directions[{{ $i }}][sort_order]" class="direction-sort-order" value="{{ $d['sort_order'] ?? $i }}">
                                <textarea name="directions[{{ $i }}][body]" class="form-control direction-body" rows="2">{{ $d['body'] ?? '' }}</textarea>
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

        <button class="btn btn-primary" type="submit">{{ $buttonText }}</button>
        <a href="{{ url('/recipes') }}" class="btn btn-link">Cancel</a>
    </div>
</form>
