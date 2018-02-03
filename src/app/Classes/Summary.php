<?php

namespace LaravelEnso\DataImport\app\Classes;

use LaravelEnso\Helpers\app\Classes\Obj;

class Summary extends Obj
{
    public $filename;
    public $structureIssues;
    public $contentIssues;
    public $successful = 0;
    public $issues = 0;
    public $date;
    public $time;

    public function __construct(string $filename)
    {
        $this->filename = $filename;
        $this->structureIssues = new Obj();
        $this->contentIssues = new Obj();
        $this->date = now()->format(config('enso.config.phpDateFormat'));
        $this->time = now()->format('H:i');
    }

    public function rowsWithIssues(string $sheetName)
    {
        if (!property_exists($this->contentIssues, $sheetName)) {
            return collect();
        }

        return collect($this->contentIssues->$sheetName)
            ->reduce(function ($rows, $category) {
                collect($category)->each(function ($issue) use ($rows) {
                    $rows->push($issue->rowNumber);
                });

                return $rows;
            }, collect())->unique();
    }

    public function addStructureIssue(string $category, string $value)
    {
        $container = $this->categoryContainer($this->structureIssues, $category);
        $container->push($value);

        $this->incIssues();
    }

    public function addContentIssue(string $sheetName, string $category, int $rowNumber = null, string $column = null, $value = null)
    {
        $container = $this->sheetContainer($this->contentIssues, $sheetName);
        $container = $this->categoryContainer($container, $category);

        $issue = new Obj(compact('rowNumber', 'column', 'value'));

        $container->push($issue);

        $this->incIssues();
    }

    public function incSuccess()
    {
        $this->successful++;
    }

    private function incIssues()
    {
        $this->issues++;
    }

    public function successful()
    {
        return $this->successful;
    }

    public function issues()
    {
        return $this->issues;
    }

    public function hasIssues()
    {
        return $this->issues > 0;
    }

    public function hasStructureIssues()
    {
        return collect($this->structureIssues)->isNotEmpty();
    }

    public function hasContentIssues()
    {
        return collect($this->contentIssues)->isNotEmpty();
    }

    private function categoryContainer(Obj $container, string $category)
    {
        if (!property_exists($container, $category)) {
            $container->set($category, collect());
        }

        return $container->$category;
    }

    private function sheetContainer(Obj $container, string $sheetName)
    {
        if (!property_exists($container, $sheetName)) {
            $container->set($sheetName, new Obj());
        }

        return $container->$sheetName;
    }
}
