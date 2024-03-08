<?php

namespace App\Http\Controllers;

use App\Models\InternalLink;
use App\Http\Requests\StoreInternalLinkRequest;
use App\Http\Requests\UpdateInternalLinkRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage as FacadesStorage;

class InternalLinkController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('pages.settings.internallink.index', [
            'title' => 'Internal Link - LCC Data Future Managment',
            'subtitle' => '',
            'breadcrumbs' => [
                ['label' => 'Site Settings', 'href' => route('site.setting')],
                ['label' => 'Internal Site Link', 'href' => 'javascript:void(0);']
            ],
            'parents' => InternalLink::whereNull('parent_id')->get(),
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

        $query = InternalLink::whereNull("parent_id")->orderByRaw(implode(',', $sorts));
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
                $childrens = InternalLink::where("parent_id",$list->id)->get();
                if($childrens->count()>0)
                    $data[] = [
                        'id' => $list->id,
                        'sl' => $i,
                        'name' => $list->name,
                        'image' => $list->image,
                        'parent_id' => $list->parent_id,
                        'link' => $list->link,
                        'deleted_at' => $list->deleted_at,
                        "_children"=> $childrens
                    ];
                else 
                    $data[] = [
                        'id' => $list->id,
                        'sl' => $i,
                        'name' => $list->name,
                        'image' => $list->image,
                        'parent_id' => $list->parent_id,
                        'link' => $list->link,
                        'deleted_at' => $list->deleted_at,
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
     * @param  \App\Http\Requests\StoreInternalLinkRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreInternalLinkRequest $request)
    {
        $internalLink = new InternalLink();
        $request->merge(['created_by'=>auth()->user()->id]);
        $internalLink->fill($request->all());
        $internalLink->save();
        $document = $request->file('file');
        $imageName = time().'_'.$document->getClientOriginalName();
        $path = $document->storeAs('public/internallink/'.$internalLink->id, $imageName);
        $data = [];

        //$data['doc_type'] = $document->getClientOriginalExtension();
        
        $data['image'] = FacadesStorage::disk('local')->url($path);
        // $data['display_file_name'] = $document->getClientOriginalName();
        // $data['current_file_name'] = $imageName;
        $data['updated_by'] = auth()->user()->id;
        $internalLink->fill($data);
        $updated = $internalLink->save();

        if($updated)
            return response()->json(['message' => 'Document successfully uploaded.'], 200);
        else
            return response()->json(['message' => 'Document not uploaded'], 302);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\InternalLink  $internalLink
     * @return \Illuminate\Http\Response
     */
    public function show(InternalLink $internalLink)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\InternalLink  $internalLink
     * @return \Illuminate\Http\Response
     */
    public function edit(InternalLink $internalLink)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateInternalLinkRequest  $request
     * @param  \App\Models\InternalLink  $internalLink
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateInternalLinkRequest $request)
    {
        $internalLink = InternalLink::find($request->id); 
        $request->merge(['updated_by'=>auth()->user()->id]);

        $document = $request->file('file');

        if($document) {
            FacadesStorage::delete($internalLink->image);
            $imageName = time().'_'.$document->getClientOriginalName();
            $path = $document->storeAs('public/internallink/'.$internalLink->id, $imageName);
            $data = [];

            //$data['doc_type'] = $document->getClientOriginalExtension();
            
            $data['image'] = FacadesStorage::disk('local')->url($path);
            // $data['display_file_name'] = $document->getClientOriginalName();
            // $data['current_file_name'] = $imageName;

            $request->merge($data);
            
        }
        $internalLink->fill($request->all());
        $updated = $internalLink->save();

        if($updated)
            return response()->json(['message' => 'Data successfully uploaded.'], 200);
        else
            return response()->json(['message' => 'Data not uploaded'], 302);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\InternalLink  $internalLink
     * @return \Illuminate\Http\Response
     */
    public function destroy($id){
        $data = InternalLink::find($id)->delete();
        return response()->json($data);
    }

    public function restore($id) {
        $data = InternalLink::where('id', $id)->withTrashed()->restore();

        response()->json($data);
    }



}
