<?php

namespace Tests\Feature;

use App\Models\Patient;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProfileApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_patient_can_see_profile(): void
    {
        $patient = Patient::factory()->create();

        $response = $this->actAsPatient($patient)
            ->getJson('/api/v1/patient/profile/me');

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.email', $patient->email)
            ->assertJsonPath('data.first_name', $patient->first_name);
    }

    public function test_unauthenticated_patient_cannot_see_profile(): void
    {
        $response = $this->getJson('/api/v1/patient/profile/me');

        $response->assertStatus(401);
    }

    public function test_patient_can_update_profile(): void
    {
        $patient = Patient::factory()->create();

        $response = $this->actAsPatient($patient)
            ->postJson('/api/v1/patient/profile/update', [
                'first_name' => 'UpdatedName',
                'last_name'  => 'UpdatedLastName',
            ]);

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.first_name', 'UpdatedName');

        $this->assertDatabaseHas('patients', [
            'id'         => $patient->id,
            'first_name' => 'UpdatedName',
        ]);
    }

    public function test_patient_can_change_password(): void
    {
        $patient = Patient::factory()->create(['password' => 'old_password']);

        $response = $this->actAsPatient($patient)
            ->postJson('/api/v1/patient/profile/update-password', [
                'old_password'              => 'old_password',
                'new_password'              => 'new_secure_pass',
                'new_password_confirmation' => 'new_secure_pass',
            ]);

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonStructure(['data' => ['token']]);
    }

    public function test_change_password_fails_with_wrong_old_password(): void
    {
        $patient = Patient::factory()->create(['password' => 'correct_password']);

        $response = $this->actAsPatient($patient)
            ->postJson('/api/v1/patient/profile/update-password', [
                'old_password'              => 'wrong_password',
                'new_password'              => 'new_secure_pass',
                'new_password_confirmation' => 'new_secure_pass',
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['old_password']);
    }

    public function test_profile_update_requires_authentication(): void
    {
        $response = $this->postJson('/api/v1/patient/profile/update', [
            'first_name' => 'Hacker',
        ]);

        $response->assertStatus(401);
    }
}
