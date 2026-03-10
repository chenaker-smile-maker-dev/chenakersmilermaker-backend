<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TestimonialResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $patientMedia = ($this->resource->patient_id && $this->resource->patient)
            ? $this->resource->patient->getFirstMedia('profile_photo')
            : null;

        return [
            'id'           => $this->resource->id,
            'patient_id'   => $this->resource->patient_id,
            'name'         => $this->resource->patient_name,
            'content'      => $this->resource->content,
            'rating'       => $this->resource->rating,
            'is_published' => $this->resource->is_published,
            'image'        => $patientMedia ? MediaResource::make($patientMedia) : null,
            'created_at'   => $this->resource->created_at,
            'updated_at'   => $this->resource->updated_at,
        ];
    }
}
