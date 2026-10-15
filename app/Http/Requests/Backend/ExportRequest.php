<?php

namespace App\Http\Requests\Backend;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class ExportRequest extends FormRequest
{
    /**
     * Allowed export formats.
     */
    public const FORMATS = ['xlsx', 'csv'];

    /**
     * Default export format.
     */
    public const DEFAULT_FORMAT = 'xlsx';

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
            'format' => ['nullable', 'in:' . implode(',', self::FORMATS)],
            'search' => ['nullable', 'string', 'max:255'],
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
            'format.in' => trans('validation.in', ['attribute' => 'format']),
            'search.max' => trans('validation.max', ['attribute' => 'search', 'max' => 255]),
        ];
    }

    /**
     * Selected export format (default xlsx).
     *
     * @return string
     */
    public function exportFormat(): string
    {
        return $this->validated('format') ?? self::DEFAULT_FORMAT;
    }

    /**
     * Export search string.
     *
     * @return string|null
     */
    public function exportSearch(): ?string
    {
        return $this->validated('search');
    }
}
