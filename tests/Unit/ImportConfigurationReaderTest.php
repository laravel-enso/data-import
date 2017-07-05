<?php

/**
 * Created by PhpStorm.
 * User: mihai
 * Date: 06.06.2017
 * Time: 11:33.
 */

namespace Tests;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use LaravelEnso\Core\app\Exceptions\EnsoException;
use LaravelEnso\DataImport\app\Classes\ImportConfiguration;

class ImportConfigurationTest extends TestCase
{
    use DatabaseMigrations;

    /** @var ImportConfiguration */
    private $importCfgReader;
    private $invalidImportCfgReader;
    private $DEFAULT_EXAMPLE_PACKAGE_TYPE = 0;

    protected function setUp()
    {
        parent::setUp();

        $this->importCfgReader = new ImportConfiguration($this->DEFAULT_EXAMPLE_PACKAGE_TYPE);
    }

    /** @test*/
    public function can_get_template_class()
    {
        $templateClass = $this->importCfgReader->templateClass;
        $this->assertEquals(\App\Importing\Templates\ExampleTemplate::class, $templateClass);
    }

    /** @test*/
    public function can_get_importer_class()
    {
        $importerClass = $this->importCfgReader->importerClass;
        $this->assertEquals(\App\Importing\Importers\ExampleImporter::class, $importerClass);
    }

    /** @test*/
    public function can_get_custom_validator_class()
    {
        $validatorClass = $this->importCfgReader->customValidatorClass;
        $this->assertEquals(\App\Importing\Validators\ExampleValidator::class, $validatorClass);
    }

    /** @test*/
    public function can_get_sheet_entries_limit()
    {
        $validatorClass = $this->importCfgReader->sheetEntriesLimit;
        $this->assertEquals(5000, $validatorClass);
    }

    /** @test*/
    public function cant_create_reader_of_invalid_type()
    {
        try {
            $this->invalidImportCfgReader = new ImportConfiguration(-1); //invalid type
        } catch (\Exception $e) {
            $this->assertInstanceOf(EnsoException::class, $e);
        }
    }
}
