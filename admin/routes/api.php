<?php

use App\Http\Controllers\DeviceController;
use App\Http\Controllers\EntryLogController;
use App\Http\Controllers\RegisteredCardController;
use App\Http\Controllers\StudentInfoController;
use App\Http\Controllers\AnnouncementController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
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


    Route::get('fetch-schedule/{studentId}', function ($studentId) {
        // Call the existing schedule endpoint
        $externalApiUrl = "http://127.0.0.1:8001/api/schedule/{$studentId}";

        $response = Http::timeout(5)->retry(3, 100)->get($externalApiUrl);
        // Optionally, you can process or log the response here
        return response()->json($response->json());
    })->name('schedule.fetch.proxy');

    //ANNOUNCEMENTS
    Route::get('/announcements', [AnnouncementController::class, 'getAnnouncements']);
});


Route::post('/device/register', [DeviceController::class, 'register']);
