<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UrgentBookingResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'                  => $this->id,
            'reason'              => $this->reason,
            'description'         => $this->description,
            'status'              => $this->status->value,
            'patient_name'        => $this->patient_name,
            'patient_phone'       => $this->patient_phone,
            'preferred_datetime'  => $this->preferred_datetime?->toIso8601String(),
            'scheduled_datetime'  => $this->scheduled_datetime?->toIso8601String(),
            'admin_notes'         => $this->admin_notes,
            'assigned_doctor'     => $this->assignedDoctor ? [
                'id'   => $this->assignedDoctor->id,
                'name' => $this->assignedDoctor->display_name,
            ] : null,
            'created_at'          => $this->created_at->toIso8601String(),
        ];
    }
}
