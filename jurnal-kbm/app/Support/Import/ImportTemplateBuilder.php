<?php

namespace App\Support\Import;

use App\Models\Guru;
use App\Models\KodeKelas;
use PhpOffice\PhpSpreadsheet\Cell\DataValidation;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

/**
 * Used ONLY by storage/app/build-import-templates.php to generate static files.
 * Runtime download uses public/templates/*.xlsx directly.
 */
final class ImportTemplateBuilder
{
    public function buildToFile(string $module, string $path): void
    {
        $definition = ImportTemplateRegistry::get($module);
        $spreadsheet = $this->buildSpreadsheet($definition);
        $writer = new Xlsx($spreadsheet);
        $writer->save($path);
    }

    private function buildSpreadsheet(array $definition): Spreadsheet
    {
        $spreadsheet = new Spreadsheet();
        $spreadsheet->removeSheetByIndex(0);

        $petunjuk = $spreadsheet->createSheet();
        $petunjuk->setTitle(ImportModule::SHEET_PETUNJUK);
        $this->fillPetunjukSheet($petunjuk, $definition);

        $data = $spreadsheet->createSheet();
        $data->setTitle(ImportModule::SHEET_DATA);
        $this->fillDataSheet($data, $definition);

        $listValues = $this->collectListValues($definition);

        if ($listValues !== []) {
            $listSheet = $spreadsheet->createSheet();
            $listSheet->setTitle('_LIST');
            $listSheet->setSheetState(\PhpOffice\PhpSpreadsheet\Worksheet\Worksheet::SHEETSTATE_HIDDEN);
            $this->fillListSheet($listSheet, $listValues);
            $this->applyValidations($data, $definition, $listValues);
        }

        $spreadsheet->setActiveSheetIndex(1);

        return $spreadsheet;
    }

    private function fillPetunjukSheet(\PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $sheet, array $definition): void
    {
        $sheet->getColumnDimension('A')->setWidth(100);

        foreach ($definition['instructions'] as $index => $line) {
            $row = $index + 1;
            $sheet->setCellValue("A{$row}", $line);

            if ($index === 0) {
                $sheet->getStyle("A{$row}")->getFont()->setBold(true)->setSize(14);
            }
        }

        $sheet->getStyle('A:A')->getAlignment()->setWrapText(true)->setVertical(Alignment::VERTICAL_TOP);
    }

    private function fillDataSheet(\PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $sheet, array $definition): void
    {
        $headers = $definition['headers'];
        $example = $definition['example'];

        $optionalNotes = [
            'nip' => 'Opsional',
            'tempat_lahir' => 'Opsional / Optional',
            'tanggal_lahir' => 'Opsional / Optional',
        ];

        foreach ($headers as $colIndex => $header) {
            $column = $this->columnLetter($colIndex + 1);
            $sheet->setCellValue("{$column}1", $header);
            $sheet->setCellValue("{$column}2", $example[$header] ?? '');
            $sheet->getColumnDimension($column)->setAutoSize(true);

            if (isset($optionalNotes[$header])) {
                $sheet->getComment("{$column}1")
                    ->getText()
                    ->createTextRun($optionalNotes[$header]);
                $sheet->getComment("{$column}1")->setWidth('140pt');
                $sheet->getComment("{$column}1")->setHeight('40pt');
            }
        }

        $lastColumn = $this->columnLetter(count($headers));
        $sheet->getStyle("A1:{$lastColumn}1")->getFont()->setBold(true);
        $sheet->getStyle("A1:{$lastColumn}1")->getFill()
            ->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setARGB('FFE2EFDA');
        $sheet->getStyle("A2:{$lastColumn}2")->getFill()
            ->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setARGB('FFFFF2CC');
        $sheet->freezePane('A3');
    }

    private function collectListValues(array $definition): array
    {
        $values = [];

        foreach ($definition['validations'] ?? [] as $column => $options) {
            if ($options === 'kode_kelas_master') {
                $query = KodeKelas::query()->orderBy('kode_kelas');
                $values[$column] = ($column === 'kelas')
                    ? $query->pluck('rombel')->filter()->values()->all()
                    : $query->pluck('kode_kelas')->filter()->values()->all();

                if ($values[$column] === []) {
                    $values[$column] = ['X-IPA-1', 'X-IPS-1'];
                }
            } elseif ($options === 'kode_guru_master') {
                $values[$column] = Guru::query()
                    ->whereNotNull('kode_guru')
                    ->orderBy('kode_guru')
                    ->pluck('kode_guru')
                    ->filter()
                    ->values()
                    ->all();

                if ($values[$column] === []) {
                    $values[$column] = ['G001', 'G002'];
                }
            } elseif (is_array($options)) {
                $values[$column] = $options;
            }
        }

        return $values;
    }

    private function fillListSheet(\PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $sheet, array $listValues): void
    {
        $col = 1;

        foreach ($listValues as $name => $items) {
            $column = $this->columnLetter($col);
            $sheet->setCellValue("{$column}1", $name);

            foreach ($items as $index => $item) {
                $sheet->setCellValue("{$column}".($index + 2), $item);
            }

            $col++;
        }
    }

    private function applyValidations(
        \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $sheet,
        array $definition,
        array $listValues,
    ): void {
        $headers = $definition['headers'];
        $validations = $definition['validations'] ?? [];
        $listColumnMap = [];
        $listColIndex = 1;

        foreach (array_keys($listValues) as $name) {
            $listColumnMap[$name] = $this->columnLetter($listColIndex);
            $listColIndex++;
        }

        foreach ($headers as $colIndex => $header) {
            if (! isset($validations[$header])) {
                continue;
            }

            $column = $this->columnLetter($colIndex + 1);
            $options = $validations[$header];

            if ($options === 'kode_kelas_master' || $options === 'kode_guru_master') {
                $listColumn = $listColumnMap[$header];
                $count = count($listValues[$header] ?? []);
                $formula = "'_LIST'!\${$listColumn}\$2:\${$listColumn}\$".($count + 1);
            } else {
                $formula = '"'.implode(',', $options).'"';
            }

            for ($row = ImportModule::DATA_ROW_START; $row <= ImportModule::DATA_ROW_END; $row++) {
                $validation = $sheet->getCell("{$column}{$row}")->getDataValidation();
                $validation->setType(DataValidation::TYPE_LIST);
                $validation->setErrorStyle(DataValidation::STYLE_STOP);
                $validation->setAllowBlank(true);
                $validation->setShowDropDown(true);
                $validation->setFormula1($formula);
            }
        }

        if (in_array('tanggal_lahir', $headers, true)) {
            $colIndex = array_search('tanggal_lahir', $headers, true);
            $column = $this->columnLetter($colIndex + 1);

            for ($row = ImportModule::DATA_ROW_START; $row <= ImportModule::DATA_ROW_END; $row++) {
                $sheet->getStyle("{$column}{$row}")
                    ->getNumberFormat()
                    ->setFormatCode('yyyy-mm-dd');
            }
        }
    }

    private function columnLetter(int $index): string
    {
        $letter = '';

        while ($index > 0) {
            $index--;
            $letter = chr(65 + ($index % 26)).$letter;
            $index = intdiv($index, 26);
        }

        return $letter;
    }
}
