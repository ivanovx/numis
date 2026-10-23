<div class="d-flex flex-wrap align-items-center gap-2 mb-3">
    <a href="{{ route('admin.csv.export', $resource) }}" class="btn btn-outline-primary btn-sm">
        Експортирай CSV
    </a>
    <form method="POST" action="{{ route('admin.csv.import', $resource) }}" enctype="multipart/form-data" class="d-flex flex-wrap gap-2 align-items-center">
        @csrf
        <label for="csv-{{ $resource }}" class="visually-hidden">Импортирай {{ ['coins' => 'монети', 'series' => 'серии', 'artists' => 'художници'][$resource] ?? $resource }} от CSV</label>
        <input id="csv-{{ $resource }}" type="file" name="csv" accept=".csv,text/csv" class="form-control form-control-sm" required>
        <button type="submit" class="btn btn-outline-secondary btn-sm">Импортирай CSV</button>
    </form>
</div>