<?php

namespace App\Http\Controllers\Api\Student;

use App\Http\Controllers\Controller;
use App\Models\FormsTable;
use Illuminate\Http\Request;

class DoItOnlineController extends Controller
{
    //
    public function formsList(Request $request)
    {
        $doItOnline = FormsTable::all();
        $iCountTotal =1 ; 
        $forms =[];
        foreach ($doItOnline as $onlineWork):
                        
            $endDate = strtotime(date("Y-m-d",strtotime($onlineWork->end_to)));
            $currentDate = strtotime(date("Y-m-d"));
        
            if( $endDate > $currentDate || $onlineWork->end_to=="0000-00-00" || $onlineWork->end_to==null):
                if($onlineWork->form_name=="Document / ID Card Replacement request"):
                    $forms[] = [
                        'id' => $iCountTotal++,
                        'name' => $onlineWork->form_name,
                        'description' => $onlineWork->form_description,
                        'link' => route('students.document-request-form.products'),
                    ];
                elseif($onlineWork->form_name=="Report any IT issues on campus" && isset($reportItAll) && count($reportItAll)>0): 
                    $forms[] = [
                        'id' => $iCountTotal++,
                        'name' => $onlineWork->form_name,
                        'description' => $onlineWork->form_description,
                        'link' => route('students.report-any-it-issues'),
                    ];
                else:
                    $forms[] = [
                        'id' => $iCountTotal++,
                        'name' => $onlineWork->form_name,
                        'description' => $onlineWork->form_description,
                        'link' => $onlineWork->form_link,
                    ];
                endif;
            endif; 
        endforeach;

        return response()->json([
            'success' => true,
            'data' => $forms,
        ], 200);
    }
}
