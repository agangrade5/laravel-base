<?php

namespace App\Http\Requests\Backend\Setting;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class GeneralSettingRequest extends FormRequest
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
            'pagination_limit' => [
                'sometimes',
                'required',
                'integer',
                Rule::in(array_keys(config('constants.general_options.pagination_limit', []))),
            ],

            'password_reset_expiry' => [
                'sometimes',
                'required',
                'integer',
                Rule::in(array_keys(config('constants.general_options.password_reset_expiry', []))),
            ],

            'date_time_format' => [
                'sometimes',
                'required',
                'string',
                Rule::in(array_keys(config('constants.general_options.date_time_format', []))),
            ],
        ];
    }

    /**
     * Get custom validation messages.
     */
    public function messages(): array
    {
        return [
            'pagination_limit.required' => trans('validation.required'),
            'pagination_limit.integer' => trans('validation.integer'),
            'pagination_limit.in' => trans('validation.in'),

            'password_reset_expiry.required' => trans('validation.required'),
            'password_reset_expiry.integer' => trans('validation.integer'),
            'password_reset_expiry.in' => trans('validation.in'),

            'date_time_format.required' => trans('validation.required'),
            'date_time_format.string' => trans('validation.string'),
            'date_time_format.in' => trans('validation.in'),
        ];
    }
}
