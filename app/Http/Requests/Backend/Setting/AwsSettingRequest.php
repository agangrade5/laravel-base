<?php

namespace App\Http\Requests\Backend\Setting;

use Illuminate\Foundation\Http\FormRequest;

class AwsSettingRequest extends FormRequest
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
            'aws_access_key_id' => ['required', 'string', 'max:255'],
            'aws_secret_access_key' => ['required', 'string', 'max:255'],
            'aws_default_region' => ['required', 'string', 'max:100'],
            'aws_bucket' => ['required', 'string', 'max:255'],
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
            'aws_access_key_id' => 'AWS access key ID',
            'aws_secret_access_key' => 'AWS secret access key',
            'aws_default_region' => 'AWS default region',
            'aws_bucket' => 'AWS S3 bucket name',
        ];
    }
}
