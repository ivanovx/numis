{{--
    Data-completeness indicator for a coin.
    Params: $coin (Coin, with artists loaded), $detailed (bool, default false)
--}}
@php
    $c = $coin->completeness();
    $tone = $c['percent'] === 100 ? 'success' : ($c['percent'] >= 50 ? 'warning' : 'danger');
    $missingLabels = array_values($c['missing']);
@endphp

@if ($detailed ?? false)
    <div class="border rounded p-3 bg-light" data-completeness="{{ $c['filled'] }}/{{ $c['total'] }}">
        <div class="d-flex justify-content-between align-items-center mb-2">
            <strong>Data completeness (Пълнота на данните)</strong>
            <span class="badge text-bg-{{ $tone }}">{{ $c['filled'] }}/{{ $c['total'] }}</span>
        </div>
        <div class="progress" style="height: .5rem" role="progressbar"
             aria-valuenow="{{ $c['percent'] }}" aria-valuemin="0" aria-valuemax="100">
            <div class="progress-bar bg-{{ $tone }}" style="width: {{ $c['percent'] }}%"></div>
        </div>
        @if ($missingLabels)
            <div class="small mt-2">
                <span class="text-muted">Still missing (Липсва):</span>
                @foreach ($missingLabels as $label)
                    <span class="badge text-bg-light border fw-normal">{{ $label }}</span>
                @endforeach
            </div>
        @else
            <div class="small mt-2 text-success">All tracked fields are filled in.</div>
        @endif
        <div class="form-text mb-0">Reflects the saved record — it updates after you press Save.</div>
    </div>
@else
    <div style="min-width: 6.5rem"
         title="{{ $missingLabels ? 'Missing: '.implode(', ', $missingLabels) : 'All data present' }}"
         data-completeness="{{ $c['filled'] }}/{{ $c['total'] }}">
        <div class="d-flex justify-content-between small lh-1 mb-1">
            <strong>{{ $c['filled'] }}/{{ $c['total'] }}</strong>
            <span class="text-muted">{{ $c['percent'] }}%</span>
        </div>
        <div class="progress" style="height: 6px" role="progressbar"
             aria-valuenow="{{ $c['percent'] }}" aria-valuemin="0" aria-valuemax="100">
            <div class="progress-bar bg-{{ $tone }}" style="width: {{ $c['percent'] }}%"></div>
        </div>
    </div>
@endif
