<?php

namespace App\Enums\Appointment;

enum ChangeRequestStatus: string
{
    case PENDING_CANCELLATION = 'pending_cancellation';
    case PENDING_RESCHEDULE = 'pending_reschedule';
    case APPROVED = 'approved';
    case REJECTED = 'rejected';
}
