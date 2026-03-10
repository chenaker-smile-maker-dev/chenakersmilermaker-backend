<?php

use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\DoctorAvailabilityController;
use App\Http\Controllers\Api\V1\PatientAppointmentController;
use App\Http\Controllers\Api\V1\PatientNotificationController;
use App\Http\Controllers\Api\V1\ProfileController;
use App\Http\Controllers\Api\V1\BookingController;
use App\Http\Controllers\Api\V1\EventController;
use App\Http\Controllers\Api\V1\TrainingController;
use App\Http\Controllers\Api\V1\TestimonialController;
use App\Http\Controllers\Api\V1\ServiceController;
use App\Http\Controllers\Api\V1\UrgentBookingController;
use App\Http\Controllers\Api\V1\WebsiteController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')
    ->group(function () {
        Route::get('website', [WebsiteController::class, 'show']);

        Route::prefix('services')->group(function () {
            Route::get('service', [ServiceController::class, 'listServices']);
            Route::get('service/{service}', [ServiceController::class, 'showService']);
        });
        Route::prefix('events')->group(function () {
            Route::get('/', [EventController::class, 'listEvents']);
            Route::get('/{event}', [EventController::class, 'showEvent']);
        });

        Route::prefix('trainings')->group(function () {
            Route::get('/', [TrainingController::class, 'listTrainings']);
            Route::get('/{training}', [TrainingController::class, 'showTraining']);
            Route::post('/{training}/reviews', [TrainingController::class, 'submitReview'])->middleware(['auth:sanctum', 'access']);
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
                Route::post('/verify-email', [AuthController::class, 'verifyEmail']);
                Route::post('/resend-verification', [AuthController::class, 'resendVerification'])->middleware(['auth:sanctum', 'access']);
            });

            Route::prefix('profile')->group(function () {
                Route::get('/me', [ProfileController::class, 'show'])->middleware(['auth:sanctum', 'access']);
                Route::post('/update', [ProfileController::class, 'update'])->middleware(['auth:sanctum', 'access']);
                Route::post('/update-password', [ProfileController::class, 'updatePassword'])->middleware(['auth:sanctum', 'access']);
            });

            Route::prefix('appointments')
                ->middleware(['auth:sanctum', 'access'])
                ->group(function () {
                    Route::get('/', [PatientAppointmentController::class, 'index']);
                    Route::get('/{appointment}', [PatientAppointmentController::class, 'show']);
                    Route::post('/{appointment}/cancel', [PatientAppointmentController::class, 'requestCancellation']);
                    Route::post('/{appointment}/reschedule', [PatientAppointmentController::class, 'requestReschedule']);
                });

            Route::prefix('notifications')
                ->middleware(['auth:sanctum', 'access'])
                ->group(function () {
                    Route::get('/', [PatientNotificationController::class, 'index']);
                    Route::get('/unread-count', [PatientNotificationController::class, 'unreadCount']);
                    Route::post('/read-all', [PatientNotificationController::class, 'markAllAsRead']);
                    Route::post('/{notification}/read', [PatientNotificationController::class, 'markAsRead']);
                    Route::delete('/{notification}', [PatientNotificationController::class, 'destroy']);
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

        Route::prefix('urgent-booking')->group(function () {
            Route::post('/submit', [UrgentBookingController::class, 'submit']);
            Route::middleware(['auth:sanctum', 'access'])->group(function () {
                Route::get('/my-bookings', [UrgentBookingController::class, 'myBookings']);
                Route::get('/{urgentBooking}', [UrgentBookingController::class, 'show']);
            });
        });
    });
