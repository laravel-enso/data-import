<?php

namespace LaravelEnso\DataImport\app\Services\Validators;

use LaravelEnso\DataImport\app\Attributes\Column as ColumnAttributes;
use LaravelEnso\DataImport\app\Attributes\Sheet;
use LaravelEnso\DataImport\app\Attributes\Template as TemplateAttributes;
use LaravelEnso\DataImport\app\Contracts\Importable;
use LaravelEnso\DataImport\app\Exceptions\TemplateException;
use LaravelEnso\Helpers\app\Classes\Obj;

class Template
{
    private $template;

    public function __construct(Obj $template)
    {
        $this->template = $template;
    }

    public function handle()
    {
        $this->rootAttributes()
            ->sheetAttributes()
            ->columnAttributes();
    }

    private function rootAttributes()
    {
        $diff = collect(TemplateAttributes::Attributes)
            ->diff($this->template->keys());

        if ($diff->isNotEmpty()) {
            throw TemplateException::missingRootAttributes($diff->implode('", "'));
        }

        return $this;
    }

    private function sheetAttributes()
    {
        $this->template->get('sheets')
            ->each(function ($sheet) {
                $this->sheetMandatory($sheet)
                    ->sheetOptional($sheet)
                    ->importer($sheet)
                    ->validator($sheet);
            });

        return $this;
    }

    private function sheetMandatory($sheet)
    {
        $diff = collect(Sheet::Mandatory)
            ->diff($sheet->keys());

        if ($diff->isNotEmpty()) {
            throw TemplateException::missingSheetAttributes($diff->implode('", "'));
        }

        return $this;
    }

    private function importer($sheet)
    {
        if (! class_exists($sheet->get('importerClass'))) {
            throw TemplateException::missingImporterClass($sheet);
        }

        if (! collect(class_implements($sheet->get('importerClass')))
            ->contains(Importable::class)) {
            throw TemplateException::importerMissingContract($sheet);
        }

        return $this;
    }

    private function validator($sheet)
    {
        if (! $sheet->has('validatorClass')) {
            return;
        }

        if (! class_exists($sheet->get('validatorClass'))) {
            throw TemplateException::missingValidatorClass($sheet);
        }

        if (! is_subclass_of($sheet->get('validatorClass'), Validator::class)) {
            throw TemplateException::incorectValidator($sheet);
        }
    }

    private function sheetOptional($sheet)
    {
        $diff = $sheet->keys()
            ->diff(Sheet::Mandatory)
            ->diff(Sheet::Optional);

        if ($diff->isNotEmpty()) {
            throw TemplateException::unknownSheetAttributes($diff->implode('", "'));
        }

        return $this;
    }

    private function columnAttributes()
    {
        $this->template->get('sheets')
            ->pluck('columns')->each(function ($columns) {
                $columns->each(function ($column) {
                    $this->columnMandatory($column)
                        ->columnOptional($column);
                });
            });
    }

    private function columnMandatory($column)
    {
        $diff = collect(ColumnAttributes::Mandatory)
            ->diff($column->keys());

        if ($diff->isNotEmpty()) {
            throw TemplateException::missingColumnAttributes($diff->implode('", "'));
        }

        return $this;
    }

    private function columnOptional($column)
    {
        $diff = $column->keys()
            ->diff(ColumnAttributes::Mandatory)
            ->diff(ColumnAttributes::Optional);

        if ($diff->isNotEmpty()) {
            throw TemplateException::unknownColumnAttributes($diff->implode('", "'));
        }
    }
}
