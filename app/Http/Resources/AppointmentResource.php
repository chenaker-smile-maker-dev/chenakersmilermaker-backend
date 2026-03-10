<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AppointmentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $doctor  = $this->resource->doctor;
        $service = $this->resource->service;

        return [
            'id'      => $this->resource->id,
            'doctor'  => $doctor ? [
                'id'        => $doctor->id,
                'name'      => [
                    'en' => $doctor->getTranslation('name', 'en'),
                    'ar' => $doctor->getTranslation('name', 'ar'),
                    'fr' => $doctor->getTranslation('name', 'fr'),
                ],
                'specialty' => [
                    'en' => $doctor->getTranslation('specialty', 'en'),
                    'ar' => $doctor->getTranslation('specialty', 'ar'),
                    'fr' => $doctor->getTranslation('specialty', 'fr'),
                ],
                'image'     => ($m = $doctor->getFirstMedia('doctor_photo'))
                    ? MediaResource::make($m)
                    : null,
                'phone'     => $doctor->phone,
            ] : null,
            'service' => $service ? [
                'id'       => $service->id,
                'name'     => [
                    'en' => $service->getTranslation('name', 'en'),
                    'ar' => $service->getTranslation('name', 'ar'),
                    'fr' => $service->getTranslation('name', 'fr'),
                ],
                'price'    => $service->price,
                'duration' => $service->duration,
                'image'    => ($m = $service->getFirstMedia('image'))
                    ? MediaResource::make($m)
                    : null,
            ] : null,
            'date'                  => $this->resource->from->format('Y-m-d'),
            'start_time'            => $this->resource->from->format('H:i'),
            'end_time'              => $this->resource->to->format('H:i'),
            'status'                => $this->resource->status->value,
            'change_request_status' => $this->resource->change_request_status,
            'price'                 => $this->resource->price,
            'admin_notes'           => $this->resource->admin_notes,
            'cancellation_reason'   => $this->resource->cancellation_reason,
            'reschedule_reason'     => $this->resource->reschedule_reason,
            'requested_new_date'    => $this->resource->requested_new_from?->format('Y-m-d'),
            'requested_new_time'    => $this->resource->requested_new_from?->format('H:i'),
            'created_at'            => $this->resource->created_at->toIso8601String(),
            'confirmed_at'          => $this->resource->confirmed_at?->toIso8601String(),
        ];
    }
}
