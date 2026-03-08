<?php

namespace Tests\Support;

use Zap\Services\ValidationService as BaseValidationService;

class RelaxedValidationService extends BaseValidationService
{
    /**
     * Allow single-day schedules and past dates by ignoring certain validation errors.
     */
    protected function validateBasicAttributes(array $attributes): array
    {
        $errors = parent::validateBasicAttributes($attributes);

        unset($errors['end_date']);
        unset($errors['start_date']);

        return $errors;
    }
}
