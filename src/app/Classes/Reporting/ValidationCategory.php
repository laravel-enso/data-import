<?php

namespace LaravelEnso\DataImport\app\Classes\Reporting;

class ValidationCategory
{
    public $name;
    public $issues;

    public function __construct(string $name)
    {
        $this->name = $name;
        $this->issues = collect();
    }

    public function addIssue($issue)
    {
        $this->issues->push($issue);
    }
}
