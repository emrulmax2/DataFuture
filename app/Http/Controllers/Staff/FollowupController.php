<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\StudentNote;
use App\Models\StudentNoteFollowedBy;
use App\Models\TermDeclaration;
use Illuminate\Http\Request;

class FollowupController extends Controller
{
    public function index(){
        $userData = \Auth::guard('web')->user();
        
        return view('pages.users.staffs.followups.index', [
            'title' => 'Followups Manager - London Churchill College',
            'breadcrumbs' => [
                ['label' => 'Followups', 'href' => 'javascript:void(0);'],
            ],
            'user' => $userData,
        ]);
    }

    public function list(Request $request){
        $querystr = (isset($request->querystr) && !empty($request->querystr) ? $request->querystr : '');

        $sorters = (isset($request->sorters) && !empty($request->sorters) ? $request->sorters : array(['field' => 'id', 'dir' => 'DESC']));
        $sorts = [];
        foreach($sorters as $sort):
            $sorts[] = $sort['field'].' '.$sort['dir'];
        endforeach;

        $query = StudentNoteFollowedBy::orderByRaw(implode(',', $sorts))->where('user_id', auth()->user()->id)->whereHas('note', function($q){
            $q->where('followed_up', 'yes')->where('followed_up_status', 'Pending');
        });

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
                    'id' => $list->student_note_id,
                    'sl' => $i,
                    'student_id' => $list->note->student_id,
                    'student_photo' => (isset($list->note->student->photo_url) && !empty($list->note->student->photo_url) ? $list->note->student->photo_url : asset('build/assets/images/user_avatar.png')),
                    'first_name' => (isset($list->note->student->first_name) && !empty($list->note->student->first_name) ? $list->note->student->first_name : ''),
                    'last_name' => (isset($list->note->student->last_name) && !empty($list->note->student->last_name) ? $list->note->student->last_name : ''),
                    'registration_no' => (isset($list->note->student->registration_no) && !empty($list->note->student->registration_no) ? $list->note->student->registration_no : ''),
                    'note' => (isset($list->note->note) && !empty($list->note->note) ? strip_tags($list->note->note) : ''),
                    'term' => (isset($list->note->term->name) && !empty($list->note->term->name) ? $list->note->term->name : ''),
                    'opening_date' => (isset($list->note->opening_date) && !empty($list->note->opening_date) ? date('jS F, Y', strtotime($list->note->opening_date)) : ''),
                    'note_document_id' => (isset($list->note->document->id) && $list->note->document->id > 0 ? $list->note->document->id : 0),
                    'followed_up' => (isset($list->note->followed_up) && !empty($list->note->followed_up) ? $list->note->followed_up : 'no'),
                    'followed_up_status' => (isset($list->note->followed_up_status) && !empty($list->note->followed_up_status) ? $list->note->followed_up_status : ''),
                    'followed' => (isset($list->note->followed_tag) && !empty($list->note->followed_tag) ? $list->note->followed_tag : ''),
                    
                    'created_by'=> (isset($list->note->user->employee->full_name) && !empty($list->note->user->employee->full_name) ? $list->note->user->employee->full_name : $list->note->user->name),
                    'created_at'=> (isset($list->note->created_at) && !empty($list->note->created_at) ? date('jS F, Y', strtotime($list->note->created_at)) : ''),
                    'deleted_at' => $list->note->deleted_at,
                    'is_ownere' => (isset($list->note->created_by) && $list->note->created_by == auth()->user()->id ? 1 : 0)
                ];
                $i++;
            endforeach;
        endif;
        return response()->json(['last_page' => $last_page, 'data' => $data]);
    }

    public function completeFollowup(Request $request){
        $theNoteId = $request->recordid;
        $theNote = StudentNote::where('id', $theNoteId)->update([
            'followed_up_status' => 'Completed', 
            'followup_completed_by' => auth()->user()->id,
            'followup_completed_at' => date('Y-m-d H:i:s')
        ]);

        return response()->json(['message' => 'Success'], 200);
    }

    public function showAllFollowups(){
        return view('pages.users.staffs.followups.show-all', [
            'title' => 'Followups Manager - London Churchill College',
            'breadcrumbs' => [
                ['label' => 'All Followups', 'href' => 'javascript:void(0);'],
            ],
            'terms' => TermDeclaration::orderBy('id', 'DESC')->get(),
        ]);
    }

    public function listAll(Request $request){
        $term_delclaration = (isset($request->term_delclaration) && $request->term_delclaration > 0 ? $request->term_delclaration : 0);
        $status = (isset($request->status) && !empty($request->status) ? $request->status : 'Pending');

        $sorters = (isset($request->sorters) && !empty($request->sorters) ? $request->sorters : array(['field' => 'id', 'dir' => 'DESC']));
        $sorts = [];
        foreach($sorters as $sort):
            $sorts[] = $sort['field'].' '.$sort['dir'];
        endforeach;

        $query = StudentNote::orderByRaw(implode(',', $sorts))->where('followed_up', 'yes')->where('followed_up_status', $status);
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
                    'note' => (isset($list->note) && !empty($list->note) ? strip_tags($list->note) : ''),
                    'term' => (isset($list->term->name) && !empty($list->term->name) ? $list->term->name : ''),
                    'opening_date' => (isset($list->opening_date) && !empty($list->opening_date) ? date('jS F, Y', strtotime($list->opening_date)) : ''),
                    'note_document_id' => (isset($list->document->id) && $list->document->id > 0 ? $list->document->id : 0),
                    'followed_up' => (isset($list->followed_up) && !empty($list->followed_up) ? $list->followed_up : 'no'),
                    'followed_up_status' => (isset($list->followed_up_status) && !empty($list->followed_up_status) ? $list->followed_up_status : ''),
                    'followed' => (isset($list->followed_tag) && !empty($list->followed_tag) ? $list->followed_tag : ''),
                    'completed_by' => (isset($list->completed->employee->full_name) && !empty($list->completed->employee->full_name) ? $list->completed->employee->full_name : ''),
                    'completed_at' => (isset($list->followup_completed_at) && !empty($list->followup_completed_at) ? date('jS F, Y', strtotime($list->followup_completed_at)) : ''),
                    
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
