<?php

namespace Tests;

use App\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use LaravelEnso\DataImport\app\Classes\Reporting\StructureIssuesContainer;
use LaravelEnso\DataImport\app\Classes\Reporting\ImportSummary;

class ReportingStructureIssuesTest extends TestCase
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
    public function test_summary_initialization()
    {
        // Assert
        $this->assertEquals($this->fileName, $this->ImportSummary->fileName);
        $this->assertFalse($this->ImportSummary->hasErrors);
        $this->assertEquals(0, $this->ImportSummary->structureIssues->count());
        $this->assertEquals(0, $this->ImportSummary->sheetIssues->count());
        $this->assertEquals(0, $this->ImportSummary->successfulEntries);
    }

    public function test_success_counting()
    {
        $this->ImportSummary->incSuccess();
        $this->assertEquals(1, $this->ImportSummary->successfulEntries);
    }

    /** @test */
    public function can_add_structure_issue()
    {
        // Arrange
        // a type that doesn't exist
        $category = 'test category';
        $value = 'test value';
        $sheetName = 'test sheet name';

        // Act
        $this->ImportSummary->addStructureIssue($category, $value, $sheetName);

        // Assert
        // the issue should be in the proper structure
        $this->assertEquals(1, $this->ImportSummary->structureIssues->count());
        $this->assertInstanceOf(StructureIssuesContainer::class, $this->ImportSummary->structureIssues->first());

        $this->checkStructureIssuesContainer($sheetName);
        $this->checkCategory($category);
        $this->checkIssue($value);
    }

    private function checkStructureIssuesContainer($sheetName)
    {
        $structureIssuesContainer = $this->ImportSummary->structureIssues->first();

        $this->assertEquals($sheetName, $structureIssuesContainer->name);
        $this->assertEquals(1, $structureIssuesContainer->categories->count());
    }

    private function checkCategory($category)
    {
        $CategoryContainer = $this->ImportSummary->structureIssues->first()
            ->categories->first();
        $this->assertEquals($category, $CategoryContainer->name);
        $this->assertEquals(1, $CategoryContainer->issues->count());
    }

    private function checkIssue($value)
    {
        $validationIssue = $this->ImportSummary->structureIssues->first()
            ->categories->first()
            ->issues->first();
        $this->assertEquals($value, $validationIssue->value);
    }
}
