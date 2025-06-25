<?php

namespace App\Exports;

use App\Models\OtherAcademicQualification;
use Maatwebsite\Excel\Concerns\FromCollection;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class OtherAcademicQualificationExport implements FromCollection, WithHeadings
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
            'Status'
        ];
    }
}
