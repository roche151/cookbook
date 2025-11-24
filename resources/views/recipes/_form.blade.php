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

    <div class="col-md-4">
        <label class="form-label">Rating (optional)</label>
        <input name="rating" type="number" step="0.1" min="0" max="5" class="form-control" value="{{ old('rating', optional($recipe)->rating) }}" placeholder="0.0 - 5.0">
    </div>

    <div class="col-12">
        <label class="form-label">Excerpt</label>
        <textarea name="excerpt" class="form-control" rows="4">{{ old('excerpt', optional($recipe)->excerpt) }}</textarea>
    </div>

    <div class="col-12">
        <button class="btn btn-primary" type="submit">{{ $buttonText }}</button>
        <a href="{{ url('/recipes') }}" class="btn btn-link">Cancel</a>
    </div>
</form>
