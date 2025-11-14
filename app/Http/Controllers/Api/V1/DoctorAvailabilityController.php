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
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\ModelNotFoundException;

#[Group('Booking Controller', weight: 3)]
class DoctorAvailabilityController extends BaseController
{
    /**
     * Get doctor's availability for a specific service
     */
    public function doctorAvailability(Request $request, GetDoctorAvailability $getDoctorAvailability)
    {
        try {
            $doctorId = $request->route('doctor');
            $serviceId = $request->route('service');

            $doctor = Doctor::find($doctorId);
            if (!$doctor) {
                return $this->sendError('Doctor not found', 404);
            }

            $service = Service::find($serviceId);
            if (!$service) {
                return $this->sendError('Service not found', 404);
            }

            $data = $getDoctorAvailability->handle($doctor, $service);
            return $this->sendResponse($data);
        } catch (\Exception $e) {
            return $this->sendError($e->getMessage(), 422);
        }
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
    public function showDoctor(Request $request, ShowDoctor $showDoctor)
    {
        try {
            $doctorId = $request->route('doctor');
            $doctor = Doctor::find($doctorId);

            if (!$doctor) {
                return $this->sendError('Doctor not found', 404);
            }

            $data = $showDoctor->handle($doctor);
            return $this->sendResponse($data);
        } catch (\Exception $e) {
            return $this->sendError($e->getMessage(), 422);
        }
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
    public function showService(Request $request, ShowService $showService)
    {
        try {
            $serviceId = $request->route('service');
            $service = Service::find($serviceId);

            if (!$service) {
                return $this->sendError('Service not found', 404);
            }

            $data = $showService->handle($service);
            return $this->sendResponse($data);
        } catch (\Exception $e) {
            return $this->sendError($e->getMessage(), 422);
        }
    }
}
