<?php

namespace App\Http\Controllers\Applicant;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ApplicantQualification;
use App\Http\Requests\ApplicantQualificationRequest;

class ApplicantQualificationCongroller extends Controller
{
    public function list(Request $request){
        $applicantId = (isset($request->applicantId) && $request->applicantId > 0 ? $request->applicantId : '0');
        $queryStr = (isset($request->querystr) && !empty($request->querystr) ? $request->querystr : '');
        $status = (isset($request->status) && $request->status > 0 ? $request->status : 1);

        $sorters = (isset($request->sorters) && !empty($request->sorters) ? $request->sorters : array(['field' => 'id', 'dir' => 'DESC']));
        $sorts = [];
        foreach($sorters as $sort):
            $sorts[] = $sort['field'].' '.$sort['dir'];
        endforeach;

        $query = ApplicantQualification::orderByRaw(implode(',', $sorts))->where('applicant_id', $applicantId);
        if(!empty($queryStr)):
            $query->where('highest_academic','LIKE','%'.$queryStr.'%');
            $query->orWhere('subjects','LIKE','%'.$queryStr.'%');
            $query->orWhere('result','LIKE','%'.$queryStr.'%');
            $query->orWhere('awarding_body','LIKE','%'.$queryStr.'%');
        endif;
        if($status == 2):
            $query->onlyTrashed();
        endif;

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
                $data[] = [
                    'id' => $list->id,
                    'sl' => $i,
                    'awarding_body' => $list->awarding_body,
                    'highest_academic' => $list->highest_academic,
                    'subjects' => $list->subjects,
                    'result' => $list->result,
                    'degree_award_date' => $list->degree_award_date,
                    'deleted_at' => $list->deleted_at
                ];
                $i++;
            endforeach;
        endif;
        return response()->json(['last_page' => $last_page, 'data' => $data]);
    }


    public function store(ApplicantQualificationRequest $request){
        $data = ApplicantQualification::create([
            'applicant_id'=> $request->applicant_id,
            'highest_academic'=> $request->highest_academic,
            'awarding_body'=> $request->awarding_body,
            'subjects'=> $request->subjects,
            'result'=> $request->result,
            'degree_award_date'=> date('Y-m-d', strtotime($request->degree_award_date)),
            'created_by' => (!is_null(\Auth::guard('applicant')->user()) ? \Auth::guard('applicant')->user()->id : auth()->user()->id)
        ]);

        return response()->json($data);
    }

    public function edit($id){
        $data = ApplicantQualification::find($id);

        if($data){
            return response()->json($data);
        }else{
            return response()->json(['message' => 'Something went wrong. Please try later'], 422);
        }
    }

    public function update(ApplicantQualificationRequest $request){      
        $data = ApplicantQualification::where('id', $request->id)->update([
            'applicant_id'=> $request->applicant_id,
            'highest_academic'=> $request->highest_academic,
            'awarding_body'=> $request->awarding_body,
            'subjects'=> $request->subjects,
            'result'=> $request->result,
            'degree_award_date'=> date('Y-m-d', strtotime($request->degree_award_date)),
            'updated_by' => (!is_null(\Auth::guard('applicant')->user()) ? \Auth::guard('applicant')->user()->id : auth()->user()->id)
        ]);


        if($data){
            return response()->json(['message' => 'Data updated'], 200);
        }else{
            return response()->json(['message' => 'No data Modified'], 422);
        }
    }

    public function destroy($id){
        $data = ApplicantQualification::find($id)->delete();
        return response()->json($data);
    }

    public function restore($id) {
        $data = ApplicantQualification::where('id', $id)->withTrashed()->restore();

        response()->json($data);
    }
}
