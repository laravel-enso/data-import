<?php

namespace LaravelEnso\DataImport\app\Classes;

use Illuminate\Database\Seeder;
use Illuminate\Http\UploadedFile;
use LaravelEnso\DataImport\app\Models\DataImport;

class ExcelSeeder extends Seeder
{
    protected $type;

    public function run()
    {
        auth()->loginUsingId(1);

        DataImport::reguard();

        (new DataImport)->store($this->importFile(), $this->type);
    }

    private function importFile()
    {
        return new UploadedFile(
            storage_path('app/seeds/'.$this->filename()),
            $this->filename(),
            null,
            null,
            null,
            true
        );
    }

    private function filename()
    {
        return $this->type.'.xlsx';
    }
}
