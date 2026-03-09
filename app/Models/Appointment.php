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
        return CalendarEvent::make($this)
            ->title($this->service->name . ($this->doctor ? ' - ' . $this->doctor->display_name : ""))
            ->backgroundColor(color: '#34D399') // ✅ Tailwind green-400
            ->start($this->from)
            ->end($this->to);
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
