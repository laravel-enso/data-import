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
    protected $sheetEntriesLimit;

    public function __construct(ImportConfiguration $config, SheetCollection $sheets, ImportSummary $summary)
    {
        parent::__construct($config->getTemplate(), $sheets, $summary);

        $this->customValidator = $config->getCustomValidator();
        $this->sheetEntriesLimit = $config->getSheetEntriesLimit();
    }

    public function run()
    {
        $this->validateSheetEntriesLimit();

        $this->sheets->each(function ($sheet) {
            $this->doValidations($sheet);
        });

        if ($this->customValidator) {
            $this->customValidator->run();
        }
    }

    private function doValidations(RowCollection $sheet)
    {
        $this->doDuplicateLinesCheck($sheet);

        $laravelRules = $this->template->getLaravelValidationRules($sheet->getTitle());
        $complexRules = $this->template->getComplexValidationRules($sheet->getTitle());

        foreach ($sheet as $index => $row) {
            $this->doLaravelValidations($sheet->getTitle(), $laravelRules, $row, $index + 1);
            $this->doComplexValidations($sheet->getTitle(), $complexRules, $row, $index + 1);
        }
    }

    private function doDuplicateLinesCheck($sheet)
    {
        $uniqueRows = $sheet->unique();
        $duplicateLines = $sheet->diffKeys($uniqueRows);
        $category = __(config('importing.validationLabels.duplicate_lines'));

        foreach ($duplicateLines->keys() as $rowNumber) {
            $issue = $this->createIssue($category, $rowNumber + 2);
            $this->summary->addIssue($issue, $sheet->getTitle());
        }
    }

    private function doLaravelValidations(string $sheetName, Object $rules, Collection $row, int $rowNumber)
    {
        $result = Validator::make($row->toArray(), $rules->toArray());

        if ($result->fails()) {
            foreach ($rules->getProperties() as $column) {
                if ($result->errors()->has($column)) {
                    foreach ($result->errors()->get($column) as $category) {
                        $issue = $this->createIssue($category, $rowNumber + 1, $column, $row->$column);
                        $this->summary->addIssue($issue, $sheetName);
                    }
                }
            }
        }
    }

    private function doComplexValidations(string $sheetName, Object $rules, Collection $row, int $rowNumber)
    {
        foreach ($row as $column => $value) {
            if (!property_exists($rules, $column)) {
                continue;
            }

            foreach ($rules->$column as $rule) {
                $this->dispatchComplexValidation($sheetName, $rule, $column, $value, $rowNumber + 1);
            }
        }
    }

    private function dispatchComplexValidation(string $sheetName, \stdClass $rule, string $column, string $value, int $rowNumber)
    {
        if ($rule->type === 'exists_in_sheet') {
            return $this->checkIfExistsInSheet($sheetName, $rule, $column, $value, $rowNumber);
        }

        if ($rule->type === 'unique_in_column') {
            return $this->checkIfIsUniqueInColumn($sheetName, $column, $value, $rowNumber);
        }

        throw new \EnsoException(
            __("Unsupported complex validation").': '.$rule->type.' '.__("for sheet").': '.$sheetName.', '.__("column").': '.$column);
    }

    private function checkIfExistsInSheet(string $sheetName, \stdClass $rule, string $column, string $value, int $rowNumber)
    {
        $category = config('importing.validationLabels.exists_in_sheet').': '.$rule->sheet.'('.$rule->column.')';
        $sheet = $this->getSheet($rule->sheet);

        if ($sheet->pluck($rule->column)->contains($value)) {
            return true;
        }

        $issue = $this->createIssue($category, $rowNumber, $column, $value);
        $this->summary->addIssue($issue, $sheetName);
    }

    private function checkIfIsUniqueInColumn(string $sheetName, string $column, string $value, int $rowNumber)
    {
        if (!$value) {
            return true;
        }

        $category = config('importing.validationLabels.is_unique_in_column').': '.$column;
        $sheet = $this->getSheet($sheetName);

        $found = $sheet->pluck($column)->search(function ($columnValue) use ($value) {
            return $value === $columnValue;
        });

        if (!$found) {
            return true;
        }

        $issue = $this->createIssue($category, $rowNumber, $column, $value);
        $this->summary->addIssue($issue, $sheetName);
    }

    private function getSheet(string $sheetName)
    {
        return $this->sheets->filter(function ($sheet) use ($sheetName) {
            return $sheet->getTitle() === $sheetName;
        })->first();
    }

    private function validateSheetEntriesLimit()
    {
        $this->sheets->each(function ($sheet) {
            if ($sheet->count() > $this->sheetEntriesLimit) {
                $category = config('importing.validationLabels.sheet_entries_limit_exceded').': '.$this->sheetEntriesLimit;
                $issue = $this->createIssue($category, null, null, $sheet->count());
                $this->summary->addIssue($issue, $sheet->getTitle());
            }
        });
    }

    private function createIssue(string $category, int $rowNumber = null, string $column = null, $value = null)
    {
        return new Issue([
            'category' => $category,
            'rowNumber' => $rowNumber,
            'column' => $column,
            'value' => $value,
        ]);
    }
}
