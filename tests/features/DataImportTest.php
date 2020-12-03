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

        $this->seed()
            ->actingAs(User::first());

        Config::set(['enso.imports.configs.userGroups' => [
            'label' => 'User Groupss',
            'template' => Str::of(self::Template)->replace(base_path(), ''),
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
            'import' => $this->uploadedFile(self::ImportFile),
            'type' => self::ImportType,
        ])->assertStatus(200)
            ->assertJsonFragment([
                'errors' => [],
                'filename' => self::ImportTestFile,
            ]);

        $this->model = DataImport::whereHas('file', fn ($file) => $file
            ->whereOriginalName(self::ImportTestFile))->first();

        $this->assertNotNull($this->model);
        $this->assertNotNull(UserGroup::whereName('ImportTestName')->first());

        Storage::assertExists($this->model->file->path);
    }

    /** @test */
    public function generates_rejected()
    {
        $this->attach(self::ContentErrorsFile);
        $this->model->refresh();

        $this->assertNotNull($this->model->rejected);
        $this->assertNotNull($this->model->rejected->file);
        Storage::assertExists($this->model->rejected->file->path);
    }

    /** @test */
    public function can_download_rejected()
    {
        $this->attach(self::ContentErrorsFile);
        $this->model->refresh();

        $resp = $this->get(route('import.rejected', [
            $this->model->rejected->id,
        ], false));

        $resp->assertStatus(200);
    }

    /** @test */
    public function download()
    {
        $this->attach(self::ImportFile);
        $this->model->refresh();

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
        $this->attach(self::ImportFile);
        $this->model->update(['status' => Statuses::Processing]);

        $response = $this->delete(route('import.destroy', [$this->model->id], false));
        $response->assertStatus(488);

        $this->model->update(['status' => Statuses::Finalized]);
    }

    /** @test */
    public function destroy()
    {
        $this->attach(self::ImportFile);
        $this->model->refresh();

        Storage::assertExists($this->model->file->path);

        $this->delete(route('import.destroy', [$this->model->id], false))
            ->assertStatus(200);

        $this->assertNull($this->model->fresh());

        Storage::assertMissing($this->model->file->path);
    }

    private function attach(string $file)
    {
        $this->model = DataImport::factory()->create([
            'type' => self::ImportType,
        ]);

        $path = "{$this->model->folder()}/{$file}";

        File::copy(self::Path.$file, Storage::path($path));

        $this->model->attach($path, self::ImportTestFile);
    }

    private function uploadedFile($file)
    {
        return new UploadedFile(
            self::Path.$file,
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
