<?php

namespace LaravelEnso\DataImport\app\Services;

use LaravelEnso\DataImport\app\Models\DataImport;
use LaravelEnso\DataImport\app\Services\Validators\Template as Validator;
use LaravelEnso\Helpers\app\Classes\JsonParser;
use LaravelEnso\Helpers\app\Classes\Obj;

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

    public function queue()
    {
        return $this->template->has('queue')
            ? $this->template->get('queue')
            : config('enso.imports.queues.processing');
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
                if ($column->has('validations')) {
                    $rules[$column->get('name')] = $column->get('validations');
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
        $class = $this->sheet($sheetName)->get('importerClass');

        return new $class;
    }

    public function customValidator($sheetName)
    {
        if ($this->sheet($sheetName)->has('validatorClass')) {
            $class = $this->sheet($sheetName)->get('validatorClass');

            return new $class;
        }
    }

    private function columns(string $sheetName)
    {
        return $this->sheet($sheetName)->get('columns');
    }

    private function sheet(string $sheetName)
    {
        return $this->sheets()
            ->first(function ($sheet) use ($sheetName) {
                return $sheet->get('name') === $sheetName;
            });
    }

    private function sheets()
    {
        return $this->template->get('sheets');
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

        return new Obj(
            (new JsonParser($path))->array()
        );
    }
}
