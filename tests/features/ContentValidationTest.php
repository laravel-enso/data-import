<?php

namespace Tests;

use App\Owner;
use App\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use LaravelEnso\DataImport\app\Models\DataImport;
use LaravelEnso\TestHelper\app\Traits\SignIn;

class ContentValidationTest extends TestCase
{
    use RefreshDatabase, SignIn;

    const IMPORT_DIRECTORY = 'testImportDirectory/';
    const PATH = __DIR__.'/../testFiles/';
    const CONTENT_ISSUES_ORIGINAL_FILE = 'content_issues_file.xlsx';
    const CONTENT_ISSUES_TEST_FILE = 'content_issues_test_file.xlsx';

    protected function setUp()
    {
        parent::setUp();

        // $this->withoutExceptionHandling();

        config()->set('enso.config.paths.imports', self::IMPORT_DIRECTORY);

        $this->signIn(User::first());
    }

    /** @test */
    public function skips_entries_with_issues()
    {
        config()->set('enso.imports.owners.stopsOnIssues', false);

        $this->post(
            route('import.run', ['owners'], false),
            ['file' => $this->getContentErrorsUploadedFile()]
        )
            ->assertStatus(200)
            ->assertJsonFragment([
                'issues' => 3,
                'successful' => 2,
            ])
            ->assertJsonFragment([
                'Doubled sheet lines',
                'rowNumber' => 4,
            ])
            ->assertJsonFragment([
                'Values must be unique in column "name"',
                'value' => 'NotUniqueName',
            ])
            ->assertJsonFragment([
                'The is active field must be true or false.',
                'column' => 'is_active',
                'value' => 'notBoolean',
            ]);

        $this->assertNull(
            Owner::whereName('BooleanTest')->first()
        );
        $this->assertNotNull(Owner::whereName('TestName')->first());
        $this->assertNotNull(
            DataImport::whereOriginalName(self::CONTENT_ISSUES_TEST_FILE)
                ->first()
        );

        $this->cleanUp();
    }

    // /** @test */ // needs refactor with custom template
    // public function stops_on_content_issues()
    // {
    //     config()->set('enso.imports.owners.stopsOnIssues', true);

    //     $this->post(
    //         route('import.run', ['owners'], false),
    //         ['file' => $this->getContentErrorsUploadedFile()]
    //     )
    //         ->assertStatus(200)
    //         ->assertJsonFragment([
    //             'contentIssues',
    //         ]);

    //     $this->assertNull(Owner::whereName('TestName')->first());
    //     $this->assertNull(
    //         DataImport::whereOriginalName(self::CONTENT_ISSUES_TEST_FILE)
    //             ->first()
    //     );

    //     $this->cleanUp();
    // }

    private function getContentErrorsUploadedFile()
    {
        \File::copy(
            self::PATH.self::CONTENT_ISSUES_ORIGINAL_FILE,
            self::PATH.self::CONTENT_ISSUES_TEST_FILE
        );

        return new UploadedFile(
            self::PATH.self::CONTENT_ISSUES_TEST_FILE,
            self::CONTENT_ISSUES_TEST_FILE,
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
