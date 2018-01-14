<?php

namespace LaravelEnso\DataImport\app\Classes;

use LaravelEnso\Helpers\app\Classes\Obj;
use LaravelEnso\DataImport\app\Exceptions\TemplateException;

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
        $rules = new Obj();

        foreach ($this->getSheet($sheetName)->columns as $column) {
            if (property_exists($column, 'laravelValidations')) {
                $rules->{$column->name} = $column->laravelValidations;
            }
        }

        return $rules;
    }

    public function getUniqueValueColumns(string $sheetName)
    {
        $columns = collect();

        foreach ($this->getSheet($sheetName)->columns as $column) {
            if (property_exists($column, 'complexValidations')) {
                foreach ($column->complexValidations as $validation) {
                    if ($validation->type === 'unique_in_column') {
                        $columns->push($column->name);
                    }
                }
            }
        }

        return $columns;
    }

    public function getExistsInSheetColumns(string $sheetName)
    {
        $columns = collect();

        foreach ($this->getSheet($sheetName)->columns as $column) {
            if (property_exists($column, 'complexValidations')) {
                $column = $this->extractExistsInSheet($column);

                if ($column) {
                    $columns->push($column);
                }
            }
        }

        return $columns;
    }

    private function extractExistsInSheet($column)
    {
        $found = false;

        foreach ($column->complexValidations as $key => $validation) {
            if ($validation->type === 'exists_in_sheet') {
                $found = true;

                continue;
            }

            unset($column->complexValidations[$key]);
        }

        return $found ? $column : null;
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
            throw new TemplateException(__('Template is not readable'));
        }

        return $template;
    }
}
