<?php

namespace App\Http\Controllers;


use App\Http\Requests\StoreTermDeclarationRequest;
use App\Http\Requests\UpdateTermDeclarationRequest;
use App\Models\TermDeclaration;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class TermDeclarationController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('pages/term-declaration/index', [
            'title' => 'Semester - LCC Data Future Managment',
            'breadcrumbs' => [['label' => 'Semesters', 'href' => 'javascript:void(0);']]
        ]);
    }

    public function list(Request $request){
        $queryStr = (isset($request->querystr) && !empty($request->querystr) ? $request->querystr : '');
        $status = (isset($request->status) && $request->status > 0 ? $request->status : 1);

        
        $total_rows = $count = TermDeclaration::count();
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

        $query = TermDeclaration::orderByRaw(implode(',', $sorts));
        if(!empty($queryStr)):
            $query->where('name','LIKE','%'.$queryStr.'%');
        endif;
        if($status == 2):
            $query->onlyTrashed();
        endif;

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
     * @param  \App\Http\Requests\StoreTermRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreTermDeclarationRequest $request)
    {
        $data = TermDeclaration::create([
            'name'=> $request->name,
            'created_by' => auth()->user()->id
        ]);

        $semesters = TermDeclaration::all()->sortByDesc("name");
        Cache::forever('terms', $semesters);

        return response()->json($data);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Term  $term
     * @return \Illuminate\Http\Response
     */
    public function show(TermDeclaration $term)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Term  $term
     * @return \Illuminate\Http\Response
     */
    public function edit(TermDeclaration $term)
    {
        
        if($term){
            return response()->json($term);
        }else{
            return response()->json(['message' => 'Something went wrong. Please try later'], 422);
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateTermRequest  $request
     * @param  \App\Models\Term  $term
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateTermDeclarationRequest $request, TermDeclaration $term)
    {
        $data = TermDeclaration::where('id', $term->id)->update([
            'name'=> $request->name,
            'updated_by' => auth()->user()->id
        ]);

        $dataset = TermDeclaration::all()->sortByDesc("name");
        Cache::forever('terms', $dataset);

        return response()->json($data);


        if($data->wasChanged()){
            return response()->json(['message' => 'Data updated'], 200);
        }else{
            return response()->json(['message' => 'No data Modified'], 304);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Term  $term
     * @return \Illuminate\Http\Response
     */
    public function destroy(TermDeclaration $term)
    {
        $data = $term->delete();

        $terms = TermDeclaration::all()->sortByDesc("name");
        Cache::forever('terms', $terms);

        return response()->json($data);
    }

    public function restore($id) {
        $data = TermDeclaration::where('id', $id)->withTrashed()->restore();
        $terms = TermDeclaration::all()->sortByDesc("name");
        Cache::forever('terms', $terms);

        return response()->json($data);
    }
}
