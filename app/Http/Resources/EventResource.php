<?php

namespace App\Http\Resources;

use App\Utils\GetModelMultilangAttribute;
use App\Utils\MediaHelper;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EventResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'             => $this->id,
            'name'           => GetModelMultilangAttribute::get($this->resource, 'title'),
            'description'    => GetModelMultilangAttribute::get($this->resource, 'description'),
            'date'           => $this->date?->toDateString(),
            'time'           => $this->time,
            'location'       => GetModelMultilangAttribute::get($this->resource, 'location'),
            'speakers'       => GetModelMultilangAttribute::get($this->resource, 'speakers'),
            'about_event'    => GetModelMultilangAttribute::get($this->resource, 'about_event'),
            'what_to_expect' => GetModelMultilangAttribute::get($this->resource, 'what_to_expect'),
            'pictures'       => MediaHelper::collection($this->resource, 'gallery'),
            'status'         => $this->status,
        ];
    }
}
