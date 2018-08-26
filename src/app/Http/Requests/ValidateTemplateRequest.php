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
        ];
    }
}
