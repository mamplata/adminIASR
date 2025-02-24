<?php

use App\Http\Controllers\AnnouncementController;
use App\Http\Controllers\RegisteredCardController;
use Inertia\Inertia;
use Illuminate\Support\Facades\Route;
use Illuminate\Foundation\Application;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\AuditLogController;
use App\Http\Controllers\DeviceController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::get('/components/buttons', function () {
    return Inertia::render('Components/Buttons');
})->middleware(['auth', 'verified'])->name('components.buttons');

Route::middleware(['auth'])->group(function () {
    Route::get('/logs/audit-logs', [AuditLogController::class, 'index'])->name('logs.audit-logs.index');
});

// ANNOUNCEMENTS
Route::middleware(['auth'])->group(function () {
    Route::get('/announcements', [AnnouncementController::class, 'index'])->name('announcements.index');
    Route::post('/announcements', [AnnouncementController::class, 'store'])->name('announcements.store');
    Route::put('/announcements/{announcements}', [AnnouncementController::class, 'update'])->name('announcements.update');
    Route::delete('/usannouncementsers/{announcements}', [AnnouncementController::class, 'destroy'])->name('announcements.destroy');
});

Route::middleware(['auth'])->group(function () {
    // REGISTERED CARDS
    Route::get('/registered-cards', [RegisteredCardController::class, 'index'])->name('registered_cards.index');
    Route::post('/registered-cards', [RegisteredCardController::class, 'store'])->name('registered_cards.store');
    // Devices
    Route::get('/devices', [DeviceController::class, 'index'])->name('devices.index');
    Route::post('/devices', [DeviceController::class, 'store'])->name('devices.store');
});

require __DIR__ . '/auth.php';
