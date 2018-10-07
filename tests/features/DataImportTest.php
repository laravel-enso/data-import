<?php

use Tests\TestCase;
use Illuminate\Support\Str;
use Illuminate\Http\UploadedFile;
use LaravelEnso\Core\app\Models\User;
use LaravelEnso\Core\app\Models\UserGroup;
use LaravelEnso\DataImport\app\Models\DataImport;
use Illuminate\Foundation\Testing\RefreshDatabase;
use LaravelEnso\FileManager\app\Classes\FileManager;

class DataImportTest extends TestCase
{
    use RefreshDatabase;

    private const ImportType = 'userGroups';
    private const Template = __DIR__.DIRECTORY_SEPARATOR.'templates'.DIRECTORY_SEPARATOR.'userGroups.json';
    private const Path = __DIR__.DIRECTORY_SEPARATOR.'testFiles'.DIRECTORY_SEPARATOR;
    private const ImportFile = 'userGroups_import.xlsx';
    private const ImportTestFile = 'userGroups_import_test.xlsx';

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
    public function index()
    {
        $this->get(route('import.index', [], false))
            ->assertStatus(200);
    }

    /** @test */
    public function getSummary()
    {
        $this->importUserGroups();

        $dataImport = DataImport::whereName(self::ImportTestFile)
                        ->first();

        $this->get(route('import.getSummary', [$dataImport->id], false))
            ->assertStatus(200);
    }

    /** @test */
    public function can_import()
    {
        $this->post(route('import.run', [self::ImportType], false), [
            'import' => $this->userGroupsImportFile()
        ])->assertStatus(200)
        ->assertJsonFragment(['successful' => 2]);

        $dataImport = DataImport::with('file')
                        ->whereName(self::ImportTestFile)
                        ->first();

        $this->assertNotNull($dataImport);

        $this->assertNotNull(
            UserGroup::whereName('ImportTestName')
                ->first()
        );

        \Storage::assertExists(
            FileManager::TestingFolder.DIRECTORY_SEPARATOR.$dataImport->file->saved_name
        );
    }

    /** @test */
    public function download()
    {
        $this->importUserGroups();

        $dataImport = DataImport::whereName(self::ImportTestFile)
                        ->first();

        $this->get(route('import.download', [$dataImport->id], false))
            ->assertStatus(200)
            ->assertHeader(
                'content-disposition',
                'attachment; filename='.self::ImportTestFile
            );
    }

    /** @test */
    public function destroy()
    {
        $this->importUserGroups();

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
    }

    private function importUserGroups()
    {
        $uploadedFile = $this->userGroupsImportFile();

        $this->post(route('import.run', [self::ImportType], false), [
            'import' => $uploadedFile
        ]);
    }

    private function userGroupsImportFile()
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
        \File::delete(self::Path.self::ImportTestFile);
    }
}
