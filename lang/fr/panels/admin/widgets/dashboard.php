<?php

return [
    // StatsOverviewWidget
    'total_patients' => 'Total des patients',
    'total_patients_desc' => 'Patients enregistrés',
    'todays_appointments' => "Rendez-vous d'aujourd'hui",
    'this_month' => 'Ce mois-ci',
    'this_month_suffix' => 'rendez-vous',
    'pending_actions' => 'Actions en attente',
    'pending_actions_desc' => 'Rendez-vous + réservations urgentes',

    // PendingActionsWidget
    'pending_appointments' => 'Rendez-vous en attente',
    'pending_appointments_desc' => 'En attente de confirmation',
    'cancellation_requests' => "Demandes d'annulation",
    'cancellation_requests_desc' => 'En attente de décision',
    'reschedule_requests' => 'Demandes de reprogrammation',
    'reschedule_requests_desc' => 'En attente de décision',

    // UrgentBookingsWidget
    'pending_urgent_bookings' => 'Réservations urgentes en attente',
    'pending_urgent_bookings_desc' => 'Nécessitent une attention immédiate',

    // TodayAppointmentsWidget
    'today_appointments_heading' => "Rendez-vous d'aujourd'hui",
    'time' => 'Heure',
    'patient' => 'Patient',
    'doctor' => 'Médecin',
    'service' => 'Service',
    'status' => 'Statut',

    // BookingCalendarWidget
    'calendar_heading'            => 'Calendrier des réservations',
    'calendar_all_doctors'        => 'Tous les médecins',
    'calendar_all_statuses'       => 'Tous les statuts',
    'calendar_filter_doctor'      => 'Filtrer par médecin',
    'calendar_filter_status'      => 'Filtrer par statut',
    'calendar_view_month'         => 'Mois',
    'calendar_view_week'          => 'Semaine',
    'calendar_view_day'           => 'Jour',
    'calendar_view_list'          => 'Liste',
    'calendar_view_resource'      => 'Par médecin',
    'calendar_doctor_legend'      => 'Médecins',
    'calendar_status_legend'      => 'Statuts',
    'calendar_create_appointment' => 'Nouveau rendez-vous',
    'calendar_view_appointment'   => 'Voir le rendez-vous',
    'calendar_edit_appointment'   => 'Modifier le rendez-vous',
    'calendar_goto_appointment'   => 'Ouvrir dans la ressource',
    'calendar_status_pending'     => 'En attente',
    'calendar_status_confirmed'   => 'Confirmé',
    'calendar_status_rejected'    => 'Refusé',
    'calendar_status_cancelled'   => 'Annulé',
    'calendar_status_completed'   => 'Terminé',

    // Status transition actions
    'calendar_confirm'            => 'Confirmer',
    'calendar_reject'             => 'Refuser',
    'calendar_cancel'             => 'Annuler',
    'calendar_complete'           => 'Terminer',
    'calendar_confirmed_success'  => 'Rendez-vous confirmé avec succès',
    'calendar_rejected_success'   => 'Rendez-vous refusé avec succès',
    'calendar_cancelled_success'  => 'Rendez-vous annulé avec succès',
    'calendar_completed_success'  => 'Rendez-vous terminé avec succès',
    'calendar_rescheduled'        => 'Rendez-vous reprogrammé avec succès',
    'calendar_confirm_body'       => 'Êtes-vous sûr de vouloir confirmer ce rendez-vous ?',
    'calendar_reject_body'        => 'Êtes-vous sûr de vouloir refuser ce rendez-vous ?',
    'calendar_cancel_body'        => 'Êtes-vous sûr de vouloir annuler ce rendez-vous ?',
    'calendar_complete_body'      => 'Êtes-vous sûr de vouloir marquer ce rendez-vous comme terminé ?',
    'calendar_doctors_selected'   => ':count médecin(s) sélectionné(s)',
    'calendar_statuses_selected'  => ':count statut(s) actif(s)',
    'calendar_clear_filter'       => 'Effacer',

    // Calendar UI
    'calendar_today'              => "Aujourd'hui",
    'calendar_availability'       => 'Disponible',
    'calendar_blocked'            => 'Bloqué',
    'calendar_create_availability'  => 'Nouvelle disponibilité',
    'calendar_create_blocked'       => 'Nouveau créneau bloqué',
    'calendar_availability_created' => 'Règle de disponibilité créée',
    'calendar_blocked_created'      => 'Créneau bloqué créé',

    // NewPatientsWidget
    'new_patients_heading'        => 'Nouveaux patients',
    'phone'                       => 'Téléphone',
    'registered'                  => 'Inscrit',
];
