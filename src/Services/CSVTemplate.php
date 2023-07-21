<?php

namespace LaravelEnso\DataImport\Services;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Config;
use LaravelEnso\DataImport\Contracts\Importable;
use LaravelEnso\DataImport\Services\Validators\Validator as CustomValidator;
use LaravelEnso\Helpers\Services\Obj;

class CSVTemplate extends Template
{
    public function header(): Collection
    {
        return $this->columns()->pluck('name');
    }

    public function descriptions(): Collection
    {
        return $this->columns()->pluck('description');
    }

    public function validations(): Collection
    {
        return $this->columns()->pluck('validations');
    }

    private function columns(): Obj
    {
        return $this->template->get('columns');
    }

    public function columnRules(): array
    {
        return $this->columnRules ??= $this->columns()
            ->filter(fn ($column) => $column->has('validations'))
            ->mapWithKeys(fn ($column) => [
                $column->get('name') => $column->get('validations'),
            ])->toArray();
    }

    public function chunkSize(): int
    {
        return $this->chunkSizes[$this->template->get('name')]
            ??= $this->template->has('chunkSize')
            ? $this->template->get('chunkSize')
            : (int) Config::get('enso.imports.chunkSize');
    }

    public function importer(): Importable
    {
        $class = $this->template->get('importerClass');

        return new $class();
    }

    public function customValidator(): ?CustomValidator
    {
        if ($this->template->has('validatorClass')) {
            $class = $this->template->get('validatorClass');

            return new $class();
        }

        return null;
    }
}
