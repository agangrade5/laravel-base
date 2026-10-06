<?php

namespace App\Http\Requests\Api\v1\User;

use App\Http\Requests\Api\v1\ApiRequest;
use App\Rules\NoScripts;
use Closure;
use Illuminate\Validation\Rule;

class UpdateUserRequest extends ApiRequest
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
        $countries = collect(config('countries.countries'));
        $userId = (int) $this->route('id');

        return [
            'name' => [
                'required',
                'string',
                'min:3',
                'max:50',
                new NoScripts(),
            ],
            'country_iso' => [
                'required',
                'string',
                Rule::in($countries->pluck('iso')->all()),
            ],
            'country_code' => [
                'required',
                'string',
                Rule::in($countries->pluck('code')->all()),
                // The country_iso and country_code pair must match the configured values.
                function (string $attribute, mixed $value, Closure $fail) use ($countries) {
                    $country = $countries->firstWhere('iso', strtolower((string) $this->input('country_iso')));

                    if ($country && $country['code'] !== $value) {
                        $fail('The country code does not match the selected country.');
                    }
                },
            ],
            'phone_number' => [
                'required',
                'regex:/^[0-9]{10,15}$/',
                // The phone number must be unique for the same country code.
                Rule::unique('users', 'phone_number')
                    ->where('country_code', $this->input('country_code'))
                    ->ignore($userId),
            ],
            'profile_image' => [
                'nullable',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:2048', // 2MB
            ],
            'remove_profile_image' => [
                'nullable',
                'boolean',
            ],
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        if ($this->filled('country_iso')) {
            $this->merge(['country_iso' => strtolower((string) $this->input('country_iso'))]);
        }
    }
}
