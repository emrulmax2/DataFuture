<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Http\Requests\LetterSetRequest;
use App\Models\LetterSet;
use Illuminate\Http\Request;

class LetterSetController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('pages.settings.letter.index', [
            'title' => 'Letter Sets - LCC Data Future Managment',
            'subtitle' => 'Communication Settings',
            'breadcrumbs' => [
                ['label' => 'Site Settings', 'href' => route('site.setting')],
                ['label' => 'Letter Sets', 'href' => 'javascript:void(0);']
            ],
        ]);
    }

    public function list(Request $request){
        $queryStr = (isset($request->querystr) && $request->querystr != '' ? $request->querystr : '');
        $status = (isset($request->status) && $request->status > 0 ? $request->status : 1);

        $sorters = (isset($request->sorters) && !empty($request->sorters) ? $request->sorters : array(['field' => 'id', 'dir' => 'DESC']));
        $sorts = [];
        foreach($sorters as $sort):
            $sorts[] = $sort['field'].' '.$sort['dir'];
        endforeach;

        $query = LetterSet::orderByRaw(implode(',', $sorts));
        if(!empty($queryStr)):
            $query->where('letter_type','LIKE','%'.$queryStr.'%');
            $query->orWhere('letter_title','LIKE','%'.$queryStr.'%');
            $query->orWhere('description','LIKE','%'.$queryStr.'%');
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
                    'letter_type' => $list->letter_type,
                    'letter_title' => $list->letter_title,
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
    public function store(LetterSetRequest $request)
    {
        $letterSet = LetterSet::create([
            'letter_type' => $request->letter_type,
            'letter_title' => $request->letter_title,
            'description' => $request->description,
            'created_by' => auth()->user()->id
        ]);
        if($letterSet):
            return response()->json(['message' => 'Letter set successfully created.'], 200);
        else:
            return response()->json(['message' => 'Something went wrong. Please try later.'], 422);
        endif;
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\LetterSet  $letterSet
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $letterSet = LetterSet::find($id);
        return response()->json($letterSet);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\LetterSet  $letterSet
     * @return \Illuminate\Http\Response
     */
    public function update(LetterSetRequest $request)
    {
        $letterSetId = $request->id;
        $letterSet = LetterSet::where('id', $letterSetId)->update([
            'letter_type' => $request->letter_type,
            'letter_title' => $request->letter_title,
            'description' => $request->description,
            'updated_by' => $letterSetId,
        ]);

        return response()->json(['message' => 'Data successfully updated'], 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\LetterSet  $letterSet
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $data = LetterSet::find($id)->delete();
        return response()->json($data);
    }

    public function restore($id) {
        $data = LetterSet::where('id', $id)->withTrashed()->restore();

        response()->json($data);
    }
}
