<?php

use App\User;
use Tests\TestCase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use LaravelEnso\TestHelper\app\Traits\SignIn;
use LaravelEnso\DataImport\app\Models\DataImport;
use Illuminate\Foundation\Testing\RefreshDatabase;

class StructureValidationTest extends TestCase
{
    use RefreshDatabase, SignIn;

    const IMPORT_DIRECTORY = 'testImportDirectory'.DIRECTORY_SEPARATOR;
    const PATH = __DIR__.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'testFiles'.DIRECTORY_SEPARATOR;
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
    public function stops_on_invalid_sheets()
    {
        $this->post(
            route('import.run', ['owners'], false),
            ['file' => $this->getInvalidSheetsUploadedFile()]
        )
            ->assertStatus(200)
            ->assertJsonFragment([
                'issues' => 2,
                'successful' => 0,
                'structureIssues',
            ])
            ->assertJsonFragment(['Missing Sheets'])
            ->assertJsonFragment(['owners'])
            ->assertJsonFragment(['Extra Sheets'])
            ->assertJsonFragment(['invalid_sheet']);

        $this->assertNull(
            DataImport::whereOriginalName(self::INVALID_SHEETS_TEST_FILE)
                ->first()
        );

        $this->cleanUp();
    }

    /** @test */
    public function stops_on_invalid_columns()
    {
        $this->post(
            route('import.run', ['owners'], false),
            ['file' => $this->getInvalidColumnsUploadedFile()]
        )
            ->assertStatus(200)
            ->assertJsonFragment([
                'issues' => 2,
            ])
            ->assertJsonFragment(['Missing Columns'])
            ->assertJsonFragment(['Sheet "owners", column "is_active"'])
            ->assertJsonFragment(['Extra Columns'])
            ->assertJsonFragment(['Sheet "owners", column "invalid_column"']);

        $this->assertNull(
            DataImport::whereOriginalName(self::INVALID_COLUMNS_TEST_FILE)
                ->first()
        );

        $this->cleanUp();
    }

    // /** @test */ //needs refactor with custom template
    // public function stops_if_exceeds_entries_limit()
    // {
    //     config()->set('enso.imports.owners.entryLimit', '1');

    //     $this->post(
    //         route('import.run', ['owners'], false),
    //         ['file' => $this->getTwoEntriesUploadedFile()]
    //     )
    //         ->assertStatus(200)
    //         ->assertJsonFragment([
    //             'structureIssues',
    //             'issues' => 1,
    //         ])
    //         ->assertJsonFragment(['Exceeded the entries limit of: 1']);

    //     $this->assertNull(
    //         DataImport::whereOriginalName(self::TWO_ENTRIES_TEST_FILE)
    //             ->first()
    //     );

    //     $this->cleanUp();
    // }

    private function getInvalidSheetsUploadedFile()
    {
        \File::copy(
            self::PATH.self::INVALID_SHEETS_FILE,
            self::PATH.self::INVALID_SHEETS_TEST_FILE
        );

        return new UploadedFile(
            self::PATH.self::INVALID_SHEETS_TEST_FILE,
            self::INVALID_SHEETS_TEST_FILE,
            null,
            null,
            null,
            true
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
            self::INVALID_COLUMNS_TEST_FILE,
            null,
            null,
            null,
            true
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
            self::TWO_ENTRIES_TEST_FILE,
            null,
            null,
            null,
            true
        );
    }

    private function cleanUp()
    {
        Storage::deleteDirectory(self::IMPORT_DIRECTORY);
    }
}
