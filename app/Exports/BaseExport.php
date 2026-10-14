<?php

namespace App\Exports;

use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\{
    FromQuery,
    ShouldAutoSize,
    WithChunkReading,
    WithHeadings,
    WithMapping,
    WithStyles
};
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

abstract class BaseExport implements
    FromQuery,
    WithHeadings,
    WithMapping,
    WithChunkReading,
    ShouldAutoSize,
    WithStyles
{
    /**
     * Create a new export instance.
     *
     * @param Builder $query Already filtered query from repository.
     *
     * @return void
     */
    public function __construct(
        protected readonly Builder $query
    ) {
    }

    /**
     * Column headings.
     *
     * @return array<int, string>
     */
    abstract public function headings(): array;

    /**
     * Convert a single model into an export row.
     *
     * @param mixed $model
     *
     * @return array<int, mixed>
     */
    abstract protected function row(mixed $model): array;

    public function query(): Builder
    {
        return $this->query;
    }

    /**
     * Number of rows to read per chunk.
     *
     * @return int
     */
    public function chunkSize(): int
    {
        return 500;
    }

    /**
     * Convert a single model into an export row.
     *
     * @param mixed $row
     *
     * @return array<int, mixed>
     */
    public function map($row): array
    {
        return array_map([$this, 'sanitize'], $this->row($row));
    }

    /**
     * Bold header row.
     *
     * @param Worksheet $sheet
     *
     * @return array
     */
    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }

    /**
     * Sanitize string value.
     *
     * @param mixed $value
     *
     * @return mixed
     */
    protected function sanitize(mixed $value): mixed
    {
        if (is_string($value) && preg_match('/^[=+\-@\t\r]/', $value)) {
            return "'" . $value;
        }

        return $value;
    }
}
