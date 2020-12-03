<?php

namespace LaravelEnso\DataImport\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Config;
use LaravelEnso\DataImport\Services\DTOs\Row;

class RejectedChunk extends Model
{
    use HasFactory;

    protected $table = 'rejected_import_chunks';

    protected $guarded = ['id'];

    protected $casts = ['header' => 'array', 'content' => 'array'];

    public function dataImport()
    {
        return $this->belongsTo(DataImport::class);
    }

    public function add(Row $row): void
    {
        $row->content()->put(
            Config::get('enso.imports.errorColumn'),
            $row->errors()->implode(' | ')
        );

        if (count($this->header) === 0) {
            $this->header = $row->content()->keys()->toArray();
        }

        $content = $this->content;
        $content[] = $row->content()->values();
        $this->content = $content;
    }

    public function count(): int
    {
        return count($this->content);
    }

    public function empty(): bool
    {
        return count($this->content) === 0;
    }
}
