<?php

namespace LaravelEnso\DataImport\app\Classes;

use LaravelEnso\Helpers\app\Classes\Obj;

class Summary extends Obj
{
    public $filename;
    public $errors;

    public function __construct(string $filename)
    {
        $this->filename = $filename;
        $this->errors = new Obj();
    }

    public function addError(string $category, string $value)
    {
        $this->categoryContainer($this->errors, $category)
            ->push($value);
    }

    public function errors()
    {
        return $this->errors;
    }

    public function hasErrors()
    {
        return collect($this->errors)->isNotEmpty();
    }

    private function categoryContainer(Obj $container, string $category)
    {
        if (! property_exists($container, $category)) {
            $container->set($category, collect());
        }

        return $container->$category;
    }
}
