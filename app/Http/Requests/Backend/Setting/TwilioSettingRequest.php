<?php

namespace App\Http\Requests\Backend\Setting;

use Illuminate\Foundation\Http\FormRequest;

class TwilioSettingRequest extends FormRequest
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
            'twilio_account_sid' => ['required', 'string', 'max:255'],
            'twilio_auth_token' => ['required', 'string', 'max:255'],
            'twilio_from_number' => ['required', 'string', 'max:50'],
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
            'twilio_account_sid' => 'Twilio Account SID',
            'twilio_auth_token' => 'Twilio Auth Token',
            'twilio_from_number' => 'Twilio From Number',
        ];
    }
}
