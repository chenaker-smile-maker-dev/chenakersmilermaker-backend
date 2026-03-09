<?php

namespace App\Actions\Training;

use App\Models\Training;
use App\Utils\GetModelMultilangAttribute;
use App\Utils\MediaHelper;

class ShowTraining
{
    public function handle(Training $training): array
    {
        $training->loadCount('approvedReviews');

        return [
            'id' => $training->id,
            'name' => GetModelMultilangAttribute::get($training, 'title'),
            'description' => GetModelMultilangAttribute::get($training, 'description'),
            'price' => $training->price,
            'trainer_name' => $training->trainer_name,
            'duration' => $training->duration,
            'video_url' => $training->video_url,
            'main_image' => MediaHelper::single($training, 'image'),
            'images' => MediaHelper::collection($training, 'images'),
            'average_rating' => $training->average_rating,
            'reviews_count' => $training->approved_reviews_count ?? 0,
            'reviews' => $training->approvedReviews()->with('patient')->get()->map(fn ($review) => [
                'id' => $review->id,
                'reviewer_name' => $review->reviewer_name ?? $review->patient?->full_name,
                'content' => $review->content,
                'rating' => $review->rating,
                'created_at' => $review->created_at->toDateTimeString(),
            ])->toArray(),
        ];
    }
}

