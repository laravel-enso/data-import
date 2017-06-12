<?php

namespace Tests;

use App\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use LaravelEnso\DataImport\app\Classes\Reporting\StructureIssuesContainer;
use LaravelEnso\DataImport\app\Classes\Reporting\ValidationSummary;

class ReportingStructureIssuesTest extends TestCase
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
    public function test_summary_initialization()
    {
        // Assert
        $this->assertEquals($this->fileName, $this->validationSummary->fileName);
        $this->assertFalse($this->validationSummary->hasErrors);
        $this->assertEquals(0, $this->validationSummary->structureIssues->count());
        $this->assertEquals(0, $this->validationSummary->sheetIssues->count());
        $this->assertEquals(0, $this->validationSummary->successfulEntries);
    }

    public function test_success_counting()
    {
        $this->validationSummary->incSuccess();
        $this->assertEquals(1, $this->validationSummary->successfulEntries);
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
        $this->validationSummary->addStructureIssue($category, $value, $sheetName);

        // Assert
        // the issue should be in the proper structure
        $this->assertEquals(1, $this->validationSummary->structureIssues->count());
        $this->assertInstanceOf(StructureIssuesContainer::class, $this->validationSummary->structureIssues->first());

        $this->checkStructureIssuesContainer($sheetName);
        $this->checkCategory($category);
        $this->checkIssue($value);
    }

    private function checkStructureIssuesContainer($sheetName)
    {
        $structureIssuesContainer = $this->validationSummary->structureIssues->first();

        $this->assertEquals($sheetName, $structureIssuesContainer->name);
        $this->assertEquals(1, $structureIssuesContainer->categories->count());
    }

    private function checkCategory($category)
    {
        $validationCategory = $this->validationSummary->structureIssues->first()
            ->categories->first();
        $this->assertEquals($category, $validationCategory->name);
        $this->assertEquals(1, $validationCategory->issues->count());
    }

    private function checkIssue($value)
    {
        $validationIssue = $this->validationSummary->structureIssues->first()
            ->categories->first()
            ->issues->first();
        $this->assertEquals($value, $validationIssue->value);
    }
}
