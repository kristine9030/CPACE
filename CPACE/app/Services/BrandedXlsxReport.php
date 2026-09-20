<?php

namespace App\Services;

use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

/**
 * Turns a faculty CSV export into an actual designed report: a maroon CPACE
 * title banner, a subtitle line with who/when/what-filters, a styled header
 * row, bordered data with light zebra striping, and sane column widths —
 * so opening the file in Excel looks like a report someone built on
 * purpose, not a bare data dump.
 *
 * Usage:
 *   $report = new BrandedXlsxReport('Class Performance Summary');
 *   $sheet = $report->sheet();
 *   $row = $report->writeBanner($sheet, 'Class Performance Summary', [
 *       'Generated ' . now()->format('M j, Y g:i A') . ' by ' . Auth::user()->name,
 *       'Scope: All Assigned Subjects · Current Term',
 *   ]);
 *   $row = $report->writeTable($sheet, $row, ['Student', 'Score'], $rows, [30, 12]);
 *   return $report->download($filename);
 */
class BrandedXlsxReport
{
    private const MAROON = '7B1D1D';
    private const MAROON_DARK = '5C1616';
    private const HEADER_BG = self::MAROON;
    private const ZEBRA_BG = 'F7F1F1';
    private const BORDER_COLOR = 'E3D8D8';

    private Spreadsheet $spreadsheet;

    public function __construct(private string $documentTitle = 'CPACE Report')
    {
        $this->spreadsheet = new Spreadsheet();
        $this->spreadsheet->getProperties()
            ->setCreator('CPACE')
            ->setTitle($documentTitle);
    }

    public function sheet(string $title = 'Report'): Worksheet
    {
        $sheet = $this->spreadsheet->getActiveSheet();
        // Sheet names can't hold most punctuation — keep it plain.
        $sheet->setTitle(substr(preg_replace('/[\\\\\/\?\*\[\]:]/', ' ', $title), 0, 31));
        $sheet->setShowGridlines(false);

        return $sheet;
    }

    /**
     * The maroon title banner + a light meta row underneath (generated-by,
     * scope, filters — whatever the caller passes as subtitle lines).
     * Returns the next free row index to start writing content at.
     */
    public function writeBanner(Worksheet $sheet, string $title, array $subtitleLines = [], int $colSpan = 8): int
    {
        $lastCol = Coordinate::stringFromColumnIndex($colSpan);

        $sheet->mergeCells("A1:{$lastCol}1");
        $sheet->setCellValue('A1', 'CPACE');
        $sheet->getStyle('A1')->applyFromArray([
            'font' => ['bold' => true, 'size' => 12, 'color' => ['argb' => 'FFFFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF' . self::MAROON_DARK]],
            'alignment' => ['vertical' => Alignment::VERTICAL_CENTER, 'indent' => 1],
        ]);
        $sheet->getRowDimension(1)->setRowHeight(22);

        $sheet->mergeCells("A2:{$lastCol}2");
        $sheet->setCellValue('A2', $title);
        $sheet->getStyle('A2')->applyFromArray([
            'font' => ['bold' => true, 'size' => 18, 'color' => ['argb' => 'FFFFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF' . self::MAROON]],
            'alignment' => ['vertical' => Alignment::VERTICAL_CENTER, 'indent' => 1],
        ]);
        $sheet->getRowDimension(2)->setRowHeight(34);

        $row = 3;
        foreach ($subtitleLines as $line) {
            $sheet->mergeCells("A{$row}:{$lastCol}{$row}");
            $sheet->setCellValue("A{$row}", $line);
            $sheet->getStyle("A{$row}")->applyFromArray([
                'font' => ['size' => 10, 'color' => ['argb' => 'FF6B6B6B'], 'italic' => true],
                'alignment' => ['vertical' => Alignment::VERTICAL_CENTER, 'indent' => 1],
            ]);
            $sheet->getRowDimension($row)->setRowHeight(16);
            $row++;
        }

        return $row + 1; // one blank row of breathing room before the next block
    }

    /**
     * A short "at a glance" strip of label/value pairs (e.g. Students: 24,
     * Avg Accuracy: 78%) rendered as small stat tiles across one row.
     * Returns the next free row.
     */
    public function writeSummaryStrip(Worksheet $sheet, int $row, array $pairs): int
    {
        $col = 1;
        foreach ($pairs as $label => $value) {
            $labelCoord = Coordinate::stringFromColumnIndex($col) . $row;
            $valueCoord = Coordinate::stringFromColumnIndex($col) . ($row + 1);

            $sheet->setCellValue($labelCoord, strtoupper((string) $label));
            $sheet->getStyle($labelCoord)->applyFromArray([
                'font' => ['size' => 8, 'bold' => true, 'color' => ['argb' => 'FF9A9A9A']],
            ]);

            $sheet->setCellValue($valueCoord, $value);
            $sheet->getStyle($valueCoord)->applyFromArray([
                'font' => ['size' => 14, 'bold' => true, 'color' => ['argb' => 'FF1A1A1A']],
            ]);

            $col++;
        }
        $sheet->getRowDimension($row + 1)->setRowHeight(20);

        return $row + 3;
    }

    /**
     * A bordered, header-styled data table starting at the given row.
     * $widths (optional) maps 1-based column index to a character width;
     * columns left out default to a reasonable size. Returns the next free row.
     *
     * @param  array<int, string>  $headers
     * @param  array<int, array<int, mixed>>  $rows
     * @param  array<int, int>  $widths
     */
    public function writeTable(Worksheet $sheet, int $row, array $headers, array $rows, array $widths = []): int
    {
        $headerRow = $row;
        $colCount = count($headers);
        $lastCol = Coordinate::stringFromColumnIndex($colCount);

        $sheet->fromArray($headers, null, "A{$headerRow}");
        $sheet->getStyle("A{$headerRow}:{$lastCol}{$headerRow}")->applyFromArray([
            'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF'], 'size' => 10],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF' . self::HEADER_BG]],
            'alignment' => ['vertical' => Alignment::VERTICAL_CENTER, 'wrapText' => true],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => 'FF' . self::MAROON_DARK]]],
        ]);
        $sheet->getRowDimension($headerRow)->setRowHeight(20);

        $dataStart = $headerRow + 1;
        $r = $dataStart;
        foreach ($rows as $i => $line) {
            $sheet->fromArray($line, null, "A{$r}");
            if ($i % 2 === 1) {
                $sheet->getStyle("A{$r}:{$lastCol}{$r}")->applyFromArray([
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF' . self::ZEBRA_BG]],
                ]);
            }
            $r++;
        }
        $lastRow = $r - 1;

        if ($lastRow >= $dataStart) {
            $sheet->getStyle("A{$dataStart}:{$lastCol}{$lastRow}")->applyFromArray([
                'font' => ['size' => 10, 'color' => ['argb' => 'FF333333']],
                'alignment' => ['vertical' => Alignment::VERTICAL_TOP, 'wrapText' => true],
                'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => 'FF' . self::BORDER_COLOR]]],
            ]);
        }

        for ($c = 1; $c <= $colCount; $c++) {
            $letter = Coordinate::stringFromColumnIndex($c);
            $sheet->getColumnDimension($letter)->setWidth($widths[$c] ?? 22);
        }

        $sheet->freezePane("A{$dataStart}");

        return $lastRow + 2;
    }

    /** Raw .xlsx bytes, ready to hand to a StreamedResponse or file_put_contents. */
    public function bytes(): string
    {
        $writer = new Xlsx($this->spreadsheet);
        ob_start();
        $writer->save('php://output');

        return ob_get_clean();
    }

    /** A ready-to-return Laravel download response. */
    public function download(string $filename)
    {
        $contents = $this->bytes();

        return response()->streamDownload(
            fn () => print($contents),
            $filename,
            ['Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet']
        );
    }
}
