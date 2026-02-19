<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TrackController;
use App\Http\Controllers\LinkController;

Route::get(
    '/', function () {
        return view('welcome');
    }
);

Route::resource('tracks', TrackController::class);
Route::post('tracks/{track}/check-now', [TrackController::class, 'checkNow'])->name('tracks.checkNow');

