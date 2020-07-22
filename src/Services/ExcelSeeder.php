<?php

namespace LaravelEnso\DataImport\Services;

use Illuminate\Database\Seeder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class ExcelSeeder extends Seeder
{
    protected string $type;
    protected string $filename;

    public function run()
    {
        (new Import($this->type, $this->importFile()))
            ->handle();
    }

    private function importFile()
    {
        //TODO refactor
        return new UploadedFile(
            Storage::path('seeds'.DIRECTORY_SEPARATOR.$this->filename),
            $this->filename,
            null,
            null,
            true
        );
    }
}
