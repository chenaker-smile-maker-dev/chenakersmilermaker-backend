<?php

namespace App\Filament\Admin\Widgets;

use App\Models\Appointment;
use App\Models\Patient;
use App\Models\UrgentBooking;
use Filament\Widgets\Widget;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class RecentActivityWidget extends Widget
{
    protected static ?int $sort = 6;

    protected int|string|array $columnSpan = 1;

    protected string $view = 'filament.admin.widgets.recent-activity';

    public function getActivities(): Collection
    {
        $appointments = Appointment::with(['patient', 'doctor'])
            ->latest()
            ->take(5)
            ->get()
            ->map(fn(Appointment $a) => [
                'type' => 'appointment',
                'icon' => 'heroicon-o-calendar-days',
                'color' => 'primary',
                'title' => 'New Appointment',
                'description' => ($a->patient?->full_name ?? 'Unknown') . ' with ' . ($a->doctor?->display_name ?? 'Unknown'),
                'created_at' => $a->created_at,
            ]);

        $patients = Patient::latest()
            ->take(5)
            ->get()
            ->map(fn(Patient $p) => [
                'type' => 'patient',
                'icon' => 'heroicon-o-user-plus',
                'color' => 'success',
                'title' => 'New Patient',
                'description' => $p->full_name . ' registered',
                'created_at' => $p->created_at,
            ]);

        $urgentBookings = UrgentBooking::latest()
            ->take(5)
            ->get()
            ->map(fn(UrgentBooking $u) => [
                'type' => 'urgent',
                'icon' => 'heroicon-o-exclamation-triangle',
                'color' => 'danger',
                'title' => 'Urgent Booking',
                'description' => $u->patient_name . ': ' . Str::limit($u->reason, 50),
                'created_at' => $u->created_at,
            ]);

        return $appointments
            ->concat($patients)
            ->concat($urgentBookings)
            ->sortByDesc('created_at')
            ->take(10)
            ->values();
    }
}
