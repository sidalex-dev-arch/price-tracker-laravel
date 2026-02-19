@extends('layouts.app')

@section('title', $track->name)

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>{{ $track->name }}</h1>
        <div>
            <a href="{{ route('tracks.edit', $track) }}" class="btn btn-warning">Редактировать</a>
            <form action="{{ route('tracks.destroy', $track) }}" method="POST" class="d-inline">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-danger" onclick="return confirm('Удалить трек {{ addslashes($track->name) }}?')">Удалить</button>
            </form>
        </div>
    </div>
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Параметры отслеживания</h5>
                    <p><strong>Желаемая цена:</strong> {{ $track->target_price ? number_format($track->target_price, 2) . ' грн' : 'не указана' }}</p>
                    <p><strong>Уведомлять при падении на:</strong> {{ $track->alert_percentage }}%</p>
                    @if ($track->notes)
                        <p><strong>Заметки:</strong> {{ $track->notes }}</p>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <h3 class="mb-3">Магазины и актуальные цены</h3>

    @if ($track->links->isEmpty())
        <div class="alert alert-info">У этого трека пока нет ссылок на магазины.</div>
    @else
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th>Магазин</th>
                        <th>Текущая цена</th>
                        <th>Последняя проверка</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($track->links as $link)
                        <tr>
                            <td>{{ $link->store_name }}</td>
                            <td>
                                @if ($link->last_price)
                                    {{ number_format($link->last_price, 2) }} грн
                                @else
                                    <span class="text-muted">Не проверено</span>
                                @endif
                            </td>
                            <td>
                                @if ($link->last_checked_at)
                                    {{ $link->last_checked_at->format('d.m.Y H:i') }}
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif

    <a href="{{ route('tracks.index') }}" class="btn btn-secondary mt-4">Вернуться к списку</a>
@endsection