<?php

namespace App\Http\Resources;

use App\Utils\MediaHelper;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TestimonialResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'patient_id' => $this->patient_id,
            'name' => $this->name,
            'content' => $this->content,
            'rating' => $this->rating,
            'is_published' => $this->is_published,
            'image' => $this->patient_id && $this->patient
                ? MediaHelper::single($this->patient, 'profile_photo')
                : null,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
