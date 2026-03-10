<?php

namespace App\Http\Controllers\Api\V1;

use App\Actions\Patient\Appointment\ListPatientAppointments;
use App\Actions\Patient\Appointment\RequestAppointmentCancellation;
use App\Actions\Patient\Appointment\RequestAppointmentReschedule;
use App\Actions\Patient\Appointment\ShowPatientAppointment;
use App\Http\Controllers\Api\BaseController;
use App\Http\Resources\AppointmentResource;
use App\Models\Appointment;
use Dedoc\Scramble\Attributes\BodyParameter;
use Dedoc\Scramble\Attributes\Group;
use Dedoc\Scramble\Attributes\QueryParameter;
use Illuminate\Http\Request;

#[Group('(patient) Appointments', weight: 5)]
class PatientAppointmentController extends BaseController
{
    /**
     * List patient's appointments.
     *
     * Returns a paginated list of the authenticated patient's appointments with optional filters.
     */
    #[QueryParameter('status', description: 'Filter by status (pending, confirmed, rejected, cancelled, completed)', type: 'string', required: false)]
    #[QueryParameter('from_date', description: 'Filter from date (Y-m-d)', type: 'string', required: false)]
    #[QueryParameter('to_date', description: 'Filter to date (Y-m-d)', type: 'string', required: false)]
    #[QueryParameter('page', description: 'Page number', type: 'int', default: 1)]
    #[QueryParameter('per_page', description: 'Items per page', type: 'int', default: 10)]
    public function index(Request $request, ListPatientAppointments $action)
    {
        $paginator = $action->handle(
            $request->user(),
            $request->only(['status', 'from_date', 'to_date']),
            $request->integer('page', 1),
            $request->integer('per_page', 10),
        );

        return $this->sendResponse([
            'data'       => AppointmentResource::collection($paginator),
            'pagination' => [
                'total'        => $paginator->total(),
                'per_page'     => $paginator->perPage(),
                'current_page' => $paginator->currentPage(),
                'last_page'    => $paginator->lastPage(),
                'from'         => $paginator->firstItem(),
                'to'           => $paginator->lastItem(),
            ],
        ]);
    }

    /**
     * Show appointment details.
     *
     * Returns detailed information about a specific appointment belonging to the authenticated patient.
     */
    public function show(Appointment $appointment, Request $request, ShowPatientAppointment $action)
    {
        try {
            return $this->sendResponse(AppointmentResource::make($action->handle($appointment, $request->user())));
        } catch (\Exception $e) {
            return $this->sendError($e->getMessage(), [], $e->getCode() ?: 422);
        }
    }

    /**
     * Request appointment cancellation.
     *
     * Submit a cancellation request for an appointment. Admin must approve.
     */
    #[BodyParameter('reason', description: 'Reason for cancellation', type: 'string', required: true, example: 'I am traveling and cannot make the appointment')]
    public function requestCancellation(Appointment $appointment, Request $request, RequestAppointmentCancellation $action)
    {
        $request->validate([
            'reason' => 'required|string|min:5|max:500',
        ]);

        try {
            $data = $action->handle($appointment, $request->user(), $request->reason);
            return $this->sendResponse($data, 'api.cancellation_submitted');
        } catch (\Exception $e) {
            return $this->sendError($e->getMessage(), [], $e->getCode() ?: 422);
        }
    }

    /**
     * Request appointment reschedule.
     *
     * Submit a reschedule request for an appointment. Admin must approve.
     */
    #[BodyParameter('reason', description: 'Reason for reschedule', type: 'string', required: true)]
    #[BodyParameter('new_date', description: 'Requested new date (d-m-Y format)', type: 'string', required: true, example: '20-03-2026')]
    #[BodyParameter('new_start_time', description: 'Requested new start time (H:i format)', type: 'string', required: true, example: '10:00')]
    public function requestReschedule(Appointment $appointment, Request $request, RequestAppointmentReschedule $action)
    {
        $request->validate([
            'reason' => 'required|string|min:5|max:500',
            'new_date' => 'required|date_format:d-m-Y|after_or_equal:today',
            'new_start_time' => 'required|date_format:H:i',
        ]);

        try {
            $data = $action->handle(
                $appointment,
                $request->user(),
                $request->reason,
                $request->new_date,
                $request->new_start_time
            );
            return $this->sendResponse($data, 'api.reschedule_submitted');
        } catch (\Exception $e) {
            return $this->sendError($e->getMessage(), [], $e->getCode() ?: 422);
        }
    }
}
