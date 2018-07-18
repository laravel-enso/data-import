<?php

use App\User;
use Tests\TestCase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use LaravelEnso\TestHelper\app\Traits\SignIn;
use LaravelEnso\DataImport\app\Models\DataImport;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ContentValidationTest extends TestCase
{
    use RefreshDatabase, SignIn;

    const IMPORT_DIRECTORY = 'testImportDirectory'.DIRECTORY_SEPARATOR;
    const PATH = __DIR__.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR
        .'testFiles'.DIRECTORY_SEPARATOR;
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
                'rowNumber' => 4,
                'value' => 'NotUniqueName',
                'column' => 'is_active',
                'value' => 'notBoolean',
            ]);

        $this->assertNull(
            config('enso.config.ownerModel')
                ::whereName('BooleanTest')->first()
        );
        $this->assertNotNull(
            config('enso.config.ownerModel')
                ::whereName('TestName')->first()
        );

        $this->assertNotNull(
            DataImport::whereOriginalName(
                self::CONTENT_ISSUES_TEST_FILE
            )->first()
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

    //     $this->assertNull(config('enso.config.ownerModel')::whereName('TestName')->first());
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
