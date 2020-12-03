<?php

namespace LaravelEnso\DataImport\Services\Validators;

use Illuminate\Support\Facades\Validator;
use LaravelEnso\DataImport\Models\DataImport;
use LaravelEnso\DataImport\Services\DTOs\Row as DTO;
use LaravelEnso\Helpers\Services\Obj;

class Row
{
    public static function run(DTO $row, DataImport $import, string $sheet): void
    {
        $rules = $import->template()->columnRules($sheet);
        $implicit = Validator::make($row->content()->all(), $rules);
        $row->errors()->push(...$implicit->errors()->all());
        $custom = $import->template()->customValidator($sheet);

        if ($custom) {
            $custom->run($row->content(), $import->createdBy, new Obj($import->params));
            $row->errors()->push(...$custom->errors());
        }
    }
}
