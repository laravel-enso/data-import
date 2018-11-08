<?php

namespace LaravelEnso\DataImport\app\Classes;

use Illuminate\Support\Collection;
use LaravelEnso\DataImport\app\Classes\Validators\Content as ContentValidator;
use LaravelEnso\DataImport\app\Classes\Validators\Structure as StructureValidator;

class Validator
{
    private $template;
    private $sheets;
    private $summary;

    public function __construct(Template $template, Collection $sheets, Summary $summary)
    {
        $this->template = $template;
        $this->sheets = $sheets;
        $this->summary = $summary;
    }

    public function run()
    {
        $this->make(StructureValidator::class)->run();

        if ($this->summary->hasIssues()) {
            return;
        }

        $this->make(ContentValidator::class)->run();

        if ($this->template->validator()) {
            $this->make($this->template->validator())
                ->run();
        }
    }

    private function make(string $validatorClass)
    {
        return new $validatorClass(
            $this->template,
            $this->sheets,
            $this->summary
        );
    }
}
