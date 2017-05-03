<?php
/**
 * Created by PhpStorm.
 * User: mihai
 * Date: 22.02.2017
 * Time: 16:05.
 */

namespace LaravelEnso\DataImport\app\Classes\Validators;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Validator;
use LaravelEnso\DataImport\app\Classes\Reporting\ValidationSummary;
use LaravelEnso\DataImport\app\Enums\ComplexValidationTypesEnum;
use LaravelEnso\Helpers\Classes\Object;
use Maatwebsite\Excel\Collections\SheetCollection;

class ContentValidator extends AbstractValidator
{
    protected $template;
    protected $xlsx;
    protected $summary;
    protected $customValidator;

    public function __construct($template, SheetCollection $xlsx, ValidationSummary $summary, $customValidatorClass)
    {
        parent::__construct($template, $xlsx, $summary);
        $this->customValidator = $customValidatorClass ? new $customValidatorClass($this->template, $this->xlsx, $this->summary) : null;
    }

    public function run()
    {
        $this->xlsx->each(function ($sheet, $index) {
            $laravelRules = $this->template->getLaravelValidationRules($sheet->getTitle());
            $complexRules = $this->template->getComplexValidationRules($sheet->getTitle());

            $this->doDuplicateLinesCheck($sheet);

            foreach ($sheet as $index => $row) {

                $this->doLaravelValidations($sheet->getTitle(), $laravelRules, $row, $index + 1);
                $this->doComplexValidations($sheet->getTitle(), $complexRules, $row, $index + 1);
            }
        });

        if ($this->customValidator) {
            $this->customValidator->run();
        }
    }

    private function doLaravelValidations(string $sheetName, array $rules, Collection $row, int $rowNumber)
    {
        $result = Validator::make($row->toArray(), $rules);

        if ($result->fails()) {
            foreach (array_keys($rules) as $column) {
                if ($result->errors()->has($column)) {
                    foreach ($result->errors()->get($column) as $category) {
                        $this->summary->addContentIssue($sheetName, $category, $rowNumber, $column, $row->$column);
                    }
                }
            }
        }
    }

    /** Complex validation dispatcher. New complex validations should be added here, with corresponding
     * specific implementation methods below.
     *
     * @param $complexValidation
     * @param $cellValue
     *
     * @throws \Exception
     *
     * @return bool - true if valid
     */
    private function doComplexValidations(string $sheetName, Object $rules, Collection $row, int $rowNumber)
    {
        foreach ($row as $column => $value) {
            if (!property_exists($rules, $column)) {
                continue;
            }

            $complexValidationTypes = new ComplexValidationTypesEnum();

            foreach ($rules->$column as $rule) {
                $type = $complexValidationTypes->getValueByKey($rule->type);
                $this->dispatchComplexValidation($sheetName, $type, $rule, $column, $value, $rowNumber);
            }
        }
    }

    private function dispatchComplexValidation(string $sheetName, string $type, $rule, string $column, $value, int $rowNumber)
    {
        switch ($rule->type) {
            case 'exists_in_sheet':
                $this->checkIfExistsInSheet($sheetName, $type, $rule, $column, $value, $rowNumber);
                break;
            case 'unique_in_column':
                $this->checkIfIsUniqueInColumn($sheetName, $type, $rule, $column, $value, $rowNumber);
                break;
            case 'duplicate_rows':
                throw new \EnsoException('Row duplication check is applied automatically and should not be in the template');
            default:
                $errorMsg = 'Unsupported complex validation: '.$rule->type.' for sheet: '.$sheetName.', column: '.$column;
                throw new \EnsoException($errorMsg);
        }
    }

    private function checkIfExistsInSheet(string $sheetName, string $type, $rule, string $column, $value, int $rowNumber)
    {
        $details = ': '.$rule->sheet.'('.$rule->column.')';
        $sheet = $this->getSheet($sheetName);
        $exists = $sheet->pluck($rule->column)->contains($value);

        if (!$exists) {
            $this->summary->addContentIssue($sheetName, $type.$details, $rowNumber, $column, $value);
        }
    }

    private function checkIfIsUniqueInColumn(string $sheetName, string $type, $rule, string $column, $value, int $rowNumber)
    {
        if (!$value) {
            return;
        }

        $sheet = $this->getSheet($sheetName);

        $found = $sheet->pluck($column)->filter(function ($columnValue) use ($value) {
            return $value === $columnValue;
        });

        if ($found->count() > 1) {
            $this->summary->addContentIssue($sheetName, $type, $rowNumber, $column, $value);
        }
    }

    private function getSheet(string $sheetName)
    {
        return $this->xlsx->filter(function ($sheet) use ($sheetName) {
            return $sheet->getTitle() === $sheetName;
        })->first();
    }

    /** Checks for duplicate lines in a sheet  and adds a content issue for each duplicate line
     *
     * @param $sheet
     */
    private function doDuplicateLinesCheck($sheet) {

        $uniqueRows = $sheet->unique();
        $duplicateRows = $sheet->diffKeys($uniqueRows);

        $sheetTitle = $sheet->getTitle();
        $issueType = (new ComplexValidationTypesEnum())->getValueByKey('duplicate_rows');

        foreach ($duplicateRows as $rowNumber =>$row) {

            $this->summary->addContentIssue($sheetTitle, $issueType, $rowNumber, 'N/A', 'N/A');
        }
    }
}
