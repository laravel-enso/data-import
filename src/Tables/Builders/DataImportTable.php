<?php

namespace LaravelEnso\DataImport\Tables\Builders;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use LaravelEnso\DataImport\Enums\Statuses;
use LaravelEnso\DataImport\Models\DataImport;
use LaravelEnso\DataImport\Models\RejectedImport;
use LaravelEnso\Tables\Contracts\ConditionalActions;
use LaravelEnso\Tables\Contracts\Table;

class DataImportTable implements Table, ConditionalActions
{
    protected const TemplatePath = __DIR__.'/../Templates/dataImports.json';

    public function query(): Builder
    {
        return DataImport::selectRaw("
            data_imports.id, data_imports.type, data_imports.status,
            files.original_name as name, data_imports.successful,
            data_imports.failed, data_imports.created_at,
            {$this->rawTime()} as time, rejected_imports.id as rejectedId,
            {$this->rawDuration()} as duration, data_imports.created_by
        ")->with('createdBy.person:id,appellative,name', 'createdBy.avatar:id,user_id')
            ->join('files', fn ($join) => $join
                ->on('files.attachable_id', 'data_imports.id')
                ->where('files.attachable_type', DataImport::morphMapKey()))
            ->leftJoin('rejected_imports', 'data_imports.id', '=', 'rejected_imports.data_import_id')
            ->leftJoin('files as rejected_files', fn ($join) => $join
                ->on('rejected_files.attachable_id', 'rejected_imports.id')
                ->where('rejected_files.attachable_type', RejectedImport::morphMapKey()));
    }

    public function templatePath(): string
    {
        return static::TemplatePath;
    }

    private function rawDuration(): string
    {
        switch (DB::getDriverName()) {
            case 'sqlite':
                return $this->sqliteDuration();
            case 'mysql':
                return $this->mysqlDuration();
            case 'pgsql':
                return $this->postgresDuration();
            default:
                return 'N/A';
        }
    }

    private function rawTime(): string
    {
        return DB::getDriverName() === 'pgsql'
            ? 'data_imports.created_at::time'
            : 'TIME(data_imports.created_at)';
    }

    private function sqliteDuration()
    {
        $days = 'julianday(data_imports.updated_at) - julianday(data_imports.created_at)';
        $seconds = "({$days}) * 86400.0";

        return "time({$seconds}, 'unixepoch')";
    }

    private function mysqlDuration()
    {
        $seconds = 'timestampdiff(second, data_imports.created_at, data_imports.updated_at)';

        return "sec_to_time({$seconds})";
    }

    private function postgresDuration()
    {
        $seconds = 'EXTRACT(EPOCH FROM (data_imports.updated_at::timestamp- data_imports.created_at::timestamp ))';

        return "($seconds || ' second')::interval";
    }

    public function render(array $row, string $action): bool
    {
        switch ($action) {
            case 'download-rejected':
                return $row['rejectedId'] !== null;
            case 'cancel':
                return Statuses::isCancellable($row['status']);
            case 'restart':
                return $row['status'] === Statuses::Cancelled;
            default:
                return true;
        }
    }
}
