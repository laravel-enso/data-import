<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use LaravelEnso\Core\Models\User;
use LaravelEnso\Core\Models\UserGroup;
use LaravelEnso\DataImport\Enums\Statuses;
use LaravelEnso\DataImport\Models\DataImport;
use LaravelEnso\DataImport\Services\Import;
use LaravelEnso\Tables\Traits\Tests\Datatable;
use Tests\TestCase;

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

    protected function setUp(): void
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

    public function tearDown(): void
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
                'filename' => self::ImportTestFile,
            ]);

        $this->model = DataImport::whereHas('file', fn ($query) => ($query->whereOriginalName(self::ImportTestFile)))->first();

        $this->assertNotNull($this->model);

        $this->assertNotNull(
            UserGroup::whereName('ImportTestName')
                ->first()
        );

        Storage::assertExists(
            $this->model->folder().DIRECTORY_SEPARATOR.$this->model->file->saved_name
        );
    }

    /** @test */
    public function generates_rejected()
    {
        $this->createImport(self::ContentErrorsFile);
        $this->updateStatus();

        $this->assertNotNull($this->model->rejected);
        $this->assertNotNull($this->model->rejected->file);
    }

    /** @test */
    public function can_download_rejected()
    {
        $this->createImport(self::ContentErrorsFile);
        $this->updateStatus();

        $resp = $this->get(route('import.rejected', [$this->model->rejected->id], false));

            $resp->assertStatus(200);
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
        $this->createImport(self::ImportFile);
        $this->model->update(['status' => Statuses::Processing]);

        $response = $this->delete(route('import.destroy', [$this->model->id], false));
        $response->assertStatus(488);

        $this->updateStatus();
    }

    /** @test */
    public function destroy()
    {
        $this->createImport(self::ImportFile);
        $this->updateStatus();

        Storage::assertExists($this->model->file->path);

        $this->delete(route('import.destroy', [$this->model->id], false))
            ->assertStatus(200);

        $this->assertNull($this->model->fresh());

        Storage::assertMissing($this->model->file->path);
    }

    private function createImport($file = null)
    {
        if ($file) {
            $this->model = (new Import(static::ImportType, $this->importFile($file)))
                ->handle()
                ->dataImport();

            return;
        }

        $this->model = DataImport::factory()->create([
            'type' => self::ImportType,
        ]);
    }

    private function updateStatus()
    {
        $this->model->update(['status' => Statuses::Finalized]);
    }

    private function importFile($file)
    {
        File::copy(
            self::Path.$file,
            self::Path.self::ImportTestFile
        );

        return new UploadedFile(
            self::Path.self::ImportTestFile,
            self::ImportTestFile,
            null,
            null,
            true
        );
    }

    private function cleanUp()
    {
        optional($this->model)->delete();

        File::delete(self::Path.self::ImportTestFile);
        Storage::deleteDirectory(Config::get('enso.files.testingFolder'));
    }
}
