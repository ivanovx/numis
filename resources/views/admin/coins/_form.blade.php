<div class="row g-3">
    <div class="col-md-6">
        <label class="form-label">Title</label>
        <input type="text" name="title" class="form-control" value="{{ old('title', $coin->title) }}" required>
    </div>

    <div class="col-md-2">
        <label class="form-label">Year</label>
        <input type="number" name="year" class="form-control" value="{{ old('year', $coin->year) }}">
    </div>

    <div class="col-md-4">
        <label class="form-label">Denomination</label>
        <input type="text" name="denomination" class="form-control" value="{{ old('denomination', $coin->denomination) }}">
    </div>

    <div class="col-md-3">
        <label class="form-label">Metal</label>
        <input type="text" name="metal" class="form-control" value="{{ old('metal', $coin->metal) }}">
    </div>

    <div class="col-md-3">
        <label class="form-label">Diameter (mm)</label>
        <input type="text" name="diameter" class="form-control" value="{{ old('diameter', $coin->diameter) }}">
    </div>

    <div class="col-md-6">
        <label class="form-label">Series</label>
        <select name="series[]" class="form-select" multiple size="6">
            @foreach ($allSeries as $term)
                <option value="{{ $term->id }}" @selected(in_array($term->id, $selected ?? []))>
                    {{ str_repeat('— ', $term->depth) }}{{ $term->name }}
                </option>
            @endforeach
        </select>
        <div class="form-text">Ctrl/Cmd-click to select multiple.</div>
    </div>

    <div class="col-md-3">
        <label class="form-label">Front image</label>
        <input type="file" name="front_image" class="form-control" accept="image/*">
        @if ($coin->front_image_url)
            <img src="{{ $coin->front_image_url }}" class="img-thumbnail mt-2" style="max-width:150px">
        @endif
    </div>

    <div class="col-md-3">
        <label class="form-label">Back image</label>
        <input type="file" name="back_image" class="form-control" accept="image/*">
        @if ($coin->back_image_url)
            <img src="{{ $coin->back_image_url }}" class="img-thumbnail mt-2" style="max-width:150px">
        @endif
    </div>

    <div class="col-12">
        <label class="form-label">Description</label>
        <textarea name="description" rows="6" class="form-control">{{ old('description', $coin->description) }}</textarea>
    </div>
</div>

<div class="mt-4">
    <button type="submit" class="btn btn-primary">Save</button>
    <a href="{{ route('admin.coins.index') }}" class="btn btn-outline-secondary">Cancel</a>
</div>
