<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\StudentNote;
use App\Models\StudentNoteFollowedBy;
use App\Models\StudentNoteFollowupComment;
use App\Models\StudentNoteFollowupCommentRead;
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

                    'unread_comment' => (isset($list->note->unread_comment_count) ? $list->note->unread_comment_count : 0),
                    
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

    public function getCommentList(Request $request){
        $note_id = $request->note_id;
        $user_id = auth()->user()->id;

        $updateRead = StudentNoteFollowupCommentRead::where('student_note_id', $note_id)->where('user_id', $user_id)->where('read', 0)->update([
            'read' => 1,
            'readed_at' => date('Y-m-d H:i:s')
        ]);
        $HTML = $this->getCommentHtml($user_id, $note_id);

        return response()->json(['htm' => $HTML], 200);
    }

    public function storeComment(Request $request){
        $user_id = auth()->user()->id;
        $note_id = $request->student_note_id;
        $comment = $request->comment;

        $noteComment = StudentNoteFollowupComment::create([
            'student_note_id' => $note_id,
            'comment' => $comment,
            'created_by' => $user_id
        ]);
        if($noteComment){
            $followUpBies = StudentNoteFollowedBy::where('student_note_id', $note_id)->whereNot('user_id', $user_id)->pluck('user_id')->unique()->toArray();
            if(!empty($followUpBies)):
                foreach($followUpBies as $user):
                    StudentNoteFollowupCommentRead::create([
                        'student_note_id' => $note_id,
                        'student_note_followup_comment_id' => $noteComment->id,
                        'user_id' => $user,
                        'read' => 0,
                    ]);
                endforeach;
            endif;
        }
        $HTML = $this->getCommentHtml($user_id, $note_id);

        return response()->json(['htm' => $HTML], 200);
    }

    public function getCommentHtml($user_id, $note_id){
        $HTML = '';
        $comments = StudentNoteFollowupComment::where('student_note_id', $note_id)->orderBy('created_at', 'ASC')->get();
        if($comments->count() > 0):
            foreach($comments as $com):
                if($com->created_by == $user_id):
                    $HTML .= '<div class="float-left mb-5 flex max-w-[90%] items-end sm:max-w-[65%]">';
                        $HTML .= '<div class="image-fit relative mr-5 hidden h-10 w-10 flex-none sm:block">';
                            $HTML .= '<img class="rounded-full" src="'.(isset($com->user->employee->photo_url) && !empty($com->user->employee->photo_url) ? $com->user->employee->photo_url : asset('build/assets/images/avater.png')).'" alt="'.(isset($com->user->employee->full_name) && !empty($com->user->employee->full_name) ? $com->user->employee->full_name : $com->user->name).'">';
                        $HTML .= '</div>';
                        $HTML .= '<div class="rounded-r-md rounded-t-md bg-slate-100 px-4 py-3 text-slate-500 dark:bg-darkmode-400 relative">';
                            $HTML .= (isset($com->reader_html) && !empty($com->reader_html) ? $com->reader_html : '');
                            $HTML .= $com->comment;
                            $HTML .= '<div class="mt-1 text-xs text-slate-500">';
                                $HTML .= date('jS F, Y H:i', strtotime($com->created_at));
                            $HTML .= '</div>';
                        $HTML .= '</div>';
                        /*$HTML .= '<div data-tw-placement="bottom-end" class="dropdown relative my-auto ml-3 hidden sm:block">';
                            $HTML .= '<a data-tw-toggle="dropdown" aria-expanded="false" href="javascript:;" class="cursor-pointer h-4 w-4 text-slate-500">';
                                $HTML .= '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" data-lucide="more-vertical" class="lucide lucide-more-vertical stroke-1.5 h-4 w-4"><circle cx="12" cy="12" r="1"></circle><circle cx="12" cy="5" r="1"></circle><circle cx="12" cy="19" r="1"></circle></svg>';
                            $HTML .= '</a>';
                            $HTML .= '<div data-transition="" data-selector=".show" data-enter="transition-all ease-linear duration-150" data-enter-from="absolute !mt-5 invisible opacity-0 translate-y-1" data-enter-to="!mt-1 visible opacity-100 translate-y-0" data-leave="transition-all ease-linear duration-150" data-leave-from="!mt-1 visible opacity-100 translate-y-0" data-leave-to="absolute !mt-5 invisible opacity-0 translate-y-1" class="dropdown-menu z-[9999] hidden absolute invisible opacity-0 translate-y-1" data-state="leave" id="_xwjt4vpqe" style="display: none; position: absolute; inset: 0px 0px auto auto; margin: 0px; transform: translate(-684px, 142px);" data-popper-placement="bottom-end">';
                                $HTML .= '<div class="dropdown-content rounded-md border-transparent bg-white p-2 shadow-[0px_3px_10px_#00000017] dark:border-transparent dark:bg-darkmode-600 w-40">';
                                    $HTML .= '<a class="cursor-pointer flex items-center p-2 transition duration-300 ease-in-out rounded-md hover:bg-slate-200/60 dark:bg-darkmode-600 dark:hover:bg-darkmode-400 dropdown-item"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" data-lucide="corner-up-left" class="lucide lucide-corner-up-left stroke-1.5 mr-2 h-4 w-4"><polyline points="9 14 4 9 9 4"></polyline><path d="M20 20v-7a4 4 0 0 0-4-4H4"></path></svg>';
                                        $HTML .= 'Reply';
                                    $HTML .= '</a>';
                                    $HTML .= '<a class="cursor-pointer flex items-center p-2 transition duration-300 ease-in-out rounded-md hover:bg-slate-200/60 dark:bg-darkmode-600 dark:hover:bg-darkmode-400 dropdown-item"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" data-lucide="trash" class="lucide lucide-trash stroke-1.5 mr-2 h-4 w-4"><path d="M3 6h18"></path><path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"></path><path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"></path></svg>';
                                        $HTML .= 'Delete';
                                    $HTML .= '</a>';
                                $HTML .= '</div>';
                            $HTML .= '</div>';
                        $HTML .= '</div>';*/
                    $HTML .= '</div>';
                    $HTML .= '<div class="clear-both"></div>';
                else:
                    $HTML .= '<div class="float-right mb-5 flex max-w-[90%] items-end sm:max-w-[65%]">';
                        /*$HTML .= '<div data-tw-placement="bottom-end" class="dropdown relative my-auto mr-3 hidden sm:block">';
                            $HTML .= '<a data-tw-toggle="dropdown" aria-expanded="false" href="javascript:;" class="cursor-pointer h-4 w-4 text-slate-500">';
                                $HTML .= '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" data-lucide="more-vertical" class="lucide lucide-more-vertical stroke-1.5 h-4 w-4"><circle cx="12" cy="12" r="1"></circle><circle cx="12" cy="5" r="1"></circle><circle cx="12" cy="19" r="1"></circle></svg>';
                            $HTML .= '</a>';
                            $HTML .= '<div data-transition="" data-selector=".show" data-enter="transition-all ease-linear duration-150" data-enter-from="absolute !mt-5 invisible opacity-0 translate-y-1" data-enter-to="!mt-1 visible opacity-100 translate-y-0" data-leave="transition-all ease-linear duration-150" data-leave-from="!mt-1 visible opacity-100 translate-y-0" data-leave-to="absolute !mt-5 invisible opacity-0 translate-y-1" class="dropdown-menu absolute z-[9999] hidden invisible opacity-0 translate-y-1" data-state="leave" style="display: none;">';
                                $HTML .= '<div class="dropdown-content rounded-md border-transparent bg-white p-2 shadow-[0px_3px_10px_#00000017] dark:border-transparent dark:bg-darkmode-600 w-40">';
                                    $HTML .= '<a class="cursor-pointer flex items-center p-2 transition duration-300 ease-in-out rounded-md hover:bg-slate-200/60 dark:bg-darkmode-600 dark:hover:bg-darkmode-400 dropdown-item"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" data-lucide="corner-up-left" class="lucide lucide-corner-up-left stroke-1.5 mr-2 h-4 w-4"><polyline points="9 14 4 9 9 4"></polyline><path d="M20 20v-7a4 4 0 0 0-4-4H4"></path></svg>';
                                        $HTML .= 'Reply';
                                    $HTML .= '</a>';
                                    $HTML .= '<a class="cursor-pointer flex items-center p-2 transition duration-300 ease-in-out rounded-md hover:bg-slate-200/60 dark:bg-darkmode-600 dark:hover:bg-darkmode-400 dropdown-item"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" data-lucide="trash" class="lucide lucide-trash stroke-1.5 mr-2 h-4 w-4"><path d="M3 6h18"></path><path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"></path><path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"></path></svg>';
                                        $HTML .= 'Delete';
                                    $HTML .= '</a>';
                                $HTML .= '</div>';
                            $HTML .= '</div>';
                        $HTML .= '</div>';*/
                        $HTML .= '<div class="rounded-l-md rounded-t-md bg-primary px-4 py-3 text-white">';
                            $HTML .= $com->comment;
                            $HTML .= '<div class="mt-1 text-xs text-white text-opacity-80">';
                                $HTML .= date('jS F, Y H:i', strtotime($com->created_at));
                            $HTML .= '</div>';
                        $HTML .= '</div>';
                        $HTML .= '<div class="image-fit relative ml-5 hidden h-10 w-10 flex-none sm:block">';
                        $HTML .= '<img class="rounded-full" src="'.(isset($com->user->employee->photo_url) && !empty($com->user->employee->photo_url) ? $com->user->employee->photo_url : asset('build/assets/images/avater.png')).'" alt="'.(isset($com->user->employee->full_name) && !empty($com->user->employee->full_name) ? $com->user->employee->full_name : $com->user->name).'">';
                        $HTML .= '</div>';
                    $HTML .= '</div>';
                    $HTML .= '<div class="clear-both"></div>';
                endif;
            endforeach;
        else:
            $HTML = '<div class="alert alert-warning-soft show flex items-center mb-2" role="alert">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" data-lucide="alert-triangle" class="lucide lucide-alert-triangle w-6 h-6 mr-2"><path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3Z"></path><path d="M12 9v4"></path><path d="M12 17h.01"></path></svg>
                        Comments not available for this followup.
                    </div>';
        endif;

        return $HTML;
    }
}
