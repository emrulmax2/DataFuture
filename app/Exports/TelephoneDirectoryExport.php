<?php

namespace App\Exports;

use App\Models\Employee;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;

class TelephoneDirectoryExport implements WithColumnWidths, FromView
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function view(): View
    {
        $query = Employee::where('status', '=', 1)->get();

        $data = array();

        $i = 0;
            
        $alphabetArray = ["","A","B","C","D","E","F","G","H","I","J","K","L","M","N","O","P","Q","R","S","T","U","V","W","X","Y","Z"];
        
        for($j=1;$j<=count($alphabetArray);$j++) {
            
            $dataArray = [];
            foreach($query as $list):
                $string = strtoupper(($list->first_name)[0]);
                $length = strlen($string);
                $number = 0;
                $level = 1;
                while ($length >= $level ) {
                    $char = $string[$length - $level];
                    $c = ord($char) - 64;        
                    $number += $c * (26 ** ($level-1));
                    $level++;
                }
                $firstName = isset($list->first_name) ? $list->first_name : '';
                $lastName = isset($list->last_name) ? $list->last_name : '';
       
                if($number==$j):
                    $dataArray[$j][] = [
                        'name' => $firstName.' '.$lastName,
                        'telephone' => isset($list->telephone) ? $list->telephone : '',
                        'mobile' => isset($list->mobile) ? $list->mobile : '',
                        'email' => isset($list->email) ? $list->email : '',
                    ];
                    
                endif;
            endforeach;
            if(isset($dataArray[$j]) && count($dataArray[$j])>0) {
                $data[$i] = ["id"=>$j, "firstcha" =>$alphabetArray[$j], "dataArray" => $dataArray[$j]];
                $i++;
            }

        } 

        return view('pages.hr.portal.reports.excel.telephonedirectoryexcel', [
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
