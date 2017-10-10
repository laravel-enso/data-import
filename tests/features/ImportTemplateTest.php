<?php

namespace Tests;

use App\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use LaravelEnso\DataImport\app\Models\ImportTemplate;
use LaravelEnso\TestHelper\app\Traits\SignIn;

class ImportTemplateTest extends TestCase
{
    use RefreshDatabase, SignIn;

    const IMPORT_DIRECTORY = 'testImportDirectory/';
    const PATH = __DIR__.'/../testFiles/';
    const TEMPLATE_FILE = 'owners_import_file.xlsx';
    const TEMPLATE_TEST_FILE = 'owners_import_test_file.xlsx';

    protected function setUp()
    {
        parent::setUp();

        $this->withoutExceptionHandling();
        config()->set('enso.config.paths.imports', self::IMPORT_DIRECTORY);
        $this->signIn(User::first());
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
            ['file' => $this->getTemplateUploadedFile()]
        )->assertStatus(200);

        $importTemplate = ImportTemplate::whereOriginalName(self::TEMPLATE_TEST_FILE)->first();
        Storage::assertExists(self::IMPORT_DIRECTORY.$importTemplate->saved_name);

        $this->assertNotNull($importTemplate);

        $this->cleanUp();
    }

    /** @test */
    public function downloadTemplate()
    {
        $importTemplate = $this->uploadTemplateFile();

        $response = $this->get(route('import.downloadTemplate', [$importTemplate->id], false));

        $response->assertStatus(200);
        $this->assertTrue(
            $response->headers->get('content-disposition')
            ===
            'attachment; filename="'.self::TEMPLATE_TEST_FILE.'"'
        );

        $this->cleanUp();
    }

    /** @test */
    public function deleteTemplate()
    {
        $importTemplate = $this->uploadTemplateFile();

        Storage::assertExists(self::IMPORT_DIRECTORY.$importTemplate->saved_name);
        $this->assertNotNull($importTemplate);

        $this->delete(route('import.deleteTemplate', [$importTemplate->id], false))
            ->assertStatus(200);

        $this->assertNull($importTemplate->fresh());
        Storage::assertMissing(self::IMPORT_DIRECTORY.$importTemplate->saved_name);

        $this->cleanUp();
    }

    private function uploadTemplateFile()
    {
        $this->post(route('import.uploadTemplate', ['owners'], false),
            ['file' => $this->getTemplateUploadedFile()]
        );
        $importTemplate = ImportTemplate::whereOriginalName(self::TEMPLATE_TEST_FILE)->first();

        return $importTemplate;
    }

    private function getTemplateUploadedFile()
    {
        \File::copy(
            self::PATH.self::TEMPLATE_FILE,
            self::PATH.self::TEMPLATE_TEST_FILE
        );

        return new UploadedFile(self::PATH.self::TEMPLATE_TEST_FILE,
            self::TEMPLATE_TEST_FILE, null, null, null, true
        );
    }

    private function cleanUp()
    {
        Storage::deleteDirectory(self::IMPORT_DIRECTORY);
    }
}
