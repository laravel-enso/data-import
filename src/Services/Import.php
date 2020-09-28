<?php

namespace LaravelEnso\DataImport\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Config;
use LaravelEnso\DataImport\Exceptions\DataImport as Exception;
use LaravelEnso\DataImport\Jobs\Import as Job;
use LaravelEnso\DataImport\Models\DataImport;
use LaravelEnso\DataImport\Services\Validators\Params\Data as Params;
use LaravelEnso\Helpers\Services\Obj;

class Import
{
    private DataImport $dataImport;
    private string $type;
    private UploadedFile $file;
    private Template $template;
    private Structure $structure;
    private Obj $params;

    public function __construct(string $type, UploadedFile $file, array $params = [])
    {
        $this->type = $type;
        $this->file = $file;
        $this->params = new Obj($params);
    }

    public function handle(): self
    {
        $this->checkAlreadyRunning()
            ->init()
            ->validate()
            ->import();

        return $this;
    }

    public function dataImport(): DataImport
    {
        return $this->dataImport;
    }

    public function summary(): array
    {
        return $this->structure->summary();
    }

    private function import(): self
    {
        if ($this->structure->isValid()) {
            tap($this->dataImport)->save()
                ->upload($this->file);

            Job::dispatch($this->dataImport, $this->template, $this->structure->sheets(), $this->params);
        }

        return $this;
    }

    private function init(): self
    {
        $this->dataImport = factory(DataImport::class)->make([
            'type' => $this->type,
            'params' => $this->params,
        ]);

        $this->template = new Template($this->type);
        $this->structure = new Structure($this->template, $this->file);

        return $this;
    }

    private function validate(): self
    {
        (new Params($this->template, $this->params))->validate();

        return $this;
    }

    private function checkAlreadyRunning(): self
    {
        if ($this->alreadyRunning()) {
            throw Exception::alreadyRunning();
        }

        return $this;
    }

    private function alreadyRunning(): bool
    {
        return DataImport::whereType($this->type)
            ->inprogress()
            ->where('created_at', '>', now()->subSeconds(
                Config::get('enso.imports.timeout')
            ))->exists();
    }
}
