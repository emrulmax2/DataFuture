<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Http\Requests\VenueBaseDatafutureRequest;
use App\Models\VenueBaseDatafuture;
use Illuminate\Http\Request;

class VenueBaseDatafutureController extends Controller
{
    public function list(Request $request){
        $queryStr = (isset($request->querystr) && !empty($request->querystr) ? $request->querystr : '');
        $status = (isset($request->status) && $request->status > 0 ? $request->status : 1);
        $venue = (isset($request->venue) && $request->venue > 0 ? $request->venue : 0);

        $sorters = (isset($request->sorters) && !empty($request->sorters) ? $request->sorters : array(['field' => 'id', 'dir' => 'DESC']));
        $sorts = [];
        foreach($sorters as $sort):
            $sorts[] = $sort['field'].' '.$sort['dir'];
        endforeach;

        $query = VenueBaseDatafuture::where('venue_id', $venue)->orderByRaw(implode(',', $sorts));
        if(!empty($queryStr)):
            $query->orWhere('field_value','LIKE','%'.$queryStr.'%');
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
                    'category' => (isset($list->field->category->name) ? $list->field->category->name : ''),
                    'datafuture_field_id' => (isset($list->field->name) ? $list->field->name : ''),
                    'field_type' => (isset($list->field->type) ? $list->field->type : ''),
                    'field_value' => $list->field_value,
                    'field_desc' => (isset($list->field->description) ? $list->field->description : ''),
                    'deleted_at' => $list->deleted_at
                ];
                $i++;
            endforeach;
        endif;
        return response()->json(['last_page' => $last_page, 'data' => $data]);
    }

    public function store(VenueBaseDatafutureRequest $request){
        $request->merge([
            'created_by' => auth()->user()->id
        ]);
        
        $courseDF = VenueBaseDatafuture::create($request->all());
        
        return response()->json($courseDF);
    }

    public function edit($id){
        $data = VenueBaseDatafuture::find($id);

        if($data){
            return response()->json($data);
        }else{
            return response()->json(['message' => 'Something went wrong. Please try later'], 422);
        }
    }

    public function update(VenueBaseDatafutureRequest $request){
        $dfID = $request->id;
        $venue_id = $request->venue_id;
        $courseDF = VenueBaseDatafuture::where('id', $dfID)->where('venue_id', $venue_id)->update([
            'datafuture_field_id'=> $request->datafuture_field_id,
            'field_value'=> $request->field_value,
            'updated_by' => auth()->user()->id
        ]);


        if($courseDF){
            return response()->json(['message' => 'Data updated'], 200);
        }else{
            return response()->json(['message' => 'something went wrong'], 422);
        }
    }

    public function destroy($id){
        $data = VenueBaseDatafuture::find($id)->delete();
        return response()->json($data);
    }

    public function restore($id) {
        $data = VenueBaseDatafuture::where('id', $id)->withTrashed()->restore();

        response()->json($data);
    }
}
