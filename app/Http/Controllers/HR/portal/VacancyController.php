<?php

namespace App\Http\Controllers\HR\portal;

use App\Http\Controllers\Controller;
use App\Http\Requests\HrVacancyStoreRequest;
use App\Models\HrVacancy;
use App\Models\HrVacancyType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class VacancyController extends Controller
{
    public function index(){

        return view('pages.hr.portal.vacancy.index', [
            'title' => 'HR Portal Vacancies - London Churchill College',
            'breadcrumbs' => [
                ['label' => 'HR Portal', 'href' => 'javascript:void(0);'],
                ['label' => 'Vacancies', 'href' => 'javascript:void(0);']
            ],
            'types' => HrVacancyType::all()
        ]);
    }

    public function list(Request $request){
        $queryStr = (isset($request->querystr) && !empty($request->querystr) ? $request->querystr : '');
        $status = (isset($request->status) ? $request->status : 1);

        $sorters = (isset($request->sorters) && !empty($request->sorters) ? $request->sorters : array(['field' => 'id', 'dir' => 'DESC']));
        $sorts = [];
        foreach($sorters as $sort):
            $sorts[] = $sort['field'].' '.$sort['dir'];
        endforeach;

        $query = HrVacancy::orderByRaw(implode(',', $sorts));
        if(!empty($queryStr)):
            $query->where('title','LIKE','%'.$queryStr.'%');
        endif;
        if($status == 2):
            $query->onlyTrashed();
        else:
            $query->where('active', $status);
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
                    'title' => $list->title,
                    'type' => (isset($list->type->name) && !empty($list->type->name) ? $list->type->name : ''),
                    'link' => (isset($list->link) && !empty($list->link) ? $list->link : ''),
                    'date' => (isset($list->date) && !empty($list->date) ? date('jS F, Y', strtotime($list->date)) : ''),
                    'document_url' => (isset($list->document_url) && !empty($list->document_url) ? $list->document_url : ''),
                    'active' => ($list->active == 1 ? $list->active : '0'),
                    'created_by'=> (isset($list->user->employee->full_name) && !empty($list->user->employee->full_name) ? $list->user->employee->full_name : $list->user->name),
                    'created_at'=> (isset($list->created_at) && !empty($list->created_at) ? date('jS F, Y', strtotime($list->created_at)) : ''),
                    'deleted_at' => $list->deleted_at
                ];
                $i++;
            endforeach;
        endif;
        return response()->json(['last_page' => $last_page, 'data' => $data]);
    }


    public function store(HrVacancyStoreRequest $request){
        $vacnancy = HrVacancy::create([
            'hr_vacancy_type_id' => $request->hr_vacancy_type_id,
            'title' => $request->title,
            'link' => (isset($request->link) && !empty($request->link) ? $request->link : null),
            'date' => (isset($request->date) && !empty($request->date) ? date('Y-m-d', strtotime($request->date)) : ''),
            'active' => (isset($request->active) && $request->active > 0 ? $request->active : 0),

            'created_by' => auth()->user()->id
        ]);

        if($vacnancy->id):
            if($request->hasFile('document')):
                $document = $request->file('document');
                $documentName = 'Vacancy_'.$vacnancy->id.'_'.time() . '.' . $document->getClientOriginalExtension();
                $path = $document->storeAs('public/vacancies/'.$vacnancy->id, $documentName, 'local');

                $userUpdate = HrVacancy::where('id', $vacnancy->id)->update([
                    'document' => $documentName
                ]);
            endif;
            return response()->json(['msg' => 'Vacancy successfully created'], 200);
        else:
            return response()->json(['msg' => 'Something went wrong. Please try again later or contact with the administrator.'], 304);
        endif;
    }

    public function edit(HrVacancy $vacancy){
        return response()->json(['row' => $vacancy], 200);
    }

    public function update(HrVacancyStoreRequest $request){
        $id = $request->id;
        $vacancyRow = HrVacancy::find($id);

        $vacnancy = HrVacancy::where('id', $id)->update([
            'hr_vacancy_type_id' => $request->hr_vacancy_type_id,
            'title' => $request->title,
            'link' => (isset($request->link) && !empty($request->link) ? $request->link : null),
            'date' => (isset($request->date) && !empty($request->date) ? date('Y-m-d', strtotime($request->date)) : ''),
            'active' => (isset($request->active) && $request->active > 0 ? $request->active : 0),

            'updated_by' => auth()->user()->id
        ]);

        if($request->hasFile('document')):
            if(isset($vacancyRow->document) && !empty($vacancyRow->document)):
                if (Storage::disk('local')->exists('public/vacancies/'.$id.'/'.$vacancyRow->document)):
                    Storage::disk('local')->delete('public/vacancies/'.$id.'/'.$vacancyRow->document);
                endif;
            endif;

            $document = $request->file('document');
            $documentName = 'Vacancy_'.$id.'_'.time() . '.' . $document->getClientOriginalExtension();
            $path = $document->storeAs('public/vacancies/'.$id, $documentName, 'local');

            $userUpdate = HrVacancy::where('id', $id)->update([
                'document' => $documentName
            ]);
        endif;

        return response()->json(['msg' => 'Vacancy successfully updated'], 200);
    }

    public function destroy($id){
        $data = HrVacancy::find($id)->delete();
        return response()->json(['message' => 'Data successfully moved to trash'], 200);
    }

    public function restore($id) {
        $data = HrVacancy::where('id', $id)->withTrashed()->restore();

        return response()->json(['message' => 'Data successfully restored.'], 200);
    }

    public function updateStatus($id){
        $title = HrVacancy::find($id);
        $active = (isset($title->active) && $title->active == 1 ? 0 : 1);

        HrVacancy::where('id', $id)->update([
            'active'=> $active,
            'updated_by' => auth()->user()->id
        ]);

        return response()->json(['message' => 'Status successfully updated'], 200);
    }
}
