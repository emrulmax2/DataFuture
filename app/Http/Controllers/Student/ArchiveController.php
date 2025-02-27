<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Address;
use App\Models\Country;
use App\Models\Employee;
use App\Models\Status;
use App\Models\StudentArchive;
use App\Models\StudentEmail;
use App\Models\TermTimeAccommodationType;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ArchiveController extends Controller
{
    public function list(Request $request){
        $student_id = (isset($request->studentId) && !empty($request->studentId) ? $request->studentId : 0);
        $queryStr = (isset($request->queryStrARCV) && $request->queryStrARCV != '' ? $request->queryStrARCV : '');

        $sorters = (isset($request->sorters) && !empty($request->sorters) ? $request->sorters : array(['field' => 'id', 'dir' => 'DESC']));
        $sorts = [];
        foreach($sorters as $sort):
            $sorts[] = $sort['field'].' '.$sort['dir'];
        endforeach;

        $query = StudentArchive::orderByRaw(implode(',', $sorts))->where('student_id', $student_id);
        if(!empty($queryStr)):
            $query->where('field_name','LIKE','%'.$queryStr.'%');
        endif;

        $total_rows = $query->count();
        $page = (isset($request->page) && $request->page > 0 ? $request->page : 0);
        $perpage = (isset($request->size) && $request->size == 'true' ? $total_rows : ($request->size > 0 ? $request->size : 10));
        $last_page = $total_rows > 0 ? ceil($total_rows / $perpage) : '';
        
        $limit = $perpage;
        $offset = ($page > 0 ? ($page - 1) * $perpage : 0);

        $Query = $query->skip($offset)
               ->take($limit)
               ->get();

        $data = array();

        if(!empty($Query)):
            $i = 1;
            foreach($Query as $list):
                $values = $this->getArchiveFieldValues($list->field_name, $list->field_value, $list->field_new_value);
                $data[] = [
                    'id' => $list->id,
                    'sl' => $i,
                    'field_name' => $list->table . ' : ' . $list->field_name,
                    'old_value' => (isset($values['field_value']) ? $values['field_value'] : ''),
                    'new_value' => (isset($values['field_new_value']) ? $values['field_new_value'] : ''),
                    'created_by'=> (isset($list->user->name) ? $list->user->name : 'Unknown'),
                    'created_at'=> (isset($list->created_at) && !empty($list->created_at) ? date('jS F, Y', strtotime($list->created_at)) : ''),
                    'deleted_at' => $list->deleted_at,
                ];
                $i++;
            endforeach;
        endif;
        return response()->json(['last_page' => $last_page, 'data' => $data]);
    }


    public function getArchiveFieldValues($fieldName, $fieldValue = null, $fieldNewValue = null){
        switch($fieldName):
            case 'status_id':
                $old_value = (!empty($fieldValue) ? Status::where('id', $fieldValue)->first()->name : '');
                $new_value = (!empty($fieldNewValue) ? Status::where('id', $fieldNewValue)->first()->name : '');
                break;
            case 'updated_by':
                $old_value = isset(Employee::where('user_id', $fieldValue)->first()->full_name) ? Employee::where('user_id', $fieldValue)->first()->full_name : '';
                $new_value = isset(Employee::where('user_id', $fieldNewValue)->first()->full_name) ? Employee::where('user_id', $fieldNewValue)->first()->full_name : '';
                break;
            case 'created_by':
                $old_value = isset(Employee::where('user_id', $fieldValue)->first()->full_name) ? Employee::where('user_id', $fieldValue)->first()->full_name : '';
                $new_value = isset(Employee::where('user_id', $fieldNewValue)->first()->full_name) ? Employee::where('user_id', $fieldNewValue)->first()->full_name : '';
                break;
            case 'term_time_accommodation_type_id':
                $old_value = (isset($fieldValue) && !empty($fieldValue) ? TermTimeAccommodationType::where('id', $fieldValue)->first()->name : '');
                $new_value = (!empty($fieldNewValue) ? TermTimeAccommodationType::where('id', $fieldNewValue)->first()->name : '');
                break;

            case 'permanent_address_id':
                $old_value = (isset($fieldValue) && !empty($fieldValue) ? Address::where('id', $fieldValue)->first()->full_address : '');
                $new_value = (!empty($fieldNewValue) ? Address::where('id', $fieldNewValue)->first()->full_address : '');
                break;
            case 'permanent_country_id':
                $old_value = (isset($fieldValue) && !empty($fieldValue) ? Country::where('id', $fieldValue)->first()->name : '');
                $new_value = (!empty($fieldNewValue) ? Country::where('id', $fieldNewValue)->first()->name : '');
                break;
            default:
                $old_value = $fieldValue;
                $new_value = $fieldNewValue;
                break; 
        endswitch;

        return ['field_value' => $old_value, 'field_new_value' => $new_value];
    }
}
