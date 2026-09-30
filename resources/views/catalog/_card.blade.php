<div class="col">
    <div class="card shadow-sm h-100">
        <div class="card-header">
            <a class="btn btn-link" href="{{ route('catalog.coin', array_merge(['locale' => app()->getLocale(), 'coin' => $coin], array_filter($filters ?? []))) }}">
                {{ $coin->title }}
            </a>
        </div>

        <div class="coin-flip-container" data-flip-container>
            @if ($coin->metal)
                <span class="coin-metal-badge">{{ \Illuminate\Support\Str::of($coin->metal)->before(' с')->before(' with') }}</span>
            @endif

            <button type="button" class="coin-flip-indicator" data-flip-toggle aria-label="{{ __('catalog.flip_hint') }}" title="{{ __('catalog.flip_hint') }}">
                <i class="bi bi-arrow-repeat" aria-hidden="true"></i>
            </button>

            <div class="coin-flip-inner">
                <div class="coin-flip-front">
                    @if ($coin->front_image_url)
                        <div class="coin-img-skeleton"></div>
                        <img src="{{ $coin->front_image_url }}" class="img-fluid" loading="lazy"
                             alt="{{ $coin->title }} — {{ __('catalog.front_label') }}"
                             onload="this.previousElementSibling?.remove()">
                    @endif
                </div>
                <div class="coin-flip-back">
                    @if ($coin->back_image_url)
                        <div class="coin-img-skeleton"></div>
                        <img src="{{ $coin->back_image_url }}" class="img-fluid" loading="lazy"
                             alt="{{ $coin->title }} — {{ __('catalog.back_label') }}"
                             onload="this.previousElementSibling?.remove()">
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
