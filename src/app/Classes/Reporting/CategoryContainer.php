<?php

namespace LaravelEnso\DataImport\app\Classes\Reporting;

class CategoryContainer
{
    public $name;
    public $issues;

    public function __construct(string $name)
    {
        $this->name = $name;
        $this->issues = collect();
    }

    public function addIssue(Issue $issue)
    {
        $this->issues->push($issue);
    }
}
