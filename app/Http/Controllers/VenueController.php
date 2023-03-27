<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\VenueRequest;
use App\Http\Requests\VenueUpdateRequest;
use App\Models\Venue;
use App\Models\User;

class VenueController extends Controller
{
    public function index()
    {
        return view('pages/venues/index', [
            'title' => 'Venues - LCC Data Future Managment',
            'breadcrumbs' => [['label' => 'Venues', 'href' => 'javascript:void(0);']]
        ]);
    }

    public function list(Request $request){
        $queryStr = (isset($request->querystr) && !empty($request->querystr) ? $request->querystr : '');
        $status = (isset($request->status) && $request->status > 0 ? $request->status : 1);

        $page = (isset($request->page) && $request->page > 0 ? $request->page : 0);
        $perpage = (isset($request->size) && $request->size > 0 ? $request->size : 10);
        $total_rows = $count = Venue::count();
        $last_page = $total_rows > 0 ? ceil($total_rows / $perpage) : '';

        $sorters = (isset($request->sorters) && !empty($request->sorters) ? $request->sorters : array(['field' => 'id', 'dir' => 'asc']));
        $sorts = [];
        foreach($sorters as $sort):
            $sorts[] = $sort['field'].' '.$sort['dir'];
        endforeach;
        
        $limit = $perpage;
        $offset = ($page > 0 ? ($page - 1) * $perpage : 0);

        $query = Venue::orderByRaw(implode(',', $sorts));
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
                    'idnumber'=> $list->idnumber,
                    'ukprn'=> $list->ukprn,
                    'postcode'=> $list->postcode,
                    'deleted_at' => $list->deleted_at
                ];
                $i++;
            endforeach;
        endif;
        return response()->json(['last_page' => $last_page, 'data' => $data]);
    }

    public function store(VenueRequest $request){
        $data = Venue::create([
            'name'=> $request->name,
            'idnumber'=> $request->idnumber,
            'ukprn'=> $request->ukprn,
            'postcode'=> $request->postcode,
            'created_by' => auth()->user()->id
        ]);
        return response()->json($data);
    }

    public function edit($id){
        $data = Venue::find($id);

        if($data){
            return response()->json($data);
        }else{
            return response()->json(['message' => 'Something went wrong. Please try later'], 422);
        }
    }

    public function update(VenueUpdateRequest $request, Venue $dataId){
        $data = Venue::where('id', $request->id)->update([
            'name'=> $request->name,
            'idnumber'=> $request->idnumber,
            'ukprn'=> $request->ukprn,
            'postcode'=> $request->postcode,
            'updated_by' => auth()->user()->id
        ]);

        return response()->json($data);


        if($data->wasChanged()){
            return response()->json(['message' => 'Data updated'], 200);
        }else{
            return response()->json(['message' => 'No data Modified'], 304);
        }
    }

    public function show($id)
    {
        return view('pages/venues/show', [
            'title' => 'Venues - LCC Data Future Managment',
            'breadcrumbs' => [
                ['label' => 'Venues', 'href' => route('venues')],
                ['label' => 'Venues Details', 'href' => 'javascript:void(0);']
            ],
            'venue' => Venue::find($id),
        ]);
    }

    public function destroy($id){
        $data = Venue::find($id)->delete();
        return response()->json($data);
    }

    public function restore($id) {
        $data = Venue::where('id', $id)->withTrashed()->restore();

        response()->json($data);
    }
}
