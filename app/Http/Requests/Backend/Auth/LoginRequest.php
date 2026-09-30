<?php

namespace App\Http\Requests\Backend\Auth;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

use App\Rules\{NoScripts, ValidEmailDomain};

class LoginRequest extends FormRequest
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
            'email' => [
                'required',
                'email',
                new NoScripts(),
                new ValidEmailDomain(),
            ],

            'password' => [
                'required',
                'string',
                new NoScripts(),
            ],

            'remember' => [
                'nullable',
                'boolean',
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
            'email.required' => trans(
                'validation.required',
                ['attribute' => 'Email address']
            ),
            'email.email' => trans('validation.email'),
            'password.required' => trans('validation.required'),
        ];
    }
}
