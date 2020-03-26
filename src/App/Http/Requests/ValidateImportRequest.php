<?php

namespace LaravelEnso\DataImport\App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use LaravelEnso\DataImport\App\Enums\ImportTypes;

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
