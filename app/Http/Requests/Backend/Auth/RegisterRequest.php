<?php

namespace App\Http\Requests\Backend\Auth;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

use App\Rules\{NoScripts, ValidEmailDomain, WithoutSpacesRule, StrictPasswordRule};

class RegisterRequest extends FormRequest
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
            /* 'password' => [
                'required',
                'confirmed',
                new NoScripts(),
                new WithoutSpacesRule(),
                new StrictPasswordRule(),
            ], */
        ];
    }

    /**
     * Get the validation error message.
     *
     * @return array
     */
    public function messages(): array
    {
        return [
            'name.required' => trans('validation.required'),

            'email.required' => trans('validation.required'),
            'email.email' => trans('validation.email'),
            'email.unique' => trans('validation.unique'),

            /* 'password.required' => trans('validation.required'),
            'password.confirmed' => trans('validation.password.confirmed'), */
        ];
    }
}
