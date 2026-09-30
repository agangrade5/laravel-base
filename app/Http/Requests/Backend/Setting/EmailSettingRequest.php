<?php

namespace App\Http\Requests\Backend\Setting;

use Illuminate\Foundation\Http\FormRequest;

class EmailSettingRequest extends FormRequest
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
            'mail_mailer' => ['required', 'string', 'in:smtp,sendmail,mailgun,ses,postmark'],
            'mail_host' => ['required', 'string', 'max:255'],
            'mail_port' => ['required', 'numeric', 'between:1,65535'],
            'mail_encryption' => ['required', 'string', 'in:tls,ssl,none'],
            'mail_username' => ['nullable', 'string', 'max:255'],
            'mail_password' => ['nullable', 'string', 'max:255'],
            'mail_from_address' => ['required', 'email', 'max:255'],
            'mail_from_name' => ['required', 'string', 'max:255'],
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
            'mail_mailer' => 'Mail driver',
            'mail_host' => 'Mail host',
            'mail_port' => 'Mail port',
            'mail_encryption' => 'Mail encryption',
            'mail_username' => 'Mail username',
            'mail_password' => 'Mail password',
            'mail_from_address' => 'From email address',
            'mail_from_name' => 'From sender name',
        ];
    }
}
