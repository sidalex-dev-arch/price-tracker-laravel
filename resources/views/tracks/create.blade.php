@extends('layouts.app')

@section('title', 'Добавить трек')

@section('content')
    <h1 class="mb-4">Добавить трек</h1>

    <form method="POST" action="{{ route('tracks.store') }}">
        @csrf

        <div class="mb-3">
            <label class="form-label">Название товара</label>
            <input type="text" name="name" class="form-control" required value="{{ old('name') }}">
        </div>

        <div class="mb-3">
            <label class="form-label">Желаемая цена (грн)</label>
            <input type="number" name="target_price" step="0.01" class="form-control" value="{{ old('target_price') }}">
        </div>

        <div class="mb-3">
            <label class="form-label">Уведомлять при падении цены на (%)</label>
            <input type="number" name="alert_percentage" class="form-control" value="{{ old('alert_percentage', 10) }}">
        </div>

        <div class="mb-3">
            <label class="form-label">Заметки</label>
            <textarea name="notes" class="form-control" rows="3">{{ old('notes') }}</textarea>
        </div>

        <div class="mb-4">
            <h5>Ссылки на магазины</h5>
            <div id="links-container">
                <div class="link-row mb-3 input-group">
                    <input type="text" name="links[0][store_name]" placeholder="" class="form-control" required>
                    <input type="url" name="links[0][url]" placeholder="" class="form-control" required>
                    <button type="button" class="btn btn-danger" onclick="this.closest('.link-row').remove()">Удалить</button>
                </div>
            </div>

            <button type="button" class="btn btn-outline-secondary" onclick="addLinkRow()">+ Добавить ещё ссылку</button>
        </div>

        <button type="submit" class="btn btn-success btn-lg">Создать трек</button>
    </form>

    <script>
        let linkIndex = 1;

        function addLinkRow() {
            const container = document.getElementById('links-container');
            const row = document.createElement('div');
            row.className = 'link-row mb-3 input-group';
            row.innerHTML = `
                <input type="text" name="links[${linkIndex}][store_name]" placeholder="Citrus / Comfy" class="form-control" required>
                <input type="url" name="links[${linkIndex}][url]" placeholder="https://..." class="form-control" required>
                <button type="button" class="btn btn-danger" onclick="this.closest('.link-row').remove()">Удалить</button>
            `;
            container.appendChild(row);
            linkIndex++;
        }
    </script>
@endsection