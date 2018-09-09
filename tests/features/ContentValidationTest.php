<?php

use App\User;
use Tests\TestCase;
use Illuminate\Http\UploadedFile;
use LaravelEnso\TestHelper\app\Traits\SignIn;
use LaravelEnso\FileManager\app\Classes\FileManager;
use LaravelEnso\DataImport\app\Models\DataImport;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ContentValidationTest extends TestCase
{
    use RefreshDatabase, SignIn;

    const Path = __DIR__.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR
        .'testFiles'.DIRECTORY_SEPARATOR;
    const ContentIssueOriginalFile = 'content_issues_file.xlsx';
    const ContentIssueTestFile = 'content_issues_test_file.xlsx';

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
    public function skips_entries_with_issues()
    {
        $this->post(
            route('import.run', ['owners'], false),
            ['import' => $this->getContentErrorsUploadedFile()]
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
            DataImport::whereName(
                self::ContentIssueTestFile
            )->first()
        );

        $this->cleanUp();
    }

    public function stops_on_content_issues()
    {
        config()->set(
            'enso.imports.configs.owners.template',
            'vendor/laravel-enso/dataimport/src/resources/testing/ownersStops.json'
        );

        $this->post(
            route('import.run', ['owners'], false),
            ['import' => $this->getContentErrorsUploadedFile()]
        )
            ->assertStatus(200)
            ->assertJsonStructure([
                'contentIssues',
            ]);

        $this->assertNull(config('enso.config.ownerModel')::whereName('TestName')->first());

        $this->assertNull(
            DataImport::whereOriginalName(self::ContentIssueTestFile)
                ->first()
        );

        $this->cleanUp();
    }

    private function getContentErrorsUploadedFile()
    {
        \File::copy(
            self::Path.self::ContentIssueOriginalFile,
            self::Path.self::ContentIssueTestFile
        );

        return new UploadedFile(
            self::Path.self::ContentIssueTestFile,
            self::ContentIssueTestFile,
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
