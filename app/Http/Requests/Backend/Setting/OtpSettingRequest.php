<?php

namespace App\Http\Requests\Backend\Setting;

use Illuminate\Foundation\Http\FormRequest;

class OtpSettingRequest extends FormRequest
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
        return [
            'otp_max_time' => ['required', 'numeric', 'in:15,30,60,90'],
            'otp_length' => ['required', 'numeric', 'in:6'],
            'otp_is_default' => ['required', 'in:0,1'],
            'otp_default' => [
                'required_if:otp_is_default,1',
                'nullable',
                'digits:6',
            ],
        ];
    }

    /**
     * Custom attribute names for validation errors.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'otp_max_time' => 'OTP max expiry time',
            'otp_length' => 'OTP digits length',
            'otp_is_default' => 'Default static OTP toggle',
            'otp_default' => 'Default static OTP code',
        ];
    }
}
