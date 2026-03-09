<?php

namespace App\Services;

class PatientNotificationTemplates
{
    public static function appointmentBooked(string $doctorName, string $date, string $time): array
    {
        return [
            'title' => [
                'en' => 'Appointment Booked',
                'ar' => 'تم حجز الموعد',
                'fr' => 'Rendez-vous réservé',
            ],
            'body' => [
                'en' => "Your appointment with {$doctorName} on {$date} at {$time} has been booked and is awaiting confirmation.",
                'ar' => "تم حجز موعدك مع {$doctorName} بتاريخ {$date} الساعة {$time} وهو في انتظار التأكيد.",
                'fr' => "Votre rendez-vous avec {$doctorName} le {$date} à {$time} a été réservé et est en attente de confirmation.",
            ],
        ];
    }

    public static function appointmentConfirmed(string $doctorName, string $date, string $time): array
    {
        return [
            'title' => [
                'en' => 'Appointment Confirmed',
                'ar' => 'تم تأكيد الموعد',
                'fr' => 'Rendez-vous confirmé',
            ],
            'body' => [
                'en' => "Your appointment with {$doctorName} on {$date} at {$time} has been confirmed.",
                'ar' => "تم تأكيد موعدك مع {$doctorName} بتاريخ {$date} الساعة {$time}.",
                'fr' => "Votre rendez-vous avec {$doctorName} le {$date} à {$time} a été confirmé.",
            ],
        ];
    }

    public static function appointmentRejected(string $doctorName, string $date, string $time, string $reason = ''): array
    {
        return [
            'title' => [
                'en' => 'Appointment Rejected',
                'ar' => 'تم رفض الموعد',
                'fr' => 'Rendez-vous refusé',
            ],
            'body' => [
                'en' => "Your appointment with {$doctorName} on {$date} at {$time} has been rejected." . ($reason ? " Reason: {$reason}" : ''),
                'ar' => "تم رفض موعدك مع {$doctorName} بتاريخ {$date} الساعة {$time}." . ($reason ? " السبب: {$reason}" : ''),
                'fr' => "Votre rendez-vous avec {$doctorName} le {$date} à {$time} a été refusé." . ($reason ? " Raison: {$reason}" : ''),
            ],
        ];
    }

    public static function appointmentCancelled(string $doctorName, string $date): array
    {
        return [
            'title' => [
                'en' => 'Appointment Cancelled',
                'ar' => 'تم إلغاء الموعد',
                'fr' => 'Rendez-vous annulé',
            ],
            'body' => [
                'en' => "Your appointment with {$doctorName} on {$date} has been cancelled.",
                'ar' => "تم إلغاء موعدك مع {$doctorName} بتاريخ {$date}.",
                'fr' => "Votre rendez-vous avec {$doctorName} le {$date} a été annulé.",
            ],
        ];
    }

    public static function cancellationApproved(string $doctorName, string $date): array
    {
        return [
            'title' => [
                'en' => 'Cancellation Approved',
                'ar' => 'تمت الموافقة على الإلغاء',
                'fr' => 'Annulation approuvée',
            ],
            'body' => [
                'en' => "Your cancellation request for the appointment with {$doctorName} on {$date} has been approved.",
                'ar' => "تمت الموافقة على طلب إلغاء موعدك مع {$doctorName} بتاريخ {$date}.",
                'fr' => "Votre demande d'annulation pour le rendez-vous avec {$doctorName} le {$date} a été approuvée.",
            ],
        ];
    }

    public static function cancellationRejected(string $doctorName, string $date): array
    {
        return [
            'title' => [
                'en' => 'Cancellation Rejected',
                'ar' => 'تم رفض طلب الإلغاء',
                'fr' => 'Annulation refusée',
            ],
            'body' => [
                'en' => "Your cancellation request for the appointment with {$doctorName} on {$date} has been rejected. Your appointment remains scheduled.",
                'ar' => "تم رفض طلب إلغاء موعدك مع {$doctorName} بتاريخ {$date}. موعدك لا يزال محدداً.",
                'fr' => "Votre demande d'annulation pour le rendez-vous avec {$doctorName} le {$date} a été refusée. Votre rendez-vous reste planifié.",
            ],
        ];
    }

    public static function rescheduleApproved(string $doctorName, string $date, string $time): array
    {
        return [
            'title' => [
                'en' => 'Reschedule Approved',
                'ar' => 'تمت الموافقة على إعادة الجدولة',
                'fr' => 'Reprogrammation approuvée',
            ],
            'body' => [
                'en' => "Your reschedule request has been approved. Your appointment with {$doctorName} is now scheduled for {$date} at {$time}.",
                'ar' => "تمت الموافقة على طلب إعادة جدولة موعدك. موعدك مع {$doctorName} محدد الآن بتاريخ {$date} الساعة {$time}.",
                'fr' => "Votre demande de reprogrammation a été approuvée. Votre rendez-vous avec {$doctorName} est maintenant planifié le {$date} à {$time}.",
            ],
        ];
    }

    public static function rescheduleRejected(string $doctorName, string $date, string $time): array
    {
        return [
            'title' => [
                'en' => 'Reschedule Rejected',
                'ar' => 'تم رفض طلب إعادة الجدولة',
                'fr' => 'Reprogrammation refusée',
            ],
            'body' => [
                'en' => "Your reschedule request has been rejected. Your appointment with {$doctorName} remains on {$date} at {$time}.",
                'ar' => "تم رفض طلب إعادة جدولة موعدك. موعدك مع {$doctorName} لا يزال بتاريخ {$date} الساعة {$time}.",
                'fr' => "Votre demande de reprogrammation a été refusée. Votre rendez-vous avec {$doctorName} reste le {$date} à {$time}.",
            ],
        ];
    }

    public static function urgentBookingAccepted(string $scheduledDatetime, string $doctorName = '', string $notes = ''): array
    {
        return [
            'title' => [
                'en' => 'Urgent Booking Accepted',
                'ar' => 'تم قبول الحجز العاجل',
                'fr' => 'Réservation urgente acceptée',
            ],
            'body' => [
                'en' => "Your urgent booking has been accepted." . ($doctorName ? " Dr. {$doctorName}" : '') . " Scheduled for {$scheduledDatetime}." . ($notes ? " Notes: {$notes}" : ''),
                'ar' => "تم قبول حجزك العاجل." . ($doctorName ? " د. {$doctorName}" : '') . " محدد في {$scheduledDatetime}." . ($notes ? " ملاحظات: {$notes}" : ''),
                'fr' => "Votre réservation urgente a été acceptée." . ($doctorName ? " Dr. {$doctorName}" : '') . " Planifiée le {$scheduledDatetime}." . ($notes ? " Notes: {$notes}" : ''),
            ],
        ];
    }

    public static function urgentBookingRejected(string $reason = ''): array
    {
        return [
            'title' => [
                'en' => 'Urgent Booking Rejected',
                'ar' => 'تم رفض الحجز العاجل',
                'fr' => 'Réservation urgente refusée',
            ],
            'body' => [
                'en' => "Your urgent booking request has been rejected." . ($reason ? " Reason: {$reason}" : ''),
                'ar' => "تم رفض طلب حجزك العاجل." . ($reason ? " السبب: {$reason}" : ''),
                'fr' => "Votre demande de réservation urgente a été refusée." . ($reason ? " Raison: {$reason}" : ''),
            ],
        ];
    }

    public static function emailVerified(): array
    {
        return [
            'title' => [
                'en' => 'Email Verified',
                'ar' => 'تم التحقق من البريد الإلكتروني',
                'fr' => 'Email vérifié',
            ],
            'body' => [
                'en' => 'Your email address has been successfully verified. You can now book appointments.',
                'ar' => 'تم التحقق من بريدك الإلكتروني بنجاح. يمكنك الآن حجز المواعيد.',
                'fr' => 'Votre adresse email a été vérifiée avec succès. Vous pouvez maintenant réserver des rendez-vous.',
            ],
        ];
    }
}
