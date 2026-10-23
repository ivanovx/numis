<div class="row g-3">
    <div class="col-12">
        <label class="form-label fw-bold mb-1">Име</label>
        <div class="row g-2">
            @php($languages = $artist->exists ? ['bg' => 'Български', 'en' => 'Английски', 'de' => 'Немски'] : ['bg' => 'Български'])
            @foreach ($languages as $code => $label)
                <div class="col-md-4">
                    <label class="form-label small text-muted">{{ $label }}</label>
                    <input type="text" name="name[{{ $code }}]" class="form-control"
                           value="{{ old('name.' . $code, $artist->translation('name', $code)) }}">
                </div>
            @endforeach
        </div>
        <div class="form-text">
            @if ($artist->exists)
                Редактирай преводите директно.
            @else
                Въведи името на български. След запазване английският и немският превод ще бъдат направени автоматично чрез DeepL.
            @endif
        </div>
    </div>

    <div class="col-md-6">
        <label class="form-label">Кратко име (slug)</label>
        <input type="text" name="slug" class="form-control" value="{{ old('slug', $artist->slug) }}" placeholder="попълва се автоматично">
    </div>
</div>

<div class="mt-4">
    <button type="submit" class="btn btn-primary">Запази</button>
    <a href="{{ route('admin.artists.index') }}" class="btn btn-outline-secondary">Отказ</a>
</div>
