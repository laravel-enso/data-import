<?php

namespace LaravelEnso\DataImport\app\Classes\Reporting;

use Carbon\Carbon;
use LaravelEnso\Helpers\Classes\AbstractObject;

class ImportSummary extends AbstractObject
{
    public $hasStructureErrors;
    public $hasContentErrors;
    public $issues;
    public $fileName;
    public $successful;
    public $errors;
    public $date;
    public $time;

    public function __construct(string $fileName)
    {
        $this->fileName = $fileName;
        $this->hasStructureErrors = false;
        $this->hasContentErrors = false;
        $this->issues = collect();
        $this->successful = 0;
        $this->errors = 0;
        $this->date = Carbon::now()->format(config('enso.config.phpDateFormat'));
        $this->time = Carbon::now()->format('H:i');
    }

    public function getRowsWithIssues(string $sheetName)
    {
        $rows = collect();

        $sheetIssues = $this->issues->filter(function ($sheet) use ($sheetName) {
            return $sheet->name === $sheetName;
        })->first();

        if (!$sheetIssues) {
            return $rows;
        }

        foreach ($sheetIssues->categories as $category) {
            foreach ($category->issues as $issue) {
                $rows->push($issue->rowNumber);
            }
        }

        return $rows->unique();
    }

    public function addStructureIssue(Issue $issue, string $category)
    {
        $this->hasStructureErrors = true;
        $this->addIssue($issue, $category, __(config('enso.importing.validationLabels.structure_issues')));
    }

    public function addContentIssue(Issue $issue, string $category, string $sheetName)
    {
        $this->hasContentErrors = true;
        $this->addIssue($issue, $category, $sheetName);
    }

    public function getSuccessfulCount()
    {
        return $this->successful;
    }

    public function getErrorCount()
    {
        return $this->errors;
    }

    private function addIssue(Issue $issue, string $category, string $sheetName = '')
    {
        $issuesContainer = $this->findOrCreateIssueContainer($sheetName);
        $issuesContainer->addIssue($issue, $category);
        $this->errors++;
    }

    public function incSuccess()
    {
        $this->successful++;
    }

    public function hasErrors()
    {
        return $this->hasStructureErrors || $this->hasContentErrors;
    }

    public function hasStructureErrors()
    {
        return $this->hasStructureErrors;
    }

    public function hasContentErrors()
    {
        return $this->hasContentErrors;
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
