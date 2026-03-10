<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DoctorResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'        => $this->resource->id,
            'name'      => [
                'en' => $this->resource->getTranslation('name', 'en'),
                'ar' => $this->resource->getTranslation('name', 'ar'),
                'fr' => $this->resource->getTranslation('name', 'fr'),
            ],
            'specialty' => [
                'en' => $this->resource->getTranslation('specialty', 'en'),
                'ar' => $this->resource->getTranslation('specialty', 'ar'),
                'fr' => $this->resource->getTranslation('specialty', 'fr'),
            ],
            'image'     => ($m = $this->resource->getFirstMedia('doctor_photo'))
                ? MediaResource::make($m)
                : null,
        ];
    }
}
