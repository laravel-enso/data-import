<?php

namespace Tests;

use App\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use LaravelEnso\DataImport\app\Classes\Reporting\SheetIssuesContainer;
use LaravelEnso\DataImport\app\Classes\Reporting\ValidationSummary;

class ReportingSheetIssuesTest extends TestCase
{
    use DatabaseMigrations;

    private $user;
    private $fileName;
    /** @var ValidationSummary */
    private $validationSummary;

    protected function setUp()
    {
        parent::setUp();

        $this->user = User::first();
        $this->fileName = 'Test Filename.xlsx';
        $this->validationSummary = new ValidationSummary($this->fileName);
    }

    /** @test */
    public function can_add_structure_issue()
    {
        // Arrange
        $category = 'test category';
        $rowNumber = 1;
        $value = 'test value';
        $sheetName = 'test sheet name';
        $column = 'test column';

        // Act
        $this->validationSummary->addContentIssue($sheetName, $category, $rowNumber, $column, $value);

        // Assert
        // the issue should be in the proper structure
        $this->assertEquals(1, $this->validationSummary->sheetIssues->count());
        $this->assertInstanceOf(SheetIssuesContainer::class, $this->validationSummary->sheetIssues->first());

        $this->checkSheetIssuesContainer($sheetName);
        $this->checkCategory($category);
        $this->checkIssue($rowNumber, $column, $value);
    }

    private function checkSheetIssuesContainer($sheetName)
    {
        $sheetIssuesContainer = $this->validationSummary->sheetIssues->first();

        $this->assertEquals($sheetName, $sheetIssuesContainer->name);
        $this->assertEquals(1, $sheetIssuesContainer->categories->count());
    }

    private function checkCategory($category)
    {
        $validationCategory = $this->validationSummary->sheetIssues->first()
            ->categories->first();
        $this->assertEquals($category, $validationCategory->name);
        $this->assertEquals(1, $validationCategory->issues->count());
    }

    private function checkIssue($rowNumber, $column, $value)
    {
        $validationIssue = $this->validationSummary->sheetIssues->first()
            ->categories->first()
            ->issues->first();
        $this->assertEquals($rowNumber, $validationIssue->rowNumber);
        $this->assertEquals($column, $validationIssue->column);
        $this->assertEquals($value, $validationIssue->value);
    }
}
