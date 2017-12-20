<?php

namespace LaravelEnso\DataImport\app\Classes\Validators;

use Illuminate\Support\Collection;
use LaravelEnso\Helpers\Classes\Obj;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Collections\RowCollection;
use Maatwebsite\Excel\Collections\SheetCollection;
use LaravelEnso\DataImport\app\Classes\Reporting\Issue;
use LaravelEnso\DataImport\app\Classes\ImportConfiguration;
use LaravelEnso\DataImport\app\Classes\Reporting\ImportSummary;

class ContentValidator extends AbstractValidator
{
    protected $customValidator;

    public function __construct(ImportConfiguration $config, SheetCollection $sheets, ImportSummary $summary)
    {
        parent::__construct($config->getTemplate(), $sheets, $summary);

        $this->customValidator = $config->getCustomValidator($sheets, $summary);
    }

    public function run()
    {
        $this->sheets->each(function ($sheet) {
            $this->doValidations($sheet);
        });

        if (!is_null($this->customValidator)) {
            $this->customValidator->run();
        }
    }

    private function doValidations(RowCollection $sheet)
    {
        $this->doDuplicateLinesCheck($sheet);

        $laravelRules = $this->template->getLaravelValidationRules($sheet->getTitle());

        foreach ($sheet as $index => $row) {
            $this->doLaravelValidations($sheet->getTitle(), $laravelRules, $row, $index + 1);
        }

        $this->doUniqueInColumnValidation($sheet->getTitle());
        $this->doExistsInSheetValidation($sheet->getTitle());
    }

    private function doDuplicateLinesCheck($sheet)
    {
        $uniqueRows = $sheet->unique();
        $duplicateLines = $sheet->diffKeys($uniqueRows);
        $category = __(config('importing.validationLabels.duplicate_lines'));

        foreach ($duplicateLines->keys() as $rowNumber) {
            $this->addIssue($sheet->getTitle(), $category, $rowNumber + 2);
        }
    }

    private function doLaravelValidations(string $sheetName, Obj $rules, Collection $row, int $rowNumber)
    {
        $result = Validator::make($row->toArray(), $rules->toArray());

        if ($result->fails()) {
            foreach ($rules->keys() as $column) {
                if ($result->errors()->has($column)) {
                    foreach ($result->errors()->get($column) as $category) {
                        $this->addIssue($sheetName, $category, $rowNumber + 1, $column, $row->$column);
                    }
                }
            }
        }
    }

    private function doUniqueInColumnValidation(string $sheet)
    {
        $this->template->getUniqueValueColumns($sheet)
            ->each(function ($column) use ($sheet) {
                $values = $this->getSheet($sheet)->pluck($column)->each(function ($value) {
                    return trim($value);
                });

                $doubles = $values->diffKeys($values->unique());

                if ($doubles->isNotEmpty()) {
                    $category = __(config('importing.validationLabels.unique_in_column')).': '.$column;

                    $doubles->each(function ($value, $rowNumber) use ($sheet, $column, $category) {
                        $this->addIssue($sheet, $category, $rowNumber + 2, $column, $value);
                    });
                }
            });
    }

    private function doExistsInSheetValidation(string $sheet)
    {
        $this->template->getExistsInSheetColumns($sheet)
            ->each(function ($column) use ($sheet) {
                $values = $this->getSheet($sheet)->pluck($column->name);

                foreach ($column->complexValidations as $validation) {
                    $sourceValues = $this->getSheet($validation->sheet)->pluck($validation->column);
                    $missingValues = $values->diff($sourceValues)->filter();

                    if ($missingValues->isNotEmpty()) {
                        $category = config('importing.validationLabels.exists_in_sheet').': '
                        .$validation->sheet.', '.__('on column').': '.$validation->column;

                        $missingValues->each(function ($value, $rowNumber) use ($sheet, $category, $column) {
                            $this->addIssue($sheet, $category, $rowNumber + 2, $column->name, $value);
                        });
                    }
                }
            });
    }

    private function addIssue(string $sheetName, string $category, int $rowNumber = null, string $column = null, $value = null)
    {
        $issue = new Issue([
            'rowNumber' => $rowNumber,
            'column' => $column,
            'value' => $value,
        ]);

        $this->summary->addContentIssue($issue, $category, $sheetName);
    }
}
