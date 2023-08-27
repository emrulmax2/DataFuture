<?php

namespace App\Http\Controllers;

use App\Models\LetterHeaderFooter;
use Illuminate\Http\Request;
use App\Http\Requests\LetterHeaderFooterRequest;
use App\Http\Requests\LetterFooterRequest;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class LetterHeaderFooterController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('pages.letterheaderfooter.index', [
            'title' => 'Letter Header & Footer - LCC Data Future Managment',
            'breadcrumbs' => [
                ['label' => 'Letter Header & Footer', 'href' => 'javascript:void(0);']
            ]
        ]);
    }

    public function letterheaderlist(Request $request){
        $queryStr = (isset($request->queryStr) && $request->queryStr != '' ? $request->queryStr : '');
        $status = (isset($request->status) && $request->status > 0 ? $request->status : 1);

        $sorters = (isset($request->sorters) && !empty($request->sorters) ? $request->sorters : array(['field' => 'id', 'dir' => 'DESC']));
        $sorts = [];
        foreach($sorters as $sort):
            $sorts[] = $sort['field'].' '.$sort['dir'];
        endforeach;

        $page = (isset($request->page) && $request->page > 0 ? $request->page : 0);
        $perpage = (isset($request->size) && $request->size > 0 ? $request->size : 10);

        $query = LetterHeaderFooter::orderByRaw(implode(',', $sorts))->where('type', 'Header');
        if(!empty($queryStr)):
            $query->where('name','LIKE','%'.$queryStr.'%');
            $query->orWhere('for','LIKE','%'.$queryStr.'%');
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
                $data[] = [
                    'id' => $list->id,
                    'sl' => $i,                  
                    'name'=> $list->name,
                    'for_letter'=> (isset($list->for_letter) && !empty($list->for_letter) ? $list->for_letter : 'No'),
                    'for_email'=> (isset($list->for_email) && !empty($list->for_email) ? $list->for_email : 'No'),
                    'current_file_name' => $list->current_file_name,
                    'url' => asset('storage/letterheaderfooter/header/'.$list->current_file_name),
                    'created_by'=> (isset($list->user->name) ? $list->user->name : 'Unknown'),
                    'created_at'=> (isset($list->created_at) && !empty($list->created_at) ? date('jS F, Y', strtotime($list->created_at)) : ''),
                    'deleted_at' => $list->deleted_at
                ];
                $i++;
            endforeach;
        endif;
        return response()->json(['last_page' => $last_page, 'data' => $data]);
    }

    public function letterfooterlist(Request $request){
        $queryStr = (isset($request->queryStr) && $request->queryStr != '' ? $request->queryStr : '');
        $status = (isset($request->status) && $request->status > 0 ? $request->status : 1);

        $sorters = (isset($request->sorters) && !empty($request->sorters) ? $request->sorters : array(['field' => 'id', 'dir' => 'DESC']));
        $sorts = [];
        foreach($sorters as $sort):
            $sorts[] = $sort['field'].' '.$sort['dir'];
        endforeach;

        $page = (isset($request->page) && $request->page > 0 ? $request->page : 0);
        $perpage = (isset($request->size) && $request->size > 0 ? $request->size : 10);

        $query = LetterHeaderFooter::orderByRaw(implode(',', $sorts))->where('type', 'Footer');
        if(!empty($queryStr)):
            $query->where('name','LIKE','%'.$queryStr.'%');
            $query->orWhere('for','LIKE','%'.$queryStr.'%');
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
                $data[] = [
                    'id' => $list->id,
                    'sl' => $i,                  
                    'name'=> $list->name,
                    'for_letter'=> (isset($list->for_letter) && !empty($list->for_letter) ? $list->for_letter : 'No'),
                    'for_email'=> (isset($list->for_email) && !empty($list->for_email) ? $list->for_email : 'No'),
                    'current_file_name' => $list->current_file_name,
                    'url' => asset('storage/letterheaderfooter/footer/'.$list->current_file_name),
                    'created_by'=> (isset($list->user->name) ? $list->user->name : 'Unknown'),
                    'created_at'=> (isset($list->created_at) && !empty($list->created_at) ? date('jS F, Y', strtotime($list->created_at)) : ''),
                    'deleted_at' => $list->deleted_at
                ];
                $i++;
            endforeach;
        endif;
        return response()->json(['last_page' => $last_page, 'data' => $data]);
    }

    public function uploadLetterHeader(Request $request){
        $document = $request->file('file');
        $imageName = time().'_'.$document->getClientOriginalName();
        $path = $document->storeAs('public/letterheaderfooter/header/', $imageName);

        $data = [];
        $data['name'] = $request->name;
        $data['path'] = asset('storage/letterheaderfooter/header/'.$imageName);
        $data['current_file_name'] = $imageName;
        $data['type'] = $request->type;
        $data['for_letter'] = (isset($request->for_letter) && !empty($request->for_letter) ? $request->for_letter : 'No');
        $data['for_email'] = (isset($request->for_email) && !empty($request->for_email) ? $request->for_email : 'No');
        $data['created_by'] = auth()->user()->id;
        
        $applicantDoc = LetterHeaderFooter::create($data);

        return response()->json(['message' => ' Upload Successfull.'], 200);
    }

    public function uploadLetterFooter(Request $request){
        $document = $request->file('file');
        $imageName = time().'_'.$document->getClientOriginalName();
        $path = $document->storeAs('public/letterheaderfooter/footer/',$imageName);

        $data = [];
        $data['name'] = $request->name;
        $data['type'] = $request->type;
        $data['for_letter'] = (isset($request->for_letter) && !empty($request->for_letter) ? $request->for_letter : 'No');
        $data['for_email'] = (isset($request->for_email) && !empty($request->for_email) ? $request->for_email : 'No');
        $data['path'] = asset('storage/letterheaderfooter/footer/'.$imageName);
        $data['current_file_name'] = $imageName;
        $data['created_by'] = auth()->user()->id;
        
        $applicantDoc = LetterHeaderFooter::create($data);

        return response()->json(['message' => ' Upload Successfull.'], 200);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\LetterHeaderFooter  $letterHeaderFooter
     * @return \Illuminate\Http\Response
     */
    public function show(LetterHeaderFooter $letterHeaderFooter)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\LetterHeaderFooter  $letterHeaderFooter
     * @return \Illuminate\Http\Response
     */
    public function edit(LetterHeaderFooter $letterHeaderFooter)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\LetterHeaderFooter  $letterHeaderFooter
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, LetterHeaderFooter $letterHeaderFooter)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\LetterHeaderFooter  $letterHeaderFooter
     * @return \Illuminate\Http\Response
     */
    public function LetterUploadDestroy(Request $request){
        $recordid = $request->recordid;
        $data = LetterHeaderFooter::find($recordid)->delete();
        return response()->json($data);
    }

    public function LetterUploadRestore(Request $request) {
        $recordid = $request->recordid;
        $data = LetterHeaderFooter::where('id', $recordid)->withTrashed()->restore();

        response()->json($data);
    }
}
