<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <title>{{ __('catalog.pdf_title') }}</title>
    <style>
        @page { margin: 16px 18px; }

        body {
            color: #142a3d;
            font-family: DejaVu Sans, sans-serif;
            font-size: 9px;
        }

        h1 {
            margin: 0 0 2px;
            color: #023E73;
            font-size: 16px;
        }

        .pdf-meta {
            margin-bottom: 10px;
            color: #4b6072;
            font-size: 8px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        thead th {
            padding: 5px 4px;
            border-bottom: 1.5px solid #023E73;
            background: #eef6f8;
            color: #023E73;
            font-size: 8px;
            text-align: left;
            text-transform: uppercase;
        }

        tbody td {
            padding: 4px;
            border-bottom: 0.5px solid #dfe6ec;
            vertical-align: middle;
        }

        tbody tr:nth-child(even) {
            background: #f7f9fb;
        }

        .pdf-thumb {
            width: 34px;
            height: 34px;
            text-align: center;
        }

        .pdf-thumb img {
            max-width: 34px;
            max-height: 34px;
        }

        .pdf-title {
            font-weight: bold;
        }

        .pdf-footer {
            position: fixed;
            bottom: -10px;
            left: 0;
            right: 0;
            color: #9aa8b3;
            font-size: 7px;
            text-align: center;
        }
    </style>
</head>
<body>
    <h1>{{ __('catalog.pdf_title') }}</h1>
    <p class="pdf-meta">
        {{ __('catalog.pdf_generated_at') }}: {{ $generatedAt->format('d.m.Y H:i') }}
        &nbsp;·&nbsp; {{ __('catalog.pdf_total') }}: {{ $coins->count() }}
    </p>

    <table>
        <thead>
            <tr>
                <th>{{ __('catalog.year_label') }}</th>
                <th>{{ __('catalog.pdf_col_name') }}</th>
                <th>{{ __('catalog.denomination_label') }}</th>
                <th>{{ __('catalog.metal_label') }}</th>
                <th>{{ __('catalog.artist_label') }}</th>
                <th>{{ __('catalog.pdf_col_front') }}</th>
                <th>{{ __('catalog.pdf_col_back') }}</th>
                <th>{{ __('catalog.pdf_col_mintage') }}</th>
                <th>{{ __('catalog.pdf_col_mint') }}</th>
                <th>{{ __('catalog.pdf_col_edge') }}</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($coins as $coin)
                @php
                    $frontPath = $coin->front_image ? \Illuminate\Support\Facades\Storage::disk('public')->path($coin->front_image) : null;
                    $backPath = $coin->back_image ? \Illuminate\Support\Facades\Storage::disk('public')->path($coin->back_image) : null;
                @endphp
                <tr>
                    <td>{{ $coin->year ?: '—' }}</td>
                    <td class="pdf-title">{{ $coin->title }}</td>
                    <td>{{ $coin->denomination ?: '—' }}</td>
                    <td>{{ $coin->metal ?: '—' }}</td>
                    <td>{{ $coin->artists->pluck('name')->implode(', ') ?: '—' }}</td>
                    <td class="pdf-thumb">
                        @if ($frontPath && file_exists($frontPath))
                            <img src="{{ $frontPath }}" alt="">
                        @else
                            —
                        @endif
                    </td>
                    <td class="pdf-thumb">
                        @if ($backPath && file_exists($backPath))
                            <img src="{{ $backPath }}" alt="">
                        @else
                            —
                        @endif
                    </td>
                    <td>{{ $coin->mintage ?: '—' }}</td>
                    <td>{{ $coin->mint ?: '—' }}</td>
                    <td>{{ $coin->edge ?: '—' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="pdf-footer">Numis · {{ config('app.url') }}</div>
</body>
</html>
