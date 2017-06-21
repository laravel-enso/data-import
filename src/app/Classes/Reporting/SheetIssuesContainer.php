<?php

namespace LaravelEnso\DataImport\app\Classes\Reporting;

class SheetIssuesContainer
{
    public $name;
    public $categories;

    public function __construct(string $name)
    {
        $this->name = $name;
        $this->categories = collect();
    }

    public function addIssue(string $category, int $rowNumber, string $column, $value)
    {
        $category = $this->findOrCreateCategory($category);
        $issue = new SheetIssue($rowNumber, $column, $value);
        $category->addIssue($issue);
    }

    /**
     * @param $category
     *
     * @return ValidationCategory|mixed|null
     */
    private function findOrCreateCategory(string $category)
    {
        $issueCategory = $this->findCategory($category);

        if (!$issueCategory) {
            $issueCategory = new ValidationCategory($category);
            $this->categories->push($issueCategory);
        }

        return $issueCategory;
    }

    /**
     * @param $category
     *
     * @return mixed|null
     */
    private function findCategory(string $category)
    {
        $foundCategory = $this->categories->filter(function ($existingCategory) use ($category) {
            return $existingCategory->name === $category;
        })->first();

        return $foundCategory ?: null;
    }
}
