<?php

namespace LaravelEnso\DataImport\app\Classes\Wrappers;

use Illuminate\Support\Collection;

class Sheet extends Collection
{
    protected $title;
    protected $header = [];

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
        if (empty($this->header)) {
            $this->setHeader($row);

            return;
        }

        $newRow = new Row();
        foreach ($this->header as $key => $value) {
            $newRow->put($value, $this->valueOrNull($row, $key));
        }

        $this->push($newRow);
    }

    private function setHeader($row)
    {
        foreach ($row as $value) {
            $this->header[] = str_replace(' ', '_', strtolower($value));
        }
    }

    private function valueOrNull($row, $key)
    {
        return !isset($row[$key]) || $row[$key] === ''
            ? null
            : $row[$key];
    }
}
