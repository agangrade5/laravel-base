<?php

namespace App\Http\Requests\Api\v1\Account;

use App\Http\Requests\Api\v1\ApiRequest;
use App\Rules\{StrictPasswordRule, WithoutSpacesRule};
use Illuminate\Validation\Rule;

class ChangePasswordRequest extends ApiRequest
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
                Rule::requiredIf(fn () => filled($this->user()?->password)),
                'nullable',
                'current_password:sanctum',
            ],
            'password' => [
                'required',
                'confirmed',
                'different:current_password',
                new WithoutSpacesRule(),
                new StrictPasswordRule(),
            ],
        ];
    }
}
