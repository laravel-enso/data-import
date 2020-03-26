<?php

namespace LaravelEnso\DataImport\App\Services;

use Box\Spout\Common\Entity\Cell;
use Box\Spout\Reader\XLSX\Sheet as XLSXSheet;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use LaravelEnso\DataImport\App\Services\DTOs\Sheet;
use LaravelEnso\DataImport\App\Services\DTOs\Sheets;
use LaravelEnso\DataImport\App\Services\Readers\XLSX;
use LaravelEnso\DataImport\App\Services\Validators\Structure as Validator;

class Structure
{
    private Template $template;
    private XLSX $xlsx;
    private Summary $summary;
    private Sheets $sheets;

    public function __construct(Template $template, UploadedFile $file)
    {
        $this->template = $template;
        $this->xlsx = new XLSX($file->getPathname());
        $this->summary = new Summary($file->getClientOriginalName());
        $this->sheets = new Sheets();
    }

    public function isValid(): bool
    {
        $this->build();

        (new Validator($this->template, $this->sheets, $this->summary))->run();

        return $this->summary->errors()->isEmpty();
    }

    public function summary(): array
    {
        return $this->summary->toArray();
    }

    public function sheets()
    {
        return $this->sheets;
    }

    private function build(): void
    {
        $iterator = $this->xlsx->sheetIterator();

        while ($iterator->valid()) {
            $this->addSheet($iterator->current());
            $iterator->next();
        }

        $this->xlsx->close();
    }

    private function addSheet(XLSXSheet $sheet): void
    {
        $name = $this->xlsx->sheetName($sheet);
        $header = $this->header($sheet);

        $this->sheets->push(
            new Sheet($name, $header)
        );
    }

    private function header(XLSXSheet $sheet): Collection
    {
        $header = $this->xlsx->rowIterator($sheet, true)->current();

        return (new Collection($header->getCells()))
            ->map(fn (Cell $cell) => Str::snake(Str::lower($cell->getValue())));
    }
}
