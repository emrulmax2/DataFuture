<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Http\Requests\SignatoryRequest;
use App\Http\Requests\SignatoryUpdateRequest;
use App\Models\Signatory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SignatoryController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('pages.settings.signatory.index', [
            'title' => 'Signatory - LCC Data Future Managment',
            'subtitle' => 'Communication Settings',
            'breadcrumbs' => [
                ['label' => 'Site Settings', 'href' => route('site.setting')],
                ['label' => 'Signatory', 'href' => 'javascript:void(0);']
            ],
        ]);
    }

    public function list(Request $request){
        $queryStr = (isset($request->queryStr) && $request->queryStr != '' ? $request->queryStr : '');
        $status = (isset($request->status) && $request->status > 0 ? $request->status : 1);

        $sorters = (isset($request->sorters) && !empty($request->sorters) ? $request->sorters : array(['field' => 'id', 'dir' => 'DESC']));
        $sorts = [];
        foreach($sorters as $sort):
            $sorts[] = $sort['field'].' '.$sort['dir'];
        endforeach;

        $page = (isset($request->page) && $request->page > 0 ? $request->page : 0);
        $perpage = (isset($request->size) && $request->size > 0 ? $request->size : 10);

        $query = Signatory::orderByRaw(implode(',', $sorts));
        if(!empty($queryStr)):
            $query->where('signatory_name','LIKE','%'.$queryStr.'%');
            $query->orWhere('signatory_post','LIKE','%'.$queryStr.'%');
        endif;
        if($status == 2):
            $query->onlyTrashed();
        endif;

        $total_rows = $query->count();
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
                $signature = '';
                if(isset($list->signature) && !empty($list->signature) && Storage::disk('google')->exists('public/signatories/'.$list->signature)):
                    $signature = Storage::disk('google')->url('public/signatories/'.$list->signature);
                endif;
                $data[] = [
                    'id' => $list->id,
                    'sl' => $i,
                    'signatory_name' => $list->signatory_name,
                    'signatory_post' => $list->signatory_post,
                    'url' => $signature,
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
    public function store(SignatoryRequest $request){
        if($request->hasFile('signatory')):
            $document = $request->file('signatory');
            $documentName = time().'_'.$document->getClientOriginalName();
            $path = $document->storeAs('public/signatories/', $documentName, 'google');
            
            $data = [];
            $data['signatory_name'] = $request->signatory_name;
            $data['signatory_post'] = $request->signatory_post;
            $data['signature'] = $documentName;
            $data['created_by'] = auth()->user()->id;
            $Signatory = Signatory::create($data);

            if($Signatory):
                return response()->json(['message' => 'Applicant Note successfully created'], 200);
            else:
                return response()->json(['message' => 'Document not selected.'], 422);
            endif;
        else:
            return response()->json(['message' => 'Document not selected.'], 422);
        endif;
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Signatory  $signatory
     * @return \Illuminate\Http\Response
     */
    public function edit(Request $request)
    {
        $signatoryID = $request->signatoryID;
        $signatory = Signatory::find($signatoryID);

        $res = [];
        $res['signatory_name'] = $signatory->signatory_name;
        $res['signatory_post'] = $signatory->signatory_post;
        $res['signature'] = (isset($signatory->signature) && !empty($signatory->signature) &&  Storage::disk('google')->exists('public/signatories/'.$signatory->signature) ? Storage::disk('google')->url('public/signatories/'.$signatory->signature) : '');

        return response()->json(['message' => $res], 200);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Signatory  $signatory
     * @return \Illuminate\Http\Response
     */
    public function update(SignatoryUpdateRequest $request)
    {
        $id = $request->id;
        $signatoryExist = Signatory::find($id);

        $data = [];
        $data['signatory_name'] = $request->signatory_name;
        $data['signatory_post'] = $request->signatory_post;
        $data['updated_by'] = auth()->user()->id;
        $Signatory = Signatory::where('id', $id)->update($data);

        if($request->hasFile('signatory')):
            $oldFileName = (isset($signatoryExist->signature) && !empty($signatoryExist->signature) ? $signatoryExist->signature : '');

            if(!empty($oldFileName) && Storage::disk('google')->exists('public/signatories/'.$oldFileName)):
                $disk = Storage::disk('google');
                $disk->delete('public/signatories/'.$oldFileName);
            endif;

            $document = $request->file('signatory');
            $documentName = time().'_'.$document->getClientOriginalName();
            $path = $document->storeAs('public/signatories/', $documentName, 'google');

            $data = [];
            $data['signature'] = $documentName;
            $Signatory = Signatory::where('id', $id)->update($data);
        endif;

        return response()->json(['message' => 'Signatory set successfully updated'], 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Signatory  $signatory
     * @return \Illuminate\Http\Response
     */

    public function destroy($id){
        $data = Signatory::find($id)->delete();
        return response()->json($data);
    }

    public function restore($id) {
        $data = Signatory::where('id', $id)->withTrashed()->restore();

        response()->json($data);
    }
}
