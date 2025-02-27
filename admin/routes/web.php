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
});

// REGISTERED CARDS
Route::middleware(['auth'])->group(function () {
    Route::get('/registered-cards', [RegisteredCardController::class, 'index'])->name('registered-cards.index');
    Route::post('/registered-cards', [RegisteredCardController::class, 'store'])->name('registered-cards.store');
    Route::get('/register-cards/checkStudentID', [RegisteredCardController::class, 'checkStudentID'])->name('registered-cards.checkStudentID');
});

// Devices
Route::middleware(['auth'])->group(function () {
    Route::get('/devices', [DeviceController::class, 'index'])->name('devices.index');
    Route::post('/devices', [DeviceController::class, 'store'])->name('devices.store');
    Route::put('/devices/{device}', [DeviceController::class, 'update'])->name('devices.update');
    Route::delete('/devices/{device}', [DeviceController::class, 'destroy'])->name('devices.destroy');
});

use Faker\Factory as Faker;

Route::middleware(['auth'])->group(
    function () {
        Route::post('/student-infos', [StudentInfoController::class, 'store'])->name('student-infos.store');
        Route::get('student-infos/check', [StudentInfoController::class, 'check'])->name('student-infos.check');
    }
);

Route::get('students/{studentId}', function ($studentId) {
    $faker = Faker::create();
    $dummyData = [
        'studentId'  => $studentId,
        'lname'      => $faker->lastName,
        'fname'      => $faker->firstName,
        'program'    => $faker->randomElement(['bsit', 'bscs']),
        'department' => 'ccs',
        'yearLevel'  => $faker->randomElement(['1', '2', '3', '4']),
    ];

    return response()->json([
        'student' => $dummyData
    ]);
})->name('students.fetch');


require __DIR__ . '/auth.php';
