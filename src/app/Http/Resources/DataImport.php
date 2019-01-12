<?php

namespace LaravelEnso\DataImport\app\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use LaravelEnso\DataImport\app\Enums\ImportTypes;

class DataImport extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'entries' => $this->entries(),
            'type' => ImportTypes::get($this->type),
            'since' => $this->created_at,
            'status' => $this->status,
        ];
    }
}
