<?php

namespace LaravelEnso\DataImport\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RejectedChunk extends Model
{
    use HasFactory;

    protected $table = 'rejected_import_chunks';

    protected $guarded = ['id'];

    protected $casts = ['header' => 'array', 'rows' => 'array'];

    public function import()
    {
        return $this->belongsTo(Import::class);
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

    public function empty(): bool
    {
        return count($this->rows) === 0;
    }
}
