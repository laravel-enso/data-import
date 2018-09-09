<?php

namespace LaravelEnso\DataImport\app\Classes\Validators;

use LaravelEnso\DataImport\app\Classes\Attributes\Sheet;
use LaravelEnso\DataImport\app\Exceptions\ConfigException;
use LaravelEnso\DataImport\app\Classes\Attributes\Column as ColumnAttributes;
use LaravelEnso\DataImport\app\Classes\Attributes\Template as TemplateAttributes;

class Template
{
    private $data;

    public function __construct(\stdClass $data)
    {
        $this->data = $data;
    }

    public function run()
    {
        $this->checkMandatoryAttributes()
            ->checkSheetAttributes()
            ->checkColumnAttributes()
            ->checkOptionalAttributes();
    }

    private function checkMandatoryAttributes()
    {
        $diff = collect(TemplateAttributes::Mandatory)
            ->diff(collect($this->data)->keys());

        if ($diff->isNotEmpty()) {
            throw new ConfigException(__(
                'Mandatory Attribute(s) Missing in template: ":attr"',
                ['attr' => $diff->implode('", "')]
            ));
        }

        return $this;
    }

    private function checkSheetAttributes()
    {
        collect($this->data->sheets)
            ->each(function ($sheet) {
                $diff = collect(Sheet::Attributes)
                    ->diff(collect($sheet)->keys());

                if ($diff->isNotEmpty()) {
                    throw new ConfigException(__(
                        'Mandatory Attribute(s) Missing in sheet object: ":attr"',
                        ['attr' => $diff->implode('", "')]
                    ));
                }
            });

        return $this;
    }

    private function checkColumnAttributes()
    {
        collect($this->data->sheets)
            ->pluck('columns')
            ->each(function ($columns) {
                collect($columns)
                    ->each(function ($column) {
                        $this->checkColumnMandatory($column)
                            ->checkColumnOptional($column);
                    });
            });

        return $this;
    }

    private function checkColumnMandatory($column)
    {
        $diff = collect(ColumnAttributes::Mandatory)
            ->diff(collect($column)->keys());

        if ($diff->isNotEmpty()) {
            throw new ConfigException(__(
                'Mandatory Attribute(s) Missing in column object: ":attr"',
                ['attr' => $diff->implode('", "')]
            ));
        }

        return $this;
    }

    private function checkColumnOptional($column)
    {
        $diff = collect($column)->keys()
            ->diff(
                collect(ColumnAttributes::Mandatory)
                    ->merge(ColumnAttributes::Optional)
            );

        if ($diff->isNotEmpty()) {
            throw new ConfigException(__(
                'Unknown Attribute(s) found in column object: ":attr"',
                ['attr' => $diff->implode('", "')]
            ));
        }

        return $this;
    }

    private function checkOptionalAttributes()
    {
        $attributes = collect(TemplateAttributes::Mandatory)
            ->merge(TemplateAttributes::Optional);

        $diff = collect($this->data)
            ->keys()
            ->diff($attributes);

        if ($diff->isNotEmpty()) {
            throw new ConfigException(__(
                'Unknown Attribute(s) Found in template: ":attr"',
                ['attr' => $diff->implode('", "')]
            ));
        }

        return $this;
    }
}
