<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithCustomValueBinder;
use PhpOffice\PhpSpreadsheet\Cell\Cell;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Cell\DefaultValueBinder;

class ArrayCollectionExport extends DefaultValueBinder implements FromArray, WithTitle, WithCustomValueBinder
{
    protected $collection;
    protected $title;
    protected ?int $textColumnIndex = null;

    public function __construct(array $collection, string $title = 'Sheet 01' ,?int $textColumnIndex = null)
    {
        $this->collection = $collection;
        $this->title = $title;
        $this->textColumnIndex = $textColumnIndex;
    }

    public function title(): string
    {
        return $this->title;
    }

    public function array(): array
    {
        return $this->collection;
    }

    public function bindValue(Cell $cell, $value): bool
    {
        if ($this->textColumnIndex !== null) {
            $columnIndex = Coordinate::columnIndexFromString($cell->getColumn());
            if ($columnIndex === $this->textColumnIndex) {
                $cell->setValueExplicit((string) $value, DataType::TYPE_STRING);
                return true;
            }
        }

        return parent::bindValue($cell, $value);
    }
}
