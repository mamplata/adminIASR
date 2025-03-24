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

        try {
            $response = Http::get($externalApiUrl);

            // If the API call fails, return a no schedule response
            if ($response->failed()) {
                return response()->json([
                    'studentId' => $studentId,
                    'schedule'  => [],
                    'message'   => 'Fetching schedule unavailable'
                ]);
            }

            return response()->json($response->json());
        } catch (\Exception $e) {
            return response()->json([
                'studentId' => $studentId,
                'schedule'  => [],
                'message'   => 'Fetching schedule unavailable'
            ]);
        }
    })->name('schedule.fetch.proxy');

    //ANNOUNCEMENTS
    Route::get('/announcements', [AnnouncementController::class, 'getAnnouncements']);

    //ENTRY LOGS
    Route::get('/entry-logs', [EntryLogController::class, 'store']);
});


Route::post('/device/register', [DeviceController::class, 'register']);
