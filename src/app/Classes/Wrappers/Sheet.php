<?php

namespace LaravelEnso\DataImport\app\Classes\Wrappers;

use Illuminate\Support\Collection;

class Sheet extends Collection
{
    protected $title;
    public $header;

    public function getTitle()
    {
        return $this->title;
    }

    public function setTitle($title)
    {
        $this->title = $title;
    }

    public function processAndAddRow($row)
    {
        $newRow = collect();
        foreach ($this->header as $key => $value) {
            $newRow->put($value, $row[$key]);
        }

        $this->push($newRow);
    }
}
