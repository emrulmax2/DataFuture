<?php

namespace App\Exports;

use App\Models\Assign;
use App\Models\Plan;
use App\Models\ResultSubmission;
use Carbon\Carbon;
use DateTime;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithHeadings;

class ResultSubmissionSampleDownload implements FromCollection,WithHeadings,WithColumnWidths
{

    protected $plan;
    
    public function __construct(Plan $plan)
    {
        //array works
        $this->plan = $plan;

        
    }

    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        $data = [];
        $studentList = Assign::with('student')
                        ->where('plan_id', $this->plan->id)
                        ->where(function ($query) {
                            $query->whereNull('attendance')
                                  ->orWhere('attendance', 1);
                        })
                        ->get();
        //dd($studentList->count());
        if($studentList->count() == 0){
            return new Collection([['','','','','','','','','','','','','','']]);
        }
        foreach ($studentList as $assignInfo) {
            $data[] = [
                'last_name' => $assignInfo->student->last_name,
                'first_name' => $assignInfo->student->first_name,
                'email' => $assignInfo->student->users->email,
                'paper_id' => '',
                'grade' => '',
                'date_uploaded' => Carbon::now()->format('Y-m-d H:i:s'),
                'publish_date' => Carbon::now()->format('Y-m-d H:i:s'),
            ];
        }

        return new Collection([$data]);

    }
    public function headings(): array
    {
        // Define the headings for the Excel file
        return [
            'Last Name',
            'First Name',
            'Email',
            'Paper ID',
            'Grade',
            'Date Uploaded',
            'Publish Date',
            // Add more headings as needed
        ];

        							

    }

    public function columnWidths(): array
    {
        return [
            'A' => 10,
            'B' => 30,
            'C' => 30,
            'D' => 20,
            'E' => 20,             
            'F' => 30,             
            'G' => 30,
            'H' => 20,
            'I' => 20,
            'J' => 20,
            'K' => 20,
            'L' => 20,
            'M' => 20,
            'N' => 20,  
        ];
    }
}
