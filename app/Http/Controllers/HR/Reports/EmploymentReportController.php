<?php

namespace App\Http\Controllers\HR\Reports;

use App\Http\Controllers\Controller;
use App\Models\EmploymentReport;
use Illuminate\Http\Request;

class EmploymentReportController extends Controller
{
    public function employmentReportlist(Request $request){
        $sorters = (isset($request->sorters) && !empty($request->sorters) ? $request->sorters : array(['field' => 'id', 'dir' => 'ASC']));
        $sorts = [];
        foreach($sorters as $sort):
            $sorts[] = $sort['field'].' '.$sort['dir'];
        endforeach;

        $query = EmploymentReport::orderByRaw(implode(',', $sorts));

        $total_rows = $query->count();
        $page = (isset($request->page) && $request->page > 0 ? $request->page : 0);
        $perpage = (isset($request->size) && $request->size == 'true' ? $total_rows : ($request->size > 0 ? $request->size : 10));
        $last_page = $total_rows > 0 ? ceil($total_rows / $perpage) : '';
        
        $limit = $perpage;
        $offset = ($page > 0 ? ($page - 1) * $perpage : 0);

        $Query= $query->skip($offset)
               ->take($limit)
               ->get();

        $data = array();

        if(!empty($Query)):
            $i = 1;
            foreach($Query as $list):
                $url = '';
                if(($list->report_description=='Birthday List')):
                    $url = route('hr.portal.reports.birthdaylist');
                elseif(($list->report_description=='Diversity Information')):
                    $url = route('hr.portal.reports.diversityreport');
                elseif(($list->report_description=='Employee Contact Detail')):
                    $url = route('hr.portal.reports.contactdetail');
                elseif(($list->report_description=='Employee Length of Service')):
                    $url = route('hr.portal.reports.lengthservice');
                elseif(($list->report_description=='Employee Starter')):
                    $url = route('hr.portal.reports.starterreport');
                elseif(($list->report_description=='Employee Record Card')):            
                    $url = route('hr.portal.reports.recordcard');
                elseif(($list->report_description=='Employee Telephone Directory')):
                    $url = route('hr.portal.reports.telephonedirectory');
                elseif(($list->report_description=='Employee Eligibility Entry')):
                    $url = route('hr.portal.reports.eligibilityreport');
                elseif(($list->report_description=='Employee Data Report')):
                    $url = route('hr.portal.reports.datareport');
                elseif(($list->report_description=='Attendance Report')):
                    $url = route('hr.portal.reports.attendance');
                elseif(($list->report_description=='Employment Hour Report')):
                    $url = route('hr.portal.reports.holiday.hour');
                endif;
                $data[] = [
                    'sl' => $list->id,
                    'report_description' => $list->report_description,
                    'file_name' => $list->file_name,
                    'last_run' => $list->last_run,
                    'url' => $url
                ];
                $i++;
            endforeach;
        endif;
        return response()->json(['last_page' => $last_page, 'data' => $data]);
    }
}
