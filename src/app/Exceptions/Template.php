<?php

namespace LaravelEnso\DataImport\App\Exceptions;

use LaravelEnso\DataImport\App\Contracts\Importable;
use LaravelEnso\DataImport\App\Services\Validators\Validator;
use LaravelEnso\Helpers\App\Classes\Obj;
use LaravelEnso\Helpers\App\Exceptions\EnsoException;

class Template extends EnsoException
{
    public static function missingRootAttributes($attrs)
    {
        return new static(__(
            'Attribute(s) Missing in template: ":attrs"',
            ['attrs' => $attrs]
        ));
    }

    public static function missingSheetAttributes($attrs)
    {
        return new static(__(
            'Mandatory Attribute(s) Missing in sheet object: ":attrs"',
            ['attrs' => $attrs]
        ));
    }

    public static function unknownSheetAttributes($attrs)
    {
        return new static(__(
            'Unknown Optional Attribute(s) in sheet object: ":attr"',
            ['attrs' => $attrs]
        ));
    }

    public static function missingColumnAttributes($attrs)
    {
        return new static(__(
            'Mandatory Attribute(s) Missing in column object: ":attr"',
            ['attrs' => $attrs]
        ));
    }

    public static function unknownColumnAttributes($attrs)
    {
        return new static(__(
            'Unknown Attribute(s) found in column object: ":attr"',
            ['attrs' => $attrs]
        ));
    }

    public static function missingImporterClass(Obj $sheet)
    {
        return new static(__(
            'Importer class ":class" for sheet ":sheet" does not exist',
            ['class' => $sheet->get('validatorClass'), 'sheet' => $sheet->get('name')]
        ));
    }

    public static function importerMissingContract(Obj $sheet)
    {
        return new static(__(
            'Importer class ":class" for sheet ":sheet" must implement the ":contract" contract',
            ['class' => $sheet->get('importerClass'), 'contract' => Importable::class]
        ));
    }

    public static function missingValidatorClass(Obj $sheet)
    {
        return new static(__(
            'Validator class ":class" for sheet ":sheet" does not exist',
            ['class' => $sheet->get('validatorClass'), 'sheet' => $sheet->get('name')]
        ));
    }

    public static function incorectValidator(Obj $sheet)
    {
        return new static(__(
            'Validator class ":class" for sheet ":sheet" must extend ":validator" class', [
                'class' => $sheet->get('validatorClass'),
                'sheet' => $sheet->get('name'),
                'validator' => Validator::class,
            ]
        ));
    }
}
