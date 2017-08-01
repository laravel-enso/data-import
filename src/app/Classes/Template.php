<?php

namespace LaravelEnso\DataImport\app\Classes;

use LaravelEnso\Helpers\Classes\Object;

class Template
{
    private $template;

    public function __construct(string $jsonTemplate)
    {
        $this->template = $this->parseTemplate($jsonTemplate);
    }

    public function getSheetNames()
    {
        $sheetNames = collect();

        foreach ($this->template->sheets as $sheet) {
            $sheetNames->push($sheet->name);
        }

        return $sheetNames;
    }

    public function getColumnsFromSheet(string $sheetName)
    {
        $columnNames = collect();

        foreach ($this->getSheet($sheetName)->columns as $column) {
            $columnNames->push($column->name);
        }

        return $columnNames;
    }

    public function getLaravelValidationRules(string $sheetName)
    {
        $rules = new Object();

        foreach ($this->getSheet($sheetName)->columns as $column) {
            if (property_exists($column, 'laravelValidations')) {
                $rules->{$column->name} = $column->laravelValidations;
            }
        }

        return $rules;
    }

    public function getComplexValidationRules(string $sheetName)
    {
        $rules = new Object();

        foreach ($this->getSheet($sheetName)->columns as $column) {
            if (property_exists($column, 'complexValidations')) {
                $rules->{$column->name} = $this->getComplexValidationsForColumn($column);
            }
        }

        return $rules;
    }

    private function getComplexValidationsForColumn($column)
    {
        $rules = collect();

        foreach ($column->complexValidations as $validation) {
            $rules->push($validation);
        }

        return $rules;
    }

    private function getSheet(string $sheetName)
    {
        foreach ($this->template->sheets as $sheet) {
            if ($sheet->name === $sheetName) {
                return $sheet;
            }
        }
    }

    private function parseTemplate(string $jsonTemplate)
    {
        $template = json_decode($jsonTemplate);

        if (!$template) {
            throw new \EnsoException('Invalid template');
        }

        return $template;
    }
}
