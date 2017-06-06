<?php

use App\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use LaravelEnso\DataImport\App\Http\Controllers\DataImportController;
use Symfony\Component\HttpFoundation\File\File;
use Tests\TestCase;

class DataImportControllerRunTest extends TestCase
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
    public function can_check_file_is_valid()
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

        //evaluate
        $respObject = json_decode($response->getContent());
        $this->assertFalse($respObject->summary->hasErrors);
        $this->assertEquals(5, $respObject->summary->successfulEntries);
    }

    public function createTempFile($path)
    {
        $tempFilePath = $this->basePath.'temp.xlsx';
        copy($path, $tempFilePath);

        return $tempFilePath;
    }
}
