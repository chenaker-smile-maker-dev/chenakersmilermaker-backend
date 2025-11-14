<?php

namespace App\Http\Controllers\Api\V1;

use App\Actions\Patient\Profile\EditPatientProfile;
use App\Actions\Patient\Profile\UpdatePatientPassword;
use App\Http\Resources\PatientResource;
use App\Http\Controllers\Api\BaseController;
use App\Http\Requests\Api\V1\Patient\Profile\EditPatientProfileRequest;
use App\Http\Requests\Api\V1\Patient\Profile\UpdatePasswordRequest;
use Dedoc\Scramble\Attributes\Group;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

#[Group('Patient Profile', weight: 2)]
class ProfileController extends BaseController
{
    public function show(Request $request)
    {
        /** @var \App\Models\Patient $patient */
        $patient = $request->user();

        return $this->sendResponse(new PatientResource($patient), 'Patient retrieved successfully.');
    }

    public function update(EditPatientProfileRequest $request, EditPatientProfile $action)
    {
        /** @var \App\Models\Patient $patient */
        $patient = Auth::user();

        $updatedPatient = $action->handle($patient, $request->validated());

        return $this->sendResponse(new PatientResource($updatedPatient), 'Profile updated successfully.');
    }

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
