<?php

namespace LaravelEnso\DataImport\app\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use LaravelEnso\DataImport\app\Enums\ImportTypes;

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
            'type' => 'string|in:'.ImportTypes::keys()->implode(','),
        ];
    }
}
