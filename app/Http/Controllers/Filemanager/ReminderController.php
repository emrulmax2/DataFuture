<?php

namespace App\Http\Controllers\Filemanager;

use App\Http\Controllers\Controller;
use App\Models\DocumentFolder;
use App\Models\DocumentInfo;
use App\Models\Employee;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ReminderController extends Controller
{
    public function index($params = ''){
        $employee = Employee::where('user_id', auth()->user()->id)->get()->first();
        $employee_id = $employee->id;

        return view('pages.filemanager.reminder', [
            'title' => 'File Manager - London Churchill College',
            'breadcrumbs' => [
                ['label' => 'File Manager', 'href' => 'javascript:void(0);'],
                ['label' => 'Reminder', 'href' => 'javascript:void(0);']
            ],
            'employee' => Employee::where('status', 1)->orderBy('first_name', 'ASC')->get(),
        ]);
    }

    public function list(Request $request){
        $expiredDate = date('Y-m-d', strtotime(date('Y-m-d').' + 60 days'));
        $queryStr = (isset($request->querystr) && !empty($request->querystr) ? $request->querystr : '');
        $documentInfoIds = $this->getMyExpiredDocIds();

        $sorters = (isset($request->sorters) && !empty($request->sorters) ? $request->sorters : array(['field' => 'id', 'dir' => 'DESC']));
        $sorts = [];
        foreach($sorters as $sort):
            $sorts[] = $sort['field'].' '.$sort['dir'];
        endforeach;

        $query = DocumentInfo::with('latestVersion', 'latestVersion.attachments')->orderByRaw(implode(',', $sorts));
        if(!empty($queryStr)):
            $query->where(function($q) use($queryStr){
                $q->where('display_file_name','LIKE','%'.$queryStr.'%')->orWhere('current_file_name','LIKE','%'.$queryStr.'%')
                    ->orWhere('description','LIKE','%'.$queryStr.'%');
            });
        endif;
        $query->whereIn('id', (!empty($documentInfoIds) ? $documentInfoIds : [0]));

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
                $attachments = [];
                if(isset($list->latestVersion->attachments) && $list->latestVersion->attachments->count() > 0):
                    $i = 1;
                    foreach($list->attachments as $theFile):
                        $attachments[$i] = [
                            'url' => $theFile->download_url,
                            'name' => $theFile->display_file_name
                        ];
                        $i++;
                    endforeach;
                endif;
                $data[] = [
                    'id' => $list->id,
                    'sl' => $i,
                    'display_file_name' => (isset($list->display_file_name) && !empty($list->display_file_name) ? $list->display_file_name : ''),
                    'expire_at' => (!empty($list->expire_at) ? date('jS F, Y', strtotime($list->expire_at)) : ''),
                    'expire_color' => (!empty($list->expire_at) && $list->expire_at < date('Y-m-d') ? 'danger' : (!empty($list->expire_at) && $list->expire_at > date('Y-m-d') && $list->expire_at < $expiredDate ? 'warning' : '')),
                    'created_at' => date('jS F, Y h:i A', strtotime($list->created_at)),
                    'created_by' => (isset($list->user->employee->full_name) && !empty($list->user->employee->full_name) ? $list->user->employee->full_name : ''),
                    'download_url' => $list->download_url,
                    'deleted_at' => $list->deleted_at,
                    'attachments' => $attachments,
                    'url' => route('file.manager', $list->path)
                ];
                $i++;
            endforeach;
        endif;
        return response()->json(['last_page' => $last_page, 'data' => $data, 'ids' => $documentInfoIds]);
    }

    public function getMyExpiredDocIds(){
        $expired = date('Y-m-d', strtotime(date('Y-m-d').' + 60 days'));
        $employee = Employee::where('user_id', auth()->user()->id)->get()->first();
        $employee_id = $employee->id;

        $expiredDocuments = DocumentInfo::whereNotNull('expire_at')->where('expire_at', '<=', $expired)->orderBy('expire_at', 'ASC')->get();
        if($expiredDocuments->count() > 0):
            $expiredDocIds = [];
            foreach($expiredDocuments as $doc):
                $paths = explode('/', $doc->path);
                $rootFolder = DocumentFolder::where('slug', $paths[0])->whereHas('permission', function($q) use($employee_id){
                                $q->where('employee_id', $employee_id);
                            })->get()->first();
                if(isset($rootFolder->id) && $rootFolder->id):
                    $expiredDocIds[] = $doc->id;
                endif;
            endforeach;
            return $expiredDocIds;
        else:
            return [];
        endif;
    }
}
