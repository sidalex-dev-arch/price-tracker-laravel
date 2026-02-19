<?php

namespace App\Http\Controllers;

use App\Models\Track;
use App\Models\TrackLink;
use Illuminate\Http\Request;
use App\Jobs\ScrapePriceJob;

class TrackController extends Controller
{
    public function index()
    {
        $tracks = Track::with('links')->get(); // подгружаем все ссылки сразу

        return view('tracks.index', compact('tracks'));
    }

    public function create()
    {
        return view('tracks.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate(
            [
            'name'              => 'required|string|max:255',
            'target_price'      => 'nullable|numeric|min:0',
            'alert_percentage'  => 'integer|min:1|max:100',
            'notes'             => 'nullable|string',
            'links.*.store_name' => 'required|string|max:100',
            'links.*.url'       => 'required|url|distinct',
            ]
        );

        $track = Track::create(
            $request->only(
                [
                'name', 'target_price', 'alert_percentage', 'notes'
                ]
            )
        );

        foreach ($request->links ?? [] as $linkData) {
            $track->links()->create($linkData);
        }

        return redirect()->route('tracks.index')
            ->with('success', 'Трек успешно создан!');
    }

    public function show(Track $track)
    {
        $track->links()->update(
            [
            'alert_triggered_at' => null,
            'alert_percentage_detected' => null,
            ]
        );

        return view('tracks.show', compact('track'));
    }

    public function edit(Track $track)
    {
        return view('tracks.edit', compact('track'));
    }

    public function update(Request $request, Track $track)
    {
        $validated = $request->validate(
            [
            'name'              => 'required|string|max:255',
            'target_price'      => 'nullable|numeric|min:0',
            'alert_percentage'  => 'integer|min:1|max:100',
            'notes'             => 'nullable|string',
            'links.*.store_name' => 'required|string|max:100',
            'links.*.url'       => 'required|url|distinct',
            ]
        );

        $track->update(
            $request->only(
                [
                'name', 'target_price', 'alert_percentage', 'notes'
                ]
            )
        );

        // Удаляем старые ссылки и добавляем новые (простой способ)
        $track->links()->delete();

        foreach ($request->links ?? [] as $linkData) {
            $track->links()->create($linkData);
        }

        return redirect()->route('tracks.show', $track)
            ->with('success', 'Трек успешно обновлён!');
    }

    public function destroy(Track $track)
    {
        $track->delete();

        return redirect()->route('tracks.index')
            ->with('success', 'Трек удалён!');
    }

    public function checkNow(Track $track)
    {
        $links = $track->links;

        foreach ($links as $link) {
            ScrapePriceJob::dispatchSync($link);  // dispatchSync — выполнит job синхронно (без очереди, сразу)
        }

        return redirect()->route('tracks.index', $track)
            ->with('success', 'Проверка завершена! Цены обновлены.');
    }
}