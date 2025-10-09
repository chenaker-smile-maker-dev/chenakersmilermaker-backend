<?php

namespace App\Http\Requests\Api\V1\Patient\Profile;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class EditPatientProfileRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $patient = Auth::user();
        $patientId = $patient->id;

        return [
            'first_name' => 'sometimes|required|string|max:100',
            'last_name' => 'sometimes|required|string|max:100',
            'phone' => 'sometimes|required|string|max:15|unique:patients,phone,' . $patientId,
            'image' => 'sometimes|nullable|file|image|mimes:jpeg,png,jpg|max:2048',
        ];
    }
}
