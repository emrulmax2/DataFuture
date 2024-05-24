<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Models\DocumentSettings;
use Illuminate\Http\Request;
use App\Http\Requests\DocumentSettingsRequests;
use App\Http\Requests\DocumentSettingsUpdateRequests;
use App\Models\User;

class DocumentSettingsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('pages.settings.documentsettings.index', [
            'title' => 'Document Settings - London Churchill College',
            'subtitle' => 'Applicant Settings',
            'breadcrumbs' => [
                ['label' => 'Site Settings', 'href' => route('site.setting')],
                ['label' => 'Document Settings', 'href' => 'javascript:void(0);']
            ]
        ]);
    }

    public function list(Request $request){
        $queryStr = (isset($request->querystr) && !empty($request->querystr) ? $request->querystr : '');
        $status = (isset($request->status) && $request->status > 0 ? $request->status : 1);

        $total_rows = $count = DocumentSettings::count();
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

        $query = DocumentSettings::orderByRaw(implode(',', $sorts));
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
                    'type' => $list->type,
                    'application' => ($list->application==1 ? 'Yes' : 'No'),
                    'admission' => ($list->admission==1 ? 'Yes' : 'No'),
                    'registration' => ($list->registration==1 ? 'Yes' : 'No'),
                    'live' => ($list->live==1 ? 'Yes' : 'No'),
                    'student_profile' => ($list->student_profile==1 ? 'Yes' : 'No'),
                    'staff' => ($list->staff==1 ? 'Yes' : 'No'),
                    'agent' => ($list->agent==1 ? 'Yes' : 'No'),
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
    public function store(DocumentSettingsRequests $request)
    {
        $request->request->add(['created_by' => auth()->user()->id]);
        if((empty($request->application)) && (empty($request->admission)) && (empty($request->registration)) && (empty($request->live)) && (empty($request->student_profile) && (empty($request->staff))&& (empty($request->agent)))) {
            return response()->json(['message' => 'Something went wrong. Please try later'], 422);
        } else{
            $data = DocumentSettings::create($request->all());

            return response()->json($data);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\DocumentSettings  $documentSettings
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\DocumentSettings  $documentSettings
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $data = DocumentSettings::find($id);

        if($data){
            return response()->json($data);
        }else{
            return response()->json(['message' => 'Something went wrong. Please try later'], 422);
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\DocumentSettings  $documentSettings
     * @return \Illuminate\Http\Response
     */
    public function update(DocumentSettingsUpdateRequests $request, DocumentSettings $dataId)
    {
        if((empty($request->application)) && (empty($request->admission)) && (empty($request->registration)) && (empty($request->live)) && (empty($request->student_profile)) && (empty($request->staff))&& (empty($request->agent))) {
            return response()->json(['message' => 'Something went wrong. Please try later'], 422);
        } else{
            $data = DocumentSettings::where('id', $request->id)->update([
                'name'=> $request->name,
                'type'=> $request->type,
                'application' => (isset($request->application) ? $request->application : '0'),
                'admission' => (isset($request->admission) ? $request->admission : '0'),
                'registration' => (isset($request->registration) ? $request->registration : '0'),
                'live' => (isset($request->live) ? $request->live : '0'),
                'student_profile' => (isset($request->student_profile) ? $request->student_profile : '0'),
                'staff' => (isset($request->staff) ? $request->staff : '0'),
                'agent' => (isset($request->agent) ? $request->agent : '0'),
                'updated_by' => auth()->user()->id
            ]);

            return response()->json($data);
        }

        /*if($data->wasChanged()){
            return response()->json(['message' => 'Data updated'], 200);
        }else{
            return response()->json(['message' => 'No data Modified'], 304);
        }*/
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\DocumentSettings  $documentSettings
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $data = DocumentSettings::find($id)->delete();
        return response()->json($data);
    }

    public function restore($id) {
        $data = DocumentSettings::where('id', $id)->withTrashed()->restore();

        response()->json($data);
    }
}
