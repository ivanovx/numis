<div class="row g-3">
    <div class="col-md-6">
        <label class="form-label">Name</label>
        <input type="text" name="name" class="form-control" value="{{ old('name', $series->name) }}" required>
    </div>

    <div class="col-md-3">
        <label class="form-label">Slug</label>
        <input type="text" name="slug" class="form-control" value="{{ old('slug', $series->slug) }}" placeholder="auto from name">
    </div>

    <div class="col-md-3">
        <label class="form-label">Parent series</label>
        <select name="parent_id" class="form-select">
            <option value="">None</option>
            @foreach ($allSeries as $term)
                <option value="{{ $term->id }}" @selected((string) old('parent_id', $series->parent_id) === (string) $term->id)>
                    {{ str_repeat('— ', $term->depth) }}{{ $term->name }}
                </option>
            @endforeach
        </select>
    </div>
</div>

<div class="mt-4">
    <button type="submit" class="btn btn-primary">Save</button>
    <a href="{{ route('admin.series.index') }}" class="btn btn-outline-secondary">Cancel</a>
</div>
