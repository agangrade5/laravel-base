<?php

namespace App\Http\Requests\Backend\User;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Rules\{NoScripts, ValidEmailDomain, ValidUrl, WithoutSpacesRule, StrictPasswordRule};

class UserRequest extends FormRequest
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
        $userId = $this->route('id');

        $rules = [
            'name' => [
                'required',
                'string',
                'max:255',
                new NoScripts(),
            ],
           'email' => [
                'required',
                'string',
                'email',
                'max:50',
                Rule::unique('users', 'email')->ignore($userId),
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
                Rule::unique('users', 'phone_number')->ignore($userId),
            ],
            'is_active' => [
                'nullable',
                'boolean',
            ],
        ];

        // if ($userId) {
        //     // Password is optional during user update
        //     $rules['password'] = [
        //         'nullable',
        //         'string',
        //         new WithoutSpacesRule(),
        //         new StrictPasswordRule(),
        //     ];
        // } else {
        //     // Password is required when adding a new user
        //     $rules['password'] = [
        //         'required',
        //         'string',
        //         new WithoutSpacesRule(),
        //         new StrictPasswordRule(),
        //     ];
        // }

        return $rules;
    }


}
