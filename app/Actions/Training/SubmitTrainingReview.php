<?php

namespace App\Actions\Training;

use App\Models\Patient;
use App\Models\Review;
use App\Models\Training;

class SubmitTrainingReview
{
    public function handle(Training $training, Patient $patient, array $data): Review
    {
        return $training->reviews()->create([
            'patient_id' => $patient->id,
            'reviewer_name' => $patient->full_name,
            'content' => $data['content'],
            'rating' => $data['rating'],
            'is_approved' => false, // requires admin approval
        ]);
    }
}
