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
    private $testFilePath;
    private $basePath;

    protected function setUp()
    {
        parent::setUp();

        $this->user = User::first();
        $this->diController = new DataImportController();
        $this->testFilePath = __DIR__.'/../testFiles/example_import_file.xlsx';
        $this->basePath = __DIR__.'/../testFiles/';
    }

    /** @test */
    public function can_get_import_summary()
    {
        $this->be($this->user);
        $testSummary = '{"fileName": "test.xlsx", "hasErrors": false, "sheetIssues": [], "structureIssues": [], "successfulEntries": 1}';

        $dataImport = DataImport::create([
            'type'          => 1,
            'original_name' => 'Test Original Name.pdf',
            'saved_name'    => 'testSavedName.pdf',
            'comment'       => 'test comment',
            'summary'       => $testSummary,
        ]);

        $response = $this->diController->getSummary($dataImport);
        $this->assertContains('summary', array_keys($response));
        $this->assertEquals($testSummary, $response['summary']);
    }

    /** @test */
    public function can_import()
    {
        $response = $this->createImport();

        //evaluate
        $respObject = json_decode($response->getContent());
        $this->assertFalse($respObject->summary->hasErrors);
        $this->assertEquals(5, $respObject->summary->successfulEntries);
    }

    /** @test */
    public function can_destroy_import()
    {
        $this->createImport();

        \Log::debug(DataImport::all());

        $dataImport = DataImport::find(1);
        $result = $this->diController->destroy($dataImport);

        $this->assertEquals('success', $result->level);
        $this->assertEquals(0, $result->errors->count());
        $this->assertNull(DataImport::find($dataImport->id));
    }

    private function createTempFile($path)
    {
        $randomPrefix = mt_rand(100, 1000);
        $tempFilePath = $this->basePath.$randomPrefix.'temp.xlsx';
        copy($path, $tempFilePath);

        return $tempFilePath;
    }

    private function createImport()
    {
        $temporaryFilePath = $this->createTempFile($this->testFilePath);

        //setup
        $name = 'original_file_name.xlsx';
        $path = $temporaryFilePath;
        $mime = 'application/vnd.ms-excel';
        //this file gets automatically deleted at the end of the test by Laravel
        $file = new \Illuminate\Http\UploadedFile($path, $name, $mime, filesize($path), null, true);

        //run
        $this->be($this->user);
        $response = $this->call('POST', 'import/run', ['type' => 0, 'enctype'=>'multipart/form-data', 'error'=>0], [], ['file_0' => $file]);

        return $response;
    }
}
