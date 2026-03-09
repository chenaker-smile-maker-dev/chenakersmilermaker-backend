<?php

namespace App\Actions\Training;

use App\Models\Training;
use App\Utils\GetModelMultilangAttribute;
use App\Utils\MediaHelper;

class ListTrainings
{
    public function handle(int $page = 1, int $perPage = 10)
    {
        $trainings = Training::withCount(['approvedReviews'])
            ->paginate($perPage, ['*'], 'page', $page);

        return [
            'data' => $trainings->getCollection()->map(fn(Training $training) => [
                'id' => $training->id,
                'name' => GetModelMultilangAttribute::get($training, 'title'),
                'description' => GetModelMultilangAttribute::get($training, 'description'),
                'price' => $training->price,
                'trainer_name' => $training->trainer_name,
                'duration' => $training->duration,
                'main_image' => MediaHelper::single($training, 'image'),
                'images' => MediaHelper::collection($training, 'images'),
                'average_rating' => $training->average_rating,
                'reviews_count' => $training->approved_reviews_count,
            ])->values()->toArray(),
            'pagination' => [
                'total' => $trainings->total(),
                'per_page' => $trainings->perPage(),
                'current_page' => $trainings->currentPage(),
                'last_page' => $trainings->lastPage(),
                'from' => $trainings->firstItem(),
                'to' => $trainings->lastItem(),
            ],
        ];
    }
}
