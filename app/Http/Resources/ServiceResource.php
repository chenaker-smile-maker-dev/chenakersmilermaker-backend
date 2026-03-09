<?php

namespace App\Http\Resources;

use App\Utils\GetModelMultilangAttribute;
use App\Utils\MediaHelper;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ServiceResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => GetModelMultilangAttribute::get($this, 'name'),
            'price' => $this->price,
            'duration' => $this->duration,
            'active' => $this->active,
            'image' => MediaHelper::single($this->resource, 'image'),
        ];
    }
}
