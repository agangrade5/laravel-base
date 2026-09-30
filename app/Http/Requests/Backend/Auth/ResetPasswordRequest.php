<?php

namespace App\Http\Requests\Backend\Auth;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

use App\Rules\{NoScripts, ValidEmailDomain, WithoutSpacesRule, StrictPasswordRule};

class ResetPasswordRequest extends FormRequest
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
            'token' => [
                'required',
            ],

            'email' => [
                'required',
                'email',
                new NoScripts(),
                new ValidEmailDomain(),
            ],

            'password' => [
                'required',
                'confirmed',
                new WithoutSpacesRule(),
                new StrictPasswordRule(),
            ],
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
            'token.required' => trans('validation.required'),

            'email.required' => trans('validation.required'),
            'email.email' => trans('validation.email'),

            'password.required' => trans('validation.required'),
            'password.confirmed' => trans('validation.password.confirmed'),
        ];
    }
}
