<?php

namespace LaravelEnso\DataImport;

use Illuminate\Support\ServiceProvider;
use LaravelEnso\Mails\Preview\PreviewDefinition;
use LaravelEnso\Mails\Preview\PreviewRegistry;

class MailServiceProvider extends ServiceProvider
{
    public function boot(PreviewRegistry $registry): void
    {
        $registry->register(new PreviewDefinition(
            key: 'data-import-done',
            name: 'Data Import Done',
            view: 'laravel-enso/data-import::emails.import',
            data: [
                'name' => 'Jane',
                'import' => new class {
                    public int $successful = 1206;

                    public int $failed = 42;

                    public int $entries = 1248;

                    public ?object $rejected = null;

                    public object $file;

                    public function __construct()
                    {
                        $this->file = new class {
                            public string $original_name = 'users-import.xlsx';

                            public function temporaryLink(): string
                            {
                                return 'https://example.com/files/users-import.xlsx';
                            }
                        };
                    }

                    public function type(): string
                    {
                        return 'Users';
                    }
                },
            ],
            section: PreviewDefinition::Core,
        ));
    }
}
