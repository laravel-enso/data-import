<?php
/**
 * Created with luv for adline.
 * User: mihai
 * Date: 4/19/18
 * Time: 1:47 PM
 */

namespace LaravelEnso\DataImport\app\Classes\Wrappers;

use Box\Spout\Reader\ReaderFactory;
use Box\Spout\Common\Type;


class SpoutReader
{

    public $reader;

    public function __construct($filePath)
    {
        $this->reader = ReaderFactory::create(Type::XLSX);
        $this->reader->open($filePath);
    }

    public function get() {

        $result = new Workbook();
        $sheetIterator = $this->reader->getSheetIterator();

        foreach ($sheetIterator as $sheet) {

            $tempSheet = new Sheet();
            $tempSheet->setTitle($sheet->getName());

            $rowIterator = $sheet->getRowIterator();
            foreach ($sheet->getRowIterator() as $row) {

                if(!$tempSheet->header) {
                    $tempSheet->header = $row;
                    continue;
                }

                $tempSheet->processAndAddRow($row);
            }

            $result->push($tempSheet);
        }

        return $result;

    }
}