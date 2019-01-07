<?php

namespace LaravelEnso\DataImport\app\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use LaravelEnso\DataImport\app\Enums\Statuses;
use LaravelEnso\DataImport\app\Models\DataImport;
use LaravelEnso\PermissionManager\app\Models\Permission;

class Upgrade extends Command
{
    protected $signature = 'enso:dataimport:upgrade';

    protected $description = 'This command will upgrade Data Import to 3.0';

    public function handle()
    {
        $this->info('The upgrade process has started');
        $this->upgrade();
        $this->info('The upgrade process was successful.');
    }

    private function upgrade()
    {
        \DB::transaction(function () {
            $this->updateTable()
                ->updatePermissions();
        });
    }

    private function updateTable()
    {
        $this->info('Updating data_imports table');

        if (Schema::hasColumn('data_imports', 'chunks')) {
            $this->info('Table already updated');

            return $this;
        }

        Schema::table('data_imports', function (Blueprint $table) {
            $table->integer('chunks')->nullable()->after('type');
            $table->integer('successful')->nullable()->after('type');
            $table->integer('failed')->nullable()->after('successful');
            $table->tinyInteger('status')->after('failed')->nullable();
        });

        DataImport::whereNull('status')
            ->update(['status' => Statuses::Processed]);

        DataImport::get()->each(function ($import) {
            $import->update([
                'successful' => optional($import->summary)->successful,
                'failed' => optional($import->summary)->issues,
            ]);
        });

        Schema::table('data_imports', function (Blueprint $table) {
            $table->tinyInteger('status')->change();
            $table->dropColumn('summary');
        });

        $this->info('Table successfuly updated');

        return $this;
    }

    private function updatePermissions()
    {
        $this->info('Updating data_imports permissions');

        if (Permission::whereName('import.store')->first()) {
            $this->info('The permissions were already updated');

            return $this;
        }

        Permission::create([
            'name' => 'import.store',
            'description' => 'Upload file for import',
            'type' => 1,
            'is_default' => false
        ]);

        Permission::whereName('import.getSummary')
            ->update(['name' => 'import.summary']);

        Permission::whereName('import.getTemplate')
            ->update(['name' => 'import.template']);

        Permission::whereName('import.summary')
            ->update([
                'name' => 'import.downloadRejected',
                'description' => 'Download rejected summary for import',
            ]);

        $this->info('Permissions successfuly updated');

        return $this;
    }
}
