<form id="coin-filter-form" method="GET" action="{{ route('catalog.index') }}" class="container mt-3 mb-4">
    <div class="row g-3">

        <div class="col-md-2">
            <label for="year_from" class="form-label">{{ __('catalog.year_from') }}</label>
            <input type="number" id="year_from" name="year_from" value="{{ $filters['year_from'] }}" class="form-control">
        </div>

        <div class="col-md-2">
            <label for="year_to" class="form-label">{{ __('catalog.year_to') }}</label>
            <input type="number" id="year_to" name="year_to" value="{{ $filters['year_to'] }}" class="form-control">
        </div>

        @foreach (['metal' => $metals, 'diameter' => $diameters, 'denomination' => $denominations] as $field => $values)
            <div class="col-md-2">
                <label for="{{ $field }}" class="form-label">{{ __('catalog.' . $field) }}</label>
                <select id="{{ $field }}" name="{{ $field }}" class="form-select">
                    <option value="">{{ __('catalog.all_' . $field . 's') }}</option>
                    @foreach ($values as $val)
                        <option value="{{ $val }}" @selected($filters[$field] === (string) $val)>{{ $val }}</option>
                    @endforeach
                </select>
            </div>
        @endforeach

        <div class="col-md-2">
            <label for="series" class="form-label">{{ __('catalog.series') }}</label>
            <select id="series" name="series" class="form-select">
                <option value="">{{ __('catalog.all_series') }}</option>
                <option value="none" @selected($filters['series'] === 'none')>{{ __('catalog.no_series_option') }}</option>
                @foreach ($allSeries as $term)
                    <option value="{{ $term->slug }}" @selected($filters['series'] === $term->slug)>{{ $term->name }}</option>
                @endforeach
            </select>
        </div>

        <div class="col-md-2">
            <label for="artist" class="form-label">{{ __('catalog.artist') }}</label>
            <select id="artist" name="artist" class="form-select">
                <option value="">{{ __('catalog.all_artists') }}</option>
                @foreach ($allArtists as $artist)
                    <option value="{{ $artist->slug }}" @selected($filters['artist'] === $artist->slug)>{{ $artist->name }}</option>
                @endforeach
            </select>
        </div>

        <div class="col-md-1 d-flex align-items-end">
            <button type="submit" class="btn btn-primary w-100">{{ __('catalog.filter') }}</button>
        </div>

    </div>
</form>
