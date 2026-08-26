<form id="coin-filter-form" method="GET" action="{{ route('catalog.index') }}" class="container mt-3 mb-4">
    <div class="row g-3">

        <div class="col-md-2">
            <label for="year_from" class="form-label">Year from</label>
            <input type="number" id="year_from" name="year_from" value="{{ $filters['year_from'] }}" class="form-control">
        </div>

        <div class="col-md-2">
            <label for="year_to" class="form-label">Year to</label>
            <input type="number" id="year_to" name="year_to" value="{{ $filters['year_to'] }}" class="form-control">
        </div>

        @foreach (['metal' => $metals, 'diameter' => $diameters] as $field => $values)
            <div class="col-md-2">
                <label for="{{ $field }}" class="form-label">{{ ucfirst($field) }}</label>
                <select id="{{ $field }}" name="{{ $field }}" class="form-select">
                    <option value="">All {{ ucfirst($field) }}</option>
                    @foreach ($values as $val)
                        <option value="{{ $val }}" @selected($filters[$field] === (string) $val)>{{ $val }}</option>
                    @endforeach
                </select>
            </div>
        @endforeach

        <div class="col-md-3">
            <label for="series" class="form-label">Series</label>
            <select id="series" name="series" class="form-select">
                <option value="">All Series</option>
                <option value="none" @selected($filters['series'] === 'none')>No Series</option>
                @foreach ($allSeries as $term)
                    <option value="{{ $term->slug }}" @selected($filters['series'] === $term->slug)>
                        {{ str_repeat('— ', $term->depth) }}{{ $term->name }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="col-md-1 d-flex align-items-end">
            <button type="submit" class="btn btn-primary w-100">Filter</button>
        </div>

    </div>
</form>
