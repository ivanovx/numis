<nav id="catalog-filter-navbar" class="navbar navbar-expand-xl bg-body-tertiary border rounded-3 shadow-sm mb-4" aria-label="{{ __('catalog.filter') }}">
    <div class="container-fluid px-3 px-lg-4">
        <span class="navbar-text filter-heading me-3" title="{{ __('catalog.filter') }}" aria-label="{{ __('catalog.filter') }}">
            <i class="bi bi-funnel-fill" aria-hidden="true"></i>
        </span>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#coin-filter-menu"
                aria-controls="coin-filter-menu" aria-expanded="false" aria-label="{{ __('catalog.filter') }}">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="coin-filter-menu">
            <form id="coin-filter-form" method="GET" action="{{ route('catalog.index') }}" class="w-100">
                <div class="row g-2 align-items-end">
                    <div class="col-6 col-md-2 col-xl-auto">
                        <label for="year_from" class="form-label small mb-1">{{ __('catalog.year_from') }}</label>
                        <input type="number" id="year_from" name="year_from" value="{{ $filters['year_from'] }}" class="form-control" placeholder="{{ __('catalog.year_from') }}">
                    </div>

                    <div class="col-6 col-md-2 col-xl-auto">
                        <label for="year_to" class="form-label small mb-1">{{ __('catalog.year_to') }}</label>
                        <input type="number" id="year_to" name="year_to" value="{{ $filters['year_to'] }}" class="form-control" placeholder="{{ __('catalog.year_to') }}">
                    </div>

                    @foreach (['metal' => $metals, 'diameter' => $diameters, 'denomination' => $denominations] as $field => $values)
                        <div class="col-12 col-md-4 col-xl">
                            <label for="{{ $field }}" class="form-label small mb-1">{{ __('catalog.' . $field) }}</label>
                            <select id="{{ $field }}" name="{{ $field }}" class="form-select">
                                <option value="">{{ __('catalog.all_' . $field . 's') }}</option>
                                @foreach ($values as $val)
                                    <option value="{{ $val }}" @selected($filters[$field] === (string) $val)>{{ $val }}</option>
                                @endforeach
                            </select>
                        </div>
                    @endforeach

                    <div class="col-12 col-md-4 col-xl">
                        <label for="category" class="form-label small mb-1">{{ __('catalog.category') }}</label>
                        <select id="category" name="category" class="form-select">
                            <option value="">{{ __('catalog.all_categories') }}</option>
                            @foreach (\App\Models\Coin::CATEGORIES as $category)
                                <option value="{{ $category }}" @selected($filters['category'] === $category)>
                                    {{ __('catalog.categories.' . $category) }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-12 col-md-4 col-xl">
                        <label for="series" class="form-label small mb-1">{{ __('catalog.series') }}</label>
                        <select id="series" name="series" class="form-select">
                            <option value="">{{ __('catalog.all_series') }}</option>
                            <option value="none" @selected($filters['series'] === 'none')>{{ __('catalog.no_series_option') }}</option>
                            @foreach ($allSeries as $term)
                                <option value="{{ $term->slug }}" @selected($filters['series'] === $term->slug)>{{ $term->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-12 col-md-4 col-xl">
                        <label for="artist" class="form-label small mb-1">{{ __('catalog.artist') }}</label>
                        <select id="artist" name="artist" class="form-select">
                            <option value="">{{ __('catalog.all_artists') }}</option>
                            @foreach ($allArtists as $artist)
                                <option value="{{ $artist->slug }}" @selected($filters['artist'] === $artist->slug)>{{ $artist->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-12 col-md-auto d-flex gap-2">
                        <button type="submit" class="btn btn-primary filter-submit" title="{{ __('catalog.filter') }}" aria-label="{{ __('catalog.filter') }}">
                            <i class="bi bi-funnel-fill" aria-hidden="true"></i>
                        </button>
                        <a href="{{ route('catalog.index') }}" class="btn btn-outline-secondary" title="{{ __('catalog.clear_filters') }}" aria-label="{{ __('catalog.clear_filters') }}">&times;</a>
                    </div>
                </div>
            </form>
        </div>
    </div>
</nav>
