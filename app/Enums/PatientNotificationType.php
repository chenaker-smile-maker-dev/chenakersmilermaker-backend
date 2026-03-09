<?php

namespace App\Enums;

enum PatientNotificationType: string
{
    case APPOINTMENT_BOOKED = 'appointment_booked';
    case APPOINTMENT_CONFIRMED = 'appointment_confirmed';
    case APPOINTMENT_REJECTED = 'appointment_rejected';
    case APPOINTMENT_CANCELLED = 'appointment_cancelled';
    case APPOINTMENT_RESCHEDULED = 'appointment_rescheduled';
    case CANCELLATION_APPROVED = 'cancellation_approved';
    case CANCELLATION_REJECTED = 'cancellation_rejected';
    case RESCHEDULE_APPROVED = 'reschedule_approved';
    case RESCHEDULE_REJECTED = 'reschedule_rejected';
    case URGENT_BOOKING_ACCEPTED = 'urgent_booking_accepted';
    case URGENT_BOOKING_REJECTED = 'urgent_booking_rejected';
    case EMAIL_VERIFIED = 'email_verified';
    case GENERAL = 'general';
}
