<?php

namespace App\Actions\Training;

use App\Models\Patient;
use App\Models\Training;

class SubmitTrainingReview
{
    public function handle(Training $training, Patient $patient, array $data): void
    {
        $training->reviews()->create([
            'reviewer_name' => $patient->full_name,
            'content' => $data['content'],
            'rating' => $data['rating'],
            'is_approved' => false,
        ]);
    }
}
