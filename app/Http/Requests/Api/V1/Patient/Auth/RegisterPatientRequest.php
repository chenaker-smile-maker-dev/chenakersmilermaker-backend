<?php

namespace App\Http\Requests\Api\V1\Patient\Auth;

use App\Enums\Patient\Gender;
use Illuminate\Foundation\Http\FormRequest;

class RegisterPatientRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $genders = Gender::values();
        return [
            'first_name' => 'required|string|max:100',
            'last_name' => 'required|string|max:100',
            'phone' => 'required|string|max:15|unique:patients,phone',
            'email' => 'required|string|email|max:100|unique:patients,email',
            'age' => 'required|integer|min:0|max:120',
            'gender' => 'required|in:' . implode(',', $genders),
            'password' => 'required|string|min:6|confirmed'
        ];
    }
}
