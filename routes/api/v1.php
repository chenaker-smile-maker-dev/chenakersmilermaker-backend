<?php

use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\DoctorAvailabilityController;
use App\Http\Controllers\Api\V1\ProfileController;
use App\Http\Controllers\Api\V1\BookingController;
use App\Http\Controllers\Api\V1\EventController;
use App\Http\Controllers\Api\V1\TrainingController;
use App\Http\Controllers\Api\V1\TestimonialController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')
    ->group(function () {
        Route::prefix('events')->group(function () {
            Route::get('/', [EventController::class, 'listEvents']);
            Route::get('/{event}', [EventController::class, 'showEvent']);
        });

        Route::prefix('trainings')->group(function () {
            Route::get('/', [TrainingController::class, 'listTrainings']);
            Route::get('/{training}', [TrainingController::class, 'showTraining']);
        });

        Route::prefix('testimonials')->group(function () {
            Route::get('/', [TestimonialController::class, 'listTestimonials']);
            Route::get('/{testimonial}', [TestimonialController::class, 'showTestimonial']);
        });

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
            Route::get('doctor', [DoctorAvailabilityController::class, 'listDoctors']);
            Route::get('service', [DoctorAvailabilityController::class, 'listServices']);
            Route::get('doctor/{doctor}', [DoctorAvailabilityController::class, 'showDoctor']);
            Route::get('service/{service}', [DoctorAvailabilityController::class, 'showService']);
            Route::get('/{doctor}/{service}/availability', [DoctorAvailabilityController::class, 'doctorAvailability']);
        });

        Route::prefix('booking')
            // ->middleware(['auth:sanctum', 'access'])
            ->group(function () {
                Route::post('/{doctor}/{service}/check-availability', [BookingController::class, 'checkAvailability']);
                Route::post('/{doctor}/{service}/book', [BookingController::class, 'bookAppointment']);
            });
    });
