<?php

namespace Tests;

use App\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use LaravelEnso\DataImport\app\Classes\Reporting\SheetIssuesContainer;
use LaravelEnso\DataImport\app\Classes\Reporting\ImportSummary;

class ReportingSheetIssuesTest extends TestCase
{
    use DatabaseMigrations;

    private $user;
    private $fileName;
    /** @var ImportSummary */
    private $ImportSummary;

    protected function setUp()
    {
        parent::setUp();

        $this->user = User::first();
        $this->fileName = 'Test Filename.xlsx';
        $this->ImportSummary = new ImportSummary($this->fileName);
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
        $this->ImportSummary->addContentIssue($sheetName, $category, $rowNumber, $column, $value);

        // Assert
        // the issue should be in the proper structure
        $this->assertEquals(1, $this->ImportSummary->sheetIssues->count());
        $this->assertInstanceOf(SheetIssuesContainer::class, $this->ImportSummary->sheetIssues->first());

        $this->checkSheetIssuesContainer($sheetName);
        $this->checkCategory($category);
        $this->checkIssue($rowNumber, $column, $value);
    }

    private function checkSheetIssuesContainer($sheetName)
    {
        $sheetIssuesContainer = $this->ImportSummary->sheetIssues->first();

        $this->assertEquals($sheetName, $sheetIssuesContainer->name);
        $this->assertEquals(1, $sheetIssuesContainer->categories->count());
    }

    private function checkCategory($category)
    {
        $CategoryContainer = $this->ImportSummary->sheetIssues->first()
            ->categories->first();
        $this->assertEquals($category, $CategoryContainer->name);
        $this->assertEquals(1, $CategoryContainer->issues->count());
    }

    private function checkIssue($rowNumber, $column, $value)
    {
        $validationIssue = $this->ImportSummary->sheetIssues->first()
            ->categories->first()
            ->issues->first();
        $this->assertEquals($rowNumber, $validationIssue->rowNumber);
        $this->assertEquals($column, $validationIssue->column);
        $this->assertEquals($value, $validationIssue->value);
    }
}
