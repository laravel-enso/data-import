<?php

namespace LaravelEnso\DataImport\Commands;

use Illuminate\Console\Command;
use LaravelEnso\DataImport\Enums\Status;
use LaravelEnso\DataImport\Models\Import;

class Purge extends Command
{
    protected $signature = 'enso:data-import:purge';

    protected $description = 'Removes old imports';

    public function handle()
    {
        Import::expired()->notDeletable()
            ->update(['status' => Status::Cancelled]);

        Import::expired()->deletable()->get()->each->purge();
    }
}
