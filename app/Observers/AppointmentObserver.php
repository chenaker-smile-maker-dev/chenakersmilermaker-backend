<?php

namespace App\Observers;

use App\Models\Appointment;
use Illuminate\Support\Facades\Log;
use Zap\Facades\Zap;

class AppointmentObserver
{
    /**
     * Handle the Appointment "created" event.
     */
    public function created(Appointment $appointment): void
    {
        // Log appointment creation
        Log::info('Appointment created', [
            'appointment_id' => $appointment->id,
            'doctor_id' => $appointment->doctor_id,
            'patient_id' => $appointment->patient_id,
            'status' => $appointment->status->value,
        ]);

        // Sync with Zap - block this time slot
        $this->syncWithZap($appointment, 'created');
    }

    /**
     * Handle the Appointment "updated" event.
     */
    public function updated(Appointment $appointment): void
    {
        // Log appointment update
        Log::info('Appointment updated', [
            'appointment_id' => $appointment->id,
            'status' => $appointment->status->value,
        ]);

        // Sync with Zap if time changed
        if ($appointment->isDirty(['from', 'to'])) {
            $this->syncWithZap($appointment, 'updated');
        }
    }

    /**
     * Handle the Appointment "deleted" event.
     */
    public function deleted(Appointment $appointment): void
    {
        // Log appointment deletion
        Log::info('Appointment deleted', [
            'appointment_id' => $appointment->id,
        ]);

        // Sync with Zap - remove the block
        $this->syncWithZap($appointment, 'deleted');
    }

    /**
     * Handle the Appointment "restored" event.
     */
    public function restored(Appointment $appointment): void
    {
        Log::info('Appointment restored', [
            'appointment_id' => $appointment->id,
        ]);
    }

    /**
     * Handle the Appointment "force deleted" event.
     */
    public function forceDeleted(Appointment $appointment): void
    {
        Log::info('Appointment force deleted', [
            'appointment_id' => $appointment->id,
        ]);
    }

    /**
     * Sync appointment with Zap scheduling system
     */
    private function syncWithZap(Appointment $appointment, string $action): void
    {
        try {
            $doctor = $appointment->doctor;

            if (!$doctor) {
                Log::warning('Doctor not found for appointment sync', [
                    'appointment_id' => $appointment->id,
                ]);
                return;
            }

            switch ($action) {
                case 'created':
                case 'updated':
                    // Create or update block time in Zap
                    $this->createZapBlock($doctor, $appointment);
                    break;

                case 'deleted':
                    // When deleted (soft delete), mark as cancelled instead of blocking
                    // This prevents blocking the slot permanently
                    Log::info('Appointment deleted - slot available again', [
                        'appointment_id' => $appointment->id,
                        'doctor_id' => $doctor->id,
                    ]);
                    break;
            }
        } catch (\Exception $e) {
            Log::error('Failed to sync appointment with Zap', [
                'appointment_id' => $appointment->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Create or update block time in Zap
     */
    private function createZapBlock(object $doctor, Appointment $appointment): void
    {
        try {
            // Check if we already created a Zap block for this appointment
            $metadata = is_array($appointment->metadata) ? $appointment->metadata : (array) $appointment->metadata;
            if (!empty($metadata['zap_block_created'])) {
                // Already synced, skip
                Log::debug('Zap block already created for appointment', [
                    'appointment_id' => $appointment->id,
                ]);
                return;
            }

            // Create block time for this appointment in Zap
            Zap::for($doctor)
                ->named('Appointment #' . $appointment->id)
                ->description('Patient appointment - ' . ($appointment->patient->user?->name ?? $appointment->patient->name ?? 'Patient'))
                ->blocked()
                ->from($appointment->from->format('Y-m-d'))
                ->to($appointment->to->format('Y-m-d'))
                ->addPeriod(
                    $appointment->from->format('H:i'),
                    $appointment->to->format('H:i')
                )
                ->daily()
                ->save();

            // Update appointment metadata to mark Zap block as created
            $metadata = is_array($appointment->metadata) ? $appointment->metadata : (array) $appointment->metadata;
            $appointment->metadata = array_merge($metadata, [
                'zap_block_created' => true,
                'zap_sync_date' => now()->toDateTimeString(),
            ]);
            $appointment->saveQuietly(); // Use saveQuietly to avoid triggering observer again

            Log::info('Zap block created for appointment', [
                'appointment_id' => $appointment->id,
                'doctor_id' => $doctor->id,
                'start' => $appointment->from->format('Y-m-d H:i'),
                'end' => $appointment->to->format('Y-m-d H:i'),
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to create Zap block for appointment', [
                'appointment_id' => $appointment->id,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
