<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CalendarController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\OrderController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| VIP Windows — Web Routes
|--------------------------------------------------------------------------
*/

// Guest routes
Route::middleware('guest:vip')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
});

// Authenticated VIP staff routes
Route::middleware('auth:vip')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    // Dashboard
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    // Orders
    Route::get('/orders', [OrderController::class, 'index'])->name('orders.index');
    Route::get('/orders/{id}', [OrderController::class, 'show'])->name('orders.show');
    Route::put('/orders/{id}/status', [OrderController::class, 'updateStatus'])->name('orders.updateStatus');
    Route::put('/orders/{id}/assign', [OrderController::class, 'assignTechnician'])->name('orders.assign');

    // Calendar management (admin)
    Route::get('/calendar', [CalendarController::class, 'index'])->name('calendar.index');
    Route::post('/calendar/slots', [CalendarController::class, 'storeSlot'])->name('calendar.storeSlot');
    Route::put('/calendar/slots/{id}', [CalendarController::class, 'updateSlot'])->name('calendar.updateSlot');
    Route::delete('/calendar/slots/{id}', [CalendarController::class, 'deleteSlot'])->name('calendar.deleteSlot');
});

// Public booking route (customers pick an install slot)
Route::get('/book/{order}', [CalendarController::class, 'showBooking'])->name('booking.show');
Route::post('/book/{order}', [CalendarController::class, 'confirmBooking'])->name('booking.confirm');
