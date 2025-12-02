<?php

namespace App\Http\Controllers\Api\V1;

use App\Actions\Patient\Auth\GenerateTokensForPatient;
use App\Actions\Patient\Auth\LoginPatient;
use App\Actions\Patient\Auth\RegisterPatient;
use App\Enums\Api\TokenAbility;
use App\Models\Patient;
use App\Http\Controllers\Api\BaseController;
use App\Http\Requests\Api\V1\Patient\Auth\LoginPatientRequest;
use App\Http\Requests\Api\V1\Patient\Auth\RegisterPatientRequest;
use App\Http\Resources\PatientResource;
use Carbon\Carbon;
use Dedoc\Scramble\Attributes\Group;
use Dedoc\Scramble\Attributes\BodyParameter;
use Illuminate\Http\Request;

#[Group('(auth) Patient Auth', weight: 4)]
class AuthController extends BaseController
{
    /**
     * Register a new patient.
     *
     * Creates a new patient account and generates access and refresh tokens for authentication.
     * The patient can then use these tokens for subsequent API requests.
     */
    #[BodyParameter('first_name', description: 'Patient first name', type: 'string', example: 'John', required: true)]
    #[BodyParameter('last_name', description: 'Patient last name', type: 'string', example: 'Doe', required: true)]
    #[BodyParameter('email', description: 'Patient email address (must be unique)', type: 'string', format: 'email', example: 'john@example.com', required: true)]
    #[BodyParameter('phone', description: 'Patient phone number (must be unique)', type: 'string', example: '201234567890', required: true)]
    #[BodyParameter('age', description: 'Patient age in years', type: 'integer', example: 30, required: true)]
    #[BodyParameter('gender', description: 'Patient gender (male or female)', type: 'string', example: 'male', required: true)]
    #[BodyParameter('password', description: 'Patient password (minimum 6 characters)', type: 'string', format: 'password', example: 'secure_password', required: true)]
    #[BodyParameter('password_confirmation', description: 'Password confirmation (must match password)', type: 'string', format: 'password', example: 'secure_password', required: true)]
    #[BodyParameter('image', description: 'Optional profile image (jpeg, png, jpg, max 2MB)', type: 'string', format: 'binary', required: false, example: 'use form-data to upload a file')]
    public function register(RegisterPatientRequest $request, RegisterPatient $registerPatient, GenerateTokensForPatient $generateTokensForPatient)
    {
        $data = $request->validated();

        // Remove image from data if present, handle separately
        $patient = $registerPatient->handle($data);

        // Attach image if uploaded
        $image = $request->file('image');
        if ($image) {
            $patient->addMedia($image)->toMediaCollection('profile_photo');
        }

        [$accessToken, $refreshToken] = $generateTokensForPatient->handle($patient);

        return $this->sendResponse([
            'token' => $accessToken->plainTextToken,
            'refresh_token' => $refreshToken->plainTextToken,
            'patient' => new PatientResource($patient),
        ], 'Patient registered successfully.');
    }

    /**
     * Login a patient.
     *
     * Authenticates a patient with email and password credentials.
     * Returns access and refresh tokens for authenticated API requests.
     */
    #[BodyParameter('email', description: 'Patient email address', type: 'string', format: 'email', example: 'john@example.com', required: true)]
    #[BodyParameter('password', description: 'Patient password', type: 'string', format: 'password', example: 'secure_password', required: true)]
    public function login(LoginPatientRequest $request, LoginPatient $loginPatient, GenerateTokensForPatient $generateTokensForPatient)
    {
        $data = $request->validated();

        $patient = $loginPatient->handle($data);
        if (!$patient) return $this->sendError(error: 'Invalid credentials.', code: 401);

        [$accessToken, $refreshToken] = $generateTokensForPatient->handle($patient);

        return $this->sendResponse([
            'token' => $accessToken->plainTextToken,
            'refresh_token' => $refreshToken->plainTextToken,
            'patient' => new PatientResource($patient),
        ], 'Patient logged in successfully.');
    }

    /**
     * Logout a patient.
     *
     * Invalidates all authentication tokens for the current patient.
     * The patient will need to login again to access protected endpoints.
     */
    public function logout(Request $request)
    {
        $patient = $request->user();
        $patient->tokens()->delete();

        return $this->sendResponse(message: 'Patient logged out successfully.');
    }

    /**
     * Refresh access token.
     *
     * Generates a new access token using the refresh token.
     * Use this endpoint when the access token expires to obtain a new one without re-logging in.
     */
    public function refreshToken(Request $request)
    {
        $request->user()->tokens()->where('name', 'access_token')->delete();
        $accessToken = $request->user()->createToken('access_token', [TokenAbility::ACCESS_API->value], Carbon::now()->addMinutes(config('sanctum.access_token_expiration')));

        return $this->sendResponse([
            'token' => $accessToken->plainTextToken,
        ], 'Access token refreshed successfully.');
    }
}
