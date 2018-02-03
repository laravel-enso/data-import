<?php

namespace LaravelEnso\DataImport\app\Classes\Reader;

use Box\Spout\Common\Type;
use Box\Spout\Reader\ReaderFactory;
use Box\Spout\Reader\XLSX\Sheet as XLSXSheet;

class XLSXReader
{
    private $filename;
    private $sheets;

    public function __construct($filename)
    {
        $this->filename = $filename;
    }

    public function sheets()
    {
        if (!isset($this->sheets)) {
            $this->readSheets();
        }

        return $this->sheets;
    }

    private function readSheets()
    {
        $this->sheets = collect();
        $reader = ReaderFactory::create(Type::XLSX);
        $reader->open($this->filename);

        foreach ($reader->getSheetIterator() as $sheet) {
            $this->sheets->push(
                $this->sheet($sheet)
            );
        }

        $reader->close();
    }

    private function sheet(XLSXSheet $sheet)
    {
        $rowCollection = $this->rowCollection($sheet);
        $keys = $rowCollection->splice(0, 1)
            ->first()->map(function ($key) {
                return snake_case($key);
            });

        $rows = $rowCollection->map(function ($row) use ($keys) {
            return new Row(
                $keys->combine($row)->all()
            );
        });

        $name = snake_case($sheet->getName());

        return new Sheet($name, $rows);
    }

    private function rowCollection(XLSXSheet $sheet)
    {
        $rowCollection = collect();

        foreach ($sheet->getRowIterator() as $row) {
            $rowCollection->push($this->trim($row));
        }

        return $rowCollection;
    }

    private function trim(array $row)
    {
        return collect($row)->map(function ($cell) {
            return is_string($cell) ? trim($cell) : $cell;
        });
    }
}
