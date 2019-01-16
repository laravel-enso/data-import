<?php

namespace LaravelEnso\DataImport\app\Classes;

use Illuminate\Database\Seeder;
use Illuminate\Http\UploadedFile;
use LaravelEnso\DataImport\app\Enums\Statuses;
use LaravelEnso\DataImport\app\Models\DataImport;

class ExcelSeeder extends Seeder
{
    protected $type;
    protected $filename;

    public function run()
    {
        // DataImport::reguard(); //TODO test!

        DataImport::create([
            'type' => $this->type,
            'status' => Statuses::Waiting,
        ])->run($this->importFile());
    }

    private function importFile()
    {
        return new UploadedFile(
            storage_path('app/seeds/'.$this->filename),
            $this->filename,
            null,
            null,
            null,
            true
        );
    }
}
