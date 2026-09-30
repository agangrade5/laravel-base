<?php

namespace App\Http\Requests\Backend\Auth;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class VerifyOtpRequest extends FormRequest
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
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'otp' => [
                'required',
                'array',
                'size:6',
            ],

            'otp.*' => [
                'required',
                'digits:1',
            ],
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'otp.required' => 'Please enter the OTP.',
            'otp.size' => 'OTP must contain 6 digits.',
            'otp.*.required' => 'Please enter all OTP digits.',
            'otp.*.digits' => 'Each OTP digit must be numeric.',
        ];
    }
}
