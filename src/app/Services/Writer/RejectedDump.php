<?php

namespace LaravelEnso\DataImport\app\Services\Writer;

use Illuminate\Support\Collection;
use LaravelEnso\DataImport\app\Models\DataImport;

class RejectedDump
{
    private $dataImport;
    private $sheetName;
    private $rejected;
    private $dump;

    public function __construct(DataImport $dataImport, string $sheetName, Collection $rejected)
    {
        $this->dataImport = $dataImport;
        $this->sheetName = $sheetName;
        $this->rejected = $rejected;
        $this->dump = collect();
    }

    public function handle()
    {
        $this->prepare()
            ->store();
    }

    private function prepare()
    {
        $this->dump->push($this->sheetName);
        $this->dump->push($this->rejected->first()->keys());
        $this->dump = $this->dump->merge($this->rejectedValues());

        return $this;
    }

    private function store()
    {
        \Storage::put(
            $this->path(), json_encode($this->dump)
        );
    }

    private function rejectedValues()
    {
        return $this->rejected->map(function ($row) {
            return $row->values();
        });
    }

    private function path()
    {
        return $this->dataImport->rejectedFolder()
            .DIRECTORY_SEPARATOR
            .'rejected_dump_'.$this->dataImport->chunks.'.json';
    }
}
