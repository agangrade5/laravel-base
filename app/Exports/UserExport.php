<?php

namespace App\Exports;

use App\Models\User;
use App\Services\DateTimeService;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class UserExport extends BaseExport implements WithColumnFormatting
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
            'Name',
            'Email',
            'Phone',
            'Role',
            'Status',
            'Created At',
        ];
    }

    /**
     * Convert a single model into an export row.
     *
     * @param User $model
     *
     * @return array<int, mixed>
     */
    protected function row(mixed $model): array
    {
        return [
            $model->id,
            $model->name,
            $model->email,
            $model->phone_number
                ? trim(($model->country_code ?? '') . ' ' . $model->phone_number)
                : '-',
            $model->roles->pluck('name')->implode(', ') ?: '-',
            $model->is_active ? 'Active' : 'Inactive',
            app(DateTimeService::class)->format($model->created_at),
        ];
    }

    /**
     * Column formats.
     *
     * @return array<string, string>
     */
    public function columnFormats(): array
    {
        return [
            'D' => NumberFormat::FORMAT_TEXT, // Phone column
        ];
    }
}
