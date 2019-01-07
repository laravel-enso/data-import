<?php

namespace LaravelEnso\DataImport\app\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ValidateImportRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'import' => 'required|file',
            'type' => 'string|in:'.$this->types()
        ];
    }

    private function types()
    {
        return implode(',', array_keys(config('enso.imports.configs')));
    }
}
