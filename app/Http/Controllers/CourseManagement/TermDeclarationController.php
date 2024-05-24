<?php

namespace App\Http\Controllers\CourseManagement;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTermDeclarationRequest;
use App\Http\Requests\UpdateTermDeclarationRequest;
use App\Models\AcademicYear;
use App\Models\TermDeclaration;
use App\Models\TermType;
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
        
        return view('pages.course-management.term-declaration.index', [
            'title' => 'Course & Semester - London Churchill College',
            'subtitle' => 'Term Declarations',
            'breadcrumbs' => [
                ['label' => 'Course Management', 'href' => 'javascript:void(0);'],
                ['label' => 'Term Declaration', 'href' => 'javascript:void(0);']
            ],
            
            'termTypes' => TermType::all(),
            'academicYears' => AcademicYear::orderBy('id','desc')->get(),
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
                    'academic_year' => $list->academicYear->name,
                    'type' => $list->termType->name,           
                    'start_date'=> $list->start_date,
                    'end_date'=> $list->end_date,
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
            'academic_year_id'=> $request->academic_year_id,
            'term_type_id'=> $request->term_type_id,
            'start_date'=> $request->start_date,
            'end_date'=> $request->end_date,
            'total_teaching_weeks'=> $request->total_teaching_weeks,
            'teaching_start_date'=> $request->teaching_start_date,
            'teaching_end_date'=> $request->teaching_end_date,
            'revision_start_date'=> $request->revision_start_date,
            'revision_end_date'=> $request->revision_end_date,
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
    public function edit(TermDeclaration $term_declaration)
    {
        
        if($term_declaration){
            return response()->json($term_declaration);
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
        //dd($request);
        // $data = TermDeclaration::where('id', $term->id)->update([
        //     'name'=> $request->name,
        //     'term_type_id'=> $request->term_type_id,
        //     'academic_year_id'=> $request->academic_year_id,
        //     'start_date'=> $request->start_date,
        //     'end_date'=> $request->end_date,
        //     'total_teaching_weeks'=> $request->total_teaching_weeks,
        //     'teaching_start_date'=> $request->teaching_start_date,
        //     'teaching_end_date'=> $request->teaching_end_date,
        //     'revision_start_date'=> $request->revision_start_date,
        //     'revision_end_date'=> $request->revision_end_date,
        //     'updated_by' => auth()->user()->id
        // ]);
        
        $request->request->add(['updated_by' => auth()->user()->id]);
        $request->request->remove('id');
        
        $term->fill($request->all());
        $term->save();
        
        if($term->wasChanged()){
            return response()->json(['message' => 'Data updated'], 200);
        }else{
            return response()->json(['message' => 'something went wrong'], 422);
        }

        $dataset = TermDeclaration::all()->sortByDesc("name");
        Cache::forever('terms', $dataset);

        return response()->json($term);


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
