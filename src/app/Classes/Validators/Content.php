<?php

namespace LaravelEnso\DataImport\app\Classes\Validators;

use LaravelEnso\Helpers\app\Classes\Obj;
use LaravelEnso\DataImport\app\Classes\Reader\Row;
use LaravelEnso\DataImport\app\Classes\Reader\Sheet;

class Content extends Validator
{
    public function run()
    {
        $this->sheets->each(function (Sheet $sheet) {
            $this->checkDuplicateLines($sheet);

            $sheet->rows()
                ->each(function ($row, $index) use ($sheet) {
                    $this->runLaravelValidations($sheet->name(), $row, $index);
                });

            $this->runUniqueInColumn($sheet->name());
            $this->runExistsInSheet($sheet->name());
        });
    }

    private function checkDuplicateLines(Sheet $sheet)
    {
        $sheet->rows()
            ->diffKeys($sheet->rows()->unique())
            ->keys()
            ->each(function ($index) use ($sheet) {
                $this->addIssue($sheet->name(), __('Doubled sheet lines'), $index + 2);
            });
    }

    private function runLaravelValidations(string $sheetName, Row $row, int $index)
    {
        $rules = $this->template->laravelValidations($sheetName);
        $validator = validator($row->toArray(), $rules);

        if ($validator->fails()) {
            foreach (array_keys($rules) as $column) {
                if ($validator->errors()->has($column)) {
                    foreach ($validator->errors()->get($column) as $category) {
                        $this->addIssue($sheetName, $category, $index + 2, $column, $row->$column);
                    }
                }
            }
        }
    }

    private function runUniqueInColumn(string $sheet)
    {
        $this->template->uniqueValueColumns($sheet)
            ->each(function ($column) use ($sheet) {
                $this->checkUniqueInColumn($sheet, $column);
            });
    }

    private function checkUniqueInColumn(string $sheet, string $column)
    {
        $values = $this->sheet($sheet)
            ->rows()
            ->pluck($column);

        $doubles = $values->diffKeys($values->unique());

        if ($doubles->isNotEmpty()) {
            $category = __(
                'Values must be unique in column ":column"',
                ['column' => $column]
            );

            $doubles->each(function ($value, $index) use ($sheet, $column, $category) {
                $this->addIssue($sheet, $category, $index + 2, $column, $value);
            });
        }
    }

    private function runExistsInSheet(string $sheet)
    {
        $this->template->existsInSheetColumns($sheet)
            ->each(function ($columnValidations) use ($sheet) {
                $columnValidations->each(function ($validation) use ($sheet) {
                    $this->checkExistsInSheet($sheet, $validation);
                });
            });
    }

    private function checkExistsInSheet(string $sheet, Obj $validation)
    {
        $values = $this->sheet($sheet)->rows()
            ->pluck($validation->column);

        $matchingValues = $this->sheet($validation->matchingSheet)->rows()
                ->pluck($validation->matchingColumn);

        $missingValues = $values->diff($matchingValues)->filter();

        if ($missingValues->isNotEmpty()) {
            $category = __(
                'Value must exist in the sheet :sheet on column :column',
                ['sheet' => $validation->matchingSheet, 'column' => $validation->matchingColumn]
            );

            $missingValues->each(function ($value, $index) use ($sheet, $category, $validation) {
                $this->addIssue($sheet, $category, $index + 2, $validation->column, $value);
            });
        }
    }

    private function addIssue(string $sheetName, string $category, int $rowNumber = null, string $column = null, $value = null)
    {
        $this->summary->addContentIssue(...func_get_args());
    }
}
