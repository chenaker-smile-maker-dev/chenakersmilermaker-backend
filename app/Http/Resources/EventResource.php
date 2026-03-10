<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EventResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'             => $this->resource->id,
            'name'           => [
                'en' => $this->resource->getTranslation('title', 'en'),
                'ar' => $this->resource->getTranslation('title', 'ar'),
                'fr' => $this->resource->getTranslation('title', 'fr'),
            ],
            'description'    => [
                'en' => $this->resource->getTranslation('description', 'en'),
                'ar' => $this->resource->getTranslation('description', 'ar'),
                'fr' => $this->resource->getTranslation('description', 'fr'),
            ],
            'date'           => $this->resource->date?->toDateString(),
            'time'           => $this->resource->time,
            'location'       => [
                'en' => $this->resource->getTranslation('location', 'en'),
                'ar' => $this->resource->getTranslation('location', 'ar'),
                'fr' => $this->resource->getTranslation('location', 'fr'),
            ],
            'speakers'       => [
                'en' => $this->resource->getTranslation('speakers', 'en'),
                'ar' => $this->resource->getTranslation('speakers', 'ar'),
                'fr' => $this->resource->getTranslation('speakers', 'fr'),
            ],
            'about_event'    => [
                'en' => $this->resource->getTranslation('about_event', 'en'),
                'ar' => $this->resource->getTranslation('about_event', 'ar'),
                'fr' => $this->resource->getTranslation('about_event', 'fr'),
            ],
            'what_to_expect' => [
                'en' => $this->resource->getTranslation('what_to_expect', 'en'),
                'ar' => $this->resource->getTranslation('what_to_expect', 'ar'),
                'fr' => $this->resource->getTranslation('what_to_expect', 'fr'),
            ],
            'pictures'       => MediaResource::collection($this->resource->getMedia('gallery')),
            'status'         => $this->resource->status,
        ];
    }
}
