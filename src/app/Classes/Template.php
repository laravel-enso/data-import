<?php

namespace LaravelEnso\DataImport\app\Classes;

use LaravelEnso\Helpers\app\Classes\Obj;

class Template
{
    private const MaxExecutionTime = 60;
    private const EntryLimit = 5000;
    private const stopsOnIssues = false;

    private $template;

    public function __construct(string $jsonTemplate)
    {
        $this->readTemplate($jsonTemplate);
    }

    public function maxExecutionTime()
    {
        return $this->template->maxExecutionTime ?? self::MaxExecutionTime;
    }

    public function importer()
    {
        return $this->template->importerClass;
    }

    public function validator()
    {
        return $this->template->validatorClass ?? null;
    }

    public function entryLimit()
    {
        return $this->template->entryLimit ?? self::EntryLimit;
    }

    public function stopsOnIssues()
    {
        return $this->template->stopsOnIssues ?? self::stopsOnIssues;
    }

    public function sheetNames()
    {
        return collect($this->template->sheets)
            ->pluck('name');
    }

    public function columns(string $sheetName)
    {
        return collect($this->sheet($sheetName)->columns)
            ->pluck('name');
    }

    public function laravelValidations(string $sheetName)
    {
        return collect($this->sheet($sheetName)->columns)
            ->reduce(function ($rules, $column) {
                if (property_exists($column, 'laravelValidations')) {
                    $rules[$column->name] = $column->laravelValidations;
                }

                return $rules;
            }, []);
    }

    public function uniqueValueColumns(string $sheetName)
    {
        return $this->columnsWithComplexValidation($sheetName, 'unique_in_column')
            ->pluck('name');
    }

    public function existsInSheetColumns(string $sheetName)
    {
        return $this->columnsWithComplexValidation($sheetName, 'exists_in_sheet')
            ->map(function ($column) {
                return $this->buildExistsInSheetValidationObject($column);
            });
    }

    private function columnsWithComplexValidation(string $sheetName, string $type)
    {
        return collect($this->sheet($sheetName)->columns)
            ->reduce(function ($columns, $column) use ($type) {
                if ($this->hasComplexValidation($column, $type)) {
                    $columns->push($column);
                }

                return $columns;
            }, collect());
    }

    private function hasComplexValidation(\stdClass $column, string $type)
    {
        return property_exists($column, 'complexValidations')
            && collect(explode('|', $column->complexValidations))
                ->first(function ($validation) use ($type) {
                    return strpos($validation, $type) >= 0;
                });
    }

    private function buildExistsInSheetValidationObject(\stdClass $column)
    {
        return collect(explode('|', $column->complexValidations))
            ->filter(function ($validation) {
                return strpos($validation, 'exists_in_sheet') === 0;
            })->values()->map(function ($validation) use ($column) {
                $args = explode(',', collect(explode(':', $validation))->last());

                return new Obj([
                    'column' => $column->name,
                    'matchingSheet' => $args[0],
                    'matchingColumn' => $args[1],
                ]);
            });
    }

    private function sheet(string $sheetName)
    {
        return collect($this->template->sheets)
            ->first(function ($sheet) use ($sheetName) {
                return $sheet->name === $sheetName;
            });
    }

    private function readTemplate(string $jsonTemplate)
    {
        $this->template = json_decode($jsonTemplate);
    }
}
