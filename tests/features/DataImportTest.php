<?php

use Tests\TestCase;
use Illuminate\Support\Str;
use Illuminate\Http\UploadedFile;
use LaravelEnso\Core\app\Models\User;
use LaravelEnso\Core\app\Models\UserGroup;
use LaravelEnso\DataImport\app\Enums\Statuses;
use LaravelEnso\DataImport\app\Classes\Template;
use LaravelEnso\DataImport\app\Models\DataImport;
use Illuminate\Foundation\Testing\RefreshDatabase;
use LaravelEnso\FileManager\app\Classes\FileManager;
use LaravelEnso\VueDatatable\app\Traits\Tests\Datatable;

class DataImportTest extends TestCase
{
    use Datatable, RefreshDatabase;

    private const ImportType = 'userGroups';
    private const Template = __DIR__.DIRECTORY_SEPARATOR.'templates'.DIRECTORY_SEPARATOR.'userGroups.json';
    private const Path = __DIR__.DIRECTORY_SEPARATOR.'testFiles'.DIRECTORY_SEPARATOR;
    private const ImportFile = 'userGroups_import.xlsx';
    private const ContentErrorsFile = 'content_errors.xlsx';
    private const ImportTestFile = 'userGroups_import_test.xlsx';

    private $permissionGroup = 'import';
    private $model;

    protected function setUp()
    {
        parent::setUp();

        // $this->withoutExceptionHandling();

        $this->seed()
            ->actingAs(User::first());

        config(['enso.imports.configs.userGroups' => [
            'label' => 'User Groupss',
            'template' => Str::replaceFirst(base_path(), '', self::Template),
        ]]);
    }

    public function tearDown()
    {
        $this->cleanUp();
        parent::tearDown();
    }

    /** @test */
    public function can_import()
    {
        $this->post(route('import.store', [], false), [
            'import' => $this->importFile(self::ImportFile),
            'type' => self::ImportType,
        ])->assertStatus(200)
        ->assertJsonFragment([
            'errors' => [],
            'filename' => self::ImportTestFile
        ]);

        $this->model = DataImport::whereHas('file', function ($query) {
            $query->whereOriginalName(self::ImportTestFile);
        })->first();

        $this->assertNotNull($this->model);

        $this->assertNotNull(
            UserGroup::whereName('ImportTestName')
                ->first()
        );

        \Storage::assertExists(
            FileManager::TestingFolder.DIRECTORY_SEPARATOR.$this->model->file->saved_name
        );
    }

    /** @test */
    public function generates_rejected()
    {
        $this->createImport(self::ContentErrorsFile);
        $this->updateStatus();

        $this->assertNotNull($this->model->rejected);
    }

    /** @test */
    public function can_download_rejected()
    {
        $this->createImport(self::ContentErrorsFile);
        $this->updateStatus();

        $this->get(route('import.downloadRejected', [$this->model->rejected->id], false))
            ->assertStatus(200);
    }

    /** @test */
    public function download()
    {
        $this->createImport(self::ImportFile);
        $this->updateStatus();

        $this->get(route('import.download', [$this->model->id], false))
            ->assertStatus(200)
            ->assertHeader(
                'content-disposition',
                'attachment; filename='.self::ImportTestFile
            );
    }

    /** @test */
    public function cant_destroy_while_running()
    {
        $this->createImport();

        $this->delete(route('import.destroy', [$this->model->id], false))
            ->assertStatus(555);

        $this->updateStatus();
    }

    /** @test */
    public function destroy()
    {
        $this->createImport(self::ImportFile);
        $this->updateStatus();

        $filename = FileManager::TestingFolder.DIRECTORY_SEPARATOR.$this->model->file->saved_name;

        \Storage::assertExists($filename);

        $this->delete(route('import.destroy', [$this->model->id], false))
            ->assertStatus(200);

        $this->assertNull($this->model->fresh());

        \Storage::assertMissing($filename);
    }

    private function createImport($file = null)
    {
        $this->model = DataImport::create([
            'type' => self::ImportType,
            'status' => Statuses::Waiting,
        ]);

        if ($file) {
            $this->model->run($this->importFile($file));
            $this->model->fresh();
        }
    }

    private function updateStatus()
    {
        $this->model->update(['status' => Statuses::Finalized]);
    }

    private function importFile($file)
    {
        \File::copy(
            self::Path.$file,
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
        optional($this->model)->delete();
        \File::delete(self::Path.self::ImportTestFile);
    }
}
