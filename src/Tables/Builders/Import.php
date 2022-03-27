<?php

namespace LaravelEnso\DataImport\Tables\Builders;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use LaravelEnso\DataImport\Enums\Statuses;
use LaravelEnso\DataImport\Models\Import as Model;
use LaravelEnso\Tables\Contracts\ConditionalActions;
use LaravelEnso\Tables\Contracts\Table;

class Import implements Table, ConditionalActions
{
    private const TemplatePath = __DIR__.'/../Templates/imports.json';

    public function query(): Builder
    {
        return Model::selectRaw("
            data_imports.id, data_imports.type, data_imports.status,
            files.original_name as name, data_imports.successful,
            data_imports.failed, data_imports.created_at,
            {$this->rawTime()} as time, rejected_imports.id as rejected_id,
            {$this->rawDuration()} as duration, data_imports.created_by
        ")->with('createdBy.person:id,appellative,name', 'createdBy.avatar:id,user_id')
            ->join('files', 'files.id', 'data_imports.file_id')
            ->leftJoin('rejected_imports', 'data_imports.id', '=', 'rejected_imports.import_id')
            ->leftJoin('files as rejected_files', fn ($join) => $join
                ->on('rejected_files.id', 'rejected_imports.file_id'));
    }

    public function templatePath(): string
    {
        return self::TemplatePath;
    }

    public function render(array $row, string $action): bool
    {
        return match ($action) {
            'download-rejected' => $row['rejected_id'] !== null,
            'cancel' => in_array($row['status'], Statuses::running()),
            'restart' => $row['status'] === Statuses::Cancelled,
            default => true,
        };
    }

    protected function rawDuration(): string
    {
        return match (DB::getDriverName()) {
            'sqlite' => $this->sqliteDuration(),
            'mysql' => $this->mysqlDuration(),
            'pgsql' => $this->postgresDuration(),
            default => 'N/A',
        };
    }

    protected function rawTime(): string
    {
        return DB::getDriverName() === 'pgsql'
            ? 'data_imports.created_at::time'
            : 'TIME(data_imports.created_at)';
    }

    protected function sqliteDuration()
    {
        $days = 'julianday(data_imports.updated_at) - julianday(data_imports.created_at)';
        $seconds = "({$days}) * 86400.0";

        return "time({$seconds}, 'unixepoch')";
    }

    protected function mysqlDuration()
    {
        $seconds = 'timestampdiff(second, data_imports.created_at, data_imports.updated_at)';

        return "sec_to_time({$seconds})";
    }

    protected function postgresDuration()
    {
        $seconds = 'EXTRACT(EPOCH FROM (data_imports.updated_at::timestamp- data_imports.created_at::timestamp ))';

        return "($seconds || ' second')::interval";
    }
}
