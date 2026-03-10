<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ServiceResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'       => $this->resource->id,
            'name'     => [
                'en' => $this->resource->getTranslation('name', 'en'),
                'ar' => $this->resource->getTranslation('name', 'ar'),
                'fr' => $this->resource->getTranslation('name', 'fr'),
            ],
            'price'    => $this->resource->price,
            'duration' => $this->resource->duration,
            'active'   => $this->resource->active,
            'image'    => ($m = $this->resource->getFirstMedia('image'))
                ? MediaResource::make($m)
                : null,
        ];
    }
}
