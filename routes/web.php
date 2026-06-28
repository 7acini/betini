<?php

use App\Http\Controllers\ClientController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ProviderController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\VehicleController;
use App\Http\Controllers\WorkshopDashboardController;
use Illuminate\Support\Facades\Route;

Route::get('/api/workshop/dashboard', WorkshopDashboardController::class)->name('workshop.dashboard');
Route::apiResource('/api/workshop/clients', ClientController::class)->except(['show']);
Route::apiResource('/api/workshop/orders', OrderController::class)->except(['show']);
Route::apiResource('/api/workshop/providers', ProviderController::class)->except(['show']);
Route::apiResource('/api/workshop/products', ProductController::class)->except(['show']);
Route::apiResource('/api/workshop/services', ServiceController::class)->except(['show']);
Route::apiResource('/api/workshop/vehicles', VehicleController::class)->except(['show']);

Route::get('/', function () {
    return view('app');
});
