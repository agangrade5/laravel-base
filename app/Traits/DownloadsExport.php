<?php

namespace App\Traits;

use App\Exports\BaseExport;
use App\Services\DateTimeService;
use Maatwebsite\Excel\Excel as ExcelWriter;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

trait DownloadsExport
{
    /**
     * Download an export as xlsx or csv.
     *
     * @param BaseExport $export
     * @param string $baseName
     * @param string $format
     *
     * @return BinaryFileResponse
     */
    protected function downloadExport(
        BaseExport $export,
        string $baseName,
        string $format = 'xlsx'
    ): BinaryFileResponse {
        $isCsv = $format === 'csv';

        $fileName = sprintf(
            '%s_%s.%s',
            $baseName,
            app(DateTimeService::class)->format(now(), 'Y-m-d_His'),
            $isCsv ? 'csv' : 'xlsx'
        );

        return Excel::download(
            $export,
            $fileName,
            $isCsv ? ExcelWriter::CSV : ExcelWriter::XLSX
        );
    }
}
