<?php

use App\Http\Controllers\DeviceController;
use App\Http\Controllers\StudentInfoController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');


Route::middleware('device.check')->group(function () {
    Route::get('/device/status', [DeviceController::class, 'status']);
    Route::get('/students', [StudentInfoController::class, 'indexApi']);
});


Route::post('/device/register', [DeviceController::class, 'register']);
