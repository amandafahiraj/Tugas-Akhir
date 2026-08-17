<?php

use App\Http\Controllers\GpsReadingController;
use Illuminate\Support\Facades\Route;

// endpoint HTTP GET yang dipanggil browser setiap 2 detik via JS untuk memperbarui peta dashboard
Route::middleware(['web', 'auth'])->get('/gps-readings', [GpsReadingController::class, 'index'])->name('api.gps-readings.index');

// endpoint HTTP POST cadangan jika alat GPS mengirim data via HTTP, bukan MQTT
Route::post('/gps-readings', [GpsReadingController::class, 'store'])->name('api.gps-readings.store');

