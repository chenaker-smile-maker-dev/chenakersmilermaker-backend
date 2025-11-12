<?php

namespace App\Http\Controllers\Api\V1;

use App\Actions\Patient\Booking\ListDoctors;
use App\Actions\Patient\Booking\ListServices;
use App\Actions\Patient\Booking\ShowDoctor;
use App\Actions\Patient\Booking\ShowService;
use App\Http\Controllers\Api\BaseController;
use App\Models\Doctor;
use App\Models\Service;
use Dedoc\Scramble\Attributes\Group;
use Illuminate\Http\Request;
use Zap\Facades\Zap;

#[Group('Booking Controller', weight: 3)]
class DoctorAvailabilityController extends BaseController
{
    /**
     * Get doctor's availability for a specific service
     */
    public function doctorAvailability(Doctor $doctor, Service $service)
    {
        // Check if service is active
        if (!$service->active) {
            return $this->sendError('Service is not active', 422);
        }

        // Check if doctor provides this service
        if (!$doctor->services()->where('service_id', $service->id)->exists()) {
            return $this->sendError('Doctor does not provide this service', 422);
        }

        // Check if doctor has any active availability schedules
        $hasAvailability = $doctor->availabilitySchedules()
            ->active()
            ->exists();

        if (!$hasAvailability) {
            return $this->sendResponse([
                'id' => $doctor->id,
                'doctor_name' => $doctor->name,
                'service_id' => $service->id,
                'service_name' => $service->name,
                'service_duration_minutes' => $service->duration ?? 30,
                'is_service_active' => $service->active,
                'next_available_slot' => null,
                'message' => 'Doctor has no availability scheduled',
            ]);
        }

        // Get the service duration in minutes
        $serviceDuration = $service->duration ?? 30;

        // Find the next available slot using Zap's fluent API
        $nextAvailableSlot = $doctor->getNextAvailableSlot(
            afterDate: now()->format('Y-m-d'),
            duration: $serviceDuration,
            dayStart: '09:00',
            dayEnd: '17:01'
        );

        return $this->sendResponse([
            'id' => $doctor->id,
            'doctor_name' => $doctor->name,
            'service_id' => $service->id,
            'service_name' => $service->name,
            'service_duration_minutes' => $serviceDuration,
            'is_service_active' => $service->active,
            'next_available_slot' => $nextAvailableSlot,
        ]);
    }

    /**
     * List all doctors
     */
    public function listDoctors(Request $request, ListDoctors $listDoctors)
    {
        $page = $request->query('page', 1);
        $perPage = $request->query('per_page', default: 10);
        $data = $listDoctors->handle($page, $perPage);
        return $this->sendResponse($data);
    }

    /**
     * Get single doctor with their services
     */
    public function showDoctor(Doctor $doctor, ShowDoctor $showDoctor)
    {
        $data = $showDoctor->handle($doctor);
        return $this->sendResponse($data);
    }

    /**
     * List all services
     */
    public function listServices(Request $request, ListServices $listServices)
    {
        $page = $request->query('page', 1);
        $perPage = $request->query('per_page', 10);
        $data = $listServices->handle($page, $perPage);
        return $this->sendResponse($data);
    }

    /**
     * Get single service
     */
    public function showService(Service $service, ShowService $showService)
    {
        $data = $showService->handle($service);
        return $this->sendResponse($data);
    }
}
