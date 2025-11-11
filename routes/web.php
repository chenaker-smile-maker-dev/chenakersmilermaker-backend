<?php

use App\Models\Doctor;
use Illuminate\Support\Facades\Route;
use Spatie\Health\Http\Controllers\HealthCheckResultsController;
// use App\Http\Controllers\Web\HealthCheckResultsController;

Route::view('/', 'web.pages.welcome');
Route::get('/health', HealthCheckResultsController::class);

/**
 * Availability endpoint 1: Check if doctor is available at a specific time
 * GET /availability-1?doctor_id=5&date=2025-03-15&start_time=14:00&end_time=16:00
 */
Route::get('/availability-1', function () {
    $doctor = Doctor::find(request('doctor_id', 5));

    if (!$doctor) {
        return response()->json(['error' => 'Doctor not found'], 404);
    }

    $date = request('date', now()->format('Y-m-d'));
    $startTime = request('start_time', '14:00');
    $endTime = request('end_time', '16:00');

    $isAvailable = $doctor->isAvailableAt($date, $startTime, $endTime);

    return response()->json([
        'doctor_id' => $doctor->id,
        'doctor_name' => $doctor->name,
        'date' => $date,
        'start_time' => $startTime,
        'end_time' => $endTime,
        'is_available' => $isAvailable,
        'message' => $isAvailable ? 'Doctor is available' : 'Doctor is not available'
    ]);
});

/**
 * Availability endpoint 2: Get available time slots for a specific date
 * GET /availability-2?doctor_id=5&date=2025-03-15&day_start=09:00&day_end=17:00&slot_duration=60
 */
Route::get('/availability-2', function () {
    $doctor = Doctor::find(request('doctor_id', 5));

    if (!$doctor) {
        return response()->json(['error' => 'Doctor not found'], 404);
    }

    $date = request('date', now()->format('Y-m-d'));
    $dayStart = request('day_start', '09:00');
    $dayEnd = request('day_end', '17:00');
    $slotDuration = (int) request('slot_duration', 60);

    $availableSlots = $doctor->getAvailableSlots($date, $dayStart, $dayEnd, $slotDuration);

    return response()->json([
        'doctor_id' => $doctor->id,
        'doctor_name' => $doctor->name,
        'date' => $date,
        'working_hours' => "{$dayStart} - {$dayEnd}",
        'slot_duration_minutes' => $slotDuration,
        'available_slots_count' => count($availableSlots),
        'available_slots' => $availableSlots
    ]);
});

/**
 * Availability endpoint 3: Find next available slot
 * GET /availability-3?doctor_id=5&after_date=2025-03-15&duration=120&day_start=09:00&day_end=17:00
 */
Route::get('/availability-3', function () {
    $doctor = Doctor::find(request('doctor_id', 5));

    if (!$doctor) {
        return response()->json(['error' => 'Doctor not found'], 404);
    }

    $afterDate = request('after_date', now()->format('Y-m-d'));
    $duration = (int) request('duration', 60);
    $dayStart = request('day_start', '09:00');
    $dayEnd = request('day_end', '17:00');

    $nextSlot = $doctor->getNextAvailableSlot($afterDate, $duration, $dayStart, $dayEnd);

    return response()->json([
        'doctor_id' => $doctor->id,
        'doctor_name' => $doctor->name,
        'search_after' => $afterDate,
        'appointment_duration_minutes' => $duration,
        'working_hours' => "{$dayStart} - {$dayEnd}",
        'next_available_slot' => $nextSlot,
        'found' => $nextSlot !== null
    ]);
});

/**
 * Availability endpoint 4: Get schedules for a date range with detailed info
 * GET /availability-4?doctor_id=5&start_date=2025-03-15&end_date=2025-03-21
 */
Route::get('/availability-4', function () {
    $doctor = Doctor::find(request('doctor_id', 5));

    if (!$doctor) {
        return response()->json(['error' => 'Doctor not found'], 404);
    }

    $startDate = request('start_date', now()->format('Y-m-d'));
    $endDate = request('end_date', now()->addDays(7)->format('Y-m-d'));

    $schedules = $doctor->schedulesForDateRange($startDate, $endDate);

    $scheduleData = $schedules->map(function ($schedule) {
        return [
            'id' => $schedule->id,
            'name' => $schedule->name,
            'type' => $schedule->schedule_type->value,
            'start_date' => $schedule->start_date->format('Y-m-d'),
            'end_date' => $schedule->end_date?->format('Y-m-d'),
            'is_recurring' => $schedule->is_recurring,
            'periods' => $schedule->periods->map(function ($period) {
                return [
                    'start_time' => $period->start_time,
                    'end_time' => $period->end_time
                ];
            })->toArray(),
            'is_active' => $schedule->is_active
        ];
    });

    return response()->json([
        'doctor_id' => $doctor->id,
        'doctor_name' => $doctor->name,
        'date_range' => "{$startDate} to {$endDate}",
        'total_schedules' => $schedules->count(),
        'availability_schedules' => $schedules->where('schedule_type.value', 'availability')->count(),
        'blocked_schedules' => $schedules->where('schedule_type.value', 'blocked')->count(),
        'appointment_schedules' => $schedules->where('schedule_type.value', 'appointment')->count(),
        'schedules' => $scheduleData
    ]);
});
