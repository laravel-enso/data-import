<?php

use App\User;
use Tests\TestCase;
use Illuminate\Http\UploadedFile;
use LaravelEnso\TestHelper\app\Traits\SignIn;
use LaravelEnso\FileManager\app\Classes\FileManager;
use LaravelEnso\DataImport\app\Models\DataImport;
use Illuminate\Foundation\Testing\RefreshDatabase;

class DataImportTest extends TestCase
{
    use RefreshDatabase, SignIn;

    const Path = __DIR__.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR
        .'testFiles'.DIRECTORY_SEPARATOR;
    const ImportFile = 'owners_import_file.xlsx';
    const ImportTestFile = 'owners_import_test_file.xlsx';

    protected function setUp()
    {
        parent::setUp();

        // $this->withoutExceptionHandling();

        $this->seed()
            ->signIn(User::first());

        config(['enso.imports.configs.owners' => [
                'label' => 'Owners',
                'template' => 'vendor/laravel-enso/dataimport/src/resources/testing/owners.json',
            ]
        ]);
    }

    /** @test */
    public function index()
    {
        $this->get(route('import.index', [], false))
            ->assertStatus(200);
    }

    /** @test */
    public function getSummary()
    {
        $this->importOwnersFile();

        $dataImport = DataImport::whereName(self::ImportTestFile)
                        ->first();

        $this->get(route('import.getSummary', [$dataImport->id], false))
            ->assertStatus(200);
    }

    /** @test */
    public function can_import()
    {
        $uploadedFile = $this->getOwnersImportUploadedFile();

        $this->post(route('import.run', ['owners'], false), [
                'import' => $uploadedFile
            ])->assertStatus(200)
            ->assertJsonFragment(['successful' => 2]);

        $dataImport = DataImport::with('file')
                        ->whereName(self::ImportTestFile)
                        ->first();

        $this->assertNotNull($dataImport);

        $this->assertNotNull(
            config('enso.config.ownerModel')::whereName('ImportTestName')
                ->first()
        );

        \Storage::assertExists(
            FileManager::TestingFolder.DIRECTORY_SEPARATOR.$dataImport->file->saved_name
        );

        $this->cleanUp();
    }

    /** @test */
    public function download()
    {
        $this->importOwnersFile();

        $dataImport = DataImport::whereName(self::ImportTestFile)
                        ->first();

        $this->get(route('import.download', [$dataImport->id], false))
            ->assertStatus(200)
            ->assertHeader(
                'content-disposition',
                'attachment; filename='.self::ImportTestFile
            );

        $this->cleanUp();
    }

    /** @test */
    public function destroy()
    {
        $this->importOwnersFile();

        $dataImport = DataImport::with('file')
                        ->whereName(self::ImportTestFile)
                        ->first();

        $filename = FileManager::TestingFolder.DIRECTORY_SEPARATOR.$dataImport->file->saved_name;
        \Storage::assertExists($filename);

        $this->assertNotNull($dataImport);

        $this->delete(route('import.destroy', [$dataImport->id], false))
            ->assertStatus(200);

        $this->assertNull($dataImport->fresh());

        \Storage::assertMissing($filename);

        $this->cleanUp();
    }

    private function importOwnersFile()
    {
        $uploadedFile = $this->getOwnersImportUploadedFile();

        $this->post(route('import.run', ['owners'], false), [
            'import' => $uploadedFile
        ]);
    }

    private function getOwnersImportUploadedFile()
    {
        \File::copy(
            self::Path.self::ImportFile,
            self::Path.self::ImportTestFile
        );

        return new UploadedFile(
            self::Path.self::ImportTestFile,
            self::ImportTestFile,
            null,
            null,
            null,
            true
        );
    }

    private function cleanUp()
    {
        \Storage::deleteDirectory(FileManager::TestingFolder);
    }
}
