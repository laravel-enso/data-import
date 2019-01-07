<?php

namespace LaravelEnso\DataImport\app\Classes;

use LaravelEnso\Helpers\app\Classes\Obj;

class Summary extends Obj
{
    public $filename;
    public $issues;

    public function __construct(string $filename)
    {
        $this->filename = $filename;
        $this->issues = new Obj();
    }

    public function addIssue(string $category, string $value)
    {
        $this->categoryContainer($this->issues, $category)
            ->push($value);
    }

    public function issues()
    {
        return $this->issues;
    }

    public function hasIssues()
    {
        return collect($this->issues)->isNotEmpty();
    }

    private function categoryContainer(Obj $container, string $category)
    {
        if (! property_exists($container, $category)) {
            $container->set($category, collect());
        }

        return $container->$category;
    }
}
