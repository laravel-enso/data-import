<?php

namespace LaravelEnso\DataImport\Commands;

use Illuminate\Console\Command;
use LaravelEnso\DataImport\Enums\Status;
use LaravelEnso\DataImport\Models\Import;

class CancelStuck extends Command
{
    protected $signature = 'enso:data-import:cancel-stuck';

    protected $description = 'Cancels stuck imports';

    public function handle()
    {
        Import::stuck()->update([
            'status' => Status::Cancelled->value,
            'batch' => null,
        ]);
    }
}
