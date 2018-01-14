<?php

namespace LaravelEnso\DataImport\app\Classes\Importers;

use Maatwebsite\Excel\Collections\SheetCollection;
use LaravelEnso\DataImport\app\Classes\ImportConfiguration;
use LaravelEnso\DataImport\app\Classes\Reporting\ImportSummary;
use LaravelEnso\DataImport\app\Classes\Validators\ContentValidator;
use LaravelEnso\DataImport\app\Classes\Validators\StructureValidator;

final class Importer
{
    protected $sheets;
    protected $summary;
    protected $structureValidator;
    protected $contentValidator;
    protected $importer;
    protected $skipsContentErrors;

    public function __construct(string $fileName, ImportConfiguration $config, SheetCollection $sheets)
    {
        $this->sheets = $sheets;
        $this->skipsContentErrors = !$config->getStopOnErrors();
        $this->summary = new ImportSummary($fileName);
        $this->structureValidator = new StructureValidator($config, $this->sheets, $this->summary);
        $this->contentValidator = new ContentValidator($config, $this->sheets, $this->summary);
        $this->importer = $config->getImporter($this->sheets, $this->summary);
    }

    public function run()
    {
        $this->structureValidator->run();

        if ($this->summary->hasStructureErrors()) {
            return false;
        }

        $this->trimContents();

        $this->contentValidator->run();

        if (!$this->summary->hasErrors() || $this->canRunWithErrors()) {
            $this->importer->run();
        }
    }

    public function fails()
    {
        return $this->summary->hasStructureErrors()
            || ($this->summary->hasContentErrors() && !$this->skipsContentErrors);
    }

    public function canRunWithErrors()
    {
        return !$this->summary->hasStructureErrors()
        && $this->skipsContentErrors;
    }

    public function getSummary()
    {
        return $this->summary;
    }

    private function trimContents()
    {
        $this->sheets->each(function ($sheet) {
            $sheet->each(function ($row) {
                foreach ($row as $key => $value) {
                    $row[$key] = is_string($value) ? trim($value) : $value;
                }
            });
        });
    }
}
