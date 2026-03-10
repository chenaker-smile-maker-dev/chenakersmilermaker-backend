<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MediaResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'       => $this->resource->id,
            'original' => $this->resource->getUrl(),
            'thumb'    => $this->resource->hasGeneratedConversion('thumb')
                ? $this->resource->getUrl('thumb')
                : null,
            'medium'   => $this->resource->hasGeneratedConversion('medium')
                ? $this->resource->getUrl('medium')
                : null,
            'hero'     => $this->resource->hasGeneratedConversion('hero')
                ? $this->resource->getUrl('hero')
                : null,
        ];
    }
}
