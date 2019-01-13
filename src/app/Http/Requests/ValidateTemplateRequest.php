<?php

namespace LaravelEnso\DataImport\app\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use LaravelEnso\DataImport\app\Enums\ImportTypes;

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
            'type' => 'string|in:'.ImportTypes::keys()->implode(','),
        ];
    }
}
