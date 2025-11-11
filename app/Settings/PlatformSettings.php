<?php

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

class PlatformSettings extends Settings
{

    public string $start_time;
    public string $end_time;

    public static function group(): string
    {
        return 'default';
    }
}
