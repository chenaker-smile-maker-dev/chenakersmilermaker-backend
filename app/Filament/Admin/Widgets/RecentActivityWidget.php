<?php

namespace App\Filament\Admin\Widgets;

use App\Models\Appointment;
use App\Models\Patient;
use App\Models\UrgentBooking;
use Filament\Widgets\Widget;
use Illuminate\Support\Collection;

class RecentActivityWidget extends Widget
{
    protected static ?int $sort = 6;
    protected static string $view = 'filament.admin.widgets.recent-activity';
    protected int|string|array $columnSpan = 1;

    public function getActivities(): Collection
    {
        $recentAppointments = Appointment::with(['patient', 'doctor'])
            ->latest()
            ->take(5)
            ->get()
            ->map(fn ($a) => [
                'icon' => 'heroicon-o-calendar',
                'color' => 'primary',
                'title' => 'New appointment booked',
                'description' => ($a->patient?->full_name ?? 'Unknown') . ' with ' . ($a->doctor?->display_name ?? 'Unknown'),
                'time' => $a->created_at,
            ]);

        $recentPatients = Patient::latest()
            ->take(3)
            ->get()
            ->map(fn ($p) => [
                'icon' => 'heroicon-o-user-plus',
                'color' => 'success',
                'title' => 'New patient registered',
                'description' => $p->full_name,
                'time' => $p->created_at,
            ]);

        $recentUrgent = UrgentBooking::latest()
            ->take(3)
            ->get()
            ->map(fn ($u) => [
                'icon' => 'heroicon-o-exclamation-triangle',
                'color' => 'danger',
                'title' => 'Urgent booking received',
                'description' => $u->patient_name,
                'time' => $u->created_at,
            ]);

        return collect()
            ->merge($recentAppointments)
            ->merge($recentPatients)
            ->merge($recentUrgent)
            ->sortByDesc('time')
            ->take(10)
            ->values();
    }
}
