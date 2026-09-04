<div class="d-flex flex-wrap align-items-center gap-2 mb-3">
    <a href="{{ route('admin.csv.export', $resource) }}" class="btn btn-outline-primary btn-sm">
        Export CSV
    </a>
    <form method="POST" action="{{ route('admin.csv.import', $resource) }}" enctype="multipart/form-data" class="d-flex flex-wrap gap-2 align-items-center">
        @csrf
        <label for="csv-{{ $resource }}" class="visually-hidden">Import {{ $resource }} CSV</label>
        <input id="csv-{{ $resource }}" type="file" name="csv" accept=".csv,text/csv" class="form-control form-control-sm" required>
        <button type="submit" class="btn btn-outline-secondary btn-sm">Import CSV</button>
    </form>
</div>