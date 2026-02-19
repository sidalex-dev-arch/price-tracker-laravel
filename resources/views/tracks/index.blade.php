@extends('layouts.app')

@section('title', 'My tracks')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Мои треки</h1>
        <a href="{{ route('tracks.create') }}" class="btn btn-primary">
            + Add track
        </a>
    </div>
@php
    $alerts = [];
    foreach ($tracks as $track) {
        if (!$track->target_price) continue;

        foreach ($track->links as $link) {
            if ($link->last_price && $link->last_price <= $track->target_price) {
                $difference = $track->target_price - $link->last_price;
                $alerts[] = [
                    'track_name' => $track->name,
                    'store_name' => $link->store_name,
                    'current_price' => number_format($link->last_price, 2),
                    'target_price' => number_format($track->target_price, 2),
                    'difference' => number_format($difference, 0),
                    'track_id' => $track->id,
                ];
            }
        }
    }
@endphp

@if (!empty($alerts))
    <div class="mb-4">
        @foreach ($alerts as $alert)
            <div class="alert alert-warning alert-dismissible fade show d-flex align-items-center justify-content-between" role="alert">
                <div>
                    <strong>The price has reached the desired level!</strong><br>
                    On track <b>{{ $alert['track_name'] }}</b> in the store <b>{{ $alert['store_name'] }}</b> 
                    price <b>{{ $alert['current_price'] }} грн</b> 
                    ( {{ $alert['difference'] }} UAH lower or equal to the desired price {{ $alert['target_price'] }} грн).
                </div>
                <div class="d-flex align-items-center">
                    <a href="{{ route('tracks.show', $alert['track_id']) }}" class="btn btn-sm btn-outline-dark me-2">
                        View track
                    </a>
                    <button type="button" class="btn-close ms-2" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            </div>
        @endforeach
    </div>
@endif

    @if ($tracks->isEmpty())
        <div class="alert alert-info">
            You don't have any tracks yet. Click "Add Track" to start tracking prices.
        </div>
    @else
        <div class="table-responsive">
            <table class="table table-striped table-hover table-bordered">
                <thead class="table-dark">
                    <tr>
                        <th>Product</th>
                        <th>Shop</th>
                        <th>Link</th>
                        <th>Desired price</th>
                        <th>Current price</th>
                        <th>Last check</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($tracks as $track)
                        @foreach ($track->links as $link)
                            <tr>
                                <td class="align-middle fw-bold">
                                    <a href="{{ route('tracks.show', $track) }}">
                                        {{ $track->name }}
                                    </a>
                                </td>
                                <td class="align-middle">{{ $link->store_name }}</td>
                                <td class="align-middle">
                                    <a href="{{ $link->url }}" target="_blank" rel="noopener noreferrer" class="text-break">
                                        {{ Str::limit($link->url, 60) }}
                                    </a>
                                </td>
                                <td class="align-middle">
                                    {{ $track->target_price ? number_format($track->target_price, 2) . ' грн' : '—' }}
                                </td>
                                <td class="align-middle">
                                    @if ($link->last_price)
                                        {{ number_format($link->last_price, 2) }} грн
                                    @else
                                        <span class="text-muted">Not verified</span>
                                    @endif
                                </td>
                                <td class="align-middle">
                                    @if ($link->last_checked_at)
                                        {{ $link->last_checked_at->format('d.m.Y H:i') }}
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                                <td class="align-middle">
                                    <form method="POST" action="{{ route('tracks.checkNow', $track) }}" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-outline-primary me-1">
                                            Check
                                        </button>
                                    </form>

                                    <a href="{{ route('tracks.show', $track) }}" class="btn btn-sm btn-outline-secondary me-1">
                                        More details

                                    </a>

                                    <form method="POST" action="{{ route('tracks.destroy', $track) }}" class="d-inline" onsubmit="return confirm('Delete track «{{ addslashes($track->name) }}» and all its links?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger">
                                            Delete
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
@endsection