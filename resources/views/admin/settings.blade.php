<!DOCTYPE html>
<html>
<head>
    <title>Настройки курсов валют</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container py-4">
    <h1>Настройки виджета курсов ЦБ</h1>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <form method="POST" action="{{ route('admin.settings.update') }}">
        @csrf
        <div class="card mb-4">
            <div class="card-header">Интервал обновления виджета (сек)</div>
            <div class="card-body">
                <input type="number" name="update_interval" value="{{ old('update_interval', $updateInterval) }}"
                       class="form-control" min="5" required>
                <small class="text-muted">Минимум 5 секунд</small>
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-header">Валюты для загрузки с ЦБ</div>
            <div class="card-body">
                <div class="row">
                    @foreach($currencies as $currency)
                        <div class="col-md-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="fetch_currencies[]"
                                       value="{{ $currency->id }}" id="fetch_{{ $currency->id }}"
                                       {{ $currency->is_fetch_enabled ? 'checked' : '' }}>
                                <label class="form-check-label" for="fetch_{{ $currency->id }}">
                                    {{ $currency->char_code }} ({{ $currency->name }})
                                </label>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-header">Валюты для отображения в виджете</div>
            <div class="card-body">
                <div class="row">
                    @foreach($currencies as $currency)
                        <div class="col-md-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="widget_currencies[]"
                                       value="{{ $currency->id }}" id="widget_{{ $currency->id }}"
                                       {{ $currency->is_widget_visible ? 'checked' : '' }}>
                                <label class="form-check-label" for="widget_{{ $currency->id }}">
                                    {{ $currency->char_code }} ({{ $currency->name }})
                                </label>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <button type="submit" class="btn btn-primary">Сохранить настройки</button>
        <a href="/widget" class="btn btn-outline-secondary">Перейти к виджету</a>
    </form>
</div>
</body>
</html>