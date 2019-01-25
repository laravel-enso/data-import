<?php

use Tests\TestCase;
use Illuminate\Support\Str;
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
    private const Template = __DIR__.DIRECTORY_SEPARATOR.'templates'.DIRECTORY_SEPARATOR.'userGroups.json';
    const TemplateFile = 'userGroups_import.xlsx';
    const TemplateTestFile = 'userGroups_import_test.xlsx';

    private $model;

    protected function setUp()
    {
        parent::setUp();

        // $this->withoutExceptionHandling();

        $this->seed()
            ->actingAs(User::first());

        config(['enso.imports.configs.userGroups' => [
            'label' => 'User Groups',
            'template' => Str::replaceFirst(base_path(), '', self::Template),
        ]]);
    }

    public function tearDown()
    {
        $this->cleanUp();
        parent::tearDown();
    }

    /** @test */
    public function can_upload_template()
    {
        $this->post(route('import.uploadTemplate', [], false), [
            'template' => $this->templateFile(),
            'type' => self::ImportType,
        ])->assertStatus(201);

        $this->model = ImportTemplate::with('file')
            ->whereHas('file', function ($query) {
                $query->whereOriginalName(self::TemplateTestFile);
            })->first();

        \Storage::assertExists(
            FileManager::TestingFolder.DIRECTORY_SEPARATOR.$this->model->file->saved_name
        );

        $this->assertNotNull($this->model);
    }

    /** @test */
    public function can_get_template()
    {
        $this->createModel();

        $this->get(route('import.template', [self::ImportType], false))
            ->assertStatus(200);
    }

    /** @test */
    public function download_template()
    {
        $this->createModel();

        $this->get(route('import.downloadTemplate', [$this->model->id], false))
            ->assertStatus(200)
            ->assertHeader(
                'content-disposition',
                'attachment; filename='.self::TemplateTestFile
            );
    }

    /** @test */
    public function delete_template()
    {
        $this->createModel();

        \Storage::assertExists(
            FileManager::TestingFolder.DIRECTORY_SEPARATOR.$this->model->file->saved_name
        );

        $this->assertNotNull($this->model);

        $this->delete(route('import.deleteTemplate', [$this->model->id], false))
            ->assertStatus(200);

        $this->assertNull($this->model->fresh());

        \Storage::assertMissing(
            FileManager::TestingFolder.DIRECTORY_SEPARATOR.$this->model->file->saved_name
        );
    }

    private function createModel()
    {
        $this->model = ImportTemplate::create([
            'type' => self::ImportType,
        ]);

        $this->model->upload($this->templateFile());
    }

    private function templateFile()
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
        $this->model->delete();
        \File::delete(self::Path.self::TemplateTestFile);
    }
}
