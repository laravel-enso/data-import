<?php

namespace LaravelEnso\DataImport\Services;

use Illuminate\Database\Seeder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Config;

class ExcelSeeder extends Seeder
{
    protected string $type;
    protected string $filename;

    public function run()
    {
        (new Import($this->type, $this->importFile()))->handle();
    }

    private function importFile()
    {
        $path = Config::get('enso.imports.seederPath');
        //TODO refactor
        return new UploadedFile("{$path}/{$this->filename}", $this->filename, null, null, true);
    }
}
