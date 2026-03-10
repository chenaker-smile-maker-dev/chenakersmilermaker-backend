<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TrainingResource extends JsonResource
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
            'price'          => $this->resource->price,
            'trainer_name'   => $this->resource->trainer_name,
            'duration'       => $this->resource->duration,
            'video_url'      => $this->resource->video_url,
            'main_image'     => ($m = $this->resource->getFirstMedia('image'))
                ? MediaResource::make($m)
                : null,
            'images'         => MediaResource::collection($this->resource->getMedia('images')),
            'average_rating' => $this->resource->average_rating,
            'reviews_count'  => $this->resource->approved_reviews_count ?? 0,
            'reviews'        => $this->when(
                $this->resource->relationLoaded('approvedReviews'),
                fn () => TrainingReviewResource::collection($this->approvedReviews),
            ),
        ];
    }
}
