<?php

namespace App\Http\Controllers;

use App\Http\Requests\CommonSmtpRequest;
use App\Http\Requests\ComonSmtpUpdateRequest;
use App\Models\ComonSmtp;
use Illuminate\Http\Request;

class CommonSmtpController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('pages/smtp/index', [
            'title' => 'SMTP Settings - LCC Data Future Managment',
            'breadcrumbs' => [['label' => 'SMTP Settings', 'href' => 'javascript:void(0);']]
        ]);
    }

    public function list(Request $request){
        $queryStr = (isset($request->querystr) && !empty($request->querystr) ? $request->querystr : '');
        $status = (isset($request->status) && $request->status > 0 ? $request->status : 1);

        $sorters = (isset($request->sorters) && !empty($request->sorters) ? $request->sorters : array(['field' => 'id', 'dir' => 'asc']));
        $sorts = [];
        foreach($sorters as $sort):
            $sorts[] = $sort['field'].' '.$sort['dir'];
        endforeach;

        $page = (isset($request->page) && $request->page > 0 ? $request->page : 0);
        $perpage = (isset($request->size) && $request->size > 0 ? $request->size : 10);

        $query = ComonSmtp::orderByRaw(implode(',', $sorts))->where('account_type', 0);
        if(!empty($queryStr)):
            $query->where('name','LIKE','%'.$queryStr.'%');
        endif;
        if($status == 2):
            $query->onlyTrashed();
        endif;

        $total_rows = $query->count();
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
                    'smtp_user' => $list->smtp_user,
                    'smtp_pass' => $list->smtp_pass,
                    'smtp_host' => $list->smtp_host,
                    'smtp_port' => $list->smtp_port,
                    'smtp_encryption' => strtoupper($list->smtp_encryption),
                    'smtp_authentication' => strtoupper($list->smtp_authentication),
                    'deleted_at' => $list->deleted_at
                ];
                $i++;
            endforeach;
        endif;
        return response()->json(['last_page' => $last_page, 'data' => $data]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(CommonSmtpRequest $request)
    {
        $request->request->add(['account_type' => 0, 'created_by' => auth()->user()->id]);
        $commonSmtp = ComonSmtp::create($request->all());

        if($commonSmtp){
            return response()->json(['message' => 'Data successfullly inserted'], 200);
        }else{
            return response()->json(['message' => 'Something went wrong. Please try later'], 422);
        }
        
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\DocumentSettings  $documentSettings
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\DocumentSettings  $documentSettings
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $smtp = ComonSmtp::find($id);
        return response()->json($smtp);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\DocumentSettings  $documentSettings
     * @return \Illuminate\Http\Response
     */
    public function update(ComonSmtpUpdateRequest $request, ComonSmtp $dataId)
    {
        $request->request->add(['updated_by' => auth()->user()->id]);
        $ComonSmtp = ComonSmtp::where('id', $request->id)->update($request->all());

        return response()->json(['message' => 'Data successfully updated'], 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\DocumentSettings  $documentSettings
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $data = ComonSmtp::find($id)->delete();
        return response()->json($data);
    }

    public function restore($id) {
        $data = ComonSmtp::where('id', $id)->withTrashed()->restore();

        response()->json($data);
    }
}
