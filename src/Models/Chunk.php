<?php

namespace LaravelEnso\DataImport\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use LaravelEnso\DataImport\Contracts\Importable;
use LaravelEnso\DataImport\Services\Template;

class Chunk extends Model
{
    use HasFactory;

    protected $table = 'import_chunks';

    protected $guarded = ['id'];

    public function import(): BelongsTo
    {
        return $this->belongsTo($this->importModel());
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

    protected function casts(): array
    {
        return [
            'header' => 'array', 'rows' => 'array',
        ];
    }

    protected function importModel(): string
    {
        return Import::class;
    }
}
