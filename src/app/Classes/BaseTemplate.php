<?php
/**
 * Created by PhpStorm.
 * User: mihai
 * Date: 22.02.2017
 * Time: 13:51.
 */

namespace LaravelEnso\DataImport\app\Classes;

use LaravelEnso\Helpers\Classes\AbstractObject;

abstract class BaseTemplate extends AbstractObject
{
    public $jsonTemplate = '{}';
    public $template = null;

    public function __construct()
    {
        $this->template = $this->parseTemplate();
    }

    public function getSheetNames()
    {
        $sheetNames = collect();

        foreach ($this->template->sheets as $sheet) {
            $sheetNames->push($sheet->name);
        }

        return $sheetNames;
    }

    public function getColumnsFromSheet($sheetName)
    {
        $columnNames = collect();
        $sheet = $this->getSheet($sheetName);

        foreach ($sheet->columns as $column) {
            $columnNames->push($column->name);
        }

        return $columnNames;
    }

    public function getSheet($sheetName)
    {
        foreach ($this->template->sheets as $sheet) {
            if ($sheet->name === $sheetName) {
                return $sheet;
            }
        }
    }

    public function getLaravelValidationRules($sheetName)
    {
        $rules = [];

        foreach ($this->getSheet($sheetName)->columns as $column) {
            if (property_exists($column, 'laravelValidations')) {
                $rules[$column->name] = $column->laravelValidations;
            }
        }

        return $rules;
    }

    public function getComplexValidationRules($sheetName)
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

    private function parseTemplate()
    {
        $template = json_decode($this->jsonTemplate);

        if (!$template) {
            throw new \EnsoException('Invalid template');
        }

        return $template;
    }

    // public function getColumnOfSheetByNames(String $sheetName, String $columnName)
    // {
    //     $sheet = $this->getSheet($sheetName);

    //     foreach ($sheet->columns as $column) {
    //         if ($column->name === $columnName) {
    //             return $column;
    //         }
    //     }
    // }

    // public function getColumnsOfSheetByName(String $sheetName)
    // {

    //     $sheet = $this->getSheet($sheetName);

    //     if ($sheet) {
    //         return $sheet->columns;
    //     }

    //     return null;
    // }

    // public function getSheetEntityByName(String $sheetName)
    // {

    //     $sheet = $this->getSheet($sheetName);

    //     if ($sheet) {
    //         return str_replace('|', '\\', $sheet->entity);
    //     }

    //     return null;
    // }

    // public function getRowsOffsetBySheetName(String $sheetName)
    // {

    //     $sheet = $this->getSheet($sheetName);

    //     if ($sheet) {
    //         return $sheet->dataRowsOffset;
    //     }

    //     return null;
    // }
}
