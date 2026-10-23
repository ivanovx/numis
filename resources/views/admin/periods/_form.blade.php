<div class="row g-3">
    <div class="col-md-6">
        <label for="period-name" class="form-label">Име</label>
        <input id="period-name" type="text" name="name" class="form-control" maxlength="255" required
               value="{{ old('name', $period->name) }}" placeholder="Например: Царство България при Фердинанд">
    </div>
    <div class="col-md-6">
        <label for="period-slug" class="form-label">Кратко име (slug)</label>
        <input id="period-slug" type="text" name="slug" class="form-control" maxlength="255"
               value="{{ old('slug', $period->slug) }}" placeholder="попълва се автоматично">
        <div class="form-text">Остави празно, за да се създаде автоматично от името.</div>
    </div>
</div>

<div class="mt-4">
    <button type="submit" class="btn btn-primary">Запази</button>
    <a href="{{ route('admin.periods.index') }}" class="btn btn-outline-secondary">Отказ</a>
</div>
