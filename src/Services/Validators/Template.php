<?php

namespace LaravelEnso\DataImport\Services\Validators;

use Illuminate\Support\Collection;
use LaravelEnso\DataImport\Attributes\Column as Column;
use LaravelEnso\DataImport\Attributes\Sheet;
use LaravelEnso\DataImport\Attributes\Template as Attributes;
use LaravelEnso\DataImport\Contracts\Importable;
use LaravelEnso\DataImport\Exceptions\Template as Exception;
use LaravelEnso\Helpers\Services\Obj;

class Template
{
    private Obj $template;

    public function __construct(Obj $template)
    {
        $this->template = $template;
    }

    public function run(): void
    {
        $this->root()
            ->sheets()
            ->columns();
    }

    private function root(): self
    {
        (new Attributes())->validateMandatory($this->template->keys());

        return $this;
    }

    private function sheets(): self
    {
        $this->template->get('sheets')
            ->each(fn ($sheet) => (new Sheet())
                ->validateMandatory($sheet->keys())
                ->rejectUnknown($sheet->keys()))
            ->each(fn ($sheet) => $this->importer($sheet)
                ->validator($sheet));

        return $this;
    }

    private function importer($sheet): self
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

    private function validator($sheet): void
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
        $this->template->get('sheets')
            ->pluck('columns')->each(fn ($columns) => $columns
                ->each(fn ($column) => (new Column())
                    ->validateMandatory($column->keys())
                    ->rejectUnknown($column->keys())));
    }
}
