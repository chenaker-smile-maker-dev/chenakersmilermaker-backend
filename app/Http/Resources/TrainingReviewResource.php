<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TrainingReviewResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'            => $this->id,
            'reviewer_name' => $this->reviewer_name,
            'content'       => $this->content,
            'rating'        => $this->rating,
            'created_at'    => $this->created_at->toIso8601String(),
        ];
    }
}
