<?php
/**
 * Created by PhpStorm.
 * User: mihai
 * Date: 21.02.2017
 * Time: 17:25
 */

namespace LaravelEnso\DataImport\app\Classes\Reporting;

use LaravelEnso\Helpers\Classes\Object;

class ValidationSummary extends Object
{
    public $hasErrors;
    public $structureIssues;
    public $sheetIssues;
    public $fileName = null;
    public $successfulEntries;

    public function __construct($fileName = null)
    {
        $this->fileName = $fileName ?: $this->fileName;
        $this->hasErrors       = false;
        $this->structureIssues = collect();
        $this->sheetIssues     = collect();
        $this->successfulEntries = 0;
    }

    public function addStructureIssue(string $category, $value, string $sheetName = '')
    {
        $issuesContainer = $this->findOrCreateStructureIssueContainer($sheetName);
        $issuesContainer->addIssue($category, $value);
        $this->hasErrors = true;
    }

    public function addContentIssue(string $sheetName, string $category, int $rowNumber, string $column, $value)
    {
        $issuesContainer = $this->findOrCreateSheetIssueContainer($sheetName);
        $issuesContainer->addIssue($category, $rowNumber, $column, $value);
        $this->hasErrors = true;
    }

    public function incSuccess()
    {
        $this->successfulEntries++;
    }

    /**
     * @param String $sheetName
     *
     * @return SheetIssuesContainer|mixed|null
     */
    private function findOrCreateSheetIssueContainer(string $sheetName)
    {
        $issuesContainer = $this->findSheetIssueContainer($sheetName);

        if (!$issuesContainer) {
            $issuesContainer = new SheetIssuesContainer($sheetName);
            $this->sheetIssues->push($issuesContainer);
        }

        return $issuesContainer;
    }

    /**
     * @param String $sheetName
     *
     * @return mixed|null
     */
    private function findSheetIssueContainer(string $sheetName)
    {
        $issueContainer = $this->sheetIssues->filter(function($container) use ($sheetName) {
            return $container->name === $sheetName;
        })->first();

        return $issueContainer ?: null;
    }

    private function findOrCreateStructureIssueContainer(string $sheetName)
    {
        $issuesContainer = $this->findStructureIssueContainer($sheetName);

        if (!$issuesContainer) {
            $issuesContainer = new StructureIssuesContainer($sheetName);
            $this->structureIssues->push($issuesContainer);
        }

        return $issuesContainer;
    }

    /**
     * @param String $sheetName
     *
     * @return mixed|null
     */
    private function findStructureIssueContainer(string $sheetName)
    {
        $issueContainer = $this->structureIssues->filter(function($container) use ($sheetName) {
            return $container->name === $sheetName;
        })->first();

        return $issueContainer ?: null;
    }
}
