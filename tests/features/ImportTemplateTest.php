<?php

use LaravelEnso\Core\app\Models\User;
use Tests\TestCase;
use Illuminate\Http\UploadedFile;
use LaravelEnso\TestHelper\app\Traits\SignIn;
use LaravelEnso\FileManager\app\Classes\FileManager;
use Illuminate\Foundation\Testing\RefreshDatabase;
use LaravelEnso\DataImport\app\Models\ImportTemplate;

class ImportTemplateTest extends TestCase
{
    use RefreshDatabase, SignIn;

    const Path = __DIR__.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR
        .'testFiles'.DIRECTORY_SEPARATOR;
    const TemplateFile = 'owners_import_file.xlsx';
    const TemplateTestFile = 'owners_import_test_file.xlsx';

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
    public function get_template()
    {
        $this->uploadTemplateFile();

        $this->get(route('import.getTemplate', ['owners'], false))
            ->assertStatus(200);
    }

    /** @test */
    public function upload_template()
    {
        $this->post(
            route('import.uploadTemplate', ['owners'], false),
            ['template' => $this->getTemplateUploadedFile()]
        )->assertStatus(201);

        $importTemplate = ImportTemplate::with('file')
            ->whereHas('file', function ($query) {
                $query->whereOriginalName(self::TemplateTestFile);
            })
            ->first();

        \Storage::assertExists(
            FileManager::TestingFolder.DIRECTORY_SEPARATOR.DIRECTORY_SEPARATOR.$importTemplate->file->saved_name
        );

        $this->assertNotNull($importTemplate);

        $this->cleanUp();
    }

    /** @test */
    public function download_template()
    {
        $importTemplate = $this->uploadTemplateFile();

        $this->get(route('import.downloadTemplate', [$importTemplate->id], false))
            ->assertStatus(200)
            ->assertHeader(
                'content-disposition',
                'attachment; filename='.self::TemplateTestFile
            );

        $this->cleanUp();
    }

    /** @test */
    public function delete_template()
    {
        $importTemplate = $this->uploadTemplateFile();

        \Storage::assertExists(
            FileManager::TestingFolder.DIRECTORY_SEPARATOR.$importTemplate->file->saved_name
        );

        $this->assertNotNull($importTemplate);

        $this->delete(route('import.deleteTemplate', [$importTemplate->id], false))
            ->assertStatus(200);

        $this->assertNull($importTemplate->fresh());

        \Storage::assertMissing(
            FileManager::TestingFolder.DIRECTORY_SEPARATOR.$importTemplate->file->saved_name
        );

        $this->cleanUp();
    }

    private function uploadTemplateFile()
    {
        $this->post(
            route('import.uploadTemplate', ['owners'], false),
            ['template' => $this->getTemplateUploadedFile()]
        );

        return ImportTemplate::with('file')
            ->whereHas('file', function ($query) {
                $query->whereOriginalName(self::TemplateTestFile);
            })
            ->first();
    }

    private function getTemplateUploadedFile()
    {
        \File::copy(
            self::Path.self::TemplateFile,
            self::Path.self::TemplateTestFile
        );

        return new UploadedFile(
            self::Path.self::TemplateTestFile,
            self::TemplateTestFile,
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
