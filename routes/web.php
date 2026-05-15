<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CalendarController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\PageController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| VIP Windows — Web Routes
|--------------------------------------------------------------------------
*/

// ─── Public pages ─────────────────────────────────────────────
Route::get('/', [PageController::class, 'home'])->name('home');
Route::get('/about', [PageController::class, 'about'])->name('about');
Route::get('/services', [PageController::class, 'services'])->name('services');
Route::get('/gallery', [PageController::class, 'gallery'])->name('gallery');
Route::get('/service-areas', [PageController::class, 'serviceAreas'])->name('service-areas');
Route::get('/contact', [PageController::class, 'contact'])->name('contact');

// ─── Auth (shared login for admin + customer) ─────────────────
Route::middleware('guest:vip')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
});

Route::middleware('auth:vip')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
});

// ─── Customer area ────────────────────────────────────────────
Route::middleware('auth:vip')->prefix('my')->name('customer.')->group(function () {
    Route::get('/', [CustomerController::class, 'dashboard'])->name('dashboard');
    Route::get('/book', [CustomerController::class, 'bookInstallation'])->name('book');
    Route::post('/book', [CustomerController::class, 'confirmBooking'])->name('book.confirm');
});

// ─── Admin / staff area ───────────────────────────────────────
Route::middleware(['auth:vip', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    // Orders
    Route::get('/orders', [OrderController::class, 'index'])->name('orders.index');
    Route::get('/orders/{id}', [OrderController::class, 'show'])->name('orders.show');
    Route::put('/orders/{id}/status', [OrderController::class, 'updateStatus'])->name('orders.updateStatus');
    Route::put('/orders/{id}/assign', [OrderController::class, 'assignTechnician'])->name('orders.assign');

    // Calendar management
    Route::get('/calendar', [CalendarController::class, 'index'])->name('calendar.index');
    Route::post('/calendar/slots', [CalendarController::class, 'storeSlot'])->name('calendar.storeSlot');
    Route::put('/calendar/slots/{id}', [CalendarController::class, 'updateSlot'])->name('calendar.updateSlot');
    Route::delete('/calendar/slots/{id}', [CalendarController::class, 'deleteSlot'])->name('calendar.deleteSlot');
});

// ─── Public booking (via link from admin) ─────────────────────
Route::get('/book/{order}', [CalendarController::class, 'showBooking'])->name('booking.show');
Route::post('/book/{order}', [CalendarController::class, 'confirmBooking'])->name('booking.confirm');
