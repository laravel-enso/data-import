<?php

namespace LaravelEnso\DataImport\app\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use LaravelEnso\DataImport\app\Models\DataImport;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Messages\BroadcastMessage;

class ImportDone extends Notification
{
    use Queueable;

    public $import;

    public function __construct(DataImport $import)
    {
        $this->import = $import;
        $this->queue = config('enso.imports.queues.notifications');
    }

    public function via($notifiable)
    {
        return array_merge(['mail'], config('enso.imports.notifications'));
    }

    public function toBroadcast($notifiable)
    {
        return new BroadcastMessage([
            'level' => 'success',
            'title' => __('Import Done'),
            'body' => $this->filename(),
            'icon' => 'file-excel',
        ]);
    }

    public function toMail($notifiable)
    {
        return (new MailMessage())
            ->subject(__(config('app.name')).': '.__('Import Done'))
            ->markdown('laravel-enso/dataimport::emails.import', [
                'name' => $notifiable->person->appellative
                    ?: $notifiable->person->name,
                'filename' => $this->filename(),
                'successful' => $this->import->successful,
                'failed' => $this->import->failed,
                'entries' => $this->import->entries(),
            ]);
    }

    public function toArray($notifiable)
    {
        return [
            'body' => __('Import done').': '.$this->filename(),
            'icon' => 'file-excel',
        ];
    }

    private function filename()
    {
        return $this->import->file->original_name;
    }
}
