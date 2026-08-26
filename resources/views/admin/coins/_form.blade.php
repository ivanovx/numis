<div class="row g-3">

    <div class="col-12">
        <label class="form-label fw-bold mb-1">Title (Заглавие)</label>
        <div class="row g-2">
            @foreach (['bg' => 'Bulgarian', 'en' => 'English', 'de' => 'German'] as $code => $label)
                <div class="col-md-4">
                    <label class="form-label small text-muted">{{ $label }}</label>
                    <input type="text" name="title[{{ $code }}]" class="form-control"
                           value="{{ old('title.' . $code, $coin->translation('title', $code)) }}">
                </div>
            @endforeach
        </div>
        <div class="form-text">At least one language is required. Blank languages fall back to another filled one on the public site.</div>
    </div>

    <div class="col-md-4">
        <label class="form-label">Series (Серия)</label>
        <select name="series_id" class="form-select">
            <option value="">— None —</option>
            @foreach ($allSeries as $term)
                <option value="{{ $term->id }}" @selected((string) old('series_id', $coin->series_id) === (string) $term->id)>
                    {{ $term->name }}
                </option>
            @endforeach
        </select>
    </div>

    <div class="col-md-8">
        <label class="form-label">Artist(s) (Художник)</label>
        <select name="artist_ids[]" class="form-select" multiple size="4">
            @foreach ($allArtists as $artist)
                <option value="{{ $artist->id }}" @selected(in_array($artist->id, old('artist_ids', $selectedArtists ?? [])))>
                    {{ $artist->name }}
                </option>
            @endforeach
        </select>
        <div class="form-text">
            Ctrl/Cmd-click to select multiple.
            Manage the artist list at <a href="{{ route('admin.artists.index') }}" target="_blank">/admin/artists</a>.
        </div>
    </div>

    <div class="col-md-2">
        <label class="form-label">Year (Година)</label>
        <input type="number" name="year" class="form-control" value="{{ old('year', $coin->year) }}">
    </div>

    <div class="col-md-3">
        <label class="form-label">Date of issue (Дата на въвеждане)</label>
        <input type="date" name="issue_date" class="form-control"
               value="{{ old('issue_date', $coin->issue_date?->format('Y-m-d')) }}">
    </div>

    <div class="col-md-3">
        <label class="form-label">Denomination (Номинална стойност)</label>
        <input type="text" name="denomination" class="form-control" value="{{ old('denomination', $coin->denomination) }}">
    </div>

    <div class="col-md-3">
        <label class="form-label">Metal, fineness (Метал, проба)</label>
        <input type="text" name="metal" list="metal-suggestions" class="form-control" value="{{ old('metal', $coin->metal) }}">
        <datalist id="metal-suggestions">
            @foreach (\App\Models\Coin::METALS as $suggestion)
                <option value="{{ $suggestion }}">
            @endforeach
        </datalist>
    </div>

    <div class="col-md-3">
        <label class="form-label">Quality (Качество)</label>
        <input type="text" name="quality" class="form-control" value="{{ old('quality', $coin->quality) }}">
    </div>

    <div class="col-md-3">
        <label class="form-label">Weight (Тегло)</label>
        <input type="text" name="weight" class="form-control" value="{{ old('weight', $coin->weight) }}">
    </div>

    <div class="col-md-3">
        <label class="form-label">Diameter (Диаметър, mm)</label>
        <input type="text" name="diameter" class="form-control" value="{{ old('diameter', $coin->diameter) }}">
    </div>

    <div class="col-md-3">
        <label class="form-label">Mintage (Тираж)</label>
        <input type="text" name="mintage" class="form-control" value="{{ old('mintage', $coin->mintage) }}">
    </div>

    @foreach ([
        'edge' => 'Edge (Гурт)',
        'mint' => 'Minted at (Отсечена в)',
    ] as $field => $label)
        <div class="col-12">
            <label class="form-label fw-bold mb-1">{{ $label }}</label>
            <div class="row g-2">
                @foreach (['bg' => 'Bulgarian', 'en' => 'English', 'de' => 'German'] as $code => $langLabel)
                    <div class="col-md-4">
                        <label class="form-label small text-muted">{{ $langLabel }}</label>
                        <input type="text" name="{{ $field }}[{{ $code }}]" class="form-control"
                               value="{{ old($field . '.' . $code, $coin->translation($field, $code)) }}">
                    </div>
                @endforeach
            </div>
        </div>
    @endforeach

    <div class="col-md-6">
        <label class="form-label">Front image (Лице)</label>
        <input type="file" name="front_image" class="form-control" accept="image/*">
        @if ($coin->front_image_url)
            <img src="{{ $coin->front_image_url }}" class="img-thumbnail mt-2" style="max-width:150px">
        @endif
        <div class="row g-2 mt-2">
            @foreach (['bg' => 'Bulgarian', 'en' => 'English', 'de' => 'German'] as $code => $label)
                <div class="col-md-4">
                    <label class="form-label small text-muted">Description ({{ $label }})</label>
                    <textarea name="front_description[{{ $code }}]" rows="3" class="form-control">{{ old('front_description.' . $code, $coin->translation('front_description', $code)) }}</textarea>
                </div>
            @endforeach
        </div>
    </div>

    <div class="col-md-6">
        <label class="form-label">Back image (Реверс)</label>
        <input type="file" name="back_image" class="form-control" accept="image/*">
        @if ($coin->back_image_url)
            <img src="{{ $coin->back_image_url }}" class="img-thumbnail mt-2" style="max-width:150px">
        @endif
        <div class="row g-2 mt-2">
            @foreach (['bg' => 'Bulgarian', 'en' => 'English', 'de' => 'German'] as $code => $label)
                <div class="col-md-4">
                    <label class="form-label small text-muted">Description ({{ $label }})</label>
                    <textarea name="back_description[{{ $code }}]" rows="3" class="form-control">{{ old('back_description.' . $code, $coin->translation('back_description', $code)) }}</textarea>
                </div>
            @endforeach
        </div>
    </div>

    <div class="col-12">
        <label class="form-label fw-bold mb-1">Description (Описание)</label>
        <div class="row g-2">
            @foreach (['bg' => 'Bulgarian', 'en' => 'English', 'de' => 'German'] as $code => $label)
                <div class="col-md-4">
                    <label class="form-label small text-muted">{{ $label }}</label>
                    <textarea name="description[{{ $code }}]" rows="6" class="form-control">{{ old('description.' . $code, $coin->translation('description', $code)) }}</textarea>
                </div>
            @endforeach
        </div>
    </div>

</div>

<div class="mt-4">
    <button type="submit" class="btn btn-primary">Save</button>
    <a href="{{ route('admin.coins.index') }}" class="btn btn-outline-secondary">Cancel</a>
</div>
