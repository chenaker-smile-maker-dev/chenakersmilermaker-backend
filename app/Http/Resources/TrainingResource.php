<?php

namespace App\Http\Resources;

use App\Utils\GetModelMultilangAttribute;
use App\Utils\MediaHelper;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TrainingResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'             => $this->id,
            'name'           => GetModelMultilangAttribute::get($this->resource, 'title'),
            'description'    => GetModelMultilangAttribute::get($this->resource, 'description'),
            'price'          => $this->price,
            'trainer_name'   => $this->trainer_name,
            'duration'       => $this->duration,
            'video_url'      => $this->video_url,
            'main_image'     => MediaHelper::single($this->resource, 'image'),
            'images'         => MediaHelper::collection($this->resource, 'images'),
            'average_rating' => $this->average_rating,
            'reviews_count'  => $this->approved_reviews_count ?? 0,
            'reviews'        => $this->when(
                $this->relationLoaded('approvedReviews'),
                fn() => TrainingReviewResource::collection($this->approvedReviews),
            ),
        ];
    }
}
