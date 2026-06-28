<?php

use App\Http\Controllers\WorkshopDashboardController;
use Illuminate\Support\Facades\Route;

Route::get('/api/workshop/dashboard', WorkshopDashboardController::class)->name('workshop.dashboard');

Route::get('/', function () {
    return view('app');
});
