<?php

namespace LaravelEnso\DataImport\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use LaravelEnso\DataImport\Services\Options;

class ValidateImport extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'import' => 'required|file',
            'type' => 'string|in:'.implode(',', Options::types()),
        ];
    }
}
