<?php

use App\Http\Controllers\AnnouncementController;
use App\Http\Controllers\RegisteredCardController;
use Inertia\Inertia;
use Illuminate\Support\Facades\Route;
use Illuminate\Foundation\Application;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\AuditLogController;
use App\Http\Controllers\DeviceController;
use App\Http\Controllers\EntryLogController;
use App\Http\Controllers\StudentInfoController;
use App\Http\Controllers\SemesterController;

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

// LOGS
Route::middleware(['auth'])->group(function () {
    Route::get('/logs/audit-logs', [AuditLogController::class, 'index'])->name('logs.audit-logs.index');
    Route::get('/logs/entry-logs', [EntryLogController::class, 'index'])->name('logs.entry-logs.index');
    Route::get('/logs/unauthorized-logs', [AuditLogController::class, 'index'])->name('logs.unauthorized-logs.index');
});

// ANNOUNCEMENTS
Route::middleware(['auth'])->group(function () {
    Route::get('/announcements', [AnnouncementController::class, 'index'])->name('announcements.index');
    Route::post('/announcements', [AnnouncementController::class, 'store'])->name('announcements.store');
    Route::post('/announcements/{announcement}', [AnnouncementController::class, 'update'])->name('announcements.update');
    Route::delete('/announcements/{announcement}', [AnnouncementController::class, 'destroy'])->name('announcements.destroy');
    Route::get('/announcements', [AnnouncementController::class, 'index'])->name('announcements.index');
});

// REGISTERED CARDS
Route::middleware(['auth'])->group(function () {
    Route::get('/registered-cards', [RegisteredCardController::class, 'index'])->name('registered-cards.index');
    Route::post('/registered-cards', [RegisteredCardController::class, 'store'])->name('registered-cards.store');
    Route::get('/register-cards/checkStudentID', [RegisteredCardController::class, 'checkStudentID'])->name('registered-cards.checkStudentID');
    Route::get('/registered-cards/check-card', [RegisteredCardController::class, 'checkCard'])->name('registered-cards.checkCard');
    Route::delete('/registered-cards/{id}', [RegisteredCardController::class, 'destroy'])
        ->name('registered-cards.destroy');
});

// Devices
Route::middleware(['auth'])->group(function () {
    Route::get('/devices', [DeviceController::class, 'index'])->name('devices.index');
    Route::post('/devices', [DeviceController::class, 'store'])->name('devices.store');
    Route::delete('/devices/{device}', [DeviceController::class, 'destroy'])->name('devices.destroy');
});

//SEMESTER SETTINGS
Route::middleware(['auth'])->group(function () {
    Route::get('/settings', [SemesterController::class, 'index'])->name('settings.index');
    Route::post('/settings', [SemesterController::class, 'store'])->name('settings.index');
});

use Faker\Factory as Faker;

//STUDENT INFORMATION
Route::middleware(['auth'])->group(
    function () {
        Route::post('/student-infos', [StudentInfoController::class, 'store'])->name('student-infos.store');
        Route::get('student-infos/check', [StudentInfoController::class, 'check'])->name('student-infos.check');
        Route::get('/check-enrollment-status', [StudentInfoController::class, 'checkEnrollmentStatus'])->name('check-enrollment-status');
    }
);


//EMAIL VERIFICATION
use App\Http\Controllers\EmailVerificationController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

Route::get('/email/verify-new/{user}/{hash}', [EmailVerificationController::class, 'verifyNewEmail'])
    ->name('verification.verify-new');

Route::get('/students/{studentId}', function ($studentId) {
    $externalApiUrl = "http://127.0.0.1:8001/api/students/{$studentId}";

    try {
        $response = Http::get($externalApiUrl);

        if ($response->failed()) {
            return response()->json(['error' => 'Failed to fetch student data from external API.'], 500);
        }

        return response()->json($response->json());
    } catch (\Exception $e) {
        return response()->json(['error' => 'An error occurred while retrieving student data.'], 500);
    }
})->name('students.fetch');

require __DIR__ . '/auth.php';
