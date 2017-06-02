<?php

use App\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use LaravelEnso\DataImport\App\Http\Controllers\DataImportController;
use LaravelEnso\DataImport\app\Models\DataImport;
use Tests\TestCase;

class DataImportControllerTest extends TestCase
{
    use DatabaseMigrations;

    private $user;
    /** @var DataImportController */
    private $diController;

    protected function setUp()
    {
        parent::setUp();

        $this->user = User::first();
        $this->diController = new DataImportController();
    }

    /** @test */
    public function can_delete_data_import()
    {
        // Arrange
        // authenticate as first user
        $this->be($this->user);
        $dataImport = DataImport::create([
            'type' => 1,
            'original_name' => 'Test Original Name.pdf',
            'saved_name' => 'testSavedName.pdf',
            'comment' => 'test comment',
            'summary' => '{}'
        ]);

        // Act
        $result = $this->diController->destroy($dataImport);

        // Assert
        // deletion was successful, without errors
        $this->assertEquals('success', $result->level);
        $this->assertEquals(0, $result->errors->count());
        $this->assertNull(DataImport::find($dataImport->id));
    }

    /** @test */
    public function can_get_import_summary() {

        $this->be($this->user);
        $testSummary = '{"fileName": "test.xlsx", "hasErrors": false, "sheetIssues": [], "structureIssues": [], "successfulEntries": 1}';

        $dataImport = DataImport::create([
            'type' => 1,
            'original_name' => 'Test Original Name.pdf',
            'saved_name' => 'testSavedName.pdf',
            'comment' => 'test comment',
            'summary' => $testSummary
        ]);


        $response = $this->diController->getSummary($dataImport);
        $this->assertContains('summary', array_keys($response));
        $this->assertEquals($testSummary, $response['summary']);
    }
}
