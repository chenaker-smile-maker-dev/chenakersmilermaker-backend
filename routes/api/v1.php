<?php

use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\DoctorAvailabilityController;
use App\Http\Controllers\Api\V1\ProfileController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    Route::prefix('patient')->group(function () {
        Route::prefix('auth')->group(function () {
            Route::post('/register', [AuthController::class, 'register']);
            Route::post('/login', [AuthController::class, 'login']);
            Route::post('/logout', [AuthController::class, 'logout'])->middleware(['auth:sanctum', 'access']);
            Route::post('/refresh-token', [AuthController::class, 'refreshToken'])->middleware(['auth:sanctum', 'refresh']);
        });

        Route::prefix('profile')->group(function () {
            Route::get('/me', [ProfileController::class, 'show'])->middleware(['auth:sanctum', 'access']);
            Route::post('/update', [ProfileController::class, 'update'])->middleware(['auth:sanctum', 'access']);
            Route::post('/update-password', [ProfileController::class, 'updatePassword'])->middleware(['auth:sanctum', 'access']);
        });
    });

    Route::prefix('appointement')->group(function () {
        Route::prefix('doctor')->group(function () {
            Route::get('doctor', [DoctorAvailabilityController::class, 'listDoctors']);
            Route::get('service', [DoctorAvailabilityController::class, 'listServices']);
            Route::get('doctor/{doctor}', [DoctorAvailabilityController::class, 'showDoctor']);
            Route::get('service/{service}', [DoctorAvailabilityController::class, 'showService']);
            Route::get('/{doctor}/{service}/availability', [DoctorAvailabilityController::class, 'doctorAvailability']);
        });
    });
});
