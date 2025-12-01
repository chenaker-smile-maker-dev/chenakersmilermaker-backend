<?php

namespace Tests\Support;

use Zap\Services\ValidationService as BaseValidationService;

class RelaxedValidationService extends BaseValidationService
{
    /**
     * Allow single-day schedules by ignoring the end_date comparison error.
     */
    protected function validateBasicAttributes(array $attributes): array
    {
        $errors = parent::validateBasicAttributes($attributes);

        unset($errors['end_date']);

        return $errors;
    }
}
