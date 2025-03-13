<?php

use App\Http\Controllers\AnnouncementController;
use App\Http\Controllers\RegisteredCardController;
use Inertia\Inertia;
use Illuminate\Support\Facades\Route;
use Illuminate\Foundation\Application;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\AuditLogController;
use App\Http\Controllers\DeviceController;
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

Route::middleware(['auth'])->group(function () {
    Route::get('/logs/audit-logs', [AuditLogController::class, 'index'])->name('logs.audit-logs.index');
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

Route::get('students/{studentId}', function ($studentId) {
    // Define the three students
    $students = [
        '1901234' => [
            'studentId'  => '1901234',
            'lName'      => 'Smith',
            'fName'      => 'John',
            'program'    => 'BSIT',
            'department' => 'CCS',
            'yearLevel'  => '1',
            'image'      => 'https://placehold.co/400',
            'last_enrolled_at' => "1st 2024",
        ],
        '1904568' => [
            'studentId'  => '1904568',
            'lName'      => 'Brown',
            'fName'      => 'Emily',
            'program'    => 'BSCS',
            'department' => 'CCS',
            'yearLevel'  => '3',
            'image'      => 'https://placehold.co/400',
            'last_enrolled_at' => "2nd 2025",
        ],
        '1901111' => [
            'studentId'  => '1901111',
            'lName'      => 'Taylor',
            'fName'      => 'Michael',
            'program'    => 'BSIT',
            'department' => 'CCS',
            'yearLevel'  => '2',
            'image'      => 'https://placehold.co/400',
            'last_enrolled_at' => "2nd 2025",
        ],
        '1901112' => [
            'studentId'  => '1901112',
            'lName'      => 'Taylor',
            'fName'      => 'Michael',
            'program'    => 'BSIT',
            'department' => 'CCS',
            'yearLevel'  => '2',
            'image'      => 'https://placehold.co/400',
            'last_enrolled_at' => "2nd 2025",
        ],
    ];

    // Check if the requested studentId exists
    if (array_key_exists($studentId, $students)) {
        return response()->json([
            'student' => $students[$studentId]
        ]);
    }

    // If studentId is not found, return an error response
    return response()->json([
        'error' => 'Student not found'
    ], 404);
})->name('students.fetch');


//EMAIL VERIFICATION
use App\Http\Controllers\EmailVerificationController;

Route::get('/email/verify-new/{user}/{hash}', [EmailVerificationController::class, 'verifyNewEmail'])
    ->name('verification.verify-new');

require __DIR__ . '/auth.php';
