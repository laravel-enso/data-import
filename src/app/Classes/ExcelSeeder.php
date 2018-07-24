<?php

namespace LaravelEnso\DataImport\app\Classes;

use Illuminate\Database\Seeder;
use Illuminate\Http\UploadedFile;
use LaravelEnso\DataImport\app\Models\DataImport;

class ExcelSeeder extends Seeder
{
    protected $type = 'type';

    public function run()
    {
        auth()->loginUsingId(1);

        DataImport::reguard();

        DataImport::store([$this->importFile()], $this->type);
    }

    private function importFile()
    {
        \File::copy(
            storage_path('app/files/'.$this->filename()),
            storage_path('app/temp/'.$this->filename())
        );

        return new UploadedFile(
            storage_path('app/temp/'.$this->filename()),
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
