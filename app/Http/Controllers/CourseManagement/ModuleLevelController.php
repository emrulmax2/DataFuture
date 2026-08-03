<?php

namespace App\Http\Controllers\CourseManagement;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\ModuleLevelsRequests;
use App\Http\Requests\ModuleLevelsUpdateRequests;
use App\Models\ModuleLevel;
use App\Models\User;

class ModuleLevelController extends Controller
{
    public function index()
    {
        return view('pages.course-management.modulelevels.index', [
            // Opts this screen into the redesigned module shell.
            'layout' => 'course-top-menu',
            'title' => 'Module Levels - London Churchill College',
            // Was "Term Module Creations" — a copy-paste from another screen.
            'subtitle' => 'Module Levels',
            'cmPageTitle' => 'Module Levels',
            'cmBackUrl' => route('course.management'),
            'cmBackLabel' => 'Back to Course Management',
            'breadcrumbs' => [
                ['label' => 'Course Management', 'href' => route('course.management')],
                ['label' => 'Module Levels', 'href' => 'javascript:void(0);']
            ]
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

        $query = ModuleLevel::orderByRaw(implode(',', $sorts));
        if(!empty($queryStr)):
            $query->where('name','LIKE','%'.$queryStr.'%');
        endif;
        if($status == 2):
            $query->onlyTrashed();
        endif;

        // Counted from the *filtered* query. It used to be an unfiltered
        // ModuleLevel::count(), so searching or switching to Archived left the
        // pager offering pages that could never return a row.
        $total_rows = (clone $query)->reorder()->count();

        $page = (isset($request->page) && $request->page > 0 ? $request->page : 0);
        $perpage = (isset($request->size) && $request->size == 'true'
            ? ($total_rows > 0 ? $total_rows : 10)
            : ($request->size > 0 ? $request->size : 10));
        // 1, not '' — an empty string reaches Tabulator as NaN and breaks the
        // pager. Now that the count is filtered, a no-hit search can land here.
        $last_page = $total_rows > 0 ? ceil($total_rows / $perpage) : 1;

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
                    'name' => $list->name,
                    'deleted_at' => $list->deleted_at
                ];
                $i++;
            endforeach;
        endif;
        return response()->json(['last_page' => $last_page, 'total' => $total_rows, 'data' => $data]);
    }

    public function store(ModuleLevelsRequests $request){
        $data = ModuleLevel::create([
            'name'=> $request->name,
            'created_by' => auth()->user()->id
        ]);
        return response()->json($data);
    }

    public function edit($id){
        $data = ModuleLevel::find($id);

        if($data){
            return response()->json($data);
        }else{
            return response()->json(['message' => 'Something went wrong. Please try later'], 422);
        }
    }

    public function update(ModuleLevelsUpdateRequests $request, ModuleLevel $dataId){
        $data = ModuleLevel::where('id', $request->id)->update([
            'name'=> $request->name,
            'updated_by' => auth()->user()->id
        ]);

        return response()->json($data);


        if($data->wasChanged()){
            return response()->json(['message' => 'Data updated'], 200);
        }else{
            return response()->json(['message' => 'No data Modified'], 304);
        }
    }

    public function destroy($id){
        $data = ModuleLevel::find($id)->delete();
        return response()->json($data);
    }

    public function restore($id) {
        $data = ModuleLevel::where('id', $id)->withTrashed()->restore();

        // The `return` was missing, so this answered 200 with an empty body.
        return response()->json($data);
    }
}
