<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
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
    public function stops_on_invalid_sheets()
    {
        config(['enso.imports.configs.userGroups' => [
            'label' => 'User Groups',
            'template' => Str::replaceFirst(base_path(), '', self::Template),
        ]]);

        $resp = $this->post(route('import.store', [], false), [
            'import' => $this->file('invalid_sheets.xlsx'),
            'type' => self::ImportType,
        ]);

        $resp->assertStatus(200)
            ->assertJsonFragment([
                'errors' => [
                    'name' => ['The name field is required.'],
                ],
            ]);

        $this->assertNull(
            DataImport::whereName(self::TestFile)
                ->first()
        );
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
