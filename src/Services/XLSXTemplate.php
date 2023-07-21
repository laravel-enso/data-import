<?php

namespace LaravelEnso\DataImport\Services;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Config;
use LaravelEnso\DataImport\Contracts\Importable;
use LaravelEnso\DataImport\Services\Validators\Validator as CustomValidator;
use LaravelEnso\Helpers\Services\Obj;

class XLSXTemplate extends Template
{
    public function header(string $sheet): Collection
    {
        return $this->columns($sheet)->pluck('name');
    }

    public function descriptions(string $sheet): Collection
    {
        return $this->columns($sheet)->pluck('description');
    }

    public function validations(string $sheet): Collection
    {
        return $this->columns($sheet)->pluck('validations');
    }

    public function columnRules(string $sheet): array
    {
        return $this->columnRules ??= $this->columns($sheet)
            ->filter(fn ($column) => $column->has('validations'))
            ->mapWithKeys(fn ($column) => [
                $column->get('name') => $column->get('validations'),
            ])->toArray();
    }

    public function chunkSize(string $sheet): int
    {
        return $this->chunkSizes[$sheet]
            ??= $this->sheet($sheet)->has('chunkSize')
            ? $this->sheet($sheet)->get('chunkSize')
            : (int) Config::get('enso.imports.chunkSize');
    }

    public function importer(string $sheet): Importable
    {
        $class = $this->sheet($sheet)->get('importerClass');

        return new $class();
    }

    public function customValidator(string $sheet): ?CustomValidator
    {
        if ($this->sheet($sheet)->has('validatorClass')) {
            $class = $this->sheet($sheet)->get('validatorClass');

            return new $class();
        }

        return null;
    }

    public function sheets(): Obj
    {
        return $this->template->get('sheets');
    }

    public function nextSheet(string $name): ?Obj
    {
        $index = $this->sheets()->search(fn ($sheet) => $sheet->get('name') === $name);

        return $this->sheets()->get($index + 1);
    }

    private function columns(string $sheet): Obj
    {
        return $this->sheet($sheet)->get('columns');
    }

    private function sheet(string $name): Obj
    {
        return $this->sheets()
            ->first(fn ($sheet) => $sheet->get('name') === $name);
    }
}
