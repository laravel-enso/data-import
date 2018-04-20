<?php

namespace LaravelEnso\DataImport\app\Classes;

use Box\Spout\Common\Type;
use Box\Spout\Reader\ReaderFactory;
use LaravelEnso\DataImport\app\Classes\Wrappers\Sheet;
use LaravelEnso\DataImport\app\Classes\Wrappers\Workbook;

class SpoutReader
{
    public $reader;

    public function __construct($filePath)
    {
        $this->reader = ReaderFactory::create(Type::XLSX);
        $this->reader->open($filePath);
    }

    public function get()
    {
        $result = new Workbook();
        $sheetIterator = $this->reader->getSheetIterator();

        foreach ($sheetIterator as $sheet) {
            $tempSheet = new Sheet();
            $tempSheet->setTitle($sheet->getName());

            $rowIterator = $sheet->getRowIterator();
            foreach ($sheet->getRowIterator() as $row) {
                $tempSheet->processAndAddRow($row);
            }

            $result->push($tempSheet);
        }

        \Log::debug('spout reader');
        \Log::debug($result);

        return $result;
    }
}
