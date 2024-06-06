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
            'title' => 'Letter Sets - London Churchill College',
            'subtitle' => 'Communication Settings',
            'breadcrumbs' => [
                ['label' => 'Site Settings', 'href' => route('site.setting')],
                ['label' => 'Letter Sets', 'href' => 'javascript:void(0);']
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

        $query = LetterSet::orderByRaw(implode(',', $sorts));
        if(!empty($queryStr)):
            $query->where('letter_type','LIKE','%'.$queryStr.'%');
            $query->orWhere('letter_title','LIKE','%'.$queryStr.'%');
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
                    'letter_type' => $list->letter_type,
                    'letter_title' => $list->letter_title,
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
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(LetterSetRequest $request)
    {
        $phase = (isset($request->phase) && !empty($request->phase) ? $request->phase : []);
        $letterSet = LetterSet::create([
            'letter_type' => $request->letter_type,
            'letter_title' => $request->letter_title,
            'description' => $request->description,
            'admission' => (isset($phase['admission']) && $phase['admission'] > 0 ? $phase['admission'] : 0),
            'live' => (isset($phase['live']) && $phase['live'] > 0 ? $phase['live'] : 0),
            'hr' => (isset($phase['hr']) && $phase['hr'] > 0 ? $phase['hr'] : 0),
            'status' => (isset($request->status) && $request->status > 0 ? $request->status : 0),
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
        $phase = (isset($request->phase) && !empty($request->phase) ? $request->phase : []);
        $letterSet = LetterSet::where('id', $letterSetId)->update([
            'letter_type' => $request->letter_type,
            'letter_title' => $request->letter_title,
            'description' => $request->description,
            'admission' => (isset($phase['admission']) && $phase['admission'] > 0 ? $phase['admission'] : 0),
            'live' => (isset($phase['live']) && $phase['live'] > 0 ? $phase['live'] : 0),
            'hr' => (isset($phase['hr']) && $phase['hr'] > 0 ? $phase['hr'] : 0),
            'status' => (isset($request->status) && $request->status > 0 ? $request->status : 0),
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

    public function updateStatus(Request $request){
        $title = LetterSet::find($request->row_id);
        $status = (isset($title->status) && $title->status == 1 ? 0 : 1);

        LetterSet::where('id', $request->row_id)->update([
            'status'=> $status,
            'updated_by' => auth()->user()->id
        ]);

        return response()->json(['message' => 'Status successfully updated'], 200);
    }

    public function updatePhaseStatus(Request $request){
        $phase = $request->phase;
        $letter = LetterSet::find($request->row_id);
        $status = (isset($letter->$phase) && $letter->$phase == 1 ? 0 : 1);

        LetterSet::where('id', $request->row_id)->update([
            $phase => $status,
            'updated_by' => auth()->user()->id
        ]);

        return response()->json(['message' => 'Status successfully updated'], 200);
    }
}
