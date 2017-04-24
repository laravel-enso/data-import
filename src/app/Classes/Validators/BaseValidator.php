<?php
/**
 * Created by PhpStorm.
 * User: mihai
 * Date: 22.02.2017
 * Time: 16:05.
 */

namespace LaravelEnso\DataImport\app\Classes\Validators;

use LaravelEnso\DataImport\app\Classes\Reporting\ValidationSummary;
use Maatwebsite\Excel\Collections\SheetCollection;

class BaseValidator extends AbstractValidator
{
    protected $template;
    protected $xlsx;
    protected $summary;
    protected $structureValidator;
    protected $contentValidator;
    protected $customValidatorClass;

    public function __construct($template, SheetCollection $xlsx, ValidationSummary $summary, $customValidatorClass, int $sheetEntriesLimit)
    {
        parent::__construct($template, $xlsx, $summary);
        $this->structureValidator = new StructureValidator($this->template, $this->xlsx, $this->summary, $sheetEntriesLimit);
        $this->contentValidator = new ContentValidator($this->template, $this->xlsx, $this->summary, $customValidatorClass);
    }

    public function run()
    {
        $this->structureValidator->run();

        if ($this->summary->hasErrors) {
            return;
        }

        $this->contentValidator->run();
    }
}
