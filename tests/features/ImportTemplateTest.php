<?php

use App\User;
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
    }

    /** @test */
    public function getTemplate()
    {
        $this->uploadTemplateFile();

        $this->get(route('import.getTemplate', ['owners'], false))
            ->assertStatus(200);
    }

    /** @test */
    public function uploadTemplate()
    {
        $this->post(
            route('import.uploadTemplate', ['owners'], false),
            ['template' => $this->getTemplateUploadedFile()]
        )->assertStatus(201);

        $importTemplate = ImportTemplate::with('file')
            ->whereName(self::TemplateTestFile)
            ->first();

        \Storage::assertExists(
            FileManager::TestingFolder.DIRECTORY_SEPARATOR.DIRECTORY_SEPARATOR.$importTemplate->file->saved_name
        );

        $this->assertNotNull($importTemplate);

        $this->cleanUp();
    }

    /** @test */
    public function downloadTemplate()
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
    public function deleteTemplate()
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
            FileManager::TestingFolder.DIRECTORY_SEPARATOR.$importTemplate->name
        );

        $this->cleanUp();
    }

    private function uploadTemplateFile()
    {
        $this->post(
            route('import.uploadTemplate', ['owners'], false),
            ['template' => $this->getTemplateUploadedFile()]
        );

        return ImportTemplate::whereName(self::TemplateTestFile)
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
