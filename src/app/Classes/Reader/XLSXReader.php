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
            $this->sheets->push($this->sheet($sheet));
        }

        $reader->close();
    }

    private function sheet(XLSXSheet $sheet)
    {
        $rowCollection = $this->rowCollection($sheet);

        $header = $rowCollection->splice(0, 1)->first()
            ->map(function ($key) {
                return $this->normalize($key);
            });

        $rows = $rowCollection->map(function ($row) use ($header) {
            return new Row(
                $header->combine($row)->all()
            );
        });

        $name = $this->normalize($sheet->getName());

        return new Sheet($name, $rows);
    }

    private function rowCollection(XLSXSheet $sheet)
    {
        $rowCollection = collect();

        foreach ($sheet->getRowIterator() as $row) {
            $rowCollection->push($this->sanitize($row));
        }

        return $rowCollection;
    }

    private function normalize($string)
    {
        return str_replace(' ', '_', (strtolower($string)));
    }

    private function sanitize(array $row)
    {
        return collect($row)->map(function ($cell) {
            if (!is_string($cell)) {
                return $cell;
            }

            return $cell === ''
                ? null
                : trim($cell);
        });
    }
}
