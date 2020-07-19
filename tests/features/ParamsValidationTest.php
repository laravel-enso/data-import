<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use LaravelEnso\Core\Models\User;
use LaravelEnso\DataImport\Models\DataImport;
use Tests\TestCase;

class ParamsValidationTest extends TestCase
{
    use RefreshDatabase;

    private const ImportType = 'userGroups';
    private const Template = __DIR__.DIRECTORY_SEPARATOR.'templates'
        .DIRECTORY_SEPARATOR.'paramsValidation.json';
    private const Path = __DIR__.DIRECTORY_SEPARATOR.'testFiles'.DIRECTORY_SEPARATOR;
    private const InvalidSheetsFile = 'invalid_sheets.xlsx';
    private const TestFile = 'test.xlsx';

    protected function setUp(): void
    {
        parent::setUp();

        // $this->withoutExceptionHandling();

        $this->seed()
            ->actingAs(User::first());
    }

    public function tearDown(): void
    {
        $this->cleanUp();
        parent::tearDown();
    }

    /** @test */
    public function cannot_import_invalid_params()
    {
        config(['enso.imports.configs.userGroups' => [
            'label' => 'User Groups',
            'template' => Str::replaceFirst(base_path(), '', self::Template),
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
}
