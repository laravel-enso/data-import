<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use LaravelEnso\Core\Models\User;
use LaravelEnso\DataImport\Models\DataImport;
use Tests\TestCase;

class ValidationTest extends TestCase
{
    use RefreshDatabase;

    private const ImportType = 'userGroups';
    private const Path = __DIR__.DIRECTORY_SEPARATOR.'testFiles'.DIRECTORY_SEPARATOR;
    private const InvalidSheetsFile = 'invalid_sheets.xlsx';
    private const InvalidColumnsFile = 'invalid_columns.xlsx';
    private const TestFile = 'test.xlsx';

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed()
            ->actingAs(User::first());
    }

    public function tearDown(): void
    {
        $this->cleanUp();
        parent::tearDown();
    }

    /** @test */
    public function stops_on_invalid_sheets()
    {
        config(['enso.imports.configs.userGroups' => [
            'label' => 'User Groups',
            'template' => $this->template('userGroups'),
        ]]);

        $this->post(route('import.store', [], false), [
            'import' => $this->file(self::InvalidSheetsFile),
            'type' => self::ImportType,
        ])->assertStatus(200)
            ->assertJsonFragment([
                'errors' => [
                    'Extra Sheets' => ['invalid_sheet'],
                    'Missing Sheets' => ['groups'],
                ],
                'filename' => self::TestFile,
            ]);

        $this->assertNull(DataImport::whereName(self::TestFile)->first());
    }

    /** @test */
    public function stops_on_invalid_columns()
    {
        config(['enso.imports.configs.userGroups' => [
            'label' => 'User Groups',
            'template' => $this->template('userGroups'),
        ]]);

        $this->post(route('import.store', [], false), [
            'import' => $this->file(self::InvalidColumnsFile),
            'type' => self::ImportType,
        ])->assertStatus(200)
            ->assertJsonFragment([
                'errors' => [
                    'Extra Columns' => ['Sheet "groups", column "invalid_column"'],
                    'Missing Columns' => ['Sheet "groups", column "description"'],
                ],
                'filename' => self::TestFile,
            ]);

        $this->assertNull(DataImport::whereName(self::TestFile)->first());
    }

    /** @test */
    public function cannot_import_invalid_params()
    {
        Config::set(['enso.imports.configs.userGroups' => [
            'label' => 'User Groups',
            'template' => $this->template('paramsValidation'),
        ]]);

        $exception = $this->post(route('import.store', [], false), [
            'import' => $this->file('invalid_sheets.xlsx'),
            'type' => self::ImportType,
        ])->exception;

        $this->assertInstanceOf(ValidationException::class, $exception);
        $this->assertArrayHasKey('name', $exception->errors());
    }

    private function file($file)
    {
        File::copy(
            self::Path.$file,
            self::Path.self::TestFile
        );

        return new UploadedFile(
            self::Path.self::TestFile,
            self::TestFile,
            null,
            null,
            true
        );
    }

    private function cleanUp()
    {
        File::delete(self::Path.self::TestFile);
    }

    protected function template($template): string
    {
        return Str::replaceFirst(base_path(), '', __DIR__.DIRECTORY_SEPARATOR.'templates'
            .DIRECTORY_SEPARATOR."{$template}.json");
    }
}
