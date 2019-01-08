<?php

namespace LaravelEnso\DataImport\app\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class Import extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'entries' => $this->entries(),
            'name' => $this->whenLoaded('file', $this->file->original_name),
            'since' => $this->created_at,
        ];
    }
}
