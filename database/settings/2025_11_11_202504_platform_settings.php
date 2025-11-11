<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->add('default.start_time', '9:00');
        $this->migrator->add('default.end_time', '17:00');
    }
};
