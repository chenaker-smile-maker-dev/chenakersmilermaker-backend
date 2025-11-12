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
use Illuminate\Http\Request;

#[Group('Patient Auth Controller', weight: 1)]
class AuthController extends BaseController
{
    /**
     * Register a new patient
     */
    public function register(RegisterPatientRequest $request, RegisterPatient $registerPatient, GenerateTokensForPatient $generateTokensForPatient)
    {
        $data = $request->validated();

        $patient = $registerPatient->handle($data);
        [$accessToken, $refreshToken] = $generateTokensForPatient->handle($patient);

        return $this->sendResponse([
            'token' => $accessToken->plainTextToken,
            'refresh_token' => $refreshToken->plainTextToken,
            'patient' => new PatientResource($patient),
        ], 'Patient registered successfully.');
    }

    /**
     * Login a patient
     */
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
     * Logout a patient
     */
    public function logout(Request $request)
    {
        $patient = $request->user();
        $patient->tokens()->delete();

        return $this->sendResponse(message: 'Patient logged out successfully.');
    }

    /**
     * Refresh access token
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
