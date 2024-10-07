<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\StudentNote;
use App\Models\StudentNoteFollowedBy;
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
                    'student_photo' => (isset($list->note->student->photo_url) && !empty($list->note->student->photo_url) ? $list->note->student->photo_url : asset('build/assets/images/user_avatar.png')),
                    'first_name' => (isset($list->note->student->first_name) && !empty($list->note->student->first_name) ? $list->note->student->first_name : ''),
                    'last_name' => (isset($list->note->student->last_name) && !empty($list->note->student->last_name) ? $list->note->student->last_name : ''),
                    'registration_no' => (isset($list->note->student->registration_no) && !empty($list->note->student->registration_no) ? $list->note->student->registration_no : ''),
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
}
