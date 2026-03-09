<?php

namespace App\Models;

use App\Enums\UrgentBookingStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class UrgentBooking extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'patient_id',
        'patient_name',
        'patient_phone',
        'patient_email',
        'reason',
        'description',
        'status',
        'admin_notes',
        'assigned_doctor_id',
        'preferred_datetime',
        'scheduled_datetime',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'preferred_datetime' => 'datetime',
            'scheduled_datetime' => 'datetime',
            'status' => UrgentBookingStatus::class,
            'metadata' => 'array',
        ];
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function assignedDoctor(): BelongsTo
    {
        return $this->belongsTo(Doctor::class, 'assigned_doctor_id');
    }
}
