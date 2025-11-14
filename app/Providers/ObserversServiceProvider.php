<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\Appointment;
use App\Observers\AppointmentObserver;

class ObserversServiceProvider extends ServiceProvider
{

    public function register(): void {}

    public function boot(): void
    {
        $this->BindObservers();
    }

    private function BindObservers(): void
    {
        Appointment::observe(AppointmentObserver::class);
    }
}
