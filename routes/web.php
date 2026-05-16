<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CalendarController;
use App\Http\Controllers\ConsultationController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\CustomerManagementController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EmailController;
use App\Http\Controllers\GalleryController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\ServiceAreaController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\VipQuoteController;
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

    // Gallery management
    Route::get('/gallery', [GalleryController::class, 'index'])->name('gallery.index');
    Route::post('/gallery', [GalleryController::class, 'store'])->name('gallery.store');
    Route::put('/gallery/{id}', [GalleryController::class, 'update'])->name('gallery.update');
    Route::delete('/gallery/{id}', [GalleryController::class, 'destroy'])->name('gallery.destroy');

    // Service areas management
    Route::get('/service-areas', [ServiceAreaController::class, 'index'])->name('service-areas.index');
    Route::post('/service-areas', [ServiceAreaController::class, 'store'])->name('service-areas.store');
    Route::put('/service-areas/{id}', [ServiceAreaController::class, 'update'])->name('service-areas.update');
    Route::delete('/service-areas/{id}', [ServiceAreaController::class, 'destroy'])->name('service-areas.destroy');

    // Customer management
    Route::get('/customers', [CustomerManagementController::class, 'index'])->name('customers.index');
    Route::get('/customers/create', [CustomerManagementController::class, 'create'])->name('customers.create');
    Route::post('/customers', [CustomerManagementController::class, 'store'])->name('customers.store');
    Route::get('/customers/{id}', [CustomerManagementController::class, 'show'])->name('customers.show');
    Route::get('/customers/{id}/edit', [CustomerManagementController::class, 'edit'])->name('customers.edit');
    Route::put('/customers/{id}', [CustomerManagementController::class, 'update'])->name('customers.update');
    Route::delete('/customers/{id}', [CustomerManagementController::class, 'destroy'])->name('customers.destroy');

    // Email
    Route::get('/email/compose', [EmailController::class, 'compose'])->name('email.compose');
    Route::post('/email/send', [EmailController::class, 'send'])->name('email.send');
    Route::get('/email/sent', [EmailController::class, 'sent'])->name('email.sent');

    // Virtual Consultations
    Route::get('/consultations', [ConsultationController::class, 'index'])->name('consultations.index');
    Route::get('/consultations/create', [ConsultationController::class, 'create'])->name('consultations.create');
    Route::post('/consultations', [ConsultationController::class, 'store'])->name('consultations.store');
    Route::put('/consultations/{id}', [ConsultationController::class, 'update'])->name('consultations.update');
    Route::delete('/consultations/{id}', [ConsultationController::class, 'destroy'])->name('consultations.destroy');

    // Quotes (full configurator)
    Route::get('/quotes', [VipQuoteController::class, 'index'])->name('quotes.index');
    Route::get('/quotes/create', [VipQuoteController::class, 'create'])->name('quotes.create');
    Route::post('/quotes', [VipQuoteController::class, 'store'])->name('quotes.store');
    Route::get('/quotes/{id}/edit', [VipQuoteController::class, 'edit'])->name('quotes.edit');
    Route::post('/quotes/{id}/save-draft', [VipQuoteController::class, 'saveDraft'])->name('quotes.saveDraft');
    Route::post('/quotes/{id}/item', [VipQuoteController::class, 'storeItem'])->name('quotes.storeItem');
    Route::delete('/quotes/{id}/item/{itemId}', [VipQuoteController::class, 'deleteItem'])->name('quotes.deleteItem');
    Route::post('/quotes/check-price', [VipQuoteController::class, 'checkPrice'])->name('quotes.checkPrice');
    Route::get('/quotes/panel-layout', [VipQuoteController::class, 'panelLayout'])->name('quotes.panel-layout');
    Route::post('/quotes/{id}/send', [VipQuoteController::class, 'sendToCustomer'])->name('quotes.send');
    Route::delete('/quotes/{id}', [VipQuoteController::class, 'destroy'])->name('quotes.destroy');

    // Settings
    Route::get('/settings', [SettingsController::class, 'index'])->name('settings.index');
    Route::post('/settings', [SettingsController::class, 'update'])->name('settings.update');
});

// ─── Public booking (via link from admin) ─────────────────────
Route::get('/book/{order}', [CalendarController::class, 'showBooking'])->name('booking.show');
Route::post('/book/{order}', [CalendarController::class, 'confirmBooking'])->name('booking.confirm');

// ─── Public consultation request ─────────────────────────────
Route::post('/consultation-request', [ConsultationController::class, 'publicRequest'])->name('consultation.request');
