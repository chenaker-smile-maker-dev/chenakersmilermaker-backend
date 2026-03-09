<?php

namespace App\Actions\Patient\UrgentBooking;

use App\Enums\PatientNotificationType;
use App\Enums\UrgentBookingStatus;
use App\Models\Patient;
use App\Models\UrgentBooking;
use App\Models\User;
use App\Notifications\Admin\NewUrgentBookingReceived;
use App\Services\PatientNotificationService;
use App\Services\PatientNotificationTemplates;
use Illuminate\Support\Facades\Notification;

class SubmitUrgentBooking
{
    public function handle(array $data, ?Patient $patient = null): UrgentBooking
    {
        $booking = UrgentBooking::create([
            'patient_id' => $patient?->id,
            'patient_name' => $data['patient_name'],
            'patient_phone' => $data['patient_phone'],
            'patient_email' => $data['patient_email'] ?? null,
            'reason' => $data['reason'],
            'description' => $data['description'] ?? null,
            'preferred_datetime' => $data['preferred_datetime'] ?? null,
            'status' => UrgentBookingStatus::PENDING,
        ]);

        // Notify all admins with high-priority notification
        $admins = User::all();
        Notification::send($admins, new NewUrgentBookingReceived($booking));

        // If patient is authenticated, notify them too
        if ($patient) {
            PatientNotificationService::send(
                $patient,
                PatientNotificationType::GENERAL->value,
                [
                    'en' => 'Urgent Booking Received',
                    'ar' => 'تم استلام طلب الحجز العاجل',
                    'fr' => 'Demande de rendez-vous urgent reçue',
                ],
                [
                    'en' => 'Your urgent booking request has been received. Our team will contact you shortly.',
                    'ar' => 'تم استلام طلب الحجز العاجل الخاص بك. سيتصل بك فريقنا قريباً.',
                    'fr' => 'Votre demande de rendez-vous urgent a été reçue. Notre équipe vous contactera prochainement.',
                ],
                ['urgent_booking_id' => $booking->id],
            );
        }

        return $booking;
    }
}
