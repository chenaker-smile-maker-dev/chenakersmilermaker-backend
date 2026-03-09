<?php

use App\Models\Patient;
use Database\Factories\PatientNotificationFactory;
use Illuminate\Notifications\DatabaseNotification;

// ─── Helpers ──────────────────────────────────────────────────────────────────

function makeNotificationsFor(Patient $patient, int $count = 1, string $state = 'unread'): void
{
    PatientNotificationFactory::new()
        ->forPatient($patient)
        ->{$state}()
        ->count($count)
        ->create();
}

// ─── List ─────────────────────────────────────────────────────────────────────

it('can list notifications', function () {
    $patient = Patient::factory()->create();
    makeNotificationsFor($patient, 3, 'unread');

    $this->actAsPatient($patient)
        ->getJson('/api/v1/patient/notifications/')
        ->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonStructure([
            'data' => [
                'data'       => [['id', 'type', 'title', 'body', 'is_read', 'created_at']],
                'pagination',
            ],
        ]);
});

it('notification title and body are multilingual objects', function () {
    $patient = Patient::factory()->create();
    makeNotificationsFor($patient, 1, 'unread');

    $response = $this->actAsPatient($patient)
        ->getJson('/api/v1/patient/notifications/');

    $response->assertOk();
    $first = $response->json('data.data.0');
    expect($first['title'])->toBeMultilang();
    expect($first['body'])->toBeMultilang();
});

it('can filter unread only', function () {
    $patient = Patient::factory()->create();
    makeNotificationsFor($patient, 2, 'read');
    makeNotificationsFor($patient, 3, 'unread');

    $response = $this->actAsPatient($patient)
        ->getJson('/api/v1/patient/notifications/?unread_only=1');

    $response->assertOk();
    expect($response->json('data.pagination.total'))->toBe(3);
});

it('patient only sees own notifications', function () {
    $patient = Patient::factory()->create();
    $other   = Patient::factory()->create();
    makeNotificationsFor($patient, 2, 'unread');
    makeNotificationsFor($other, 5, 'unread');

    $response = $this->actAsPatient($patient)
        ->getJson('/api/v1/patient/notifications/');

    $response->assertOk();
    expect($response->json('data.pagination.total'))->toBe(2);
});

// ─── Unread count ─────────────────────────────────────────────────────────────

it('can get unread count', function () {
    $patient = Patient::factory()->create();
    makeNotificationsFor($patient, 4, 'unread');
    makeNotificationsFor($patient, 2, 'read');

    $this->actAsPatient($patient)
        ->getJson('/api/v1/patient/notifications/unread-count')
        ->assertOk()
        ->assertJsonPath('data.unread_count', 4);
});

// ─── Mark as read ─────────────────────────────────────────────────────────────

it('can mark notification as read', function () {
    $patient = Patient::factory()->create();
    $notification = PatientNotificationFactory::new()
        ->forPatient($patient)
        ->unread()
        ->create();

    $this->actAsPatient($patient)
        ->postJson("/api/v1/patient/notifications/{$notification->id}/read")
        ->assertOk()
        ->assertJsonPath('success', true);

    expect($notification->fresh()->read_at)->not()->toBeNull();
});

it('cannot mark another patients notification as read', function () {
    $patient = Patient::factory()->create();
    $other   = Patient::factory()->create();
    $notification = PatientNotificationFactory::new()
        ->forPatient($other)
        ->unread()
        ->create();

    $this->actAsPatient($patient)
        ->postJson("/api/v1/patient/notifications/{$notification->id}/read")
        ->assertStatus(403);
});

// ─── Mark all read ────────────────────────────────────────────────────────────

it('can mark all notifications as read', function () {
    $patient = Patient::factory()->create();
    makeNotificationsFor($patient, 3, 'unread');

    $this->actAsPatient($patient)
        ->postJson('/api/v1/patient/notifications/read-all')
        ->assertOk()
        ->assertJsonPath('success', true);

    expect($patient->unreadNotifications()->count())->toBe(0);
});

// ─── Delete ───────────────────────────────────────────────────────────────────

it('can delete notification', function () {
    $patient = Patient::factory()->create();
    $notification = PatientNotificationFactory::new()
        ->forPatient($patient)
        ->create();

    $this->actAsPatient($patient)
        ->deleteJson("/api/v1/patient/notifications/{$notification->id}")
        ->assertOk()
        ->assertJsonPath('success', true);

    $this->assertDatabaseMissing('notifications', ['id' => $notification->id]);
});

it('cannot delete another patients notification', function () {
    $patient = Patient::factory()->create();
    $other   = Patient::factory()->create();
    $notification = PatientNotificationFactory::new()
        ->forPatient($other)
        ->create();

    $this->actAsPatient($patient)
        ->deleteJson("/api/v1/patient/notifications/{$notification->id}")
        ->assertStatus(403);
});

// ─── Auth required ────────────────────────────────────────────────────────────

it('notifications require authentication', function () {
    $this->getJson('/api/v1/patient/notifications/')
        ->assertStatus(401);
});
