<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\StudentNote;
use App\Models\TermDeclaration;
use Illuminate\Http\Request;

class FlagManagementController extends Controller
{
    public function index(){
        return view('pages.users.staffs.flags.index', [
            'title' => 'Flags Manager - London Churchill College',
            'breadcrumbs' => [
                ['label' => 'Raised Flags', 'href' => 'javascript:void(0);'],
            ],
            'terms' => TermDeclaration::orderBy('id', 'DESC')->get(),
        ]);
    }

    public function list(Request $request){
        $term_delclaration = (isset($request->term_delclaration) && $request->term_delclaration > 0 ? $request->term_delclaration : 0);
        $status = (isset($request->status) && !empty($request->status) ? $request->status : 'Active');

        $sorters = (isset($request->sorters) && !empty($request->sorters) ? $request->sorters : array(['field' => 'id', 'dir' => 'DESC']));
        $sorts = [];
        foreach($sorters as $sort):
            $sorts[] = $sort['field'].' '.$sort['dir'];
        endforeach;

        $query = StudentNote::orderByRaw(implode(',', $sorts))->where('is_flaged', 'Yes')->where('flaged_status', $status);
        if($term_delclaration > 0):
            $query->where('term_declaration_id', $term_delclaration);
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
                    'student_id' => $list->student_id,
                    'student_photo' => (isset($list->student->photo_url) && !empty($list->student->photo_url) ? $list->student->photo_url : asset('build/assets/images/user_avatar.png')),
                    'first_name' => (isset($list->student->first_name) && !empty($list->student->first_name) ? $list->student->first_name : ''),
                    'last_name' => (isset($list->student->last_name) && !empty($list->student->last_name) ? $list->student->last_name : ''),
                    'registration_no' => (isset($list->student->registration_no) && !empty($list->student->registration_no) ? $list->student->registration_no : ''),
                    'term' => (isset($list->term->name) && !empty($list->term->name) ? $list->term->name : ''),
                    'opening_date' => (isset($list->opening_date) && !empty($list->opening_date) ? date('jS F, Y', strtotime($list->opening_date)) : ''),
                    'note_document_id' => (isset($list->document->id) && $list->document->id > 0 ? $list->document->id : 0),
                    
                    'is_flaged' => (isset($list->is_flaged) && !empty($list->is_flaged) ? $list->is_flaged : 'No'),
                    'flaged_status' => (isset($list->flaged_status) && !empty($list->flaged_status) ? $list->flaged_status : ''),
                    'student_flag_id' => ($list->student_flag_id > 0 ? $list->student_flag_id : '0'),
                    'flag_name' => ($list->student_flag_id > 0 && isset($list->flag->name) && !empty($list->flag->name) ? $list->flag->name : ''),
                    'flag_color' => ($list->student_flag_id > 0 && isset($list->flag->color) && !empty($list->flag->color) ? $list->flag->color : ''),
                    
                    'created_by'=> (isset($list->user->employee->full_name) && !empty($list->user->employee->full_name) ? $list->user->employee->full_name : $list->user->name),
                    'created_at'=> (isset($list->created_at) && !empty($list->created_at) ? date('jS F, Y', strtotime($list->created_at)) : ''),
                    'deleted_at' => $list->deleted_at,
                    'is_ownere' => (isset($list->created_by) && $list->created_by == auth()->user()->id ? 1 : 0)
                ];
                $i++;
            endforeach;
        endif;
        return response()->json(['last_page' => $last_page, 'data' => $data]);
    }
}
