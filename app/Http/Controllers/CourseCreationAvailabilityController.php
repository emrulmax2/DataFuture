<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CourseCreation;
use App\Models\CourseCreationAvailability;
use App\Http\Requests\CourseCreationAvailabilityRequest;

class CourseCreationAvailabilityController extends Controller
{
    public function list(Request $request){
        $coursecreationid = (isset($request->coursecreationid) && $request->coursecreationid > 0 ? $request->coursecreationid : '');

        
        $total_rows = CourseCreationAvailability::where('course_creation_id', $coursecreationid)->count();
        
        $page = (isset($request->page) && $request->page > 0 ? $request->page : 0);
        $perpage = (isset($request->size) && $request->size == 'true' ? $total_rows : ($request->size > 0 ? $request->size : 10));
        $last_page = $total_rows > 0 ? ceil($total_rows / $perpage) : '';

        $sorters = (isset($request->sorters) && !empty($request->sorters) ? $request->sorters : array(['field' => 'id', 'dir' => 'DESC']));
        $sorts = [];
        foreach($sorters as $sort):
            $sorts[] = $sort['field'].' '.$sort['dir'];
        endforeach;
        
        $limit = $perpage;
        $offset = ($page > 0 ? ($page - 1) * $perpage : 0);

        $Query = CourseCreationAvailability::where('course_creation_id', $coursecreationid)->orderByRaw(implode(',', $sorts))->skip($offset)
               ->take($limit)
               ->get();

        $data = array();
        if(!empty($Query)):
            $i = 1;
            foreach($Query as $list):
                $data[] = [
                    'id' => $list->id,
                    'sl' => $i,
                    'admission_date' => $list->admission_date,
                    'admission_end_date' => $list->admission_end_date,
                    'course_start_date' => $list->course_start_date,
                    'course_end_date' => $list->course_end_date,
                    'last_joinning_date'=> $list->last_joinning_date,
                    'type'=> $list->type,
                    'deleted_at' => $list->deleted_at
                ];
                $i++;
            endforeach;
        endif;
        return response()->json(['last_page' => $last_page, 'data' => $data]);
    }


    public function store(CourseCreationAvailabilityRequest $request){
        $request->request->add(['created_by' => auth()->user()->id]);
        $courseCreationAV = CourseCreationAvailability::create($request->all());
        
        return response()->json($courseCreationAV);
    }

    public function edit($id){
        $data = CourseCreationAvailability::find($id);

        if($data){
            return response()->json($data);
        }else{
            return response()->json(['message' => 'Something went wrong. Please try later'], 422);
        }
    }

    public function update(CourseCreationAvailabilityRequest $request){
        $CCA_ID = $request->id;
        $request->request->add(['updated_by' => auth()->user()->id]);

        $courseCreationAvailability = CourseCreationAvailability::find($CCA_ID);
        $courseCreationAvailability->fill($request->all());
        $courseCreationAvailability->save();
        
        if($courseCreationAvailability->wasChanged()){
            return response()->json(['message' => 'Data updated'], 200);
        }else{
            return response()->json(['message' => 'something went wrong'], 422);
        }
    }
}
