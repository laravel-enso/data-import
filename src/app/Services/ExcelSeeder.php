<?php

namespace LaravelEnso\DataImport\app\Services;

use Illuminate\Database\Seeder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use LaravelEnso\DataImport\app\Enums\Statuses;
use LaravelEnso\DataImport\app\Models\DataImport;

class ExcelSeeder extends Seeder
{
    protected $type;
    protected $filename;

    public function run()
    {
        // DataImport::reguard(); //TODO test!

        factory(DataImport::class)->create([
            'type' => $this->type,
            'status' => Statuses::Waiting,
        ])->handle($this->importFile());
    }

    private function importFile()
    {
        return new UploadedFile(
            Storage::path('seeds'.DIRECTORY_SEPARATOR.$this->filename),
            $this->filename,
            null,
            null,
            null,
            true
        );
    }
}
