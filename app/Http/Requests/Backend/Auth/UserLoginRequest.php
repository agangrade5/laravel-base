<?php

namespace App\Http\Requests\Backend\Auth;

use App\Rules\{NoScripts, ValidEmailDomain};
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UserLoginRequest extends FormRequest
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
        $rules = [
            'login_type' => [
                'required',
                'in:email,phone',
            ],
        ];

        if ($this->input('login_type') === 'email') {

            $rules['email'] = [
                'required',
                'string',
                'email',
                'max:50',
                new NoScripts(),
                new ValidEmailDomain(),
            ];

        } else {

            $rules['country_code'] = [
                'required',
                'string',
                'max:5',
            ];

            $rules['phone'] = [
                'required',
                'digits_between:7,15',
            ];
        }

        return $rules;
    }
}
