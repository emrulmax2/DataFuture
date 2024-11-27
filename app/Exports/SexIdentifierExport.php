<?php

namespace App\Exports;

use App\Models\SexIdentifier;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Illuminate\Support\Collection;

class SexIdentifierExport implements FromCollection, WithHeadings
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        return new Collection([
            ['','','','','',]
        ]);
    }
        
    public function headings(): array
    {
        return [
            'Name',
            'Is Hesa',
            'Hesa Code',
            'Is DF',
            'DF Code',
            'Status'
        ];
    }
}
