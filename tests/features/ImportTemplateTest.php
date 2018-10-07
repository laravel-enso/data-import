<?php

use Tests\TestCase;
use Illuminate\Http\UploadedFile;
use LaravelEnso\Core\app\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use LaravelEnso\FileManager\app\Classes\FileManager;
use LaravelEnso\DataImport\app\Models\ImportTemplate;

class ImportTemplateTest extends TestCase
{
    use RefreshDatabase;

    private const ImportType = 'userGroups';
    private const Path = __DIR__.DIRECTORY_SEPARATOR.'testFiles'.DIRECTORY_SEPARATOR;
    const TemplateFile = 'userGroups_import.xlsx';
    const TemplateTestFile = 'userGroups_import_test.xlsx';

    protected function setUp()
    {
        parent::setUp();

        // $this->withoutExceptionHandling();

        $this->seed()
            ->actingAs(User::first());
    }

    public function tearDown()
    {
        $this->cleanUp();
        parent::tearDown();
    }

    /** @test */
    public function get_template()
    {
        $this->uploadTemplateFile();

        $this->get(route('import.getTemplate', [self::ImportType], false))
            ->assertStatus(200);
    }

    /** @test */
    public function upload_template()
    {
        $this->post(
            route('import.uploadTemplate', [self::ImportType], false),
            ['template' => $this->templateImportFile()]
        )->assertStatus(201);

        $importTemplate = ImportTemplate::with('file')
            ->whereHas('file', function ($query) {
                $query->whereOriginalName(self::TemplateTestFile);
            })
            ->first();

        \Storage::assertExists(
            FileManager::TestingFolder.DIRECTORY_SEPARATOR.$importTemplate->file->saved_name
        );

        $this->assertNotNull($importTemplate);
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
    }

    private function uploadTemplateFile()
    {
        $this->post(
            route('import.uploadTemplate', [self::ImportType], false),
            ['template' => $this->templateImportFile()]
        );

        return ImportTemplate::with('file')
            ->whereHas('file', function ($query) {
                $query->whereOriginalName(self::TemplateTestFile);
            })
            ->first();
    }

    private function templateImportFile()
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
        \File::delete(self::Path.self::TemplateTestFile);
    }
}
