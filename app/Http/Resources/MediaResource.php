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
            'thumb'    => $this->resource->getUrl('thumb') ?: null,
        ];
    }
}
