<?php

namespace LaravelEnso\DataImport\Services\Readers;

use Box\Spout\Common\Entity\Cell;
use Box\Spout\Reader\Common\Creator\ReaderEntityFactory;
use Box\Spout\Reader\XLSX\Reader;
use Box\Spout\Reader\XLSX\RowIterator;
use Box\Spout\Reader\XLSX\Sheet;
use Box\Spout\Reader\XLSX\SheetIterator;
use Exception;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use LaravelEnso\DataImport\Exceptions\DataImport;

class XLSX
{
    private string $file;
    private bool $open;
    private Reader $reader;

    public function __construct(string $file)
    {
        $this->file = $file;
        $this->open = false;
        $this->reader = ReaderEntityFactory::createXLSXReader();
    }

    public function __destruct()
    {
        if ($this->open) {
            $this->reader->close();
            $this->open = false;
        }
    }

    public function sheets(): Collection
    {
        $iterator = $this->sheetIterator();
        $sheets = new Collection();

        while ($iterator->valid()) {
            $sheets->push($this->name($iterator->current()->getName()));
            $iterator->next();
        }

        return $sheets;
    }

    public function header(string $sheet): Collection
    {
        $header = $this->rowIterator($this->sheet($sheet))->current();

        return Collection::wrap($header->getCells())
            ->map(fn (Cell $cell) => $this->name($cell->getValue()));
    }

    public function sheet(string $name): ?Sheet
    {
        $iterator = $this->sheetIterator();

        while ($this->name($iterator->current()->getName()) !== $name) {
            $iterator->next();
        }

        return $iterator->current();
    }

    public function sheetIterator(): SheetIterator
    {
        $this->ensureIsOpen();

        $iterator = $this->reader->getSheetIterator();
        $iterator->rewind();

        return $iterator;
    }

    public function rowIterator(Sheet $sheet): RowIterator
    {
        $iterator = $sheet->getRowIterator();
        $iterator->rewind();

        return $iterator;
    }

    private function name(string $name): string
    {
        return Str::of($name)->lower()->snake();
    }

    private function ensureIsOpen(): void
    {
        if (! $this->open) {
            $this->open();
        }
    }

    private function open(): void
    {
        try {
            $this->reader->open($this->file);
        } catch (Exception $exception) {
            throw DataImport::fileNotReadable($this->file);
        }

        $this->open = true;
    }
}
