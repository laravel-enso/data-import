<?php

namespace LaravelEnso\DataImport\app\Classes\Validators;

use LaravelEnso\DataImport\app\Classes\ImportConfiguration;
use LaravelEnso\DataImport\app\Classes\Reporting\ImportSummary;
use Maatwebsite\Excel\Collections\SheetCollection;

class BaseValidator extends AbstractValidator
{
    protected $structureValidator;
    protected $contentValidator;

    public function __construct(ImportConfiguration $config, SheetCollection $sheets, ImportSummary $summary)
    {
        parent::__construct($config->getTemplate(), $sheets, $summary);

        $this->structureValidator = new StructureValidator($config, $sheets, $this->summary);
        $this->contentValidator = new ContentValidator($config, $sheets, $this->summary);
    }

    public function run()
    {
        $this->structureValidator->run();

        if (!$this->summary->hasErrors()) {
            $this->contentValidator->run();
        }
    }
}
