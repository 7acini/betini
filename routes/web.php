<?php

use App\Http\Controllers\ClientController;
use App\Http\Controllers\WorkshopDashboardController;
use Illuminate\Support\Facades\Route;

Route::get('/api/workshop/dashboard', WorkshopDashboardController::class)->name('workshop.dashboard');
Route::apiResource('/api/workshop/clients', ClientController::class)->except(['show']);

Route::get('/', function () {
    return view('app');
});
