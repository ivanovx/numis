@extends('layouts.app')

@section('content')
    <div class="container-fluid px-3 px-lg-4 catalog-detail">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
            <a href="{{ url()->previous() === url()->current() ? route('catalog.index') : url()->previous() }}" class="btn btn-outline-secondary">
                &larr; {{ __('catalog.back_to_catalog') }}
            </a>
            @if ($coin->series)
                <span class="catalog-detail-kicker">{{ $coin->series->name }}</span>
            @endif
        </div>

        <article class="catalog-detail-card">
            <header class="catalog-detail-header">
                <p class="catalog-detail-eyebrow mb-2">{{ $coin->category ? __('catalog.categories.' . $coin->category) : '' }}</p>
                <h1>{{ $coin->title }}</h1>
                @if ($coin->year)
                    <p class="mb-0">{{ __('catalog.year_label') }}: {{ $coin->year }}</p>
                @endif
            </header>

            <div class="row g-0">
                <div class="col-lg-5 catalog-detail-images">
                    <div class="row g-3">
                        @foreach ([['image' => $coin->front_image_url, 'label' => __('catalog.front_label'), 'description' => $coin->front_description], ['image' => $coin->back_image_url, 'label' => __('catalog.back_label'), 'description' => $coin->back_description]] as $side)
                            <div class="col-6 text-center">
                                <div class="catalog-detail-image-frame">
                                    @if ($side['image'])
                                        <img src="{{ $side['image'] }}" class="img-fluid" alt="{{ $coin->title }} — {{ $side['label'] }}">
                                    @else
                                        <span class="text-muted">{{ $side['label'] }}</span>
                                    @endif
                                </div>
                                <div class="small text-muted mt-2">{{ $side['label'] }}</div>
                                @if ($side['description'])
                                    <div class="catalog-rich-text text-start mt-2">{!! $side['description'] !!}</div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="col-lg-7 catalog-detail-content">
                    <dl class="row catalog-spec-list mb-0">
                        @foreach ([
                            'year' => __('catalog.year_label'),
                            'issue_date' => __('catalog.issue_date_label'),
                            'denomination' => __('catalog.denomination_label'),
                            'metal' => __('catalog.metal_label'),
                            'quality' => __('catalog.quality_label'),
                            'weight' => __('catalog.weight_label'),
                            'diameter' => __('catalog.diameter_label'),
                            'edge' => __('catalog.edge_label'),
                            'mintage' => __('catalog.mintage_label'),
                            'mint' => __('catalog.mint_label'),
                        ] as $field => $label)
                            @php($value = $field === 'issue_date' ? $coin->issue_date?->format('d.m.Y') : $coin->$field)
                            @if ($value)
                                <dt class="col-sm-5">{{ $label }}</dt>
                                <dd class="col-sm-7">{{ $value }}{{ $field === 'diameter' ? ' mm' : '' }}</dd>
                            @endif
                        @endforeach

                        @if ($coin->artists->isNotEmpty())
                            <dt class="col-sm-5">{{ __('catalog.artist_label') }}</dt>
                            <dd class="col-sm-7">{{ $coin->artistNames() }}</dd>
                        @endif
                        <dt class="col-sm-5">{{ __('catalog.series_label') }}</dt>
                        <dd class="col-sm-7">{{ $coin->series?->name ?? __('catalog.no_series') }}</dd>
                    </dl>

                    @if ($coin->description)
                        <section class="catalog-rich-text catalog-detail-description mt-4">
                            {!! $coin->description !!}
                        </section>
                    @endif
                </div>
            </div>
        </article>
    </div>
@endsection