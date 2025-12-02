<?php

namespace App\Actions\Training;

use App\Models\Training;

class ShowTraining
{
    public function handle(Training $training)
    {
        return $training;
    }
}
