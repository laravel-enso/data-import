<?php

namespace LaravelEnso\DataImport\app\Classes\Reporting;

use LaravelEnso\Helpers\Classes\AbstractObject;

class IssueContainer extends AbstractObject
{
    public $name;
    public $categories;

    public function __construct(string $name)
    {
        $this->name = $name;
        $this->categories = collect();
    }

    public function addIssue(Issue $issue)
    {
        $category = $this->findOrCreateCategory($issue->category);
        $category->addIssue($issue);
    }

    private function findOrCreateCategory(String $category)
    {
        $issueCategory = $this->findCategory($category);

        if (!$issueCategory) {
            $issueCategory = new CategoryContainer($category);
            $this->categories->push($issueCategory);
        }

        return $issueCategory;
    }

    private function findCategory(String $category)
    {
        return $this->categories->filter(function ($existingCategory) use ($category) {
            return $existingCategory->name === $category;
        })->first();
    }
}
