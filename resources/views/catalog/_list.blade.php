@if ($coins->count())

<div class="container mt-4">
    <div class="row g-4">

        @foreach ($coins as $coin)
            <div class="col-12 col-sm-6 col-md-4 col-lg-3">
                <div class="card shadow-sm h-100">
                    <div class="card-header">
                        <a class="btn btn-link" data-bs-toggle="modal" data-bs-target="#coinModal-{{ $coin->id }}">
                            {{ $coin->title }}
                        </a>
                    </div>
                    <div class="coin-flip-container">
                        <div class="coin-flip-inner">
                            <div class="coin-flip-front">
                                @if ($coin->front_image_url)
                                    <img src="{{ $coin->front_image_url }}" class="img-fluid" alt="{{ $coin->title }} front">
                                @endif
                            </div>
                            <div class="coin-flip-back">
                                @if ($coin->back_image_url)
                                    <img src="{{ $coin->back_image_url }}" class="img-fluid" alt="{{ $coin->title }} back">
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="card-body">
                        <ul class="list-unstyled small">
                            @if ($coin->year)<li><strong>Year:</strong> {{ $coin->year }}</li>@endif
                            @if ($coin->metal)<li><strong>Metal:</strong> {{ $coin->metal }}</li>@endif
                            @if ($coin->diameter)<li><strong>Diameter:</strong> {{ $coin->diameter }} mm</li>@endif
                        </ul>
                    </div>
                    <div class="card-footer">
                        <span class="text-muted">{{ $coin->seriesNames() }}</span>
                    </div>
                </div>
            </div>

            <div class="modal fade" id="coinModal-{{ $coin->id }}" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-lg modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">{{ $coin->title }}</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div class="text-center mb-3">
                                @if ($coin->front_image_url)
                                    <img src="{{ $coin->front_image_url }}" class="img-fluid mb-2" style="max-height:200px" alt="front">
                                @endif
                                @if ($coin->back_image_url)
                                    <img src="{{ $coin->back_image_url }}" class="img-fluid" style="max-height:200px" alt="back">
                                @endif
                            </div>
                            <ul class="list-unstyled">
                                @if ($coin->year)<li><strong>Year:</strong> {{ $coin->year }}</li>@endif
                                @if ($coin->metal)<li><strong>Metal:</strong> {{ $coin->metal }}</li>@endif
                                @if ($coin->diameter)<li><strong>Diameter:</strong> {{ $coin->diameter }} mm</li>@endif
                                <li><strong>Series:</strong> {{ $coin->seriesNames() }}</li>
                            </ul>
                            @if ($coin->description)
                                <div class="mt-3">{!! nl2br(e($coin->description)) !!}</div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        @endforeach

    </div>
</div>

<div class="container">
    {{ $coins->links() }}
</div>

@else
    <p class="text-center mt-4">No coins found.</p>
@endif
