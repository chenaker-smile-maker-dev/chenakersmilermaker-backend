<?php

use App\Models\Patient;

it('authenticated patient can see profile', function () {
    $patient = Patient::factory()->create();

    $this->actAsPatient($patient)
        ->getJson('/api/v1/patient/profile/me')
        ->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonPath('data.email', $patient->email)
        ->assertJsonPath('data.first_name', $patient->first_name);
});

it('unauthenticated patient cannot see profile', function () {
    $this->getJson('/api/v1/patient/profile/me')
        ->assertStatus(401);
});

it('patient can update profile', function () {
    $patient = Patient::factory()->create();

    $this->actAsPatient($patient)
        ->postJson('/api/v1/patient/profile/update', [
            'first_name' => 'UpdatedName',
            'last_name'  => 'UpdatedLastName',
        ])
        ->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonPath('data.first_name', 'UpdatedName');

    $this->assertDatabaseHas('patients', [
        'id'         => $patient->id,
        'first_name' => 'UpdatedName',
    ]);
});

it('patient can change password', function () {
    $patient = Patient::factory()->create(['password' => 'old_password']);

    $this->actAsPatient($patient)
        ->postJson('/api/v1/patient/profile/update-password', [
            'old_password'              => 'old_password',
            'new_password'              => 'new_secure_pass',
            'new_password_confirmation' => 'new_secure_pass',
        ])
        ->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonStructure(['data' => ['token']]);
});

it('change password fails with wrong old password', function () {
    $patient = Patient::factory()->create(['password' => 'correct_password']);

    $this->actAsPatient($patient)
        ->postJson('/api/v1/patient/profile/update-password', [
            'old_password'              => 'wrong_password',
            'new_password'              => 'new_secure_pass',
            'new_password_confirmation' => 'new_secure_pass',
        ])
        ->assertStatus(422)
        ->assertJsonValidationErrors(['old_password']);
});

it('profile update requires authentication', function () {
    $this->postJson('/api/v1/patient/profile/update', [
        'first_name' => 'Hacker',
    ])->assertStatus(401);
});

it('profile response message contains all 3 locales', function () {
    $patient = Patient::factory()->create();

    $response = $this->actAsPatient($patient)
        ->getJson('/api/v1/patient/profile/me');

    $response->assertOk();
    expect($response->json('message'))->toBeMultilang();
});
