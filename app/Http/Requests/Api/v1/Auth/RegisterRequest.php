<?php

namespace App\Http\Requests\Api\v1\Auth;

use App\Http\Requests\Api\v1\ApiRequest;

use App\Rules\{NoScripts, ValidEmailDomain};

class RegisterRequest extends ApiRequest
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
            'name' => [
                'required',
                'string',
                'min:3',
                'max:50',
                new NoScripts(),
            ],
            'email' => [
                'required',
                'string',
                'email',
                'max:50',
                'unique:users,email',
                new NoScripts(),
                new ValidEmailDomain(),
            ],
            'country_iso' => [
                'required',
                'string',
                'in:' . collect(config('countries.countries'))
                    ->pluck('iso')
                    ->implode(','),
            ],
            'country_code' => [
                'required',
                'string',
                'in:' . collect(config('countries.countries'))
                    ->pluck('code')
                    ->implode(','),
            ],
            'phone_number' => [
                'required',
                'regex:/^[0-9]{10,15}$/',
                'unique:users,phone_number',
            ],
            'timezone' => [
                'required',
                'timezone'
            ],
            'device_id' => [
                'required',
                'string',
                'max:255'
            ],
            'device_type' => [
                'required',
                'in:android,ios'
            ],
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'email' => strtolower(trim((string) $this->input('email'))),
            'country_iso' => strtolower((string) $this->input('country_iso')),
        ]);
    }
}
