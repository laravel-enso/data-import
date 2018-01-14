<?php

namespace Tests;

use App\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use LaravelEnso\DataImport\app\Models\DataImport;
use LaravelEnso\TestHelper\app\Traits\SignIn;

class StructureValidationTest extends TestCase
{
    use RefreshDatabase, SignIn;

    const IMPORT_DIRECTORY = 'testImportDirectory/';
    const PATH = __DIR__.'/../testFiles/';
    const INVALID_SHEETS_FILE = 'invalid_sheets_file.xlsx';
    const INVALID_SHEETS_TEST_FILE = 'invalid_sheets_test_file.xlsx';
    const INVALID_COLUMNS_FILE = 'invalid_columns_file.xlsx';
    const INVALID_COLUMNS_TEST_FILE = 'invalid_columns_test_file.xlsx';
    const TWO_ENTRIES_FILE = 'owners_import_file.xlsx';
    const TWO_ENTRIES_TEST_FILE = 'owners_import_test_file.xlsx';

    protected function setUp()
    {
        parent::setUp();

        // $this->withoutExceptionHandling();

        config()->set('enso.config.paths.imports', self::IMPORT_DIRECTORY);

        $this->signIn(User::first());
    }

    /** @test */
    public function cant_import_file_with_invalid_sheets()
    {
        $this->post(route('import.run', ['owners'], false),
            ['file' => $this->getInvalidSheetsUploadedFile()])
            ->assertStatus(200)
            ->assertJsonFragment([
                'hasStructureErrors' => true,
                'errors'             => 2,
            ])
            ->assertJsonFragment([
                'name'  => 'Missing Sheets',
                'value' => 'owners',
            ])
            ->assertJsonFragment([
                'name'  => 'Extra Sheets',
                'value' => 'invalid_sheet',
            ]);

        $this->assertNull(DataImport::whereOriginalName(self::INVALID_SHEETS_TEST_FILE)
                ->first()
        );

        $this->cleanUp();
    }

    /** @test */
    public function cant_import_file_with_invalid_columns()
    {
        $this->post(route('import.run', ['owners'], false),
            ['file' => $this->getInvalidColumnsUploadedFile()])
            ->assertStatus(200)
            ->assertJsonFragment([
                'hasStructureErrors' => true,
                'errors'             => 2,
            ])
            ->assertJsonFragment([
                'name'  => 'Missing Columns',
                'value' => 'is_active',
            ])
            ->assertJsonFragment([
                'name'  => 'Extra Columns',
                'value' => 'invalid_column',
            ]);
        $this->assertNull(DataImport::whereOriginalName(self::INVALID_COLUMNS_TEST_FILE)
                ->first()
        );

        $this->cleanUp();
    }

    /** @test */
    public function cant_import_file_that_exceeds_entries_limit()
    {
        config()->set('enso.imports.owners.sheetEntriesLimit', '1');

        $this->post(route('import.run', ['owners'], false),
            ['file' => $this->getTwoEntriesUploadedFile()])
            ->assertStatus(200)
            ->assertJsonFragment([
                'hasStructureErrors' => true,
                'errors'             => 1,
                'name'               => 'Exceeded the entries limit of: 1',
            ]);

        $this->assertNull(DataImport::whereOriginalName(self::TWO_ENTRIES_TEST_FILE)
                ->first()
        );

        $this->cleanUp();
    }

    private function getInvalidSheetsUploadedFile()
    {
        \File::copy(
            self::PATH.self::INVALID_SHEETS_FILE,
            self::PATH.self::INVALID_SHEETS_TEST_FILE
        );

        return new UploadedFile(
            self::PATH.self::INVALID_SHEETS_TEST_FILE,
            self::INVALID_SHEETS_TEST_FILE, null, null, null, true
        );
    }

    private function getInvalidColumnsUploadedFile()
    {
        \File::copy(
            self::PATH.self::INVALID_COLUMNS_FILE,
            self::PATH.self::INVALID_COLUMNS_TEST_FILE
        );

        return new UploadedFile(
            self::PATH.self::INVALID_COLUMNS_TEST_FILE,
            self::INVALID_COLUMNS_TEST_FILE, null, null, null, true
        );
    }

    private function getTwoEntriesUploadedFile()
    {
        \File::copy(
            self::PATH.self::TWO_ENTRIES_FILE,
            self::PATH.self::TWO_ENTRIES_TEST_FILE
        );

        return new UploadedFile(
            self::PATH.self::TWO_ENTRIES_TEST_FILE,
            self::TWO_ENTRIES_TEST_FILE, null, null, null, true
        );
    }

    private function cleanUp()
    {
        Storage::deleteDirectory(self::IMPORT_DIRECTORY);
    }
}
