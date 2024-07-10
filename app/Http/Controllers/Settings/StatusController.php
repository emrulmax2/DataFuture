<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Status;
use App\Http\Requests\StatusRequest;
use App\Models\EmailTemplate;
use App\Models\LetterSet;
use App\Models\Signatory;

class StatusController extends Controller
{
    public function index()
    {
        return view('pages.settings.status.index', [
            'title' => 'Statuses - London Churchill College',
            'subtitle' => 'Applicant Settings',
            'breadcrumbs' => [
                ['label' => 'Site Settings', 'href' => route('site.setting')],
                ['label' => 'Statuses', 'href' => 'javascript:void(0);']
            ],
            'letters' => LetterSet::where('hr', '!=', 1)->where('status', 1)->orderBy('letter_title', 'ASC')->get(),
            'emails' => EmailTemplate::where('hr', '!=', 1)->where('status', 1)->orderBy('email_title', 'ASC')->get(),
            'signatories' => Signatory::orderBy('signatory_name', 'ASC')->get(),
        ]);
    }

    public function list(Request $request){
        $queryStr = (isset($request->querystr) && !empty($request->querystr) ? $request->querystr : '');
        $status = (isset($request->status) && $request->status > 0 ? $request->status : 1);

        $sorters = (isset($request->sorters) && !empty($request->sorters) ? $request->sorters : array(['field' => 'id', 'dir' => 'DESC']));
        $sorts = [];
        foreach($sorters as $sort):
            $sorts[] = $sort['field'].' '.$sort['dir'];
        endforeach;

        $query = Status::orderByRaw(implode(',', $sorts));
        if(!empty($queryStr)):
            $query->where('name','LIKE','%'.$queryStr.'%');
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
                    'name' => $list->name,
                    'type' => $list->type,
                    'letter_set_id' => $list->letter_set_id,
                    'letter_name' => (isset($list->letter->letter_title) && !empty($list->letter->letter_title) ? $list->letter->letter_title : ''),
                    'signatory_name' => (isset($list->signatory->signatory_name) && !empty($list->signatory->signatory_name) ? $list->signatory->signatory_name : ''),
                    'email_template_id' => $list->email_template_id,
                    'email_name' => (isset($list->mail->email_title) && !empty($list->mail->email_title) ? $list->mail->email_title : ''),
                    'deleted_at' => $list->deleted_at
                ];
                $i++;
            endforeach;
        endif;
        return response()->json(['last_page' => $last_page, 'data' => $data]);
    }

    public function store(StatusRequest $request){
        $letter_set_id = (isset($request->letter_set_id) && $request->letter_set_id > 0 ? $request->letter_set_id : 0);
        $signatory_id = ($letter_set_id > 0 && isset($request->signatory_id) && $request->signatory_id > 0 ? $request->signatory_id : 0);
        $email_template_id = ($letter_set_id == 0 && isset($request->email_template_id) && $request->email_template_id > 0 ? $request->email_template_id : 0);
        $data = Status::create([
            'name'=> $request->name,
            'type'=> $request->type,
            'letter_set_id' => $letter_set_id,
            'signatory_id' => $signatory_id,
            'email_template_id' => $email_template_id,
            'created_by' => auth()->user()->id
        ]);
        return response()->json($data);
    }

    public function edit($id){
        $data = Status::find($id);

        if($data){
            return response()->json($data);
        }else{
            return response()->json(['message' => 'Something went wrong. Please try later'], 422);
        }
    }

    public function update(StatusRequest $request){  
        $letter_set_id = (isset($request->letter_set_id) && $request->letter_set_id > 0 ? $request->letter_set_id : 0);
        $signatory_id = ($letter_set_id > 0 && isset($request->signatory_id) && $request->signatory_id > 0 ? $request->signatory_id : 0);
        $email_template_id = ($letter_set_id == 0 && isset($request->email_template_id) && $request->email_template_id > 0 ? $request->email_template_id : 0);    
        $data = Status::where('id', $request->id)->update([
            'name'=> $request->name,
            'type'=> $request->type,
            'letter_set_id' => $letter_set_id,
            'signatory_id' => $signatory_id,
            'email_template_id' => $email_template_id,
            'updated_by' => auth()->user()->id
        ]);


        if($data){
            return response()->json(['message' => 'Data updated'], 200);
        }else{
            return response()->json(['message' => 'No data Modified'], 422);
        }
    }

    public function destroy($id){
        $data = Status::find($id)->delete();
        return response()->json($data);
    }

    public function restore($id) {
        $data = Status::where('id', $id)->withTrashed()->restore();

        response()->json($data);
    }
}
