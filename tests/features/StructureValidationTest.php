<?php

use App\User;
use Tests\TestCase;
use Illuminate\Http\UploadedFile;
use LaravelEnso\TestHelper\app\Traits\SignIn;
use LaravelEnso\FileManager\app\Classes\FileManager;
use LaravelEnso\DataImport\app\Models\DataImport;
use Illuminate\Foundation\Testing\RefreshDatabase;

class StructureValidationTest extends TestCase
{
    use RefreshDatabase, SignIn;

    const Path = __DIR__.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR
        .'testFiles'.DIRECTORY_SEPARATOR;
    const InvalidSheetsFile = 'invalid_sheets_file.xlsx';
    const InvalidSheetsTestFile = 'invalid_sheets_test_file.xlsx';
    const InvalidColumnsFile = 'invalid_columns_file.xlsx';
    const InvalidColumnsTestFile = 'invalid_columns_test_file.xlsx';
    const TwoEntriesFile = 'owners_import_file.xlsx';
    const TwoEntriesTestFile = 'owners_import_test_file.xlsx';

    protected function setUp()
    {
        parent::setUp();

        // $this->withoutExceptionHandling();

        $this->seed()
            ->signIn(User::first());

        config(['enso.imports.configs.owners' => [
                'label' => 'Owners',
                'template' => 'vendor/laravel-enso/dataimport/src/resources/testing/owners.json',
            ]
        ]);
    }

    /** @test */
    public function stops_on_invalid_sheets()
    {
        $this->post(
            route('import.run', ['owners'], false),
            ['import' => $this->getInvalidSheetsUploadedFile()]
        )
            ->assertStatus(200)
            ->assertJsonFragment([
                'issues' => 2,
                'successful' => 0,
            ])->assertJsonStructure([
                'structureIssues',
            ]);

        $this->assertNull(
            DataImport::whereName(self::InvalidSheetsTestFile)
                ->first()
        );

        $this->cleanUp();
    }

    /** @test */
    public function stops_on_invalid_columns()
    {
        $this->post(
            route('import.run', ['owners'], false),
            ['import' => $this->getInvalidColumnsUploadedFile()]
        )
            ->assertStatus(200)
            ->assertJsonFragment([
                'issues' => 2,
            ]);

        $this->assertNull(
            DataImport::whereName(self::InvalidColumnsTestFile)
                ->first()
        );

        $this->cleanUp();
    }

    /** @test */
    public function stops_if_exceeds_entries_limit()
    {
        config()->set(
            'enso.imports.configs.owners.template',
            'vendor/laravel-enso/dataimport/src/resources/testing/ownersLimit.json'
        );

        $this->post(
            route('import.run', ['owners'], false),
            ['import' => $this->getTwoEntriesUploadedFile()]
        )
            ->assertStatus(200)
            ->assertJsonStructure([
                'structureIssues',
                'issues',
            ]);

        $this->assertNull(
            DataImport::whereName(self::TwoEntriesTestFile)
                ->first()
        );

        $this->cleanUp();
    }

    private function getInvalidSheetsUploadedFile()
    {
        \File::copy(
            self::Path.self::InvalidSheetsFile,
            self::Path.self::InvalidSheetsTestFile
        );

        return new UploadedFile(
            self::Path.self::InvalidSheetsTestFile,
            self::InvalidSheetsTestFile,
            null,
            null,
            null,
            true
        );
    }

    private function getInvalidColumnsUploadedFile()
    {
        \File::copy(
            self::Path.self::InvalidColumnsFile,
            self::Path.self::InvalidColumnsTestFile
        );

        return new UploadedFile(
            self::Path.self::InvalidColumnsTestFile,
            self::InvalidColumnsTestFile,
            null,
            null,
            null,
            true
        );
    }

    private function getTwoEntriesUploadedFile()
    {
        \File::copy(
            self::Path.self::TwoEntriesFile,
            self::Path.self::TwoEntriesTestFile
        );

        return new UploadedFile(
            self::Path.self::TwoEntriesTestFile,
            self::TwoEntriesTestFile,
            null,
            null,
            null,
            true
        );
    }

    private function cleanUp()
    {
        \Storage::deleteDirectory(FileManager::TestingFolder);
    }
}
