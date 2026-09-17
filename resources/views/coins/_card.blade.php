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
            </ul>
        </div>
        <div class="card-footer">
            <span class="text-muted">{{ $coin->series?->name ?? __('catalog.no_series') }}</span>
        </div>
    </div>
</div>
