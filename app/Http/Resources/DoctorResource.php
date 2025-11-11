<?php

namespace App\Http\Resources;

use App\Utils\GetModelMultilangAttribute;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DoctorResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => GetModelMultilangAttribute::get($this, 'name'),
            'specialty' => GetModelMultilangAttribute::get($this, 'specialty'),
            // 'email' => $this->email ?? null,
            // 'phone' => $this->phone ?? null,
            'thumbnail_url' => $this->thumb_image,
        ];
    }
}
