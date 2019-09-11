<?php

use Tests\TestCase;
use Illuminate\Support\Str;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use LaravelEnso\Core\app\Models\User;
use LaravelEnso\DataImport\app\Models\DataImport;
use Illuminate\Foundation\Testing\RefreshDatabase;

class StructureValidationTest extends TestCase
{
    use RefreshDatabase;

    private const ImportType = 'userGroups';
    private const Template = __DIR__.DIRECTORY_SEPARATOR.'templates'
        .DIRECTORY_SEPARATOR.'userGroups.json';
    private const Path = __DIR__.DIRECTORY_SEPARATOR.'testFiles'.DIRECTORY_SEPARATOR;
    private const InvalidSheetsFile = 'invalid_sheets.xlsx';
    private const InvalidColumnsFile = 'invalid_columns.xlsx';
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

        $this->post(route('import.store', [], false), [
            'import' => $this->file(self::InvalidSheetsFile),
            'type' => self::ImportType,
        ])->assertStatus(200)
        ->assertJsonFragment([
            'errors' => [
                'Extra Sheets' => ['invalid_sheet'],
                'Missing Sheets' => ['groups']
            ],
            'filename' => self::TestFile,
        ]);

        $this->assertNull(
            DataImport::whereName(self::TestFile)
                ->first()
        );
    }

    /** @test */
    public function stops_on_invalid_columns()
    {
        config(['enso.imports.configs.userGroups' => [
            'label' => 'User Groups',
            'template' => Str::replaceFirst(base_path(), '', self::Template),
        ]]);

        $this->post(route('import.store', [], false), [
            'import' => $this->file(self::InvalidColumnsFile),
            'type' => self::ImportType,
        ])->assertStatus(200)
        ->assertJsonFragment([
            'errors' => [
                'Extra Columns' => ['Sheet "groups", column "invalid_column"'],
                'Missing Columns' => ['Sheet "groups", column "description"']
            ],
            'filename' => self::TestFile,
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
            null,
            true
        );
    }

    private function cleanUp()
    {
        File::delete(self::Path.self::TestFile);
    }
}
