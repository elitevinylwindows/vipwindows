<?php

use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AdminAvailabilityController;
use App\Http\Controllers\CalendarController;
use App\Http\Controllers\AdminServiceJobController;
use App\Http\Controllers\ConsultationController;
use App\Http\Controllers\ContentController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\CustomerManagementController;
use App\Http\Controllers\InstallerManagementController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EmailController;
use App\Http\Controllers\GalleryController;
use App\Http\Controllers\MasterHubController;
use App\Http\Controllers\Master\SeriesController;
use App\Http\Controllers\Master\SeriesConfigurationController;
use App\Http\Controllers\Master\SeriesWindowTypeController;
use App\Http\Controllers\Master\SizeLimitController;
use App\Http\Controllers\Master\Colors\AvailableColorController;
use App\Http\Controllers\Master\Colors\ColorConfigurationController;
use App\Http\Controllers\Master\Colors\ExteriorColorController;
use App\Http\Controllers\Master\Colors\InteriorColorController;
use App\Http\Controllers\Master\Colors\LaminateColorController;
use App\Http\Controllers\Master\Glass\GlassOptionController;
use App\Http\Controllers\Master\Glass\PaneController;
use App\Http\Controllers\Master\Glass\TemperedController;
use App\Http\Controllers\Master\Glass\ThicknessController;
use App\Http\Controllers\Master\ProfileManagerController;
use App\Http\Controllers\Master\DeductionManagerController;
use App\Http\Controllers\Master\Prices\MatrixController;
use App\Http\Controllers\Master\Prices\MarkupController;
use App\Http\Controllers\Master\Grids\GridController;
use App\Http\Controllers\Master\FrameController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\ServiceAreaController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\JobController;
use App\Http\Controllers\VipQuoteController;
use App\Http\Controllers\Installer\InstallerDashboardController;
use App\Http\Controllers\Installer\InstallerQuoteController;
use App\Http\Controllers\Installer\InstallerJobController;
use App\Http\Controllers\Installer\InstallerInvoiceController;
use App\Http\Controllers\Installer\InstallerCustomerController;
use App\Http\Controllers\Installer\InstallerAvailabilityController;
use App\Http\Controllers\Installer\InstallerProfileController;
use App\Http\Controllers\Installer\InstallerAttendanceController;
use App\Http\Controllers\Installer\InstallerServiceController;
use App\Http\Controllers\Installer\InstallerMessageController;
use App\Http\Controllers\Installer\InstallerTechMeasureController;
use App\Http\Controllers\Installer\InstallerServiceJobController;
use App\Http\Controllers\Admin\EmailTemplateController;
use App\Http\Controllers\Admin\MessageController;
use App\Http\Controllers\Admin\TeamMemberController;
use App\Http\Controllers\Admin\VipMasterController;
use App\Http\Controllers\TechMeasureController;
use App\Http\Controllers\CrewController;
use App\Http\Controllers\PublicBookingController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\ServiceRateController;
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

    // Audio streaming for voice notes (accessible by both admin and installer)
    Route::get('/messages/audio/{messageId}', [\App\Http\Controllers\Admin\MessageController::class, 'streamAudio'])->name('messages.audio');
});

// ─── Customer area ────────────────────────────────────────────
Route::middleware(['auth:vip', 'customer'])->prefix('my')->name('customer.')->group(function () {
    Route::get('/', [CustomerController::class, 'dashboard'])->name('dashboard');
    Route::get('/book', [CustomerController::class, 'bookInstallation'])->name('book');
    Route::get('/book/slots', [CustomerController::class, 'getSlots'])->name('book.slots');
    Route::post('/book', [CustomerController::class, 'confirmBooking'])->name('book.confirm');
});

// ─── Admin login (no auth required) ──────────────────────────
Route::get('/admin/login', [AuthController::class, 'showAdminLogin'])->name('admin.login');
Route::post('/admin/login', [AuthController::class, 'adminLogin'])->name('admin.login.submit');

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
    Route::post('/calendar/events', [CalendarController::class, 'storeEvent'])->name('calendar.storeEvent');
    Route::get('/calendar/event/{id}', [CalendarController::class, 'showEvent'])->name('calendar.showEvent');
    Route::put('/calendar/event/{id}', [CalendarController::class, 'updateEvent'])->name('calendar.updateEvent');
    Route::post('/calendar/event/{id}/reminder', [CalendarController::class, 'sendReminder'])->name('calendar.sendReminder');
    Route::post('/calendar/event/{id}/reschedule', [CalendarController::class, 'rescheduleEvent'])->name('calendar.rescheduleEvent');
    Route::delete('/calendar/event/{id}', [CalendarController::class, 'deleteEvent'])->name('calendar.deleteEvent');

    // Admin availability management
    Route::get('/calendar/availability', [AdminAvailabilityController::class, 'index'])->name('calendar.availability');
    Route::post('/calendar/availability/weekly', [AdminAvailabilityController::class, 'saveWeekly'])->name('calendar.availability.saveWeekly');
    Route::post('/calendar/availability/override', [AdminAvailabilityController::class, 'addOverride'])->name('calendar.availability.addOverride');
    Route::delete('/calendar/availability/override/{id}', [AdminAvailabilityController::class, 'removeOverride'])->name('calendar.availability.removeOverride');

    // Content (combined gallery + service areas)
    Route::get('/content', [ContentController::class, 'index'])->name('content.index');

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

    // Installer management
    Route::get('/installers', [InstallerManagementController::class, 'index'])->name('installers.index');
    Route::post('/installers', [InstallerManagementController::class, 'store'])->name('installers.store');
    Route::get('/installers/{id}', [InstallerManagementController::class, 'show'])->name('installers.show');
    Route::put('/installers/{id}', [InstallerManagementController::class, 'update'])->name('installers.update');
    Route::delete('/installers/{id}', [InstallerManagementController::class, 'destroy'])->name('installers.destroy');
    Route::post('/installers/approve-pay/{logId}', [InstallerManagementController::class, 'approvePay'])->name('installers.approvePay');

    // Crew management
    Route::get('/crews', [CrewController::class, 'index'])->name('crews.index');
    Route::post('/crews', [CrewController::class, 'store'])->name('crews.store');
    Route::get('/crews/{id}', [CrewController::class, 'show'])->name('crews.show');
    Route::put('/crews/{id}', [CrewController::class, 'update'])->name('crews.update');
    Route::delete('/crews/{id}', [CrewController::class, 'destroy'])->name('crews.destroy');

    // Service Jobs (admin view — matches installer services page)
    Route::get('/service-jobs', [AdminServiceJobController::class, 'index'])->name('service-jobs.index');
    Route::get('/service-jobs/{id}', [AdminServiceJobController::class, 'show'])->name('service-jobs.show');

    // Services management (Service Types)
    Route::get('/services', [ServiceController::class, 'index'])->name('services.index');
    Route::post('/services', [ServiceController::class, 'store'])->name('services.store');
    Route::get('/services/{id}', [ServiceController::class, 'show'])->name('services.show');
    Route::put('/services/{id}', [ServiceController::class, 'update'])->name('services.update');
    Route::delete('/services/{id}', [ServiceController::class, 'destroy'])->name('services.destroy');
    Route::post('/services/{id}/toggle-active', [ServiceController::class, 'toggleActive'])->name('services.toggleActive');

    // Installation Types (sub-types under Installation service)
    Route::post('/services/install-types', [ServiceController::class, 'storeInstallType'])->name('services.installTypes.store');
    Route::put('/services/install-types/{id}', [ServiceController::class, 'updateInstallType'])->name('services.installTypes.update');
    Route::delete('/services/install-types/{id}', [ServiceController::class, 'destroyInstallType'])->name('services.installTypes.destroy');

    // Attendance (admin view)
    Route::get('/attendance', [AttendanceController::class, 'index'])->name('attendance.index');
    Route::match(['get', 'post'], '/attendance/backfill', [AttendanceController::class, 'backfillTimeLogs'])->name('attendance.backfill');

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

    // Messages (admin ↔ installer)
    Route::get('/messages', [MessageController::class, 'index'])->name('messages.index');
    Route::get('/messages/unread-count', [MessageController::class, 'unreadCount'])->name('messages.unreadCount');
    Route::post('/messages/start', [MessageController::class, 'startConversation'])->name('messages.start');
    Route::get('/messages/{id}', [MessageController::class, 'show'])->name('messages.show');
    Route::post('/messages/{id}/send', [MessageController::class, 'send'])->name('messages.send');
    Route::delete('/messages/{id}/message/{messageId}', [MessageController::class, 'deleteMessage'])->name('messages.deleteMessage');

    // Tech Measures (admin view + convert to quote)
    Route::get('/tech-measures', [TechMeasureController::class, 'index'])->name('tech-measures.index');
    Route::get('/tech-measures/{id}', [TechMeasureController::class, 'show'])->name('tech-measures.show');
    Route::put('/tech-measures/{id}', [TechMeasureController::class, 'update'])->name('tech-measures.update');
    Route::post('/tech-measures/{id}/send-email', [TechMeasureController::class, 'sendEmail'])->name('tech-measures.sendEmail');
    Route::post('/tech-measures/from-event', [TechMeasureController::class, 'createFromEvent'])->name('tech-measures.fromEvent');
    Route::post('/tech-measures/{id}/convert-to-quote', [TechMeasureController::class, 'convertToQuote'])->name('tech-measures.convertToQuote');
    Route::post('/tech-measures/{id}/item', [TechMeasureController::class, 'addItem'])->name('tech-measures.addItem');
    Route::put('/tech-measures/{id}/item/{itemId}', [TechMeasureController::class, 'updateItem'])->name('tech-measures.updateItem');
    Route::delete('/tech-measures/{id}/item/{itemId}', [TechMeasureController::class, 'removeItem'])->name('tech-measures.removeItem');
    Route::post('/tech-measures/{id}/notes', [TechMeasureController::class, 'updateNotes'])->name('tech-measures.updateNotes');
    Route::post('/tech-measures/{id}/grids', [TechMeasureController::class, 'updateGrids'])->name('tech-measures.updateGrids');
    Route::post('/tech-measures/{id}/photo', [TechMeasureController::class, 'uploadPhoto'])->name('tech-measures.uploadPhoto');
    Route::delete('/tech-measures/{id}/photo/{photoId}', [TechMeasureController::class, 'deletePhoto'])->name('tech-measures.deletePhoto');

    // Quotes (full configurator)
    Route::get('/quotes', [VipQuoteController::class, 'index'])->name('quotes.index');
    Route::get('/quotes/create', [VipQuoteController::class, 'create'])->name('quotes.create');
    Route::post('/quotes', [VipQuoteController::class, 'store'])->name('quotes.store');
    Route::post('/quotes/check-price', [VipQuoteController::class, 'checkPrice'])->name('quotes.checkPrice');
    Route::get('/quotes/panel-layout', [VipQuoteController::class, 'panelLayout'])->name('quotes.panel-layout');
    Route::get('/quotes/shapes', [VipQuoteController::class, 'shapes'])->name('quotes.shapes');
    Route::get('/quotes/series-map', [VipQuoteController::class, 'seriesMap'])->name('quotes.seriesMap');
    Route::get('/quotes/{id}', [VipQuoteController::class, 'show'])->name('quotes.show');
    Route::put('/quotes/{id}', [VipQuoteController::class, 'update'])->name('quotes.update');
    Route::get('/quotes/{id}/edit', [VipQuoteController::class, 'edit'])->name('quotes.edit');
    Route::post('/quotes/{id}/save-draft', [VipQuoteController::class, 'saveDraft'])->name('quotes.saveDraft');
    Route::post('/quotes/{id}/item', [VipQuoteController::class, 'storeItem'])->name('quotes.storeItem');
    Route::delete('/quotes/{id}/item/{itemId}', [VipQuoteController::class, 'deleteItem'])->name('quotes.deleteItem');
    Route::post('/quotes/{id}/apply-discounts', [VipQuoteController::class, 'applyDiscounts'])->name('quotes.applyDiscounts');
    Route::post('/quotes/{id}/send', [VipQuoteController::class, 'sendToCustomer'])->name('quotes.send');
    Route::delete('/quotes/{id}', [VipQuoteController::class, 'destroy'])->name('quotes.destroy');

    // Invoices
    Route::get('/invoices', [InvoiceController::class, 'index'])->name('invoices.index');
    Route::post('/invoices', [InvoiceController::class, 'store'])->name('invoices.store');
    Route::get('/invoices/{id}', [InvoiceController::class, 'show'])->name('invoices.show');
    Route::put('/invoices/{id}', [InvoiceController::class, 'update'])->name('invoices.update');
    Route::post('/invoices/{id}/item', [InvoiceController::class, 'addItem'])->name('invoices.addItem');
    Route::delete('/invoices/{id}/item/{itemId}', [InvoiceController::class, 'removeItem'])->name('invoices.removeItem');
    Route::post('/invoices/{id}/payment', [InvoiceController::class, 'recordPayment'])->name('invoices.recordPayment');
    Route::post('/invoices/{id}/send', [InvoiceController::class, 'sendToCustomer'])->name('invoices.send');
    Route::delete('/invoices/{id}', [InvoiceController::class, 'destroy'])->name('invoices.destroy');

    // Jobs
    Route::get('/jobs', [JobController::class, 'index'])->name('jobs.index');
    Route::post('/jobs', [JobController::class, 'store'])->name('jobs.store');
    Route::get('/jobs/{id}', [JobController::class, 'show'])->name('jobs.show');
    Route::put('/jobs/{id}', [JobController::class, 'update'])->name('jobs.update');
    Route::post('/jobs/{id}/assign', [JobController::class, 'assign'])->name('jobs.assign');
    Route::post('/jobs/{id}/status', [JobController::class, 'updateStatus'])->name('jobs.updateStatus');
    Route::post('/jobs/{id}/note', [JobController::class, 'addNote'])->name('jobs.addNote');
    Route::post('/jobs/{id}/send-email', [EmailTemplateController::class, 'sendJobEmail'])->name('jobs.sendEmail');
    Route::post('/jobs/{id}/send-estimate', [EmailTemplateController::class, 'sendEstimate'])->name('jobs.sendEstimate');
    Route::delete('/jobs/{id}', [JobController::class, 'destroy'])->name('jobs.destroy');

    // Team Members
    Route::get('/team', [TeamMemberController::class, 'index'])->name('team.index');
    Route::post('/team', [TeamMemberController::class, 'store'])->name('team.store');
    Route::put('/team/{id}', [TeamMemberController::class, 'update'])->name('team.update');
    Route::delete('/team/{id}', [TeamMemberController::class, 'destroy'])->name('team.destroy');
    Route::post('/team/{id}/toggle-status', [TeamMemberController::class, 'toggleStatus'])->name('team.toggleStatus');

    // Email Templates
    Route::get('/email-templates', [EmailTemplateController::class, 'index'])->name('email-templates.index');
    Route::put('/email-templates/{id}', [EmailTemplateController::class, 'update'])->name('email-templates.update');
    Route::post('/email-templates/preview', [EmailTemplateController::class, 'preview'])->name('email-templates.preview');

    // Settings
    Route::get('/settings', [SettingsController::class, 'index'])->name('settings.index');
    Route::post('/settings', [SettingsController::class, 'update'])->name('settings.update');
    Route::put('/settings/rate/{id}', [SettingsController::class, 'updateRate'])->name('settings.updateRate');
    Route::post('/settings/truncate-preview', [SettingsController::class, 'truncatePreview'])->name('settings.truncatePreview');
    Route::post('/settings/truncate', [SettingsController::class, 'truncate'])->name('settings.truncate');

    // Service Rates
    Route::get('/settings/rates', [ServiceRateController::class, 'index'])->name('settings.rates');
    Route::post('/settings/rates', [ServiceRateController::class, 'store'])->name('settings.rates.store');
    Route::put('/settings/rates/{id}', [ServiceRateController::class, 'update'])->name('settings.rates.update');
    Route::delete('/settings/rates/{id}', [ServiceRateController::class, 'destroy'])->name('settings.rates.destroy');

    // ── Master Data ──────────────────────────────────────────
    Route::get('/master', [MasterHubController::class, 'index'])->name('master.hub');

    // Series
    Route::get('/master/series', [SeriesController::class, 'index'])->name('master.series.index');
    Route::post('/master/series', [SeriesController::class, 'store'])->name('master.series.store');
    Route::put('/master/series/{id}', [SeriesController::class, 'update'])->name('master.series.update');
    Route::delete('/master/series/{id}', [SeriesController::class, 'destroy'])->name('master.series.destroy');

    // Series Configurations
    Route::get('/master/series/configurations', [SeriesConfigurationController::class, 'index'])->name('master.series.configurations');
    Route::post('/master/series/configurations', [SeriesConfigurationController::class, 'store'])->name('master.series.configurations.store');
    Route::put('/master/series/configurations/{id}', [SeriesConfigurationController::class, 'update'])->name('master.series.configurations.update');
    Route::post('/master/series/configurations/{id}/toggle-active', [SeriesConfigurationController::class, 'toggleActive'])->name('master.series.configurations.toggleActive');
    Route::delete('/master/series/configurations/{id}', [SeriesConfigurationController::class, 'destroy'])->name('master.series.configurations.destroy');
    Route::post('/master/series/configurations/import', [SeriesConfigurationController::class, 'import'])->name('master.series.configurations.import');

    // Series Window Types
    Route::get('/master/series/window-types', [SeriesWindowTypeController::class, 'index'])->name('master.series.window-types');
    Route::put('/master/series/window-types/{seriesId}', [SeriesWindowTypeController::class, 'update'])->name('master.series.window-types.update');

    // Size Limits
    Route::get('/master/series/size-limits', [SizeLimitController::class, 'index'])->name('master.series.size-limits');
    Route::put('/master/series/size-limits/{seriesId}', [SizeLimitController::class, 'update'])->name('master.series.size-limits.update');

    // ── Colors ───────────────────────────────────────────────
    Route::get('/master/colors/available', [AvailableColorController::class, 'index'])->name('master.colors.available');
    Route::put('/master/colors/available/{seriesId}', [AvailableColorController::class, 'update'])->name('master.colors.available.update');

    Route::get('/master/colors/configurations', [ColorConfigurationController::class, 'index'])->name('master.colors.configurations');
    Route::post('/master/colors/configurations', [ColorConfigurationController::class, 'store'])->name('master.colors.configurations.store');
    Route::put('/master/colors/configurations/{id}', [ColorConfigurationController::class, 'update'])->name('master.colors.configurations.update');
    Route::delete('/master/colors/configurations/{id}', [ColorConfigurationController::class, 'destroy'])->name('master.colors.configurations.destroy');

    Route::get('/master/colors/exterior', [ExteriorColorController::class, 'index'])->name('master.colors.exterior');
    Route::post('/master/colors/exterior', [ExteriorColorController::class, 'store'])->name('master.colors.exterior.store');
    Route::put('/master/colors/exterior/{id}', [ExteriorColorController::class, 'update'])->name('master.colors.exterior.update');
    Route::delete('/master/colors/exterior/{id}', [ExteriorColorController::class, 'destroy'])->name('master.colors.exterior.destroy');

    Route::get('/master/colors/interior', [InteriorColorController::class, 'index'])->name('master.colors.interior');
    Route::post('/master/colors/interior', [InteriorColorController::class, 'store'])->name('master.colors.interior.store');
    Route::put('/master/colors/interior/{id}', [InteriorColorController::class, 'update'])->name('master.colors.interior.update');
    Route::delete('/master/colors/interior/{id}', [InteriorColorController::class, 'destroy'])->name('master.colors.interior.destroy');

    Route::get('/master/colors/laminate', [LaminateColorController::class, 'index'])->name('master.colors.laminate');
    Route::post('/master/colors/laminate', [LaminateColorController::class, 'store'])->name('master.colors.laminate.store');
    Route::put('/master/colors/laminate/{id}', [LaminateColorController::class, 'update'])->name('master.colors.laminate.update');
    Route::delete('/master/colors/laminate/{id}', [LaminateColorController::class, 'destroy'])->name('master.colors.laminate.destroy');

    // ── Glass ────────────────────────────────────────────────
    Route::get('/master/glass/options', [GlassOptionController::class, 'index'])->name('master.glass.options');
    Route::put('/master/glass/options', [GlassOptionController::class, 'update'])->name('master.glass.options.update');

    Route::get('/master/glass/panes', [PaneController::class, 'index'])->name('master.glass.panes');
    Route::put('/master/glass/panes', [PaneController::class, 'update'])->name('master.glass.panes.update');
    Route::delete('/master/glass/panes/{paneType}', [PaneController::class, 'destroy'])->name('master.glass.panes.destroy');

    Route::get('/master/glass/tempered', [TemperedController::class, 'index'])->name('master.glass.tempered');
    Route::put('/master/glass/tempered', [TemperedController::class, 'update'])->name('master.glass.tempered.update');

    Route::get('/master/glass/thickness', [ThicknessController::class, 'index'])->name('master.glass.thickness');
    Route::post('/master/glass/thickness', [ThicknessController::class, 'store'])->name('master.glass.thickness.store');
    Route::put('/master/glass/thickness/{id}', [ThicknessController::class, 'update'])->name('master.glass.thickness.update');
    Route::delete('/master/glass/thickness/{id}', [ThicknessController::class, 'destroy'])->name('master.glass.thickness.destroy');
    Route::post('/master/glass/thickness/assignments', [ThicknessController::class, 'saveAssignments'])->name('master.glass.thickness.assignments');
    Route::post('/master/glass/thickness/size-rules', [ThicknessController::class, 'saveSizeRule'])->name('master.glass.thickness.size-rules.save');
    Route::delete('/master/glass/thickness/size-rules/{id}', [ThicknessController::class, 'destroySizeRule'])->name('master.glass.thickness.size-rules.destroy');

    // ── Profiles & Deductions ────────────────────────────────
    Route::get('/master/profiles', [ProfileManagerController::class, 'index'])->name('master.profiles.index');
    Route::get('/master/profiles/list', [ProfileManagerController::class, 'getProfiles'])->name('master.profiles.list');
    Route::get('/master/profiles/manipulations/{profileSetId}', [ProfileManagerController::class, 'getManipulations'])->name('master.profiles.manipulations');
    Route::post('/master/profiles/manipulations', [ProfileManagerController::class, 'saveManipulation'])->name('master.profiles.manipulations.save');
    Route::delete('/master/profiles/manipulations/{id}', [ProfileManagerController::class, 'deleteManipulation'])->name('master.profiles.manipulations.delete');
    Route::get('/master/profiles/{id}', [ProfileManagerController::class, 'getProfile'])->name('master.profiles.show');
    Route::put('/master/profiles/{id}', [ProfileManagerController::class, 'update'])->name('master.profiles.update');

    Route::get('/master/deductions', [DeductionManagerController::class, 'index'])->name('master.deductions.index');
    Route::get('/master/deductions/configurations', [DeductionManagerController::class, 'getConfigurations'])->name('master.deductions.configurations');
    Route::get('/master/deductions/manipulations/{configId}', [DeductionManagerController::class, 'getManipulations'])->name('master.deductions.manipulations');
    Route::post('/master/deductions/manipulations', [DeductionManagerController::class, 'saveManipulation'])->name('master.deductions.manipulations.save');
    Route::delete('/master/deductions/manipulations/{id}', [DeductionManagerController::class, 'deleteManipulation'])->name('master.deductions.manipulations.delete');
    Route::post('/master/deductions/bulk-update', [DeductionManagerController::class, 'bulkUpdate'])->name('master.deductions.bulk-update');
    Route::post('/master/deductions/panel-layout', [DeductionManagerController::class, 'panelLayout'])->name('master.deductions.panel-layout');

    // ── Pricing ──────────────────────────────────────────────
    Route::get('/master/prices/matrix', [MatrixController::class, 'index'])->name('master.prices.matrix');
    Route::get('/master/prices/matrix/data', [MatrixController::class, 'getData'])->name('master.prices.matrix.data');
    Route::post('/master/prices/matrix', [MatrixController::class, 'store'])->name('master.prices.matrix.store');
    Route::put('/master/prices/matrix/{id}', [MatrixController::class, 'update'])->name('master.prices.matrix.update');
    Route::delete('/master/prices/matrix/{id}', [MatrixController::class, 'destroy'])->name('master.prices.matrix.destroy');

    Route::get('/master/prices/markup', [MarkupController::class, 'index'])->name('master.prices.markup');
    Route::post('/master/prices/markup', [MarkupController::class, 'store'])->name('master.prices.markup.store');
    Route::put('/master/prices/markup/{id}', [MarkupController::class, 'update'])->name('master.prices.markup.update');
    Route::delete('/master/prices/markup/{id}', [MarkupController::class, 'destroy'])->name('master.prices.markup.destroy');

    // ── Grids ────────────────────────────────────────────────
    Route::get('/master/grids/types', [GridController::class, 'types'])->name('master.grids.types');
    Route::post('/master/grids/types', [GridController::class, 'storeType'])->name('master.grids.types.store');
    Route::put('/master/grids/types/{id}', [GridController::class, 'updateType'])->name('master.grids.types.update');
    Route::delete('/master/grids/types/{id}', [GridController::class, 'destroyType'])->name('master.grids.types.destroy');

    Route::get('/master/grids/profiles', [GridController::class, 'profiles'])->name('master.grids.profiles');
    Route::post('/master/grids/profiles', [GridController::class, 'storeProfile'])->name('master.grids.profiles.store');
    Route::put('/master/grids/profiles/{id}', [GridController::class, 'updateProfile'])->name('master.grids.profiles.update');
    Route::delete('/master/grids/profiles/{id}', [GridController::class, 'destroyProfile'])->name('master.grids.profiles.destroy');

    Route::get('/master/grids/patterns', [GridController::class, 'patterns'])->name('master.grids.patterns');
    Route::post('/master/grids/patterns', [GridController::class, 'storePattern'])->name('master.grids.patterns.store');
    Route::put('/master/grids/patterns/{id}', [GridController::class, 'updatePattern'])->name('master.grids.patterns.update');
    Route::delete('/master/grids/patterns/{id}', [GridController::class, 'destroyPattern'])->name('master.grids.patterns.destroy');

    // ── Frames ───────────────────────────────────────────────
    Route::get('/master/frames', [FrameController::class, 'index'])->name('master.frames.index');
    Route::post('/master/frames', [FrameController::class, 'store'])->name('master.frames.store');
    Route::put('/master/frames/{id}', [FrameController::class, 'update'])->name('master.frames.update');
    Route::delete('/master/frames/{id}', [FrameController::class, 'destroy'])->name('master.frames.destroy');

    // ── VIP Master ──────────────────────────────────────────
    Route::get('/vip-master', [VipMasterController::class, 'index'])->name('vip-master.index');
    Route::get('/vip-master/{category}', [VipMasterController::class, 'list'])->name('vip-master.list');
    Route::post('/vip-master/{category}', [VipMasterController::class, 'store'])->name('vip-master.store');
    Route::put('/vip-master/{category}/{id}', [VipMasterController::class, 'update'])->name('vip-master.update');
    Route::post('/vip-master/{category}/{id}/toggle', [VipMasterController::class, 'toggle'])->name('vip-master.toggle');
    Route::delete('/vip-master/{category}/{id}', [VipMasterController::class, 'destroy'])->name('vip-master.destroy');
    Route::post('/vip-master/{category}/reorder', [VipMasterController::class, 'reorder'])->name('vip-master.reorder');
});

// ─── Installer portal ────────────────────────────────────────
Route::middleware(['auth:vip', 'installer'])->prefix('installer')->name('installer.')->group(function () {
    Route::get('/', [InstallerDashboardController::class, 'index'])->name('dashboard');

    // Quotes (full configurator — mirrors enterprise)
    Route::get('/quotes', [InstallerQuoteController::class, 'index'])->name('quotes.index');
    Route::get('/quotes/create', [InstallerQuoteController::class, 'create'])->name('quotes.create');
    Route::post('/quotes', [InstallerQuoteController::class, 'store'])->name('quotes.store');
    Route::post('/quotes/check-price', [InstallerQuoteController::class, 'checkPrice'])->name('quotes.checkPrice');
    Route::get('/quotes/series-types/{seriesId}', [InstallerQuoteController::class, 'getSeriesTypes'])->name('quotes.seriesTypes');
    Route::get('/quotes/window-preview', [InstallerQuoteController::class, 'windowPreview'])->name('quotes.windowPreview');
    Route::post('/quotes/schema/price', [InstallerQuoteController::class, 'getSchemaPrice'])->name('quotes.schemaPrice');
    Route::get('/quotes/panel-layout', [InstallerQuoteController::class, 'panelLayout'])->name('quotes.panel-layout');
    Route::get('/quotes/shapes', [InstallerQuoteController::class, 'shapes'])->name('quotes.shapes');
    Route::get('/quotes/series-map', [InstallerQuoteController::class, 'seriesMap'])->name('quotes.seriesMap');
    Route::get('/quotes/{id}', [InstallerQuoteController::class, 'show'])->name('quotes.show');
    Route::put('/quotes/{id}', [InstallerQuoteController::class, 'update'])->name('quotes.update');
    Route::get('/quotes/{id}/edit', [InstallerQuoteController::class, 'edit'])->name('quotes.edit');
    Route::post('/quotes/{id}/save-draft', [InstallerQuoteController::class, 'saveDraft'])->name('quotes.saveDraft');
    Route::post('/quotes/{id}/item', [InstallerQuoteController::class, 'storeItem'])->name('quotes.storeItem');
    Route::post('/quotes/{id}/items/{itemId}/qty', [InstallerQuoteController::class, 'updateItemQty'])->name('quotes.updateItemQty');
    Route::delete('/quotes/{id}/item/{itemId}', [InstallerQuoteController::class, 'deleteItem'])->name('quotes.deleteItem');
    Route::post('/quotes/{id}/apply-discounts', [InstallerQuoteController::class, 'applyDiscounts'])->name('quotes.applyDiscounts');
    Route::post('/quotes/{id}/send', [InstallerQuoteController::class, 'sendToCustomer'])->name('quotes.send');
    Route::delete('/quotes/{id}', [InstallerQuoteController::class, 'destroy'])->name('quotes.destroy');

    // Calendar
    Route::get('/calendar', [InstallerJobController::class, 'calendar'])->name('calendar');

    // Jobs
    Route::get('/jobs', [InstallerJobController::class, 'index'])->name('jobs.index');
    Route::post('/jobs', [InstallerJobController::class, 'store'])->name('jobs.store');
    Route::get('/jobs/{id}', [InstallerJobController::class, 'show'])->name('jobs.show');
    Route::put('/jobs/{id}', [InstallerJobController::class, 'update'])->name('jobs.update');
    Route::delete('/jobs/{id}', [InstallerJobController::class, 'destroy'])->name('jobs.destroy');
    Route::post('/jobs/{id}/status', [InstallerJobController::class, 'updateStatus'])->name('jobs.updateStatus');
    Route::post('/jobs/{id}/note', [InstallerJobController::class, 'addNote'])->name('jobs.addNote');
    Route::post('/jobs/{id}/item', [InstallerJobController::class, 'addItem'])->name('jobs.addItem');
    Route::delete('/jobs/{id}/item/{itemId}', [InstallerJobController::class, 'removeItem'])->name('jobs.removeItem');
    Route::post('/jobs/{id}/item/{itemId}/toggle', [InstallerJobController::class, 'toggleItem'])->name('jobs.toggleItem');
    Route::post('/jobs/{id}/clock-in', [InstallerJobController::class, 'clockIn'])->name('jobs.clockIn');
    Route::post('/jobs/{id}/clock-out', [InstallerJobController::class, 'clockOut'])->name('jobs.clockOut');
    Route::get('/jobs/{id}/time-logs', [InstallerJobController::class, 'timeLogs'])->name('jobs.timeLogs');
    Route::post('/jobs/{id}/upload-image', [InstallerJobController::class, 'uploadImage'])->name('jobs.uploadImage');
    Route::post('/jobs/{id}/send-reminder', [InstallerJobController::class, 'sendReminder'])->name('jobs.sendReminder');
    Route::post('/jobs/{id}/reschedule', [InstallerJobController::class, 'reschedule'])->name('jobs.reschedule');
    Route::post('/calendar-events/{id}/reschedule', [InstallerJobController::class, 'rescheduleEvent'])->name('events.reschedule');

    // Invoices
    Route::get('/invoices', [InstallerInvoiceController::class, 'index'])->name('invoices.index');
    Route::post('/invoices', [InstallerInvoiceController::class, 'store'])->name('invoices.store');
    Route::get('/invoices/{id}', [InstallerInvoiceController::class, 'show'])->name('invoices.show');
    Route::post('/invoices/{id}/item', [InstallerInvoiceController::class, 'addItem'])->name('invoices.addItem');
    Route::delete('/invoices/{id}/item/{itemId}', [InstallerInvoiceController::class, 'removeItem'])->name('invoices.removeItem');
    Route::post('/invoices/{id}/send', [InstallerInvoiceController::class, 'sendToCustomer'])->name('invoices.send');
    Route::post('/invoices/from-quote/{quoteId}', [InstallerInvoiceController::class, 'createFromQuote'])->name('invoices.fromQuote');

    // Customers
    Route::get('/customers', [InstallerCustomerController::class, 'index'])->name('customers.index');
    Route::post('/customers', [InstallerCustomerController::class, 'store'])->name('customers.store');
    Route::get('/customers/{id}', [InstallerCustomerController::class, 'show'])->name('customers.show');
    Route::put('/customers/{id}', [InstallerCustomerController::class, 'update'])->name('customers.update');
    Route::delete('/customers/{id}', [InstallerCustomerController::class, 'destroy'])->name('customers.destroy');

    // My Services (pricing)
    Route::get('/services', [InstallerServiceController::class, 'index'])->name('services');
    Route::post('/services', [InstallerServiceController::class, 'store'])->name('services.store');
    Route::put('/services/{id}', [InstallerServiceController::class, 'update'])->name('services.update');
    Route::delete('/services/{id}', [InstallerServiceController::class, 'destroy'])->name('services.destroy');

    // Availability & Bookings
    Route::get('/availability', [InstallerAvailabilityController::class, 'index'])->name('availability');
    Route::post('/availability/weekly', [InstallerAvailabilityController::class, 'saveWeekly'])->name('availability.weekly');
    Route::post('/availability/override', [InstallerAvailabilityController::class, 'addOverride'])->name('availability.override');
    Route::delete('/availability/override/{id}', [InstallerAvailabilityController::class, 'removeOverride'])->name('availability.override.delete');
    Route::get('/availability/slots', [InstallerAvailabilityController::class, 'slotsForDate'])->name('availability.slots');
    Route::get('/bookings', [InstallerAvailabilityController::class, 'bookings'])->name('bookings');
    Route::put('/bookings/{id}', [InstallerAvailabilityController::class, 'updateBooking'])->name('bookings.update');

    // Attendance (job-based — clock in/out happens via Start Route and Complete Job)
    Route::get('/attendance', [InstallerAttendanceController::class, 'index'])->name('attendance.index');

    // Messages (installer ↔ admin)
    Route::get('/messages', [InstallerMessageController::class, 'index'])->name('messages.index');
    Route::get('/messages/unread-count', [InstallerMessageController::class, 'unreadCount'])->name('messages.unreadCount');
    Route::post('/messages/start', [InstallerMessageController::class, 'startConversation'])->name('messages.start');
    Route::get('/messages/{id}', [InstallerMessageController::class, 'show'])->name('messages.show');
    Route::post('/messages/{id}/send', [InstallerMessageController::class, 'send'])->name('messages.send');
    Route::delete('/messages/{id}/message/{messageId}', [InstallerMessageController::class, 'deleteMessage'])->name('messages.deleteMessage');

    // Tech Measures (installer/tech data entry)
    Route::get('/tech-measures', [InstallerTechMeasureController::class, 'index'])->name('tech-measures.index');
    Route::get('/tech-measures/{id}', [InstallerTechMeasureController::class, 'show'])->name('tech-measures.show');
    Route::post('/tech-measures/{id}/start', [InstallerTechMeasureController::class, 'start'])->name('tech-measures.start');
    Route::post('/tech-measures/{id}/clock-in', [InstallerTechMeasureController::class, 'clockIn'])->name('tech-measures.clockIn');
    Route::post('/tech-measures/{id}/clock-out', [InstallerTechMeasureController::class, 'clockOut'])->name('tech-measures.clockOut');
    Route::post('/tech-measures/{id}/complete', [InstallerTechMeasureController::class, 'complete'])->name('tech-measures.complete');
    Route::post('/tech-measures/{id}/item', [InstallerTechMeasureController::class, 'addItem'])->name('tech-measures.addItem');
    Route::put('/tech-measures/{id}/item/{itemId}', [InstallerTechMeasureController::class, 'updateItem'])->name('tech-measures.updateItem');
    Route::delete('/tech-measures/{id}/item/{itemId}', [InstallerTechMeasureController::class, 'removeItem'])->name('tech-measures.removeItem');
    Route::post('/tech-measures/{id}/photo', [InstallerTechMeasureController::class, 'uploadPhoto'])->name('tech-measures.uploadPhoto');
    Route::delete('/tech-measures/{id}/photo/{photoId}', [InstallerTechMeasureController::class, 'deletePhoto'])->name('tech-measures.deletePhoto');
    Route::post('/tech-measures/{id}/notes', [InstallerTechMeasureController::class, 'updateNotes'])->name('tech-measures.updateNotes');
    Route::post('/tech-measures/{id}/grids', [InstallerTechMeasureController::class, 'updateGrids'])->name('tech-measures.updateGrids');
    Route::get('/tech-measures/{id}/pdf', [InstallerTechMeasureController::class, 'downloadPdf'])->name('tech-measures.downloadPdf');

    // Service Jobs (calendar-based service events)
    Route::get('/service-jobs', [InstallerServiceJobController::class, 'index'])->name('service-jobs.index');
    Route::get('/service-jobs/{id}', [InstallerServiceJobController::class, 'show'])->name('service-jobs.show');
    Route::post('/service-jobs/{id}/clock-in', [InstallerServiceJobController::class, 'clockIn'])->name('service-jobs.clockIn');
    Route::post('/service-jobs/{id}/clock-out', [InstallerServiceJobController::class, 'clockOut'])->name('service-jobs.clockOut');
    Route::post('/service-jobs/{id}/complete', [InstallerServiceJobController::class, 'complete'])->name('service-jobs.complete');

    // VIP Master options API (for dropdowns)
    Route::get('/vip-master-options/{category}', [VipMasterController::class, 'options'])->name('vip-master-options');

    // Profile
    Route::get('/profile', [InstallerProfileController::class, 'index'])->name('profile');
    Route::put('/profile', [InstallerProfileController::class, 'update'])->name('profile.update');
    Route::post('/profile/logo', [InstallerProfileController::class, 'uploadLogo'])->name('profile.uploadLogo');
    Route::get('/profile/logo', function () { return redirect()->route('installer.profile')->with('error', 'Logo upload failed — file may be too large. Max 2MB.'); });
});

// ─── Public booking (via link from admin) ─────────────────────
Route::get('/book/{order}', [CalendarController::class, 'showBooking'])->name('booking.show')->where('order', '[0-9]+');
Route::post('/book/{order}', [CalendarController::class, 'confirmBooking'])->name('booking.confirm')->where('order', '[0-9]+');

// ─── Public installer booking (shareable link, no login) ─────
Route::get('/book/installer/{slug}', [PublicBookingController::class, 'show'])->name('public.book.installer');
Route::get('/book/installer/{slug}/slots', [PublicBookingController::class, 'getSlots'])->name('public.book.slots');
Route::post('/book/installer/{slug}', [PublicBookingController::class, 'confirm'])->name('public.book.confirm');
Route::get('/book/installer/{slug}/success', [PublicBookingController::class, 'success'])->name('public.book.success');

// ─── Public website booking (goes to VIP admin) ──────────────
Route::get('/book-installation', [PublicBookingController::class, 'websiteBook'])->name('public.book.website');
Route::get('/book-installation/slots', [PublicBookingController::class, 'websiteSlots'])->name('public.book.website.slots');
Route::post('/book-installation', [PublicBookingController::class, 'websiteConfirm'])->name('public.book.website.confirm');
Route::get('/book-installation/success', [PublicBookingController::class, 'websiteSuccess'])->name('public.book.website.success');

// ─── Public consultation request ─────────────────────────────
Route::post('/consultation-request', [ConsultationController::class, 'publicRequest'])->name('consultation.request');
