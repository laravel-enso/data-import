<?php

namespace LaravelEnso\DataImport\app\Models;

use Illuminate\Http\UploadedFile;
use Illuminate\Database\Eloquent\Model;
use LaravelEnso\TrackWho\app\Traits\CreatedBy;
use LaravelEnso\FileManager\app\Traits\HasFile;
use LaravelEnso\ActivityLog\app\Traits\LogActivity;
use LaravelEnso\FileManager\app\Contracts\Attachable;
use LaravelEnso\DataImport\app\Classes\Importers\DataImporter;
use LaravelEnso\DataImport\app\Classes\Validators\Template as TemplateValidator;

class DataImport extends Model implements Attachable
{
    use HasFile, CreatedBy, LogActivity;

    protected $extensions = ['xlsx'];

    protected $fillable = ['type', 'name', 'summary'];

    protected $casts = ['summary' => 'object'];

    protected $loggableLabel = 'type';

    protected $loggable = [];

    public function getSuccessfulAttribute()
    {
        $import = self::find($this->id);

        return $import->summary->successful;
    }

    public function getIssuesAttribute()
    {
        $import = self::find($this->id);

        return collect($import->summary->contentIssues)
            ->count();
    }

    public function summary()
    {
        return json_encode($this->summary);
    }

    public function store(UploadedFile $file, $type)
    {
        $this->validateTemplate($type);

        $importer = new DataImporter($file, $type);

        \DB::transaction(function () use ($importer, $file, $type) {
            $importer->run();

            if (!$importer->fails()) {
                $this->create([
                    'name' => $file->getClientOriginalName(),
                    'type' => $type,
                    'summary' => $importer->summary(),
                ])->upload($file);
            }
        });

        return $importer->summary();
    }

    private function validateTemplate(string $type)
    {
        if (app()->environment() === 'local') {
            (new TemplateValidator($type))->run();
        }

        return $this;
    }

    public function folder()
    {
        return config('enso.paths.imports');
    }
}
