<?php

namespace App\Http\Requests\Backend\Auth;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

use App\Rules\{WithoutSpacesRule, StrictPasswordRule};

class ChangePasswordRequest extends FormRequest
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
            'current_password' => [
                'required',
                'current_password:web',
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
     * Get custom validation messages.
     */
    public function messages(): array
    {
        return [
            'current_password.required' => trans('validation.required'),
            'current_password.current_password' => trans('validation.current_password'),
            'password.required' => trans('validation.required'),
            'password.confirmed' => trans('validation.password.confirmed'),
        ];
    }
}
