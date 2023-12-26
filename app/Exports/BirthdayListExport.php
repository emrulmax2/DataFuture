<?php

namespace App\Exports;

use App\Models\Employee;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use DateTime;

class BirthdayListExport implements WithColumnFormatting, WithColumnWidths, FromView
{
    /**
    * @return \Illuminate\Support\Collection
    */

    public function view(): View
    {
        $query = Employee::where('status', '=', 1)->get();

        $data = array();

        $i = 0;
        
        $monthArray = ["","January","February","March","April","May","June","July","August","September","October","November","December"];
        
        for($j=1;$j<=count($monthArray);$j++) {
            
            $dataArray = [];
            foreach($query as $list):
                $birthDate = strtotime($list->date_of_birth);
                $today = strtotime(date('Y-m-d'));
                $secs = $today - $birthDate;

                $ageCalVar = new DateTime("@0");
                $ageDiffSec = new DateTime("@$secs");
                $ageDiff =  date_diff($ageCalVar, $ageDiffSec);
                $age = $ageDiff->format('%y Years, %m months and %d days');
                
                $foundMonth = date('m', strtotime($list->date_of_birth));

                if($foundMonth==$j):
                    $dataArray[$j][] = [
                        'name' => $list->first_name.' '.$list->last_name,
                        'works_no' => $list->employment->works_number,
                        'gender' => $list->sex->name,
                        'date_of_birth' => date('F m Y', strtotime($list->date_of_birth)),
                        'age' => $age
                    ];
                    
                endif;
            endforeach;
            if(isset($dataArray[$j]) && count($dataArray[$j])>0) {
                $data[$i] = ["id"=>$j, "month" =>$monthArray[$j], "dataArray" => $dataArray[$j]];
                $i++;
            }
        
        }  

        return view('pages.hr.portal.reports.excel.birthdayexcel', [
            'dataList' => $data
        ]);
    }

    public function columnFormats(): array
    {
        return [
            'B' => NumberFormat::FORMAT_NUMBER,
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 30,
            'B' => 20,
            'D' => 18,
            'E' => 30,             
        ];
    }
}
