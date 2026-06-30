<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ProviderController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\VehicleController;
use App\Http\Controllers\VehicleCatalogController;
use App\Http\Controllers\WorkshopDashboardController;
use App\Http\Controllers\WorkshopAppointmentController;
use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome')->name('landing.home');
Route::view('/maintenance', 'maintenance')->name('landing.maintenance');
Route::post('/api/landing/appointments', [WorkshopAppointmentController::class, 'store'])->name('landing.appointments.store');
Route::get('/api/landing/vehicle-catalog/brands', [VehicleCatalogController::class, 'brands'])->name('landing.vehicle-catalog.brands');
Route::get('/api/landing/vehicle-catalog/models', [VehicleCatalogController::class, 'models'])->name('landing.vehicle-catalog.models');

Route::middleware('guest')->group(function (): void {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.store');
});

Route::middleware('auth')->group(function (): void {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    Route::get('/api/workshop/dashboard', WorkshopDashboardController::class)->name('workshop.dashboard');
    Route::get('/api/workshop/vehicle-catalog/brands', [VehicleCatalogController::class, 'brands'])->name('vehicle-catalog.brands');
    Route::get('/api/workshop/vehicle-catalog/models', [VehicleCatalogController::class, 'models'])->name('vehicle-catalog.models');
    Route::apiResource('/api/workshop/clients', ClientController::class)->except(['show']);
    Route::apiResource('/api/workshop/orders', OrderController::class)->except(['show']);
    Route::apiResource('/api/workshop/providers', ProviderController::class)->except(['show']);
    Route::apiResource('/api/workshop/products', ProductController::class)->except(['show']);
    Route::apiResource('/api/workshop/services', ServiceController::class)->except(['show']);
    Route::apiResource('/api/workshop/vehicles', VehicleController::class)->except(['show']);
    Route::get('/api/workshop/appointments', [WorkshopAppointmentController::class, 'index'])->name('appointments.index');
    Route::patch('/api/workshop/appointments/{appointment}', [WorkshopAppointmentController::class, 'update'])->name('appointments.update');
    Route::post('/api/workshop/appointments/{appointment}/convert-to-order', [WorkshopAppointmentController::class, 'convertToOrder'])->name('appointments.convert-to-order');

    Route::get('/portal', function () {
        return view('app');
    })->name('portal');
});
