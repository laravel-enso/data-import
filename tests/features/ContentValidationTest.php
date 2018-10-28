<?php

use Tests\TestCase;
use Illuminate\Support\Str;
use Illuminate\Http\UploadedFile;
use LaravelEnso\Core\app\Models\User;
use LaravelEnso\Core\app\Models\UserGroup;
use LaravelEnso\DataImport\app\Models\DataImport;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ContentValidationTest extends TestCase
{
    use RefreshDatabase;

    private const ImportType = 'userGroups';
    private const TemplatePath = __DIR__.DIRECTORY_SEPARATOR.'templates'.DIRECTORY_SEPARATOR;
    private const Path = __DIR__.DIRECTORY_SEPARATOR.'testFiles'.DIRECTORY_SEPARATOR;
    private const ContentIssuesFile = 'content_issues.xlsx';
    private const TestFile = 'content_issues_test.xlsx';

    protected function setUp()
    {
        parent::setUp();

        // $this->withoutExceptionHandling();

        $this->seed()
            ->actingAs(User::first());
    }

    public function tearDown()
    {
        $this->cleanUp();
        parent::tearDown();
    }

    /** @test */
    public function skips_entries_with_issues()
    {
        config(['enso.imports.configs.userGroups' => [
            'label' => 'User Groups',
            'template' => Str::replaceFirst(
                base_path(),
                '',
                self::TemplatePath.'userGroups.json'
            ),
        ]]);

        $this->post(route('import.run', [self::ImportType], false), [
            'import' => $this->contentErrorsImportFile()
        ])->assertStatus(200)
        ->assertJsonFragment([
            'issues' => 3,
            'successful' => 2,
            'rowNumber' => 4,
            'value' => 'NotUniqueName'
        ]);

        $this->assertNull(
            UserGroup::whereName('MissingDescription')->first()
        );

        $this->assertNotNull(
            UserGroup::whereName('TestName')->first()
        );

        $this->assertNotNull(
            DataImport::whereHas('file', function ($query) {
                $query->whereOriginalName(self::TestFile);
            })->first()
        );
    }

    public function stops_on_content_issues()
    {
        config(['enso.imports.configs.userGroups' => [
            'label' => 'User Groups',
            'template' => Str::replaceFirst(
                base_path(),
                '',
                self::TemplatePath.'userGroupsStops.json'
            ),
        ]]);

        $this->post(
            route('import.run', [self::ImportType], false),
            ['import' => $this->contentErrorsImportFile()]
        )->assertStatus(200)
        ->assertJsonStructure([
            'contentIssues',
        ]);

        $this->assertNull(UserGroup::whereName('TestName')->first());

        $this->assertNull(
            DataImport::whereOriginalName(self::TestFile)
                ->first()
        );
    }

    private function contentErrorsImportFile()
    {
        \File::copy(
            self::Path.self::ContentIssuesFile,
            self::Path.self::TestFile
        );

        return new UploadedFile(
            self::Path.self::TestFile,
            self::Path.self::TestFile,
            null,
            null,
            null,
            true
        );
    }

    private function cleanUp()
    {
        \File::delete(self::Path.self::TestFile);
    }
}
