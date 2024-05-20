<?php

namespace App\Http\Controllers\HR\Reports;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Employee;
use PDF;
use DateTime;

class EligibilityReportController extends Controller
{
    public function index(){
        return view('pages.hr.portal.reports.eligibilityreport', [
            'title' => 'HR Portal - London Churchill College',
            'breadcrumbs' => [
                ['label' => 'Eligibility Expiry Report', 'href' => 'javascript:void(0);']
            ]
        ]);
    }

    public function visaList(Request $request, $paginationOn=true){
        $queryStr = (isset($request->querystr) && !empty($request->querystr) ? $request->querystr : '');

        $sorters = (isset($request->sorters) && !empty($request->sorters) ? $request->sorters : array(['field' => 'id', 'dir' => 'ASC']));
        $sorts = [];
        foreach($sorters as $sort):
            $sorts[] = $sort['field'].' '.$sort['dir'];
        endforeach;

        $query = Employee::orderByRaw(implode(',', $sorts));
        if(!empty($queryStr)):
            $query->where('first_name','LIKE','%'.$queryStr.'%');
            $query->orWhere('last_name','LIKE','%'.$queryStr.'%');
        endif;

        $total_rows = $query->count();
        $page = (isset($request->page) && $request->page > 0 ? $request->page : 0);
        $perpage = (isset($request->size) && $request->size == 'true' ? $total_rows : ($request->size > 0 ? $request->size : 10));
        $last_page = $total_rows > 0 ? ceil($total_rows / $perpage) : '';
        
        $limit = $perpage;
        $offset = ($page > 0 ? ($page - 1) * $perpage : 0);

        if($paginationOn==true)
            $Query = $query->skip($offset)
                ->take($limit)
                ->get();
        else       
            $Query = $query->get();

        $data = array();

        if(!empty($Query)):
            $i = 1;
            foreach($Query as $list):
                $today = strtotime(date('Y-m-d'));
                $permitExpDate = isset($list->eligibilities->workpermit_expire) ? (strtotime($list->eligibilities->workpermit_expire)) : $today;
                
                $secs = $permitExpDate - $today;

                if($permitExpDate>$today):
                    $days = floor($secs/86400).' Days';
                elseif($permitExpDate<$today):
                    $days = 'Expired';
                endif;
                $firstName = isset($list->first_name) ? $list->first_name : '';
                $lastName = isset($list->last_name) ? $list->last_name : '';
                $data[] = [
                    'name' => $firstName.' '.$lastName,
                    'workpermit_number' => isset($list->eligibilities->workpermit_number) ? $list->eligibilities->workpermit_number : '',
                    'workpermit_expire' => isset($list->eligibilities->workpermit_expire) ? date('F d, Y',strtotime($list->eligibilities->workpermit_expire)) : '',
                    'days_remained' => isset($list->eligibilities->workpermit_expire) ? $days : '',
                ];
                $i++;
            endforeach;
        endif;
        return response()->json(['last_page' => $last_page, 'data' => $data]);
    }

    public function passportList(Request $request, $paginationOn=true){
        $queryStr = (isset($request->querystr) && !empty($request->querystr) ? $request->querystr : '');

        $sorters = (isset($request->sorters) && !empty($request->sorters) ? $request->sorters : array(['field' => 'id', 'dir' => 'ASC']));
        $sorts = [];
        foreach($sorters as $sort):
            $sorts[] = $sort['field'].' '.$sort['dir'];
        endforeach;

        $query = Employee::orderByRaw(implode(',', $sorts));
        if(!empty($queryStr)):
            $query->where('first_name','LIKE','%'.$queryStr.'%');
            $query->orWhere('last_name','LIKE','%'.$queryStr.'%');
        endif;

        $total_rows = $query->count();
        $page = (isset($request->page) && $request->page > 0 ? $request->page : 0);
        $perpage = (isset($request->size) && $request->size == 'true' ? $total_rows : ($request->size > 0 ? $request->size : 10));
        $last_page = $total_rows > 0 ? ceil($total_rows / $perpage) : '';
        
        $limit = $perpage;
        $offset = ($page > 0 ? ($page - 1) * $perpage : 0);

        if($paginationOn==true)
            $Query = $query->skip($offset)
                ->take($limit)
                ->get();
        else       
            $Query = $query->get();
        $data = array();

        if(!empty($Query)):
            $i = 1;
            foreach($Query as $list):
                $today = strtotime(date('Y-m-d'));
                $permitExpDate = isset($list->eligibilities->doc_expire) ? (strtotime($list->eligibilities->doc_expire)) : $today;
                $secs =  $permitExpDate-$today;

                if($permitExpDate>$today):
                    $days = floor($secs/86400).' Days';
                elseif($permitExpDate<$today):
                    $days = 'Expired';
                endif;
                $firstName = isset($list->first_name) ? $list->first_name : '';
                $lastName = isset($list->last_name) ? $list->last_name : '';
                $data[] = [
                    'name' => $firstName.' '.$lastName,
                    'document_type' => isset($list->eligibilities->employeeDocType->name) ? $list->eligibilities->employeeDocType->name : '',
                    'doc_number' => isset($list->eligibilities->doc_number) ? $list->eligibilities->doc_number : '',
                    'doc_expire' => isset($list->eligibilities->doc_expire) ? date('F d, Y',strtotime($list->eligibilities->doc_expire)) : '',
                    'days_remained' => isset($list->eligibilities->doc_expire) ? $days : '',
                ];
                $i++;
            endforeach;
        endif;
        return response()->json(['last_page' => $last_page, 'data' => $data]);
    }

    public function generateVisaPDF(Request $request)
    {
        set_time_limit(300);
        $items = Employee::all();
        $items->load(['eligibilities']);

        $i = 0;
        $dataList =[];

        foreach($items as $item) {
            $eligibilities = $item->eligibilities;
            
            $today = strtotime(date('Y-m-d'));
            $workPermitExpDate = isset($eligibilities->workpermit_expire) ? (strtotime($eligibilities->workpermit_expire)) : $today;
            $secs = $workPermitExpDate - $today;

            if($workPermitExpDate>$today):
                $workDays = floor($secs/86400).' Days';
            elseif($workPermitExpDate<$today):
                $workDays = 'Expired';
            endif;

            $firstName = isset($item->first_name) ? $item->first_name : '';
            $lastName = isset($item->last_name) ? $item->last_name : '';

            $dataList[$i++] = [
                'name' => $firstName.' '.$lastName,
                'workpermit_number' => isset($eligibilities->workpermit_number) ? $eligibilities->workpermit_number : '',
                'workpermit_expire' => isset($eligibilities->workpermit_expire) ? date('F d, Y',strtotime($eligibilities->workpermit_expire)) : '',
                'workpermit_days' => isset($eligibilities->workpermit_expire) ? $workDays : '',
            ];
        } 

        $pdf = PDF::loadView('pages.hr.portal.reports.pdf.eligibilityvisapdf',compact('dataList'));
        return $pdf->download('Eligibility Expiry Visa Report.pdf');
    }

    public function generatePassportPDF(Request $request)
    {
        set_time_limit(300);
        $items = Employee::all();
        $items->load(['eligibilities']);

        $i = 0;
        $dataList =[];

        foreach($items as $item) {
            $eligibilities = $item->eligibilities;
            
            $today = strtotime(date('Y-m-d'));

            $docPermitExpDate = isset($eligibilities->doc_expire) ? (strtotime($eligibilities->doc_expire)) : $today;
            $secs =  $docPermitExpDate-$today;

            if($docPermitExpDate>$today):
                $docDays = floor($secs/86400).' Days';
            elseif($docPermitExpDate<$today):
                $docDays = 'Expired';
            endif;
            $firstName = isset($item->first_name) ? $item->first_name : '';
            $lastName = isset($item->last_name) ? $item->last_name : '';

            $dataList[$i++] = [
                'name' => $firstName.' '.$lastName,
                'document_type' => isset($eligibilities->employeeDocType->name) ? $eligibilities->employeeDocType->name : '',
                'doc_number' => isset($eligibilities->doc_number) ? $eligibilities->doc_number : '',
                'doc_expire' => isset($eligibilities->doc_expire) ? date('F d, Y',strtotime($eligibilities->doc_expire)) : '',
                'doc_days' => isset($eligibilities->doc_expire) ? $docDays : '',
            ];
        } 

        $pdf = PDF::loadView('pages.hr.portal.reports.pdf.eligibilitypassportpdf',compact('dataList'));
        return $pdf->download('Eligibility Expiry Passport Report.pdf');
    }
}
