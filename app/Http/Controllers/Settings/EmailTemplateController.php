<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Models\EmailTemplate;
use Illuminate\Http\Request;
use App\Http\Requests\EmailTemplateRequest;
use Illuminate\Support\Str;

class EmailTemplateController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('pages.settings.email.index', [
            'title' => 'Email Template - London Churchill College',
            'subtitle' => 'Communication Settings',
            'breadcrumbs' => [
                ['label' => 'Site Settings', 'href' => route('site.setting')],
                ['label' => 'Email Template', 'href' => 'javascript:void(0);']
            ],
        ]);
    }

    public function list(Request $request){
        $queryStr = (isset($request->querystr) && $request->querystr != '' ? $request->querystr : '');
        $status = (isset($request->status) ? $request->status : 1);
        $phase = (isset($request->phase) && $request->phase > 0 ? $request->phase : '');

        $sorters = (isset($request->sorters) && !empty($request->sorters) ? $request->sorters : array(['field' => 'id', 'dir' => 'DESC']));
        $sorts = [];
        foreach($sorters as $sort):
            $sorts[] = $sort['field'].' '.$sort['dir'];
        endforeach;

        $query = EmailTemplate::orderByRaw(implode(',', $sorts));
        if(!empty($queryStr)):
            $query->orWhere('email_title','LIKE','%'.$queryStr.'%');
            $query->orWhere('description','LIKE','%'.$queryStr.'%');
        endif;
        if(!empty($phase)): $query->where($phase, 1); endif;
        if($status == 2):
            $query->onlyTrashed();
        else:
            $query->where('status', $status);
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
                $data[] = [
                    'id' => $list->id,
                    'sl' => $i,
                    'email_title' => $list->email_title,
                    'description' => Str::limit($list->description,20),
                    'admission' => (isset($list->admission) && $list->admission > 0 ? $list->admission : 0),
                    'live' => (isset($list->live) && $list->live > 0 ? $list->live : 0),
                    'hr' => (isset($list->hr) && $list->hr > 0 ? $list->hr : 0),
                    'status' => (isset($list->status) && $list->status > 0 ? $list->status : 0),
                    'deleted_at' => $list->deleted_at
                ];
                $i++;
            endforeach;
        endif;
        return response()->json(['last_page' => $last_page, 'data' => $data]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(EmailTemplateRequest $request)
    {
        $phase = (isset($request->phase) && !empty($request->phase) ? $request->phase : []);
        $email = EmailTemplate::create([
            'email_title' => $request->email_title,
            'description' => $request->description,
            'admission' => (isset($phase['admission']) && $phase['admission'] > 0 ? $phase['admission'] : 0),
            'live' => (isset($phase['live']) && $phase['live'] > 0 ? $phase['live'] : 0),
            'hr' => (isset($phase['hr']) && $phase['hr'] > 0 ? $phase['hr'] : 0),
            'status' => (isset($request->status) && $request->status > 0 ? $request->status : 0),
            'created_by' => auth()->user()->id
        ]);
        if($email):
            return response()->json(['message' => 'Letter set successfully created.'], 200);
        else:
            return response()->json(['message' => 'Something went wrong. Please try later.'], 422);
        endif;
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\EmailTemplate  $emailTemplate
     * @return \Illuminate\Http\Response
     */
    public function show(EmailTemplate $emailTemplate)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\EmailTemplate  $emailTemplate
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $email = EmailTemplate::find($id);
        return response()->json($email);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\EmailTemplate  $emailTemplate
     * @return \Illuminate\Http\Response
     */
    public function update(EmailTemplateRequest $request)
    {
        $phase = (isset($request->phase) && !empty($request->phase) ? $request->phase : []);
        $emailId = $request->id;
        $email = EmailTemplate::where('id', $emailId)->update([
            'email_title' => $request->email_title,
            'description' => $request->description,
            'admission' => (isset($phase['admission']) && $phase['admission'] > 0 ? $phase['admission'] : 0),
            'live' => (isset($phase['live']) && $phase['live'] > 0 ? $phase['live'] : 0),
            'hr' => (isset($phase['hr']) && $phase['hr'] > 0 ? $phase['hr'] : 0),
            'status' => (isset($request->status) && $request->status > 0 ? $request->status : 0),
            'updated_by' => $emailId,
        ]);

        return response()->json(['message' => 'Data successfully updated'], 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\EmailTemplate  $emailTemplate
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $data = EmailTemplate::find($id)->delete();
        return response()->json($data);
    }

    public function restore($id) {
        $data = EmailTemplate::where('id', $id)->withTrashed()->restore();

        response()->json($data);
    }

    public function updateStatus(Request $request){
        $title = EmailTemplate::find($request->row_id);
        $status = (isset($title->status) && $title->status == 1 ? 0 : 1);

        EmailTemplate::where('id', $request->row_id)->update([
            'status'=> $status,
            'updated_by' => auth()->user()->id
        ]);

        return response()->json(['message' => 'Status successfully updated'], 200);
    }

    public function updatePhaseStatus(Request $request){
        $phase = $request->phase;
        $letter = EmailTemplate::find($request->row_id);
        $status = (isset($letter->$phase) && $letter->$phase == 1 ? 0 : 1);

        EmailTemplate::where('id', $request->row_id)->update([
            $phase => $status,
            'updated_by' => auth()->user()->id
        ]);

        return response()->json(['message' => 'Status successfully updated'], 200);
    }
}
