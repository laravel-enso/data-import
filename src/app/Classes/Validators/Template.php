<?php

namespace LaravelEnso\DataImport\app\Classes\Validators;

use LaravelEnso\Helpers\app\Classes\Obj;
use LaravelEnso\DataImport\app\Contracts\Importable;
use LaravelEnso\DataImport\app\Classes\Attributes\Sheet;
use LaravelEnso\DataImport\app\Exceptions\TemplateException;
use LaravelEnso\DataImport\app\Classes\Attributes\Column as ColumnAttributes;
use LaravelEnso\DataImport\app\Classes\Attributes\Template as TemplateAttributes;

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
            throw new TemplateException(__(
                'Attribute(s) Missing in template: ":attr"',
                ['attr' => $diff->implode('", "')]
            ));
        }

        return $this;
    }

    private function sheetAttributes()
    {
        collect($this->template->get('sheets'))
            ->each(function ($sheet) {
                $this->sheetMandatory($sheet)
                    ->sheetOptional($sheet);
            });

        return $this;
    }

    private function sheetMandatory($sheet)
    {
        $diff = collect(Sheet::Mandatory)
            ->diff(collect($sheet)->keys());

        if ($diff->isNotEmpty()) {
            throw new TemplateException(__(
                'Mandatory Attribute(s) Missing in sheet object: ":attr"',
                ['attr' => $diff->implode('", "')]
            ));
        }

        $this->checkImporter($sheet);

        return $this;
    }

    private function checkImporter($sheet)
    {
        if (! class_exists($sheet->importerClass)) {
            throw new TemplateException(__(
                'Importer class ":class" for sheet ":sheet" does not exist',
                ['class' => $sheet->importerClass, 'sheet' => $sheet->name]
            ));
        }

        if (! collect(class_implements($sheet->importerClass))
            ->contains(Importable::class)) {
            throw new TemplateException(__(
                'Importer class ":class" for sheet ":sheet" must implement the ":contract" contract',
                ['class' => $sheet->importerClass, 'contract' => Importable::class]
            ));
        }
    }

    private function sheetOptional($sheet)
    {
        $diff = collect($sheet)->keys()
            ->diff(Sheet::Mandatory)
            ->diff(Sheet::Optional);

        if ($diff->isNotEmpty()) {
            throw new TemplateException(__(
                'Unknown Optional Attribute(s) in sheet object: ":attr"',
                ['attr' => $diff->implode('", "')]
            ));
        }
    }

    private function columnAttributes()
    {
        collect($this->template->get('sheets'))
            ->pluck('columns')->each(function ($columns) {
                collect($columns)->each(function ($column) {
                    $this->columnMandatory($column)
                        ->columnOptional($column);
                });
            });
    }

    private function columnMandatory($column)
    {
        $diff = collect(ColumnAttributes::Mandatory)
            ->diff(collect($column)->keys());

        if ($diff->isNotEmpty()) {
            throw new TemplateException(__(
                'Mandatory Attribute(s) Missing in column object: ":attr"',
                ['attr' => $diff->implode('", "')]
            ));
        }

        return $this;
    }

    private function columnOptional($column)
    {
        $diff = collect($column)->keys()
            ->diff(ColumnAttributes::Mandatory)
            ->diff(ColumnAttributes::Optional);

        if ($diff->isNotEmpty()) {
            throw new TemplateException(__(
                'Unknown Attribute(s) found in column object: ":attr"',
                ['attr' => $diff->implode('", "')]
            ));
        }
    }
}
