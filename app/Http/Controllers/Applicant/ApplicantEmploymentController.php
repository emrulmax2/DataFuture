<?php

namespace App\Http\Controllers\Applicant;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ApplicantEmployment;
use App\Http\Requests\ApplicantEmploymentRequest;
use App\Models\Address;
use App\Models\EmploymentReference;

class ApplicantEmploymentController extends Controller
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

        $query = ApplicantEmployment::orderByRaw(implode(',', $sorts))->where('applicant_id', $applicantId);
        if(!empty($queryStr)):
            $query->where('company_name','LIKE','%'.$queryStr.'%');
            $query->orWhere('company_phone','LIKE','%'.$queryStr.'%');
            $query->orWhere('position','LIKE','%'.$queryStr.'%');
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
                $address = '';
                $address .= $list->address_line_1.'<br/>';
                $address .= ($list->address_line_2 != '' ? $list->address_line_2.'<br/>' : '');
                $address .= ($list->city != '' ? $list->city.', ' : '');
                $address .= ($list->state != '' ? $list->state.', ' : '');
                $address .= ($list->post_code != '' ? $list->post_code.', ' : '');
                $address .= ($list->country != '' ? '<br/>'.$list->country : '');
                $data[] = [
                    'id' => $list->id,
                    'sl' => $i,
                    'company_name' => $list->company_name,
                    'company_phone' => $list->company_phone,
                    'position' => $list->position,
                    'start_date' => $list->start_date,
                    'continuing' => $list->continuing,
                    'end_date' => ($list->continuing == 1 ? 'Continue' : $list->end_date),
                    'address' => $address,
                    'name' => $list->reference[0]->name,
                    'contact_position' => $list->reference[0]->position,
                    'contact_phone' => $list->reference[0]->phone,
                    'deleted_at' => $list->deleted_at
                ];
                $i++;
            endforeach;
        endif;
        return response()->json(['last_page' => $last_page, 'data' => $data]);
    }


    public function store(ApplicantEmploymentRequest $request){
        $continuing = (isset($request->continuing) && $request->continuing > 0 ? $request->continuing : 0);
        $employment = ApplicantEmployment::create([
            'applicant_id'=> $request->applicant_id,
            'company_name'=> $request->company_name,
            'company_phone'=> $request->company_phone,
            'position'=> $request->position,
            'start_date'=> $request->start_date,
            'continuing'=> $continuing,
            'end_date'=> ($continuing != 1 && isset($request->end_date) && !empty($request->end_date) ? $request->end_date : ''),
            'address_line_1' => (isset($request->employment_address_line_1) ? $request->employment_address_line_1 : ''),
            'address_line_2' => (isset($request->employment_address_line_2) ? $request->employment_address_line_2 : ''),
            'state' => (isset($request->employment_address_state) ? $request->employment_address_state : ''),
            'post_code' => (isset($request->employment_address_postal_zip_code) ? $request->employment_address_postal_zip_code : ''),
            'city' => (isset($request->employment_address_city) ? $request->employment_address_city : ''),
            'country' => (isset($request->employment_address_country) ? $request->employment_address_country : ''),
            'created_by' => (!is_null(\Auth::guard('applicant')->user()) ? \Auth::guard('applicant')->user()->id : auth()->user()->id)
        ]);
        if($employment):
            $reference = EmploymentReference::create([
                'applicant_employment_id' => $employment->id,
                'name' => $request->contact_name,
                'position' => $request->contact_position,
                'phone' => $request->contact_phone,
                'email' => (isset($request->contact_email) ? $request->contact_email : null),
                'created_by' => (!is_null(\Auth::guard('applicant')->user()) ? \Auth::guard('applicant')->user()->id : auth()->user()->id)
            ]);
        endif;

        return response()->json($employment);
    }

    public function edit($id){
        $data = ApplicantEmployment::with(['reference'])->where('id', $id)->first();

        if($data){
            return response()->json($data);
        }else{
            return response()->json(['message' => 'Something went wrong. Please try later'], 422);
        }
    }

    public function update(ApplicantEmploymentRequest $request){
        $employmentID = $request->id;
        $referenceID = $request->ref_id;   
        $employment = ApplicantEmployment::with(['reference'])->where('id', $employmentID)->first();

        $continuing = (isset($request->continuing) && $request->continuing > 0 ? $request->continuing : 0);
        $employment = ApplicantEmployment::where('id', $employmentID)->update([
            'company_name'=> $request->company_name,
            'company_phone'=> $request->company_phone,
            'position'=> $request->position,
            'start_date'=> $request->start_date,
            'continuing'=> $continuing,
            'end_date'=> ($continuing != 1 && isset($request->end_date) && !empty($request->end_date) ? $request->end_date : ''),
            'address_line_1' => (isset($request->employment_address_line_1) ? $request->employment_address_line_1 : ''),
            'address_line_2' => (isset($request->employment_address_line_2) ? $request->employment_address_line_2 : ''),
            'state' => (isset($request->employment_address_state) ? $request->employment_address_state : ''),
            'post_code' => (isset($request->employment_address_postal_zip_code) ? $request->employment_address_postal_zip_code : ''),
            'city' => (isset($request->employment_address_city) ? $request->employment_address_city : ''),
            'country' => (isset($request->employment_address_country) ? $request->employment_address_country : ''),
            'updated_by' => (!is_null(\Auth::guard('applicant')->user()) ? \Auth::guard('applicant')->user()->id : auth()->user()->id)
        ]);
        $reference = EmploymentReference::where('id', $referenceID)->update([
            'name' => $request->contact_name,
            'position' => $request->contact_position,
            'phone' => $request->contact_phone,
            'email' => (isset($request->contact_email) ? $request->contact_email : null),
            'updated_by' => (!is_null(\Auth::guard('applicant')->user()) ? \Auth::guard('applicant')->user()->id : auth()->user()->id)
        ]);


        if($employment){
            return response()->json(['message' => 'Data updated'], 200);
        }else{
            return response()->json(['message' => 'No data Modified'], 422);
        }
    }

    public function destroy($id){
        $data = ApplicantEmployment::find($id)->delete();
        return response()->json($data);
    }

    public function restore($id) {
        $data = ApplicantEmployment::where('id', $id)->withTrashed()->restore();

        response()->json($data);
    }
}
