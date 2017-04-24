<?php
/**
 * Created by PhpStorm.
 * User: mihai
 * Date: 22.02.2017
 * Time: 12:37
 */

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
