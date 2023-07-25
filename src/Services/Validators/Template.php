<?php

namespace LaravelEnso\DataImport\Services\Validators;

use Illuminate\Support\Collection;
use LaravelEnso\DataImport\Attributes\Column;
use LaravelEnso\DataImport\Attributes\CSV;
use LaravelEnso\DataImport\Attributes\Sheet;
use LaravelEnso\DataImport\Attributes\Template as Attributes;
use LaravelEnso\DataImport\Contracts\Importable;
use LaravelEnso\DataImport\Exceptions\Template as Exception;
use LaravelEnso\Helpers\Services\Obj;

class Template
{
    public function __construct(private Obj $template)
    {
    }

    public function run(): void
    {
        $this->root()->sheets()->columns();
    }

    private function root(): self
    {
        if ($this->isCSV()) {
            $this->validateCSV($this->template);

            return $this;
        }

        (new Attributes())->validateMandatory($this->template->keys())
            ->rejectUnknown($this->template->keys());

        return $this;
    }

    private function sheets(): self
    {
        if ($this->isCSV()) {
            return $this;
        }

        $this->template->get('sheets')
            ->each(fn ($sheet) => $this->validateSheet($sheet));

        return $this;
    }

    private function validateSheet(Obj $sheet): void
    {
        (new Sheet())->validateMandatory($sheet->keys())
            ->rejectUnknown($sheet->keys());

        $this->importer($sheet)->validator($sheet);
    }

    private function validateCSV(Obj $template): void
    {
        (new CSV())->validateMandatory($template->keys())
            ->rejectUnknown($template->keys());

        $this->importer($template)->validator($template);
    }

    private function importer(Obj $sheet): self
    {
        if (! class_exists($sheet->get('importerClass'))) {
            throw Exception::missingImporterClass($sheet);
        }

        $implements = class_implements($sheet->get('importerClass'));
        $underContract = Collection::wrap($implements)->contains(Importable::class);

        if (! $underContract) {
            throw Exception::importerMissingContract($sheet);
        }

        return $this;
    }

    private function validator(Obj $sheet): void
    {
        if (! $sheet->has('validatorClass')) {
            return;
        }

        if (! class_exists($sheet->get('validatorClass'))) {
            throw Exception::missingValidatorClass($sheet);
        }

        if (! is_subclass_of($sheet->get('validatorClass'), Validator::class)) {
            throw Exception::incorectValidator($sheet);
        }
    }

    private function columns(): void
    {
        $validateColumn = fn ($column) => (new Column())
            ->validateMandatory($column->keys())
            ->rejectUnknown($column->keys());

        if ($this->isCSV()) {
            $columns = $this->template->get('columns');
        } else {
            $columns = $this->template->get('sheets')->pluck('columns');
        }

        $columns->each($validateColumn);
    }

    private function isCSV(): bool
    {
        return $this->template->has('fieldDelimiter')
            || ! $this->template->has('sheets');
    }
}
