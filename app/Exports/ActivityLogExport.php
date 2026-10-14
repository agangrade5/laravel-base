<?php

namespace App\Exports;

use App\Services\DateTimeService;
use Illuminate\Database\Eloquent\Builder;
use Spatie\Activitylog\Models\Activity;

class ActivityLogExport extends BaseExport
{
    /**
     * Column headings.
     *
     * @return array<int, string>
     */
    public function headings(): array
    {
        return [
            'ID',
            'User',
            'Email',
            'Log Name',
            'Description',
            'Causer Type',
            'Causer ID',
            'Properties',
            'Date & Time',
        ];
    }

    /**
     * Convert a single Activity record into an export row.
     *
     * @param Activity $model
     *
     * @return array<int, mixed>
     */
    protected function row(mixed $model): array
    {
        return [
            $model->id,
            $model->causer?->name ?? 'System',
            $model->causer?->email ?? '-',
            $model->log_name ?? 'default',
            $model->description,
            $model->causer_type ? class_basename($model->causer_type) : '-',
            $model->causer_id ?? '-',
            $this->toJson($model->properties),
            app(DateTimeService::class)->format($model->created_at),
        ];
    }

    /**
     * Get attribute changes only if the column exists in the table
     * (newer versions of spatie/laravel-activitylog have `attribute_changes`).
     *
     * @param Activity $model
     *
     * @return mixed
     */
    private function changesOf(Activity $model): mixed
    {
        return array_key_exists('attribute_changes', $model->getAttributes())
            ? $model->attribute_changes
            : null;
    }

    /**
     * Encode value as JSON string, empty values become "-".
     *
     * @param mixed $value
     *
     * @return string
     */
    private function toJson(mixed $value): string
    {
        if (empty($value) || (is_object($value) && method_exists($value, 'isEmpty') && $value->isEmpty())) {
            return '-';
        }

        return json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }
}
