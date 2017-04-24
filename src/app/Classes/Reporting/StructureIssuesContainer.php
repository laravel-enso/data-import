<?php
/**
 * Created by PhpStorm.
 * User: mihai
 * Date: 22.02.2017
 * Time: 12:37.
 */

namespace LaravelEnso\DataImport\app\Classes\Reporting;

class StructureIssuesContainer
{
    public $name;
    public $categories;

    public function __construct(string $name)
    {
        $this->name = $name;
        $this->categories = collect();
    }

    public function addIssue(string $category, string $value)
    {
        $category = $this->findOrCreateCategory($category);
        $issue = new StructureIssue($value);
        $category->addIssue($issue);
    }

    private function findOrCreateCategory(String $category)
    {
        $issueCategory = $this->findCategory($category);

        if (!$issueCategory) {
            $issueCategory = new ValidationCategory($category);
            $this->categories->push($issueCategory);
        }

        return $issueCategory;
    }

    private function findCategory(String $category)
    {
        $foundCategory = $this->categories->filter(function ($existingCategory) use ($category) {
            return $existingCategory->name === $category;
        })->first();

        return $foundCategory ?: null;
    }
}
