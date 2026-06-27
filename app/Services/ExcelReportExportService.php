<?php

namespace App\Services;

use App\Models\Company;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use PhpOffice\PhpSpreadsheet\Worksheet\PageSetup;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ExcelReportExportService
{
    private const BLACK = '000000';

    private const DARK_GRAY = '333333';

    private const MID_GRAY = '666666';

    private const LIGHT_GRAY = 'E5E5E5';

    private const ROW_ALT = 'F7F7F7';

    /**
     * @param  array<int, string>  $headings
     * @param  array<int, array<int, mixed>>  $rows
     * @param  array<int, string>  $metaLines
     * @param  array<string, mixed>  $summary
     * @param  array<int, int>  $numericColumns  1-based column indexes to right-align
     */
    public function download(
        string $filename,
        string $title,
        array $headings,
        array $rows,
        array $metaLines = [],
        array $summary = [],
        array $numericColumns = [],
    ): StreamedResponse {
        $company = Company::current();
        $spreadsheet = new Spreadsheet;
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle(substr(preg_replace('/[^A-Za-z0-9 ]/', '', $title) ?: 'Report', 0, 31));
        $sheet->setShowGridlines(false);

        $columnCount = max(count($headings), 1);
        $lastColumn = $this->columnLetter($columnCount);
        $headerRow = 10;
        $dataStartRow = $headerRow + 1;
        $dataEndRow = $dataStartRow + max(count($rows), 1) - 1;

        $this->buildHeaderBlock($sheet, $company, $title, $metaLines, $lastColumn);
        $this->buildTableHeader($sheet, $headings, $headerRow, $lastColumn);
        $this->buildDataRows($sheet, $rows, $dataStartRow, $dataEndRow, $lastColumn, $numericColumns);

        $footerStartRow = $dataEndRow + 2;
        $sheet->getRowDimension($footerStartRow - 1)->setRowHeight(14);
        $footerStartRow = $this->buildFooter($sheet, $summary, $footerStartRow, $lastColumn);

        $sheet->getStyle("A1:{$lastColumn}{$footerStartRow}")->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
        $sheet->getPageSetup()
            ->setOrientation($columnCount > 6 ? PageSetup::ORIENTATION_LANDSCAPE : PageSetup::ORIENTATION_PORTRAIT)
            ->setPaperSize(PageSetup::PAPERSIZE_A4)
            ->setFitToWidth(1)
            ->setFitToHeight(0)
            ->setRowsToRepeatAtTopByStartAndEnd($headerRow, $headerRow)
            ->setPrintArea("A1:{$lastColumn}{$footerStartRow}");

        $sheet->getPageMargins()
            ->setTop(0.55)
            ->setBottom(0.45)
            ->setLeft(0.45)
            ->setRight(0.45)
            ->setHeader(0.2)
            ->setFooter(0.2);

        $sheet->getHeaderFooter()
            ->setOddHeader('')
            ->setEvenHeader('')
            ->setOddFooter('&RPage &P of &N')
            ->setEvenFooter('&RPage &P of &N');

        return response()->streamDownload(function () use ($spreadsheet) {
            (new Xlsx($spreadsheet))->save('php://output');
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    /**
     * @param  array<int, string>  $metaLines
     */
    protected function buildHeaderBlock(
        \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $sheet,
        Company $company,
        string $title,
        array $metaLines,
        string $lastColumn,
    ): void {
        $sheet->getRowDimension(1)->setRowHeight(12);
        $sheet->getRowDimension(2)->setRowHeight(32);
        $sheet->getRowDimension(3)->setRowHeight(18);
        $sheet->getRowDimension(4)->setRowHeight(10);
        $sheet->getRowDimension(5)->setRowHeight(6);
        $sheet->getRowDimension(6)->setRowHeight(24);
        $sheet->getRowDimension(7)->setRowHeight(18);
        $sheet->getRowDimension(8)->setRowHeight(8);
        $sheet->getRowDimension(9)->setRowHeight(4);

        $logoPath = $this->resolveLogoPath($company);
        $textColumn = 'A';

        if ($logoPath) {
            $drawing = new Drawing;
            $drawing->setName('Company Logo');
            $drawing->setDescription('Company Logo');
            $drawing->setPath($logoPath);
            $drawing->setHeight(54);
            $drawing->setCoordinates('A2');
            $drawing->setOffsetX(8);
            $drawing->setOffsetY(4);
            $drawing->setWorksheet($sheet);

            $textColumn = 'C';
            $sheet->getColumnDimension('A')->setWidth(11);
            $sheet->getColumnDimension('B')->setWidth(2);
        }

        $sheet->mergeCells("{$textColumn}2:{$lastColumn}2");
        $sheet->setCellValue("{$textColumn}2", strtoupper($company->name));
        $sheet->getStyle("{$textColumn}2")->getFont()->setBold(true)->setSize(14)->getColor()->setRGB(self::BLACK);
        $sheet->getStyle("{$textColumn}2")->getAlignment()->setVertical(Alignment::VERTICAL_BOTTOM);

        if ($company->address) {
            $sheet->mergeCells("{$textColumn}3:{$lastColumn}3");
            $sheet->setCellValue("{$textColumn}3", $company->address);
            $sheet->getStyle("{$textColumn}3")->getFont()->setSize(9)->getColor()->setRGB(self::MID_GRAY);
            $sheet->getStyle("{$textColumn}3")->getAlignment()->setWrapText(true);
        }

        $sheet->mergeCells("A5:{$lastColumn}5");
        $sheet->getStyle("A5:{$lastColumn}5")->applyFromArray([
            'borders' => [
                'bottom' => [
                    'borderStyle' => Border::BORDER_MEDIUM,
                    'color' => ['rgb' => self::BLACK],
                ],
            ],
        ]);

        $sheet->mergeCells("A6:{$lastColumn}6");
        $sheet->setCellValue('A6', strtoupper($title));
        $sheet->getStyle('A6')->getFont()->setBold(true)->setSize(12)->getColor()->setRGB(self::BLACK);
        $sheet->getStyle('A6')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $metaText = $metaLines !== []
            ? implode('  |  ', $metaLines)
            : 'All records';

        $sheet->mergeCells("A7:{$lastColumn}7");
        $sheet->setCellValue('A7', $metaText);
        $sheet->getStyle('A7')->getFont()->setSize(9)->getColor()->setRGB(self::MID_GRAY);
        $sheet->getStyle('A7')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setWrapText(true);

        $sheet->mergeCells("A9:{$lastColumn}9");
        $sheet->getStyle("A9:{$lastColumn}9")->applyFromArray([
            'borders' => [
                'bottom' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => self::BLACK],
                ],
            ],
        ]);
    }

    /**
     * @param  array<int, string>  $headings
     */
    protected function buildTableHeader(
        \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $sheet,
        array $headings,
        int $headerRow,
        string $lastColumn,
    ): void {
        $sheet->getRowDimension($headerRow)->setRowHeight(22);

        foreach ($headings as $index => $heading) {
            $column = $this->columnLetter($index + 1);
            $sheet->setCellValue("{$column}{$headerRow}", strtoupper($heading));
            $sheet->getColumnDimension($column)->setAutoSize(true);
        }

        $sheet->getStyle("A{$headerRow}:{$lastColumn}{$headerRow}")->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => self::BLACK], 'size' => 9],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => self::LIGHT_GRAY],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => self::BLACK],
                ],
            ],
        ]);
    }

    /**
     * @param  array<int, array<int, mixed>>  $rows
     * @param  array<int, int>  $numericColumns
     */
    protected function buildDataRows(
        \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $sheet,
        array $rows,
        int $dataStartRow,
        int $dataEndRow,
        string $lastColumn,
        array $numericColumns,
    ): void {
        foreach ($rows as $rowIndex => $row) {
            $excelRow = $dataStartRow + $rowIndex;
            $sheet->getRowDimension($excelRow)->setRowHeight(18);

            foreach ($row as $columnIndex => $value) {
                $column = $this->columnLetter($columnIndex + 1);
                $sheet->setCellValue("{$column}{$excelRow}", $value);
            }

            if ($rowIndex % 2 === 1) {
                $sheet->getStyle("A{$excelRow}:{$lastColumn}{$excelRow}")->applyFromArray([
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => self::ROW_ALT],
                    ],
                ]);
            }
        }

        if ($rows === []) {
            $sheet->mergeCells("A{$dataStartRow}:{$lastColumn}{$dataStartRow}");
            $sheet->setCellValue("A{$dataStartRow}", 'No records found for the selected filters.');
            $sheet->getStyle("A{$dataStartRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("A{$dataStartRow}")->getFont()->getColor()->setRGB(self::MID_GRAY);
        }

        if ($rows !== []) {
            $sheet->getStyle("A{$dataStartRow}:{$lastColumn}{$dataEndRow}")->applyFromArray([
                'font' => ['size' => 9, 'color' => ['rgb' => self::DARK_GRAY]],
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => ['rgb' => self::BLACK],
                    ],
                ],
            ]);

            foreach ($numericColumns as $columnIndex) {
                $column = $this->columnLetter($columnIndex);
                $sheet->getStyle("{$column}{$dataStartRow}:{$column}{$dataEndRow}")
                    ->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_RIGHT);
            }
        }
    }

    /**
     * @param  array<string, mixed>  $summary
     */
    protected function buildFooter(
        \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $sheet,
        array $summary,
        int $footerStartRow,
        string $lastColumn,
    ): int {
        if ($summary !== []) {
            $summaryParts = collect($summary)
                ->map(fn ($value, $label) => strtoupper($label).': '.$value)
                ->implode('  |  ');

            $sheet->mergeCells("A{$footerStartRow}:{$lastColumn}{$footerStartRow}");
            $sheet->setCellValue("A{$footerStartRow}", $summaryParts);
            $sheet->getStyle("A{$footerStartRow}")->getFont()->setBold(true)->setSize(9)->getColor()->setRGB(self::BLACK);
            $sheet->getStyle("A{$footerStartRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
            $footerStartRow++;
        }

        $generatedLine = 'Generated on '.now()->format('d M Y, h:i A').' by '.(auth()->user()?->name ?? 'System');
        $sheet->mergeCells("A{$footerStartRow}:{$lastColumn}{$footerStartRow}");
        $sheet->setCellValue("A{$footerStartRow}", $generatedLine);
        $sheet->getStyle("A{$footerStartRow}")->getFont()->setSize(8)->getColor()->setRGB(self::MID_GRAY);
        $sheet->getStyle("A{$footerStartRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getRowDimension($footerStartRow + 1)->setRowHeight(16);

        $sheet->getStyle("A{$footerStartRow}:{$lastColumn}{$footerStartRow}")->applyFromArray([
            'borders' => [
                'top' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => self::BLACK],
                ],
            ],
        ]);

        return $footerStartRow + 1;
    }

    protected function resolveLogoPath(Company $company): ?string
    {
        if (! $company->logo_path) {
            return null;
        }

        $path = storage_path('app/public/'.$company->logo_path);

        return is_file($path) ? $path : null;
    }

    protected function columnLetter(int $columnIndex): string
    {
        $letter = '';

        while ($columnIndex > 0) {
            $columnIndex--;
            $letter = chr(65 + ($columnIndex % 26)).$letter;
            $columnIndex = intdiv($columnIndex, 26);
        }

        return $letter;
    }
}
