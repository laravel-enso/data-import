<?php

namespace LaravelEnso\DataImport\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Config;
use LaravelEnso\DataImport\Models\DataImport;

class ImportDone extends Notification implements ShouldQueue
{
    use Queueable;

    public $dataImport;

    public function __construct(DataImport $dataImport)
    {
        $this->dataImport = $dataImport;
    }

    public function via()
    {
        return array_merge(['mail'], config('enso.imports.notifications'));
    }

    public function toBroadcast()
    {
        return (new BroadcastMessage([
            'level' => 'success',
            'title' => $this->title(),
            'body' => $this->filename(),
            'icon' => 'file-excel',
        ]))->onQueue($this->queue);
    }

    public function toMail($notifiable)
    {
        return (new MailMessage())
            ->subject($this->subject())
            ->markdown('laravel-enso/data-import::emails.import', [
                'name' => $notifiable->person->appellative
                    ?? $notifiable->person->name,
                'dataImport' => $this->dataImport,
            ]);
    }

    public function toArray()
    {
        return [
            'body' => "{$this->title()}: {$this->filename()}",
            'icon' => 'file-excel',
        ];
    }

    private function title()
    {
        return __(':name import done', ['name' => $this->dataImport->name()]);
    }

    private function filename()
    {
        return $this->dataImport->file->original_name;
    }

    private function subject()
    {
        $name = __(Config::get('app.name'));

        return "{$name}: {$this->title()}";
    }
}
