<?php

namespace App\Actions\Training;

use App\Models\Training;

class ShowTraining
{
    public function handle(Training $training): Training
    {
        $training->loadCount(['approvedReviews']);
        $training->load(['approvedReviews' => fn($q) => $q->latest()]);

        return $training;
    }
}
