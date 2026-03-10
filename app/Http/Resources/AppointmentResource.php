<?php

namespace App\Http\Resources;

use App\Utils\GetModelMultilangAttribute;
use App\Utils\MediaHelper;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AppointmentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $doctor  = $this->doctor;
        $service = $this->service;

        return [
            'id'      => $this->id,
            'doctor'  => $doctor ? [
                'id'        => $doctor->id,
                'name'      => GetModelMultilangAttribute::get($doctor, 'name'),
                'specialty' => GetModelMultilangAttribute::get($doctor, 'specialty'),
                'image'     => MediaHelper::single($doctor, 'doctor_photo'),
                'phone'     => $doctor->phone,
            ] : null,
            'service' => $service ? [
                'id'       => $service->id,
                'name'     => GetModelMultilangAttribute::get($service, 'name'),
                'price'    => $service->price,
                'duration' => $service->duration,
                'image'    => MediaHelper::single($service, 'image'),
            ] : null,
            'date'                  => $this->from->format('Y-m-d'),
            'start_time'            => $this->from->format('H:i'),
            'end_time'              => $this->to->format('H:i'),
            'status'                => $this->status->value,
            'change_request_status' => $this->change_request_status,
            'price'                 => $this->price,
            'admin_notes'           => $this->admin_notes,
            'cancellation_reason'   => $this->cancellation_reason,
            'reschedule_reason'     => $this->reschedule_reason,
            'requested_new_date'    => $this->requested_new_from?->format('Y-m-d'),
            'requested_new_time'    => $this->requested_new_from?->format('H:i'),
            'created_at'            => $this->created_at->toIso8601String(),
            'confirmed_at'          => $this->confirmed_at?->toIso8601String(),
        ];
    }
}
