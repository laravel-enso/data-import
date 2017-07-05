<?php

namespace LaravelEnso\DataImport\app\Classes\Reporting;

use LaravelEnso\Helpers\Classes\AbstractObject;

class ImportSummary extends AbstractObject
{
    public $hasErrors;
    public $issues;
    public $fileName;
    public $successfulEntries;

    public function __construct(string $fileName)
    {
        $this->fileName = $fileName;
        $this->hasErrors = false;
        $this->issues = collect();
        $this->successfulEntries = 0;
    }

    public function addIssue(Issue $issue, string $sheetName = '')
    {
        $issuesContainer = $this->findOrCreateIssueContainer($sheetName);
        $issuesContainer->addIssue($issue);
        $this->hasErrors = true;
    }

    public function incSuccess()
    {
        $this->successfulEntries++;
    }

    public function hasErrors()
    {
        return $this->hasErrors;
    }

    private function findOrCreateIssueContainer(string $sheetName)
    {
        $issuesContainer = $this->findIssueContainer($sheetName);

        if (!$issuesContainer) {
            $issuesContainer = new IssueContainer($sheetName);
            $this->issues->push($issuesContainer);
        }

        return $issuesContainer;
    }

    private function findIssueContainer(string $sheetName)
    {
        return $this->issues->filter(function ($container) use ($sheetName) {
            return $container->name === $sheetName;
        })->first();
    }
}
