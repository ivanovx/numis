@php($languages = $coin->exists ? ['bg' => 'Български', 'en' => 'Английски', 'de' => 'Немски'] : ['bg' => 'Български'])

<div class="row g-3">
    @if ($coin->exists)
        <div class="col-12">
            @include('admin.coins._completeness', ['coin' => $coin, 'detailed' => true])
        </div>
    @endif

    <div class="col-12">
        <label class="form-label fw-bold mb-1">Заглавие</label>
        <div class="row g-2">
            @foreach ($languages as $code => $label)
                <label class="form-label small text-muted">{{ $label }}</label>
                <input type="text" name="title[{{ $code }}]" class="form-control" value="{{ old('title.' . $code, $coin->translation('title', $code)) }}">
            @endforeach
        </div>
        <div class="form-text">
            @if ($coin->exists)
                Редактирай преводите директно. Ако липсва превод, в публичния сайт ще се показва наличният текст на друг език.
            @else
                Въведи текста на български. След запазване английският и немският превод ще бъдат направени автоматично чрез DeepL.
            @endif
        </div>
    </div>

    <div class="col-md-4">
        <label class="form-label">Серия</label>
        <select name="series_id" class="form-select">
            <option value="">— Без серия —</option>
            @foreach ($allSeries as $term)
                <option value="{{ $term->id }}" @selected((string) old('series_id', $coin->series_id) === (string) $term->id)>
                    {{ $term->name }}
                </option>
            @endforeach
        </select>
    </div>

    <div class="col-md-4">
        <label class="form-label">Исторически период</label>
        <select name="period_id" class="form-select">
            <option value="">— Без период —</option>
            @foreach ($allPeriods as $period)
                <option value="{{ $period->id }}" @selected((string) old('period_id', $coin->period_id) === (string) $period->id)>
                    {{ $period->name }}
                </option>
            @endforeach
        </select>
        <div class="form-text">Управлявай периодите от <a href="{{ route('admin.periods.index') }}" target="_blank">/admin/periods</a>.</div>
    </div>

    <div class="col-md-3">
        <label class="form-label">Категория</label>
        <select name="category" class="form-select" required>
            <option value="">— Избери категория —</option>
            @foreach (\App\Models\Coin::CATEGORIES as $category)
                <option value="{{ $category }}" @selected(old('category', $coin->category) === $category)>
                    {{ __('catalog.categories.' . $category) }}
                </option>
            @endforeach
        </select>
    </div>

     <div class="col-md-2">
        <label class="form-label">Година</label>
        <input type="number" name="year" class="form-control" value="{{ old('year', $coin->year) }}">
    </div>

     <div class="col-md-3">
        <label class="form-label">Дата на въвеждане</label>
        <input type="date" name="issue_date" class="form-control"
               value="{{ old('issue_date', $coin->issue_date?->format('Y-m-d')) }}">
    </div>

    <div class="col-md-12">
        <label class="form-label">Художник(ци)</label>
        <select name="artist_ids[]" class="form-select" multiple size="4">
            @foreach ($allArtists as $artist)
                <option value="{{ $artist->id }}" @selected(in_array($artist->id, old('artist_ids', $selectedArtists ?? [])))>
                    {{ $artist->name }}
                </option>
            @endforeach
        </select>
        <div class="form-text">
            За избор на няколко задръж Ctrl/Cmd и щракни върху имената.
            Управлявай художниците от <a href="{{ route('admin.artists.index') }}" target="_blank">/admin/artists</a>.
        </div>
    </div>

    <div class="col-md-3">
        <label class="form-label">Номинал</label>
        <input type="text" name="denomination" class="form-control" value="{{ old('denomination', $coin->denomination) }}">
    </div>

    <div class="col-md-3">
        <label class="form-label">Метал, проба</label>
        <input type="text" name="metal" list="metal-suggestions" class="form-control" value="{{ old('metal', $coin->metal) }}">
        <datalist id="metal-suggestions">
            @foreach (\App\Models\Coin::METALS as $suggestion)
                <option value="{{ $suggestion }}">
            @endforeach
        </datalist>
    </div>

    <div class="col-md-3">
        <label class="form-label">Качество</label>
        <input type="text" name="quality" class="form-control" value="{{ old('quality', $coin->quality) }}">
    </div>

    <div class="col-md-3">
        <label class="form-label">Тегло</label>
        <input type="text" name="weight" class="form-control" value="{{ old('weight', $coin->weight) }}">
    </div>

    <div class="col-md-3">
        <label class="form-label">Диаметър (мм)</label>
        <input type="text" name="diameter" class="form-control" value="{{ old('diameter', $coin->diameter) }}">
    </div>

    <div class="col-md-3">
        <label class="form-label">Тираж</label>
        <input type="text" name="mintage" class="form-control" value="{{ old('mintage', $coin->mintage) }}">
    </div>

    <div class="col-12 mt-4">
        <label class="form-label fw-bold mb-0">Каталожни номера</label>
        <div class="form-text mt-0">Номера, по които колекционерите търсят монетите. Префикси като „KM#“ и „N#“ не са задължителни — премахват се автоматично.</div>
    </div>

    <div class="col-md-3">
        <label class="form-label">Krause (KM#)</label>
        <input type="text" name="km_number" class="form-control" maxlength="64" placeholder="напр. 202.1"
               value="{{ old('km_number', $coin->km_number) }}">
    </div>

    <div class="col-md-3">
        <label class="form-label">Numista (N#)</label>
        <input type="text" name="numista_number" class="form-control" maxlength="64" placeholder="напр. 12345 или адрес от numista.com"
               value="{{ old('numista_number', $coin->numista_number) }}">
    </div>

    <div class="col-md-3">
        <label class="form-label">Код на БНБ</label>
        <input type="text" name="bnb_number" class="form-control" maxlength="100" placeholder="напр. NC_2025_9_100LV"
               value="{{ old('bnb_number', $coin->bnb_number) }}">
        @if ($coin->bnbUrl('bg'))
            <div class="form-text"><a href="{{ $coin->bnbUrl('bg') }}" target="_blank" rel="noopener">Отвори в bnb.bg ↗</a></div>
        @else
            <div class="form-text">Кодът от адреса на монетата в bnb.bg (можеш да поставиш и целия адрес).</div>
        @endif
    </div>

    <div class="col-md-3">
        <label class="form-label">Други каталози</label>
        <input type="text" name="other_references" class="form-control" maxlength="255" placeholder="напр. Schön 123; Фриш 45"
               value="{{ old('other_references', $coin->other_references) }}">
    </div>

    @foreach ([
        'edge' => 'Гурт',
        'mint' => 'Отсечена в',
    ] as $field => $label)
        <div class="col-12">
            <label class="form-label fw-bold mb-1">{{ $label }}</label>
            <div class="row g-2">
                @foreach ($languages as $code => $langLabel)
                    <div class="col-md-4">
                        <label class="form-label small text-muted">{{ $langLabel }}</label>
                        <input type="text" name="{{ $field }}[{{ $code }}]" class="form-control"
                               value="{{ old($field . '.' . $code, $coin->translation($field, $code)) }}">
                    </div>
                @endforeach
            </div>
        </div>
    @endforeach

    <div class="col-12 col-lg-6">
        <div class="image-side-block">
            <label class="form-label">Снимка на лицевата страна</label>
            <input type="file" name="front_image" class="form-control" accept="image/*">
            @if ($coin->front_image_url)
                <img src="{{ $coin->front_image_url }}" class="img-thumbnail mt-2" style="max-width:150px">
            @endif
            <div class="row g-2 mt-2">
                @foreach ($languages as $code => $label)
                    <div class="col-12">
                        <label class="form-label small text-muted">Описание ({{ $label }})</label>
                        @php($frontDescriptionId = 'front-description-' . $code)
                        <textarea id="{{ $frontDescriptionId }}" name="front_description[{{ $code }}]" class="d-none">{{ old('front_description.' . $code, $coin->translation('front_description', $code)) }}</textarea>
                        <div class="rich-text-editor" data-rich-text-editor="{{ $frontDescriptionId }}"></div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <div class="col-12 col-lg-6">
        <div class="image-side-block">
            <label class="form-label">Снимка на обратната страна (реверс)</label>
            <input type="file" name="back_image" class="form-control" accept="image/*">
            @if ($coin->back_image_url)
                <img src="{{ $coin->back_image_url }}" class="img-thumbnail mt-2" style="max-width:150px">
            @endif
            <div class="row g-2 mt-2">
                @foreach ($languages as $code => $label)
                    <div class="col-12">
                        <label class="form-label small text-muted">Описание ({{ $label }})</label>
                        @php($backDescriptionId = 'back-description-' . $code)
                        <textarea id="{{ $backDescriptionId }}" name="back_description[{{ $code }}]" class="d-none">{{ old('back_description.' . $code, $coin->translation('back_description', $code)) }}</textarea>
                        <div class="rich-text-editor" data-rich-text-editor="{{ $backDescriptionId }}"></div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

   <!-- <div class="col-12">
        <label class="form-label fw-bold mb-1">Описание</label>
        <div class="row g-2">
            @foreach ($languages as $code => $label)
                <div class="col-12">
                    <label class="form-label small text-muted">{{ $label }}</label>
                    @php($descriptionId = 'description-' . $code)
                    <textarea id="{{ $descriptionId }}" name="description[{{ $code }}]" class="d-none">{{ old('description.' . $code, $coin->translation('description', $code)) }}</textarea>
                    <div class="rich-text-editor" data-rich-text-editor="{{ $descriptionId }}"></div>
                </div>
            @endforeach
        </div>
    </div>-->
</div>

<div class="mt-10" style="margin-top: 5rem;">
    <button type="submit" class="btn btn-primary">Запази</button>
    <a href="{{ route('admin.coins.index') }}" class="btn btn-outline-secondary">Отказ</a>
</div>
