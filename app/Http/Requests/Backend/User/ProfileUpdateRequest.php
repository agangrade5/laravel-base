<?php

namespace App\Http\Requests\Backend\User;

use App\Rules\NoScripts;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProfileUpdateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return auth()->check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $userId = $this->user()?->id;
        $countryCode = $this->input('country_code', $this->user()?->country_code ?? '+91') ?: '+91';

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
                'in:' . collect(config('countries.countries'))
                    ->pluck('iso')
                    ->implode(','),
            ],
            'country_code' => [
                'nullable',
                'string',
                'in:' . collect(config('countries.countries'))
                    ->pluck('code')
                    ->implode(','),
            ],
            'phone_number' => [
                'required',
                'regex:/^[0-9]{10,15}$/',
                Rule::unique('users', 'phone_number')->ignore($userId),
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
}
