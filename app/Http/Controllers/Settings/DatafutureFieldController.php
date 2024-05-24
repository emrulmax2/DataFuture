<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Http\Requests\DatafutureFieldRequest;
use App\Models\DatafutureField;
use App\Models\DatafutureFieldCategory;
use Illuminate\Http\Request;

class DatafutureFieldController extends Controller
{
    public function index()
    {
        return view('pages.settings.datafuture.fields.index', [
            'title' => 'Datafuture Field Settings - London Churchill College',
            'subtitle' => 'Datafuture Settings',
            'breadcrumbs' => [
                ['label' => 'Site Settings', 'href' => route('site.setting')],
                ['label' => 'DF Fields', 'href' => 'javascript:void(0);']
            ],
            'categories' => DatafutureFieldCategory::orderBy('name', 'ASC')->get()
        ]);
    }

    public function list(Request $request){
        $queryStr = (isset($request->querystr) && !empty($request->querystr) ? $request->querystr : '');
        $status = (isset($request->status) && $request->status > 0 ? $request->status : 1);
        $category = (isset($request->category) && $request->category > 0 ? $request->category : 0);

        $sorters = (isset($request->sorters) && !empty($request->sorters) ? $request->sorters : array(['field' => 'id', 'dir' => 'DESC']));
        $sorts = [];
        foreach($sorters as $sort):
            $sorts[] = $sort['field'].' '.$sort['dir'];
        endforeach;

        $query = DatafutureField::orderByRaw(implode(',', $sorts));
        if(!empty($queryStr)):
            $query->where('name','LIKE','%'.$queryStr.'%');
            $query->orWhere('description','LIKE','%'.$queryStr.'%');
        endif;
        if($category > 0):
            $query->where('datafuture_field_category_id', $category);
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
                $data[] = [
                    'id' => $list->id,
                    'sl' => $i,
                    'category' => (isset($list->category->name) && !empty($list->category->name) ? $list->category->name : ''),
                    'name' => $list->name,
                    'type' => (isset($list->type) && !empty($list->type) ? ucfirst($list->type) : ''),
                    'description' => (isset($list->description) && !empty($list->description) ? $list->description : ''),
                    'deleted_at' => $list->deleted_at
                ];
                $i++;
            endforeach;
        endif;
        return response()->json(['last_page' => $last_page, 'data' => $data]);
    }

    public function store(DatafutureFieldRequest $request){
        $data = DatafutureField::create([
            'datafuture_field_category_id'=> $request->datafuture_field_category_id,
            'name'=> $request->name,
            'type'=> $request->type,
            'description'=> $request->description,
            'created_by' => auth()->user()->id
        ]);
        return response()->json($data);
    }

    public function edit($id){
        $data = DatafutureField::find($id);

        if($data){
            return response()->json($data);
        }else{
            return response()->json(['message' => 'Something went wrong. Please try later'], 422);
        }
    }

    public function update(DatafutureFieldRequest $request){      
        $data = DatafutureField::where('id', $request->id)->update([
            'datafuture_field_category_id'=> $request->datafuture_field_category_id,
            'name'=> $request->name,
            'type'=> $request->type,
            'description'=> $request->description,
            'updated_by' => auth()->user()->id
        ]);


        if($data){
            return response()->json(['message' => 'Data updated'], 200);
        }else{
            return response()->json(['message' => 'No data Modified'], 422);
        }
    }

    public function destroy($id){
        $data = DatafutureField::find($id)->delete();
        return response()->json($data);
    }

    public function restore(Request $request) {
        $data = DatafutureField::where('id', $request->id)->withTrashed()->restore();

        response()->json($data);
    }
}
