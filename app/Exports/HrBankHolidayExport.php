<?php

namespace App\Exports;

Use App\Models\HrBankHoliday;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Illuminate\Support\Collection;

class HrBankHolidayExport implements FromCollection, WithHeadings
{
    protected $holidayYearId;

    public function __construct($holidayYearId)
    {
        $this->holidayYearId = $holidayYearId;
    }

    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        return new Collection([
            [$this->holidayYearId, '', '', '', '', '',]
        ]);
    }

    public function headings(): array
    {
        return [
            'Holiday Year',
            'Name',
            'Start Date',
            'End Date',
            'Duration',
            'Description'
        ];
    }
}
