<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithTitle;

class ArrayCollectionExport implements FromArray, WithTitle
{
    protected $collection;
    protected $title;

    public function __construct(array $collection, string $title = 'Sheet 01')
    {
        $this->collection = $collection;
        $this->title = $title;
    }

    public function title(): string
    {
        return $this->title;
    }

    public function array(): array
    {
        return $this->collection;
    }
}
