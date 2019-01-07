<?php

namespace LaravelEnso\DataImport\app\Classes\Writer;

use Illuminate\Support\Collection;
use LaravelEnso\DataImport\app\Models\DataImport;

class RejectedDump
{
    private $import;
    private $sheetName;
    private $rejected;
    private $index;
    private $dump;

    public function __construct(DataImport $import, string $sheetName, Collection $rejected, int $index)
    {
        $this->import = $import;
        $this->sheetName = $sheetName;
        $this->rejected = $rejected;
        $this->index = $index;
    }

    public function handle()
    {
        \Storage::put($this->path(), $this->dump());
    }

    private function dump()
    {
        $this->dump = collect();
        $this->dump->push($this->sheetName);
        $this->dump->push($this->rejected->first()->keys());
        $this->dump = $this->dump->merge($this->rejectedValues());

        return json_encode($this->dump);
    }

    private function rejectedValues()
    {
        return $this->rejected->map(function ($row) {
            return $row->values();
        });
    }

    private function path()
    {
        return $this->import->rejectedFolder()
            .DIRECTORY_SEPARATOR
            .'rejected_dump_'.$this->index.'.json';
    }
}
