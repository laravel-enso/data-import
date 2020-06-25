<?php

namespace LaravelEnso\DataImport\Services\Validators;

use Illuminate\Support\Collection;
use LaravelEnso\DataImport\Attributes\Column as ColumnAttributes;
use LaravelEnso\DataImport\Attributes\Sheet;
use LaravelEnso\DataImport\Attributes\Template as TemplateAttributes;
use LaravelEnso\DataImport\Contracts\Importable;
use LaravelEnso\DataImport\Exceptions\Template as Exception;
use LaravelEnso\Helpers\Classes\Obj;

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
        $diff = (new Collection(TemplateAttributes::Attributes))
            ->diff($this->template->keys());

        if ($diff->isNotEmpty()) {
            throw Exception::missingRootAttributes($diff->implode('", "'));
        }

        return $this;
    }

    private function sheets(): self
    {
        $this->template->get('sheets')
            ->each(fn ($sheet) => $this->sheetMandatory($sheet)
                ->sheetOptional($sheet)
                ->importer($sheet)
                ->validator($sheet));

        return $this;
    }

    private function sheetMandatory($sheet): self
    {
        $diff = (new Collection(Sheet::Mandatory))->diff($sheet->keys());

        if ($diff->isNotEmpty()) {
            throw Exception::missingSheetAttributes($diff->implode('", "'));
        }

        return $this;
    }

    private function importer($sheet): self
    {
        if (! class_exists($sheet->get('importerClass'))) {
            throw Exception::missingImporterClass($sheet);
        }

        if (! (new Collection(class_implements($sheet->get('importerClass'))))
            ->contains(Importable::class)) {
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

    private function sheetOptional($sheet): self
    {
        $diff = $sheet->keys()
            ->diff(Sheet::Mandatory)
            ->diff(Sheet::Optional);

        if ($diff->isNotEmpty()) {
            throw Exception::unknownSheetAttributes($diff->implode('", "'));
        }

        return $this;
    }

    private function columns(): void
    {
        $this->template->get('sheets')
            ->pluck('columns')->each(fn ($columns) => $columns
                ->each(fn ($column) => $this->columnMandatory($column)
                    ->columnOptional($column)));
    }

    private function columnMandatory($column): self
    {
        $diff = (new Collection(ColumnAttributes::Mandatory))->diff($column->keys());

        if ($diff->isNotEmpty()) {
            throw Exception::missingColumnAttributes($diff->implode('", "'));
        }

        return $this;
    }

    private function columnOptional($column)
    {
        $diff = $column->keys()
            ->diff(ColumnAttributes::Mandatory)
            ->diff(ColumnAttributes::Optional);

        if ($diff->isNotEmpty()) {
            throw Exception::unknownColumnAttributes($diff->implode('", "'));
        }
    }
}
