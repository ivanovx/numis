@if ($coins->count())

<div class="container-fluid px-3 px-lg-4 mt-4">
    <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-lg-5 g-4">
        @php($previousYear = '__initial__')

        @foreach ($coins as $coin)
            @php($coinYear = $coin->year ?: 'unknown')
            @if (($filters['category'] ?? '') === 'exchange' && $previousYear !== $coinYear)
                <div class="col-12 catalog-year-heading">
                    <h2>{{ $coinYear === 'unknown' ? __('catalog.unknown_year') : $coinYear }}</h2>
                    <span>{{ __('catalog.exchange_group') }}</span>
                </div>
                @php($previousYear = $coinYear)
            @endif
            <div class="col">
                <div class="card shadow-sm h-100">
                    <div class="card-header">
                                <a class="btn btn-link" href="{{ route('catalog.coin', ['locale' => app()->getLocale(), 'coin' => $coin]) }}">
                            {{ $coin->title }}
                        </a>
                    </div>
                    <div class="coin-flip-container">
                        <div class="coin-flip-inner">
                            <div class="coin-flip-front">
                                @if ($coin->front_image_url)
                                    <img src="{{ $coin->front_image_url }}" class="img-fluid" alt="{{ $coin->title }} — {{ __('catalog.front_label') }}">
                                @endif
                            </div>
                            <div class="coin-flip-back">
                                @if ($coin->back_image_url)
                                    <img src="{{ $coin->back_image_url }}" class="img-fluid" alt="{{ $coin->title }} — {{ __('catalog.back_label') }}">
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="card-body">
                        <ul class="list-unstyled small">
                            @if ($coin->year)<li><strong>{{ __('catalog.year_label') }}:</strong> {{ $coin->year }}</li>@endif
                            @if ($coin->denomination)<li><strong>{{ __('catalog.denomination_label') }}:</strong> {{ $coin->denomination }}</li>@endif
                            @if ($coin->metal)<li><strong>{{ __('catalog.metal_label') }}:</strong> {{ $coin->metal }}</li>@endif
                            @if ($coin->category)<li><strong>{{ __('catalog.category') }}:</strong> {{ __('catalog.categories.' . $coin->category) }}</li>@endif
                        </ul>
                    </div>
                    <div class="card-footer">
                        <span class="text-muted">{{ $coin->series?->name ?? __('catalog.no_series') }}</span>
                    </div>
                </div>
            </div>

            <div class="modal fade" id="coinModal-{{ $coin->id }}" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-lg modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">{{ $coin->title }}</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ __('catalog.close') }}"></button>
                        </div>
                        <div class="modal-body">

                            <div class="row text-center mb-3">
                                <div class="col-6">
                                    @if ($coin->front_image_url)
                                        <img src="{{ $coin->front_image_url }}" class="img-fluid mb-1" style="max-height:200px" alt="{{ __('catalog.front_label') }}">
                                    @endif
                                    <div class="small text-muted">{{ __('catalog.front_label') }}</div>
                                    @if ($coin->front_description)
                                        <div class="small mt-1">{!! $coin->front_description !!}</div>
                                    @endif
                                </div>
                                <div class="col-6">
                                    @if ($coin->back_image_url)
                                        <img src="{{ $coin->back_image_url }}" class="img-fluid mb-1" style="max-height:200px" alt="{{ __('catalog.back_label') }}">
                                    @endif
                                    <div class="small text-muted">{{ __('catalog.back_label') }}</div>
                                    @if ($coin->back_description)
                                        <div class="small mt-1">{!! $coin->back_description !!}</div>
                                    @endif
                                </div>
                            </div>

                            <ul class="list-unstyled mb-3">
                                @if ($coin->year)<li><strong>{{ __('catalog.year_label') }}:</strong> {{ $coin->year }}</li>@endif
                                @if ($coin->issue_date)<li><strong>{{ __('catalog.issue_date_label') }}:</strong> {{ $coin->issue_date->format('d.m.Y') }}</li>@endif
                                @if ($coin->denomination)<li><strong>{{ __('catalog.denomination_label') }}:</strong> {{ $coin->denomination }}</li>@endif
                                @if ($coin->metal)<li><strong>{{ __('catalog.metal_label') }}:</strong> {{ $coin->metal }}</li>@endif
                                @if ($coin->category)<li><strong>{{ __('catalog.category') }}:</strong> {{ __('catalog.categories.' . $coin->category) }}</li>@endif
                                @if ($coin->quality)<li><strong>{{ __('catalog.quality_label') }}:</strong> {{ $coin->quality }}</li>@endif
                                @if ($coin->weight)<li><strong>{{ __('catalog.weight_label') }}:</strong> {{ $coin->weight }}</li>@endif
                                @if ($coin->diameter)<li><strong>{{ __('catalog.diameter_label') }}:</strong> {{ $coin->diameter }} mm</li>@endif
                                @if ($coin->edge)<li><strong>{{ __('catalog.edge_label') }}:</strong> {{ $coin->edge }}</li>@endif
                                @if ($coin->mintage)<li><strong>{{ __('catalog.mintage_label') }}:</strong> {{ $coin->mintage }}</li>@endif
                                @if ($coin->mint)<li><strong>{{ __('catalog.mint_label') }}:</strong> {{ $coin->mint }}</li>@endif
                                @if ($coin->artists->isNotEmpty())<li><strong>{{ __('catalog.artist_label') }}:</strong> {{ $coin->artistNames() }}</li>@endif
                                <li><strong>{{ __('catalog.series_label') }}:</strong> {{ $coin->series?->name ?? __('catalog.no_series') }}</li>
                            </ul>

                            @if ($coin->description)
                                <div class="mt-3">{!! $coin->description !!}</div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        @endforeach

    </div>
</div>

<div class="container-fluid px-3 px-lg-4 catalog-pagination py-4">
    <div class="d-flex justify-content-center">
        {{ $coins->onEachSide(1)->links('pagination::bootstrap-5') }}
    </div>
</div>

@else
    <p class="text-center mt-4">{{ __('catalog.no_coins_found') }}</p>
@endif
