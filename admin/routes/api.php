<?php

use App\Http\Controllers\DeviceController;
use App\Http\Controllers\EntryLogController;
use App\Http\Controllers\RegisteredCardController;
use App\Http\Controllers\StudentInfoController;
use App\Http\Controllers\AnnouncementController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');


Route::middleware('device.check')->group(function () {
    Route::get('/device/status', [DeviceController::class, 'status']);
    Route::get('/students', [StudentInfoController::class, 'indexApi']);

    // NFC Card Scanning route
    Route::post('/card/scan', [RegisteredCardController::class, 'scanCard']);

    // ENTRY LOGS
    Route::post('/entry-logs', [EntryLogController::class, 'store']);

    //ANNOUNCEMENTS
    Route::get('/announcements', [AnnouncementController::class, 'getAnnouncements']);
});


Route::post('/device/register', [DeviceController::class, 'register']);
