<?php

namespace LaravelEnso\DataImport\app\Services;

use LaravelEnso\Helpers\app\Classes\Obj;

class Summary
{
    private $filename;
    private $errors;

    public function __construct(string $filename)
    {
        $this->filename = $filename;
        $this->errors = new Obj();
    }

    public function toArray()
    {
        return [
            'filename' => $this->filename,
            'errors' => $this->errors,
        ];
    }

    public function addError(string $category, string $value)
    {
        $this->category($this->errors, $category)
            ->push($value);
    }

    public function errors()
    {
        return $this->errors;
    }

    public function hasErrors()
    {
        return $this->errors->isNotEmpty();
    }

    private function category(Obj $container, string $category)
    {
        if (! $container->has($category)) {
            $container->set($category, collect());
        }

        return $container->get($category);
    }
}
