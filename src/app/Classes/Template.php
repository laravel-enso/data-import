<?php

namespace LaravelEnso\DataImport\app\Classes;

use LaravelEnso\Helpers\app\Classes\Obj;
use LaravelEnso\Helpers\app\Classes\JsonParser;
use LaravelEnso\DataImport\app\Models\DataImport;
use LaravelEnso\DataImport\app\Classes\Validators\Template as Validator;

class Template
{
    private $template;

    public function __construct(DataImport $dataImport)
    {
        $this->template = $this->template($dataImport);

        if ($this->shouldValidate()) {
            $this->validate();
        }
    }

    public function timeout()
    {
        return $this->template->has('timeout')
            ? $this->template->get('timeout')
            : config('enso.imports.timeout');
    }

    public function sheetNames()
    {
        return $this->sheets()->pluck('name');
    }

    public function header(string $sheetName)
    {
        return $this->columns($sheetName)->pluck('name');
    }

    public function validationRules(string $sheetName)
    {
        return $this->columns($sheetName)
            ->reduce(function ($rules, $column) {
                if (property_exists($column, 'validations')) {
                    $rules[$column->name] = $column->validations;
                }

                return $rules;
            }, []);
    }

    public function chunkSize($sheetName)
    {
        return $this->sheet($sheetName)->has('chunkSize')
            ? $this->sheet($sheetName)->get('chunkSize')
            : config('enso.imports.chunkSize');
    }

    public function importer($sheetName)
    {
        $importerClass = $this->sheet($sheetName)->get('importerClass');

        return new $importerClass;
    }

    public function customValidator($sheetName)
    {
        if (! $this->sheet($sheetName)->has('validatorClass')) {
            return;
        }

        $validatorClass = $this->sheet($sheetName)->get('validatorClass');

        return new $validatorClass;
    }

    private function columns(string $sheetName)
    {
        return collect($this->sheet($sheetName)->get('columns'));
    }

    private function sheet(string $sheetName)
    {
        return $this->sheets()
            ->first(function ($sheet) use ($sheetName) {
                return $sheet->name === $sheetName;
            });
    }

    private function sheets()
    {
        return collect($this->template->get('sheets'));
    }

    private function validate()
    {
        (new Validator($this->template))
            ->handle();
    }

    private function shouldValidate()
    {
        return ! app()->environment('production')
            || config('enso.imports.validations') === 'always';
    }

    private function template(DataImport $dataImport)
    {
        $path = base_path(config(
            'enso.imports.configs.'.$dataImport->type.'.template'
        ));

        $template = (new JsonParser($path))
            ->object();

        return new Obj($template);
    }
}
