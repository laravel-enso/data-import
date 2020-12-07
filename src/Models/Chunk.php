<?php

namespace LaravelEnso\DataImport\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use LaravelEnso\DataImport\Contracts\Importable;
use LaravelEnso\DataImport\Services\Template;

class Chunk extends Model
{
    use HasFactory;

    protected $table = 'import_chunks';

    protected $guarded = ['id'];

    protected $casts = ['header' => 'array', 'rows' => 'array'];

    public function import()
    {
        return $this->belongsTo(DataImport::class);
    }

    public function template(): Template
    {
        return $this->import->template();
    }

    public function importer(): Importable
    {
        return $this->template()->importer($this->sheet);
    }

    public function add(array $row): void
    {
        $rows = $this->rows;
        $rows[] = $row;
        $this->rows = $rows;
    }

    public function count(): int
    {
        return count($this->rows);
    }
}
