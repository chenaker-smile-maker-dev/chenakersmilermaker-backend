<?php

namespace App\Http\Controllers\Api\V1;

use App\Actions\Patient\Profile\EditPatientProfile;
use App\Actions\Patient\Profile\UpdatePatientPassword;
use App\Http\Resources\PatientResource;
use App\Http\Controllers\Api\BaseController;
use App\Http\Requests\Api\V1\Patient\Profile\EditPatientProfileRequest;
use App\Http\Requests\Api\V1\Patient\Profile\UpdatePasswordRequest;
use Dedoc\Scramble\Attributes\Group;
use Dedoc\Scramble\Attributes\BodyParameter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

#[Group('Patient Profile', weight: 2)]
class ProfileController extends BaseController
{
    /**
     * Get authenticated patient profile.
     *
     * Retrieves the complete profile information of the currently authenticated patient.
     * Returns all patient details including personal information and contact details.
     */
    public function show(Request $request)
    {
        /** @var \App\Models\Patient $patient */
        $patient = $request->user();

        return $this->sendResponse(new PatientResource($patient), 'Patient retrieved successfully.');
    }

    /**
     * Update patient profile.
     *
     * Updates the personal information of the authenticated patient.
     * Allows modification of name, phone, email, and other profile details.
     */
    #[BodyParameter('first_name', description: 'Patient first name', type: 'string', example: 'John', required: false)]
    #[BodyParameter('last_name', description: 'Patient last name', type: 'string', example: 'Doe', required: false)]
    #[BodyParameter('phone', description: 'Patient phone number (must be unique)', type: 'string', example: '201234567890', required: false)]
    #[BodyParameter('image', description: 'Patient profile image (jpeg, png, jpg, max 2MB)', type: 'file', format: 'binary', required: false)]
    public function update(EditPatientProfileRequest $request, EditPatientProfile $action)
    {
        /** @var \App\Models\Patient $patient */
        $patient = Auth::user();

        $updatedPatient = $action->handle($patient, $request->validated());

        return $this->sendResponse(new PatientResource($updatedPatient), 'Profile updated successfully.');
    }

    /**
     * Update patient password.
     *
     * Changes the patient's password and generates a new access token for security.
     * The old password must be provided for verification before the change is applied.
     */
    #[BodyParameter('old_password', description: 'Current patient password for verification', type: 'string', format: 'password', example: 'current_password', required: true)]
    #[BodyParameter('new_password', description: 'New password (minimum 6 characters)', type: 'string', format: 'password', example: 'new_secure_password', required: true)]
    #[BodyParameter('new_password_confirmation', description: 'New password confirmation (must match new_password)', type: 'string', format: 'password', example: 'new_secure_password', required: true)]
    public function updatePassword(UpdatePasswordRequest $request, UpdatePatientPassword $action)
    {
        /** @var \App\Models\Patient $patient */
        $patient = Auth::user();

        $accessToken = $action->handle($patient, $request->validated());

        return $this->sendResponse([
            'token' => $accessToken->plainTextToken,
        ], 'Password updated successfully. Please use the new access token.');
    }
}
