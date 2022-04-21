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
        return Model::selectRaw(implode(', ', $this->select()))
            ->with($this->with());
    }

    public function templatePath(): string
    {
        return self::TemplatePath;
    }

    public function render(array $row, string $action): bool
    {
        $hasFile = $row['file_id'] !== null;

        return match ($action) {
            'download-rejected' => $row['rejected'] !== null,
            'download' => $hasFile,
            'cancel' => in_array($row['status'], Statuses::running()),
            'restart' => $hasFile && $row['status'] === Statuses::Cancelled,
            default => true,
        };
    }

    protected function select(): array
    {
        return [
            'id', 'type', 'status', 'successful', 'failed', 'created_at',
            'file_id', 'created_by', "{$this->rawTime()} as time",
            "{$this->rawDuration()} as duration",
        ];
    }

    protected function with(): array
    {
        return [
            'file:id,original_name', 'rejected:import_id,file_id',
            'createdBy' => fn ($user) => $user->with([
                'person:id,appellative,name', 'avatar:id,user_id',
            ]),
        ];
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
            ? 'created_at::time'
            : 'TIME(created_at)';
    }

    protected function sqliteDuration()
    {
        $days = 'julianday(updated_at) - julianday(created_at)';
        $seconds = "({$days}) * 86400.0";

        return "time({$seconds}, 'unixepoch')";
    }

    protected function mysqlDuration()
    {
        $seconds = 'timestampdiff(second, created_at, updated_at)';

        return "sec_to_time({$seconds})";
    }

    protected function postgresDuration()
    {
        $seconds = 'EXTRACT(EPOCH FROM (updated_at::timestamp - created_at::timestamp ))';

        return "($seconds || ' second')::interval";
    }
}
