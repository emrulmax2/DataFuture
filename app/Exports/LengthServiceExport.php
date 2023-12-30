<?php

namespace App\Exports;

use App\Models\Employee;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use DateTime;

class LengthServiceExport implements WithColumnWidths, FromView
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function view(): View
    {
        $query = Employee::where('status', '=', 1)->get();

        $data = array();

        $i = 0;
        
        $yearArray = ["0","1","2","3","4","5","6","7","8","9","10","11","12","13","14","15","16","17","18","19","20","21","22","23","24","25","26","27","28","29","30","31","32","33","34","35","36","37","38","39","40","41","42","43","44","45","46","47","48","49","50","51","52","53"];
        
        for($j=0;$j<=count($yearArray);$j++) {
            
            $dataArray = [];
            foreach($query as $list):
                $today = strtotime(date('Y-m-d'));
                $startedOn = isset($list->employment->started_on) ? strtotime($list->employment->started_on) : $today;
                if(isset($list->workingPattern->end_to) && $list->workingPattern->end_to != ''){
                    $endedOn = strtotime($list->workingPattern->end_to);
                    $secs = $endedOn - $startedOn;                    
                }else
                    $secs = $today - $startedOn;

                $lenCalVar = new DateTime("@0");
                $lenDiffSec = new DateTime("@$secs");
                $lenDiff =  date_diff($lenCalVar, $lenDiffSec);
                $length = $lenDiff->format('%y Years, %m months and %d days');
            
                $yearLength = $lenDiff->format('%y');
            
                if($yearLength==$j):
                    $dataArray[$j][] = [
                        'name' => $list->first_name.' '.$list->last_name,
                        'started_on' => isset($list->employment->started_on) ? $list->employment->started_on : '',
                        'ended_on' => isset($list->workingPattern->end_to) ? $list->workingPattern->end_to : '',
                        'length' => isset($list->employment->started_on) ? $length : '',
                    ];
                    
                endif;
            endforeach;
            if(isset($dataArray[$j]) && count($dataArray[$j])>0) {
                $data[$i] = ["id"=>$j, "year" =>$yearArray[$j], "dataArray" => $dataArray[$j]];
                $i++;
            }

        }

        return view('pages.hr.portal.reports.excel.lengthserviceexcel', [
            'dataList' => $data
        ]);
    }

    public function columnWidths(): array
    {
        return [
            'A' => 20,
            'B' => 20,
            'C' => 20,
            'D' => 40,             
        ];
    }
}
