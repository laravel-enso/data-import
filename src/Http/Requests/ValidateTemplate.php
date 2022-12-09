<?php

namespace LaravelEnso\DataImport\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use LaravelEnso\DataImport\Services\Options;

class ValidateTemplate extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'template' => 'required|file',
            'type' => 'string|in:'.implode(',', Options::types()),
        ];
    }
}
