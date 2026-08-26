<div class="row g-3">
    <div class="col-md-6">
        <label class="form-label">Name</label>
        <input type="text" name="name" class="form-control" value="{{ old('name', $artist->name) }}" required>
    </div>

    <div class="col-md-6">
        <label class="form-label">Slug</label>
        <input type="text" name="slug" class="form-control" value="{{ old('slug', $artist->slug) }}" placeholder="auto from name">
    </div>
</div>

<div class="mt-4">
    <button type="submit" class="btn btn-primary">Save</button>
    <a href="{{ route('admin.artists.index') }}" class="btn btn-outline-secondary">Cancel</a>
</div>
