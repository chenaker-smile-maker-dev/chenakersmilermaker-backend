<?php

namespace App\Http\Controllers\Api\V1;

use App\Actions\Patient\Booking\GetDoctorAvailability;
use App\Actions\Patient\Booking\ListDoctors;
use App\Actions\Patient\Booking\ListServices;
use App\Actions\Patient\Booking\ShowDoctor;
use App\Actions\Patient\Booking\ShowService;
use App\Http\Controllers\Api\BaseController;
use App\Models\Doctor;
use App\Models\Service;
use Dedoc\Scramble\Attributes\Group;
use Dedoc\Scramble\Attributes\QueryParameter;
use Illuminate\Http\Request;

#[Group('(booking) Doctor Availability', weight: 6)]
class DoctorAvailabilityController extends BaseController
{
    /**
     * Get doctor's next available slot.
     *
     * Retrieves the next available appointment slot for a doctor offering a specific service.
     * Takes into account the doctor's availability schedule (days of week and hours) and existing appointments.
     * Returns the first available slot within the next 30 days.
     */
    public function doctorAvailability(Doctor $doctor, Service $service, GetDoctorAvailability $getDoctorAvailability)
    {
        try {
            $data = $getDoctorAvailability->handle($doctor, $service);
            return $this->sendResponse($data);
        } catch (\Exception $e) {
            return $this->sendError($e->getMessage(), 422);
        }
    }

    /**
     * List all doctors with pagination.
     *
     * Returns a paginated list of all active doctors in the system.
     * Each doctor includes basic information and count of services they provide.
     */
    #[QueryParameter('page', description: 'Page number for pagination.', type: 'int', default: 1, example: 1)]
    #[QueryParameter('per_page', description: 'Number of doctors per page.', type: 'int', default: 10, example: 20)]
    public function listDoctors(Request $request, ListDoctors $listDoctors)
    {
        $page = $request->query('page', 1);
        $perPage = $request->query('per_page', default: 10);
        $data = $listDoctors->handle($page, $perPage);
        return $this->sendResponse($data);
    }

    /**
     * Get doctor details with their services.
     *
     * Retrieves detailed information about a specific doctor including their profile
     * and the complete list of services they provide with pricing and duration.
     */
    public function showDoctor(Doctor $doctor, ShowDoctor $showDoctor)
    {
        try {
            $data = $showDoctor->handle($doctor);
            return $this->sendResponse($data);
        } catch (\Exception $e) {
            return $this->sendError($e->getMessage(), 422);
        }
    }

    /**
     * List all services with pagination.
     *
     * Returns a paginated list of all active medical services available in the system.
     * Each service includes pricing, duration, and availability status.
     */
    #[QueryParameter('page', description: 'Page number for pagination.', type: 'int', default: 1, example: 1)]
    #[QueryParameter('per_page', description: 'Number of services per page.', type: 'int', default: 10, example: 15)]
    public function listServices(Request $request, ListServices $listServices)
    {
        $page = $request->query('page', 1);
        $perPage = $request->query('per_page', 10);
        $data = $listServices->handle($page, $perPage);
        return $this->sendResponse($data);
    }

    /**
     * Get service details.
     *
     * Retrieves detailed information about a specific service including description,
     * pricing, duration, and list of doctors who provide this service.
     */
    public function showService(Service $service, ShowService $showService)
    {
        try {
            $data = $showService->handle($service);
            return $this->sendResponse($data);
        } catch (\Exception $e) {
            return $this->sendError($e->getMessage(), 422);
        }
    }
}
