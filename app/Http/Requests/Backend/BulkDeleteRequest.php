<?php

namespace App\Http\Requests\Backend;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class BulkDeleteRequest extends FormRequest
{
    /**
     * Max records allowed in a single bulk action.
     */
    public const MAX_IDS = 100;

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
            'ids' => ['required', 'array', 'min:1', 'max:' . self::MAX_IDS],
            'ids.*' => ['integer', 'distinct'],
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'ids.required' => trans('validation.required'),
            'ids.array' => trans('validation.array'),
            'ids.min' => trans('validation.min', ['attribute' => 'ids', 'min' => 1]),
            'ids.max' => trans('validation.max', ['attribute' => 'ids', 'max' => self::MAX_IDS]),

            'ids.*.integer' => trans('validation.integer'),
            'ids.*.distinct' => trans('validation.distinct'),
        ];
    }

    /**
     * Get the IDs of the records to be deleted.
     *
     * @return array<int, int>
     */
    public function ids(): array
    {
        return array_map('intval', $this->validated('ids'));
    }
}
