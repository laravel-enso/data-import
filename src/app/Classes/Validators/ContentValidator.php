<?php

namespace LaravelEnso\DataImport\app\Classes\Validators;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Validator;
use LaravelEnso\DataImport\app\Classes\ImportConfiguration;
use LaravelEnso\DataImport\app\Classes\Reporting\ImportSummary;
use LaravelEnso\DataImport\app\Classes\Reporting\Issue;
use LaravelEnso\Helpers\Classes\Object;
use Maatwebsite\Excel\Collections\RowCollection;
use Maatwebsite\Excel\Collections\SheetCollection;

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
        // $this->doExistsInSheetValidation($sheet->getTitle());
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

    private function doLaravelValidations(string $sheetName, Object $rules, Collection $row, int $rowNumber)
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
        $columns = $this->template->getUniqueValueColumns($sheet);

        $columns->each(function ($column) use ($sheet) {
            $category = __(config('importing.validationLabels.unique_in_column')).': '.$column;
            $values = $this->getSheet($sheet)->pluck($column)->each(function ($value) {
                return trim($value);
            });

            $uniqueValues = $values->unique();
            $doubles = $values->diffKeys($uniqueValues);

            $doubles->each(function ($value, $rowNumber) use ($sheet, $column, $category) {
                $this->addIssue($sheet, $category, $rowNumber + 1, $column, $value);
            });
        });
    }

    // private function doExistsInSheetValidation(string $sheetName, \stdClass $rule, string $column, string $value, int $rowNumber)
    // {
    //     $category = config('importing.validationLabels.exists_in_sheet').': '
    //         .$rule->sheet.', '.__('on column').': '.$rule->column;

    //     $sheet = $this->getSheet($rule->sheet);

    //     if ($sheet->pluck($rule->column)->contains($value)) {
    //         return true;
    //     }

    //     $this->addIssue($sheetName, $category, $rowNumber, $column, $value);
    // }

    private function addIssue(string $sheetName, string $category, int $rowNumber = null, string $column = null, $value = null)
    {
        $issue = new Issue([
            'rowNumber' => $rowNumber,
            'column'    => $column,
            'value'     => $value,
        ]);

        $this->summary->addContentIssue($issue, $category, $sheetName);
    }
}
