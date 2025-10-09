<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PatientResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'full_name' => $this->full_name,
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'phone' => $this->phone,
            'email' => $this->email,
            'age' => $this->age,
            'gender' => $this->gender,
            'image' => $this->image,
            'thumb_image' => $this->thumb_image,
            // 'email_verified' => $this->email_verified_at ? true : false,
        ];
    }
}
