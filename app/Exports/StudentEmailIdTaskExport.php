<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;

class StudentEmailIdTaskExport implements FromArray
{
    protected $collection;

    public function __construct(array $collection)
    {
        $this->collection = $collection;
    }

    public function array(): array
    {
        return $this->collection;
    }
}
