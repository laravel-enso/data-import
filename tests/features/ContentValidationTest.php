<?php

namespace Tests;

use App\Owner;
use App\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use LaravelEnso\DataImport\app\Models\DataImport;
use LaravelEnso\TestHelper\app\Classes\TestHelper;

class ContentValidationTest extends TestHelper
{
    use DatabaseMigrations;

    const IMPORT_DIRECTORY         = 'testImportDirectory/';
    const PATH                     = __DIR__ . '/../testFiles/';
    const CONTENT_ERRORS_FILE      = 'content_errors_file.xlsx';
    const CONTENT_ERRORS_TEST_FILE = 'content_errors_test_file.xlsx';

    protected function setUp()
    {
        parent::setUp();

        // $this->disableExceptionHandling();

        config()->set('laravel-enso.paths.imports', self::IMPORT_DIRECTORY);

        $this->signIn(User::first());
    }

    /** @test */
    public function cant_import_entries_with_errors()
    {
        $this->post(route('import.run', ['owners'], false),
            ['file' => $this->getContentErrorsUploadedFile()])
            ->assertStatus(200)
            ->assertJsonFragment([
                'hasContentErrors' => true,
                'errors'           => 3,
                'successful'       => 1,
            ])
            ->assertJsonFragment([
                'name'      => 'This sheet lines are doubles',
                'rowNumber' => 3,
            ])
            ->assertJsonFragment([
                'name'  => 'Value must be unique in its column: name',
                'value' => 'NotUniqueName',
            ])
            ->assertJsonFragment([
                'name'   => 'The is active field must be true or false.',
                'column' => 'is_active',
                'value'  => 'notBoolean',
            ]);

        $this->assertNull(Owner::whereIn('name', ['NotUniqueName', 'BooleanTest'])
                ->first()
        );
        $this->assertNotNull(Owner::whereName('TestName')->first());
        $this->assertNotNull(
            DataImport::whereOriginalName(self::CONTENT_ERRORS_TEST_FILE)
                ->first()
        );

        $this->cleanUp();
    }

    /** @test */
    public function stops_on_content_errors()
    {
        config()->set('importing.configs.owners.stopOnErrors', true);

        $this->post(route('import.run', ['owners'], false),
            ['file' => $this->getContentErrorsUploadedFile()])
            ->assertStatus(200)
            ->assertJsonFragment([
                'hasContentErrors' => true,
                'errors'           => 3,
                'successful'       => 0,
            ]);

        $this->assertNull(Owner::whereName('TestName')->first());
        $this->assertNull(
            DataImport::whereOriginalName(self::CONTENT_ERRORS_TEST_FILE)
                ->first()
        );

        $this->cleanUp();
    }

    private function getContentErrorsUploadedFile()
    {
        \File::copy(
            self::PATH . self::CONTENT_ERRORS_FILE,
            self::PATH . self::CONTENT_ERRORS_TEST_FILE
        );

        return new UploadedFile(
            self::PATH . self::CONTENT_ERRORS_TEST_FILE,
            self::CONTENT_ERRORS_TEST_FILE, null, null, null, true
        );
    }

    private function cleanUp()
    {
        Storage::deleteDirectory(self::IMPORT_DIRECTORY);
    }
}
