<div class="row g-3">
    <div class="col-12">
        <label class="form-label fw-bold mb-1">Name</label>
        <div class="row g-2">
            @foreach (['bg' => 'Bulgarian', 'en' => 'English', 'de' => 'German'] as $code => $label)
                <div class="col-md-4">
                    <label class="form-label small text-muted">{{ $label }}</label>
                    <input type="text" name="name[{{ $code }}]" class="form-control"
                           value="{{ old('name.' . $code, $series->translation('name', $code)) }}">
                </div>
            @endforeach
        </div>
        <div class="form-text">At least one language is required.</div>
    </div>

    <div class="col-md-6">
        <label class="form-label">Slug</label>
        <input type="text" name="slug" class="form-control" value="{{ old('slug', $series->slug) }}" placeholder="auto from name">
    </div>
</div>

<div class="mt-4">
    <button type="submit" class="btn btn-primary">Save</button>
    <a href="{{ route('admin.series.index') }}" class="btn btn-outline-secondary">Cancel</a>
</div>
