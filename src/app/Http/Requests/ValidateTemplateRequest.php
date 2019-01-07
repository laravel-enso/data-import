<?php

namespace LaravelEnso\DataImport\app\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ValidateTemplateRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'template' => 'required|file',
            'type' => 'string|in:'.$this->types()
        ];
    }

    private function types()
    {
        return implode(',', array_keys(config('enso.imports.configs')));
    }
}
