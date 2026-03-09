<?php

namespace Tests\Feature;

use App\Enums\Appointment\AppointmentStatus;
use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\Patient;
use App\Models\Service;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class PatientAppointmentApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Carbon::setTestNow(Carbon::create(2026, 6, 15, 10, 0));
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    private function makeAppointment(Patient $patient, array $attrs = []): Appointment
    {
        $doctor  = Doctor::factory()->create();
        $service = Service::factory()->create(['duration' => 60]);

        return Appointment::factory()->create(array_merge([
            'patient_id' => $patient->id,
            'doctor_id'  => $doctor->id,
            'service_id' => $service->id,
            'status'     => AppointmentStatus::PENDING,
            'from'       => now()->addDay(),
            'to'         => now()->addDay()->addHour(),
        ], $attrs));
    }

    // ─── List ─────────────────────────────────────────────────────────────────

    public function test_patient_can_list_their_appointments(): void
    {
        $patient = Patient::factory()->create();
        $this->makeAppointment($patient);
        $this->makeAppointment($patient);

        // Another patient's appointment should not appear
        $other = Patient::factory()->create();
        $this->makeAppointment($other);

        $response = $this->actAsPatient($patient)
            ->getJson('/api/v1/patient/appointments/');

        $response->assertOk()
            ->assertJsonPath('success', true);

        $this->assertEquals(2, $response->json('data.pagination.total'));
    }

    public function test_can_filter_appointments_by_status(): void
    {
        $patient = Patient::factory()->create();
        $this->makeAppointment($patient, ['status' => AppointmentStatus::PENDING]);
        $this->makeAppointment($patient, ['status' => AppointmentStatus::CONFIRMED]);
        $this->makeAppointment($patient, ['status' => AppointmentStatus::COMPLETED]);

        $response = $this->actAsPatient($patient)
            ->getJson('/api/v1/patient/appointments/?status=pending');

        $response->assertOk();
        $this->assertEquals(1, $response->json('data.pagination.total'));
    }

    public function test_list_appointments_requires_authentication(): void
    {
        $response = $this->getJson('/api/v1/patient/appointments/');

        $response->assertStatus(401);
    }

    // ─── Show ─────────────────────────────────────────────────────────────────

    public function test_patient_can_view_their_appointment(): void
    {
        $patient     = Patient::factory()->create();
        $appointment = $this->makeAppointment($patient);

        $response = $this->actAsPatient($patient)
            ->getJson("/api/v1/patient/appointments/{$appointment->id}");

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.id', $appointment->id);
    }

    public function test_patient_cannot_view_another_patients_appointment(): void
    {
        $patient = Patient::factory()->create();
        $other   = Patient::factory()->create();
        $appointment = $this->makeAppointment($other);

        $response = $this->actAsPatient($patient)
            ->getJson("/api/v1/patient/appointments/{$appointment->id}");

        // Should return 403 or 422 (appointment_not_yours exception)
        $this->assertContains($response->status(), [403, 422]);
        $this->assertFalse($response->json('success'));
    }

    // ─── Cancel ───────────────────────────────────────────────────────────────

    public function test_patient_can_request_appointment_cancellation(): void
    {
        Notification::fake();
        $patient     = Patient::factory()->create();
        $appointment = $this->makeAppointment($patient, ['status' => AppointmentStatus::PENDING]);

        $response = $this->actAsPatient($patient)
            ->postJson("/api/v1/patient/appointments/{$appointment->id}/cancel", [
                'reason' => 'I have a scheduling conflict with another appointment.',
            ]);

        $response->assertOk()
            ->assertJsonPath('success', true);

        $this->assertDatabaseHas('appointments', [
            'id'                    => $appointment->id,
            'change_request_status' => 'pending_cancellation',
        ]);
    }

    public function test_cancellation_requires_a_reason(): void
    {
        $patient     = Patient::factory()->create();
        $appointment = $this->makeAppointment($patient);

        $response = $this->actAsPatient($patient)
            ->postJson("/api/v1/patient/appointments/{$appointment->id}/cancel", []);

        $response->assertStatus(422);
    }

    public function test_cannot_cancel_a_completed_appointment(): void
    {
        $patient     = Patient::factory()->create();
        $appointment = $this->makeAppointment($patient, ['status' => AppointmentStatus::COMPLETED]);

        $response = $this->actAsPatient($patient)
            ->postJson("/api/v1/patient/appointments/{$appointment->id}/cancel", [
                'reason' => 'I no longer need this appointment to be completed.',
            ]);

        $response->assertStatus(422);
    }

    public function test_cannot_cancel_a_rejected_appointment(): void
    {
        $patient     = Patient::factory()->create();
        $appointment = $this->makeAppointment($patient, ['status' => AppointmentStatus::REJECTED]);

        $response = $this->actAsPatient($patient)
            ->postJson("/api/v1/patient/appointments/{$appointment->id}/cancel", [
                'reason' => 'I would like to cancel this rejected appointment.',
            ]);

        $response->assertStatus(422);
    }

    // ─── Reschedule ───────────────────────────────────────────────────────────

    public function test_patient_can_request_reschedule(): void
    {
        Notification::fake();
        $patient     = Patient::factory()->create();
        $appointment = $this->makeAppointment($patient, ['status' => AppointmentStatus::CONFIRMED]);

        $response = $this->actAsPatient($patient)
            ->postJson("/api/v1/patient/appointments/{$appointment->id}/reschedule", [
                'reason'         => 'I need to change to a more convenient time slot.',
                'new_date'       => now()->addDays(5)->format('d-m-Y'),
                'new_start_time' => '10:00',
            ]);

        $response->assertOk()
            ->assertJsonPath('success', true);

        $this->assertDatabaseHas('appointments', [
            'id'                    => $appointment->id,
            'change_request_status' => 'pending_reschedule',
        ]);
    }

    public function test_reschedule_validates_date_format(): void
    {
        $patient     = Patient::factory()->create();
        $appointment = $this->makeAppointment($patient);

        $response = $this->actAsPatient($patient)
            ->postJson("/api/v1/patient/appointments/{$appointment->id}/reschedule", [
                'reason'         => 'I need to change the time for this appointment.',
                'new_date'       => '2026-06-20',  // wrong format, should be d-m-Y
                'new_start_time' => '10:00',
            ]);

        $response->assertStatus(422);
    }

    public function test_cannot_reschedule_if_pending_change_exists(): void
    {
        Notification::fake();
        $patient     = Patient::factory()->create();
        $appointment = $this->makeAppointment($patient, [
            'status'                 => AppointmentStatus::PENDING,
            'change_request_status'  => 'pending_reschedule',
        ]);

        $response = $this->actAsPatient($patient)
            ->postJson("/api/v1/patient/appointments/{$appointment->id}/reschedule", [
                'reason'         => 'Trying to reschedule again while one is pending.',
                'new_date'       => now()->addDays(5)->format('d-m-Y'),
                'new_start_time' => '14:00',
            ]);

        $response->assertStatus(422);
    }
}
