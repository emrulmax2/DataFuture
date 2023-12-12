<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Models\EmployeeEligibilites;
use Carbon\Carbon;
use Illuminate\Http\Request;

class EmployeePassportExpiryController extends Controller
{
    public function index(){
        return view('pages.hr.portal.passport-expiry', [
            'title' => 'HR Portal Visa Expiry - LCC Data Future Managment',
            'breadcrumbs' => [
                ['label' => 'HR Portal', 'href' => route('hr.portal')],
                ['label' => 'Visa Expiry', 'href' => 'javascript:void(0);']
            ]
        ]);
    }

    public function list(Request $request){
        $expireDate = Carbon::now()->addDays(60)->format('Y-m-d');

        $sorters = (isset($request->sorters) && !empty($request->sorters) ? $request->sorters : array(['field' => 'doc_expire', 'dir' => 'DESC']));
        $sorts = [];
        foreach($sorters as $sort):
            $sorts[] = $sort['field'].' '.$sort['dir'];
        endforeach;

        $query = EmployeeEligibilites::where('document_type', 1)->whereDate('doc_expire', '<=', $expireDate)
                 ->whereHas('employee', function($q){
                    $q->where('status', 1);
                 })
                 ->orderBy('doc_expire', 'ASC');

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
                $expiryDate = date('Y-m-d', strtotime($list->doc_expire));
                $days = '';
                $class = '';
                if(date('Y-m-d') > $expiryDate){
                    $date = \Carbon\Carbon::parse($expiryDate);
                    $now = \Carbon\Carbon::now();

                    $days = $date->diffInDays($now);
                    $class .= 'btn-danger';
                }else{
                    $date = \Carbon\Carbon::parse($expiryDate);
                    $now = \Carbon\Carbon::now();

                    $days = $date->diffInDays($now);
                    $class .= 'btn-warning';
                }
                $data[] = [
                    'sl' => $i,
                    'id' => $list->id,
                    'employee_id' => $list->employee_id,
                    'url' => route('profile.employee.view', $list->employee_id),
                    'photo_url' => $list->employee->photo_url,
                    'name' => $list->employee->first_name.' '.$list->employee->last_name,
                    'designation' => (isset($list->employee->employment->employeeJobTitle->name) ? $list->employee->employment->employeeJobTitle->name : ''),
                    
                    'doc_number' => $list->doc_number,
                    'doc_expire' => date('D jS M, Y', strtotime($list->doc_expire)),
                    'days' => $days,
                    'class' => $class,

                    'deleted_at' => $list->deleted_at
                ];
                $i++;
            endforeach;
        endif;
        return response()->json(['last_page' => $last_page, 'data' => $data]);
    }
}
