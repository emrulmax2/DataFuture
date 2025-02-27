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
                
                switch($list->field_name) {
                        case 'status_id':
                            $old_value = (isset($list->field_value) && !empty($list->field_value) ? Status::where('id', $list->field_value)->first()->name : '');
                            $new_value = (isset($list->field_new_value) && !empty($list->field_new_value) ? Status::where('id', $list->field_new_value)->first()->name : '');
                        break;
                        case 'updated_by':
                            isset(Employee::where('user_id', $list->field_value)->first()->full_name) ? $old_value = Employee::where('user_id', $list->field_value)->first()->full_name : '';
                            isset(Employee::where('user_id', $list->field_new_value)->first()->full_name) ? $new_value = Employee::where('user_id', $list->field_new_value)->first()->full_name : '';
                           
                        break;
                        case 'created_by':
                            isset(Employee::where('user_id', $list->field_value)->first()->full_name) ? $old_value = Employee::where('user_id', $list->field_value)->first()->full_name : '';
                            isset(Employee::where('user_id', $list->field_new_value)->first()->full_name) ? $new_value = Employee::where('user_id', $list->field_new_value)->first()->full_name : '';
                        break;
                        case 'term_time_accommodation_type_id':
                            $old_value = (isset($list->field_value) && !empty($list->field_value) ? TermTimeAccommodationType::where('id', $list->field_value)->first()->name : '');
                            $new_value = (isset($list->field_new_value) && !empty($list->field_new_value) ? TermTimeAccommodationType::where('id', $list->field_new_value)->first()->name : '');
                        break;

                        case 'permanent_address_id':
                            $old_value = (isset($list->field_value) && !empty($list->field_value) ? Address::where('id', $list->field_value)->first()->full_address : '');
                            $new_value = (isset($list->field_new_value) && !empty($list->field_new_value) ? Address::where('id', $list->field_new_value)->first()->full_address : '');
                        break;
                        case 'permanent_country_id':
                            $old_value = (isset($list->field_value) && !empty($list->field_value) ? Country::where('id', $list->field_value)->first()->name : '');
                            $new_value = (isset($list->field_new_value) && !empty($list->field_new_value) ? Country::where('id', $list->field_new_value)->first()->name : '');
                        break;
                    default:
                            $old_value = $list->field_value;
                            $new_value = $list->field_new_value;
                        break;                    
                        }
                $data[] = [
                    'id' => $list->id,
                    'sl' => $i,
                    'field_name' => $list->table . ' : ' . $list->field_name,
                    'old_value' => $old_value,
                    'new_value' => $new_value,
                    'created_by'=> (isset($list->user->name) ? $list->user->name : 'Unknown'),
                    'created_at'=> (isset($list->created_at) && !empty($list->created_at) ? date('jS F, Y', strtotime($list->created_at)) : ''),
                    'deleted_at' => $list->deleted_at,
                ];
                $i++;
            endforeach;
        endif;
        return response()->json(['last_page' => $last_page, 'data' => $data]);
    }



}
