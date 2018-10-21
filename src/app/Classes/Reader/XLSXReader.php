<?php

namespace LaravelEnso\DataImport\app\Classes\Reader;

use Exception;
use Box\Spout\Common\Type;
use Box\Spout\Reader\ReaderFactory;
use Box\Spout\Reader\XLSX\Sheet as XLSXSheet;
use LaravelEnso\DataImport\app\Exceptions\FileException;

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
        if (! isset($this->sheets)) {
            $this->readSheets();
        }

        return $this->sheets;
    }

    private function readSheets()
    {
        $this->sheets = collect();
        $reader = ReaderFactory::create(Type::XLSX);

        try {
            $reader->open($this->filename);
        } catch (Exception $exception) {
            throw new FileException(__('Unable to read file'));
        }

        foreach ($reader->getSheetIterator() as $sheet) {
            $this->sheets->push($this->sheet($sheet));
        }

        $reader->close();
    }

    private function sheet(XLSXSheet $sheet)
    {
        $rowCollection = $this->rowCollection($sheet);

        $header = optional($rowCollection->splice(0, 1)->first())
            ->map(function ($key) {
                return $this->normalize($key);
            });

        if (! $header) {
            throw new FileException(__('Please remove any empty sheets from the import file'));
        }

        $headerLength = $header->count();

        $rows = $rowCollection->map(function ($row) use ($header, $headerLength) {
            return new Row(
                $header->combine(
                    $row->pad($headerLength, null)
                )->all()
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
            if (! is_string($cell)) {
                return $cell;
            }

            return trim($cell) ?? null;
        });
    }
}
