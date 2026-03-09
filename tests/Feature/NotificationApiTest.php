<?php

namespace Tests\Feature;

use App\Models\Patient;
use App\Models\PatientNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NotificationApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_list_notifications(): void
    {
        $patient = Patient::factory()->create();
        PatientNotification::factory()->count(3)->create(['patient_id' => $patient->id]);

        $response = $this->actAsPatient($patient)
            ->getJson('/api/v1/patient/notifications/');

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonStructure([
                'data' => [
                    'data' => [['id', 'type', 'title', 'body', 'is_read', 'created_at']],
                    'pagination',
                ],
            ]);
    }

    public function test_can_filter_unread_only(): void
    {
        $patient = Patient::factory()->create();
        PatientNotification::factory()->read()->count(2)->create(['patient_id' => $patient->id]);
        PatientNotification::factory()->unread()->count(3)->create(['patient_id' => $patient->id]);

        $response = $this->actAsPatient($patient)
            ->getJson('/api/v1/patient/notifications/?unread_only=1');

        $response->assertOk();
        $this->assertEquals(3, $response->json('data.pagination.total'));
    }

    public function test_patient_only_sees_own_notifications(): void
    {
        $patient = Patient::factory()->create();
        $other   = Patient::factory()->create();

        PatientNotification::factory()->count(2)->create(['patient_id' => $patient->id]);
        PatientNotification::factory()->count(5)->create(['patient_id' => $other->id]);

        $response = $this->actAsPatient($patient)
            ->getJson('/api/v1/patient/notifications/');

        $response->assertOk();
        $this->assertEquals(2, $response->json('data.pagination.total'));
    }

    public function test_can_get_unread_count(): void
    {
        $patient = Patient::factory()->create();
        PatientNotification::factory()->unread()->count(4)->create(['patient_id' => $patient->id]);
        PatientNotification::factory()->read()->count(2)->create(['patient_id' => $patient->id]);

        $response = $this->actAsPatient($patient)
            ->getJson('/api/v1/patient/notifications/unread-count');

        $response->assertOk()
            ->assertJsonPath('data.unread_count', 4);
    }

    public function test_can_mark_notification_as_read(): void
    {
        $patient      = Patient::factory()->create();
        $notification = PatientNotification::factory()->unread()->create(['patient_id' => $patient->id]);

        $response = $this->actAsPatient($patient)
            ->postJson("/api/v1/patient/notifications/{$notification->id}/read");

        $response->assertOk()
            ->assertJsonPath('success', true);

        $this->assertNotNull($notification->fresh()->read_at);
    }

    public function test_cannot_mark_another_patients_notification_as_read(): void
    {
        $patient      = Patient::factory()->create();
        $other        = Patient::factory()->create();
        $notification = PatientNotification::factory()->unread()->create(['patient_id' => $other->id]);

        $response = $this->actAsPatient($patient)
            ->postJson("/api/v1/patient/notifications/{$notification->id}/read");

        $response->assertStatus(403);
    }

    public function test_can_mark_all_notifications_as_read(): void
    {
        $patient = Patient::factory()->create();
        PatientNotification::factory()->unread()->count(3)->create(['patient_id' => $patient->id]);

        $response = $this->actAsPatient($patient)
            ->postJson('/api/v1/patient/notifications/read-all');

        $response->assertOk()
            ->assertJsonPath('success', true);

        $this->assertEquals(
            0,
            PatientNotification::where('patient_id', $patient->id)->whereNull('read_at')->count()
        );
    }

    public function test_can_delete_notification(): void
    {
        $patient      = Patient::factory()->create();
        $notification = PatientNotification::factory()->create(['patient_id' => $patient->id]);

        $response = $this->actAsPatient($patient)
            ->deleteJson("/api/v1/patient/notifications/{$notification->id}");

        $response->assertOk()
            ->assertJsonPath('success', true);

        $this->assertDatabaseMissing('patient_notifications', ['id' => $notification->id]);
    }

    public function test_cannot_delete_another_patients_notification(): void
    {
        $patient      = Patient::factory()->create();
        $other        = Patient::factory()->create();
        $notification = PatientNotification::factory()->create(['patient_id' => $other->id]);

        $response = $this->actAsPatient($patient)
            ->deleteJson("/api/v1/patient/notifications/{$notification->id}");

        $response->assertStatus(403);
    }

    public function test_notifications_require_authentication(): void
    {
        $response = $this->getJson('/api/v1/patient/notifications/');

        $response->assertStatus(401);
    }
}
