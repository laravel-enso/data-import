<?php

namespace Tests;

use App\User;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use LaravelEnso\DataImport\App\Http\Controllers\ImportTemplateController;
use LaravelEnso\DataImport\app\Models\ImportTemplate;

class ImportTemplateControllerTest extends TestCase
{
    use DatabaseMigrations;

    private $user;
    /** @var ImportTemplateController */
    private $itController;

    protected function setUp()
    {
        parent::setUp();

        $this->user = User::first();
        $this->itController = new ImportTemplateController();
    }

    /** @test */
    public function can_get_existing_template()
    {
        $type = 1;

        // Arrange
        // authenticate as first user
        $this->be($this->user);
        $importTemplate = ImportTemplate::create([
            'type'          => $type,
            'original_name' => 'Test Original Name.xls',
            'saved_name'    => 'testSavedName.xls',
        ]);

        // Act
        $result = $this->itController->getTemplate($type);

        // Assert
        // got proper template
        $this->assertEquals($importTemplate->id, $result->id);
    }

    /** @test */
    public function can_get_blank_template()
    {
        // Arrange
        // a type that doesn't exist
        $type = 99;

        // Act
        $result = $this->itController->getTemplate($type);

        // Assert
        // should be able to get a blank (new) template
        $this->assertNull($result->id);
    }

    /** @test */
    public function get_exception_for_nonexistent_file_download()
    {
        $this->be($this->user);
        $importTemplate = ImportTemplate::create([
            'type'          => 1,
            'original_name' => 'Test Original Name.xls',
            'saved_name'    => 'testSavedName.xls',
        ]);

        // Act
        try {
            $result = $this->itController->download($importTemplate);
        } catch (\Exception $e) {
            // Assert
            // should file not found exception
            $this->assertInstanceOf(FileNotFoundException::class, $e);
        }
    }
}
