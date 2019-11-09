<?php

namespace LaravelEnso\DataImport\app\Services\Reader;

use Box\Spout\Common\Entity\Cell;
use Box\Spout\Common\Entity\Row;
use Box\Spout\Reader\Common\Creator\ReaderEntityFactory;
use Exception;
use Illuminate\Support\Str;
use LaravelEnso\DataImport\app\Exceptions\DataImportException;

class XLSX
{
    private $file;
    private $reader;

    public function __construct($file)
    {
        $this->file = $file;
        $this->reader = ReaderEntityFactory::createXLSXReader();
    }

    public function open()
    {
        try {
            $this->reader->open($this->file);
        } catch (Exception $exception) {
            throw DataImportException::fileNotReadable();
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
        return Str::snake(Str::lower(($string)));
    }

    protected function normalizeHeader(Row $row)
    {
        return collect($row->getCells())
            ->map(function (Cell $cell) {
                return Str::snake(Str::lower($cell->getValue()));
            })->toArray();
    }
}
