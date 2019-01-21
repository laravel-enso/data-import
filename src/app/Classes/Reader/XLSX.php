<?php

namespace LaravelEnso\DataImport\app\Classes\Reader;

use Exception;
use Box\Spout\Common\Type;
use Illuminate\Support\Str;
use Box\Spout\Reader\ReaderFactory;
use LaravelEnso\DataImport\app\Exceptions\XLSXException;

class XLSX
{
    private $file;
    private $reader;

    public function __construct($file)
    {
        $this->file = $file;
        $this->reader = ReaderFactory::create(Type::XLSX);
    }

    public function open()
    {
        try {
            $this->reader->open($this->file);
        } catch (Exception $exception) {
            throw new XLSXException(__('Unable to read file'));
        }

        return $this;
    }

    public function close()
    {
        $this->reader->close();

        return $this;
    }

    protected function sheetIterator()
    {
        $iterator = $this->reader->getSheetIterator();
        $iterator->rewind();

        return $iterator;
    }

    protected function sheet($sheetName)
    {
        $sheetIterator = $this->reader->getSheetIterator();
        $sheetIterator->rewind();

        while ($sheetIterator->valid()
            && $this->normalizeSheet($sheetIterator->current()->getName()) !== $sheetName) {
            $sheetIterator->next();
        }

        return $sheetIterator->current();
    }

    protected function normalizeSheet($string)
    {
        return Str::camel(Str::lower(($string)));
    }

    protected function normalizeHeader($row)
    {
        return collect($row)->map(function ($cell) {
            return Str::snake(Str::lower(($cell)));
        })->toArray();
    }
}
