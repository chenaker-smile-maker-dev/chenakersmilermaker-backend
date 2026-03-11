<?php

namespace App\Models;

use App\Enums\Appointment\AppointmentStatus;
use App\Enums\Appointment\ChangeRequestStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Guava\Calendar\Contracts\Eventable;
use Guava\Calendar\ValueObjects\CalendarEvent;
use Illuminate\Database\Eloquent\SoftDeletes;

class Appointment extends Model implements Eventable
{
    use HasFactory;
    use SoftDeletes;
    protected $fillable = [
        'from',
        'to',
        'doctor_id',
        'service_id',
        'patient_id',
        'price',
        'status',
        'metadata',
        'admin_notes',
        'cancellation_reason',
        'reschedule_reason',
        'original_from',
        'original_to',
        'change_request_status',
        'requested_new_from',
        'requested_new_to',
        'confirmed_by',
        'confirmed_at',
    ];

    protected function casts(): array
    {
        return [
            'from' => 'datetime',
            'to' => 'datetime',
            'original_from' => 'datetime',
            'original_to' => 'datetime',
            'requested_new_from' => 'datetime',
            'requested_new_to' => 'datetime',
            'confirmed_at' => 'datetime',
            'price' => 'integer',
            'status' => AppointmentStatus::class,
            'change_request_status' => \App\Enums\Appointment\ChangeRequestStatus::class,
            'metadata' => 'json',
        ];
    }

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(Doctor::class);
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class)->withDefault();
    }

    public function confirmedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'confirmed_by');
    }

    public function toCalendarEvent(): CalendarEvent
    {
        $statusColor = match ($this->status) {
            \App\Enums\Appointment\AppointmentStatus::PENDING   => '#F59E0B', // amber-400
            \App\Enums\Appointment\AppointmentStatus::CONFIRMED => '#3B82F6', // blue-500
            \App\Enums\Appointment\AppointmentStatus::REJECTED  => '#EF4444', // red-500
            \App\Enums\Appointment\AppointmentStatus::CANCELLED => '#6B7280', // gray-500
            \App\Enums\Appointment\AppointmentStatus::COMPLETED => '#10B981', // emerald-500
            default                                             => '#6B7280',
        };

        $doctorName = $this->doctor?->display_name ?? '';
        $patientName = $this->patient?->full_name ?? '';
        $serviceName = $this->service?->name ?? '';

        return CalendarEvent::make($this)
            ->title($serviceName . ($doctorName ? ' · ' . $doctorName : ''))
            ->backgroundColor($statusColor)
            ->textColor('#ffffff')
            ->start($this->from)
            ->end($this->to)
            ->extendedProps([
                'status'      => $this->status->value,
                'doctor_id'   => $this->doctor_id,
                'doctor_name' => $doctorName,
                'patient'     => $patientName,
                'service'     => $serviceName,
                'price'       => $this->price,
            ]);
    }

    public function scopeBetween($query, $start, $end)
    {
        return $query->whereDate('from', '>=', $start)
            ->whereDate('to', '<=', $end);
    }

    public function getDisplayNameAttribute(): string | null
    {
        return "appointement - " . $this->id;
    }
}
