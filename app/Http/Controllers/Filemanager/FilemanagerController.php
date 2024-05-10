<?php

namespace App\Http\Controllers\Filemanager;

use App\Http\Controllers\Controller;
use App\Http\Requests\CreateFolderRequest;
use App\Models\DocumentFolder;
use App\Models\DocumentFolderPermission;
use App\Models\DocumentRoleAndPermission;
use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class FilemanagerController extends Controller
{
    public function index($params = ''){
        $parent_id = 0;
        $parameters = [];
        if(!empty($params)):
            $parameters = explode('/', $params);
            if(!empty($params)):
                $currentFolderSlug = end($parameters);
                $currentFolder = DocumentFolder::where('slug', $currentFolderSlug)->get()->first();
                if(isset($currentFolder->id) && $currentFolder->id > 0):
                    $parent_id = $currentFolder->id;
                endif;
            endif;
        endif;
        $currentEmployee = Employee::where('user_id', auth()->user()->id)->get()->first();
        $my_folder_ids = DocumentFolderPermission::where('employee_id', $currentEmployee->id)->whereHas('folder', function($q) use($parent_id){
                        $q->where('parent_id', $parent_id);
                    })->pluck('document_folder_id')->unique()->toArray();


        return view('pages.filemanager.index', [
            'title' => 'File Manager - LCC Data Future Managment',
            'breadcrumbs' => [
                ['label' => 'File Manager', 'href' => 'javascript:void(0);']
            ],
            'employee' => Employee::where('status', 1)->orderBy('first_name', 'ASC')->get(),
            'theFolder' => ($parent_id > 0 ? DocumentFolder::find($parent_id) : []),
            'folders' => DocumentFolder::whereIn('id', $my_folder_ids)->orderBy('name', 'asc')->get(),
            'params' => $params,
            'parent_id' => $parent_id
        ]);
    }

    public function createFolder(CreateFolderRequest $request){
        $folderName = $request->name;
        $parent_id = (isset($request->parent_id) && $request->parent_id > 0 ? $request->parent_id : 0);
        $employee_ids = (isset($request->employee_ids) && !empty($request->employee_ids) ? $request->employee_ids : []);
        $permission = (isset($request->permission) && !empty($request->permission) ? $request->permission : []);

        $data = [];
        $data['parent_id'] = $parent_id;
        $data['name'] = $folderName;
        $data['created_by'] = auth()->user()->id;

        $folder = DocumentFolder::create($data);
        if($folder->id && !empty($employee_ids)):
            foreach($employee_ids as $employee_id):
                if(isset($permission[$employee_id]) && $permission[$employee_id] > 0):
                    $data = [];
                    $data['document_role_and_permission_id'] = $permission[$employee_id];
                    $data['document_folder_id'] = $folder->id;
                    $data['employee_id'] = $employee_id;

                    DocumentFolderPermission::create($data);
                endif;
            endforeach;
        endif;

        return response()->json(['res' => 'Folder successfully created.'], 200);
    }

    public function employeePermissionSet(Request $request){
        $employee_id = $request->employee_id;
        $employee = Employee::find($employee_id);

        $html = '';
        $html .= '<tr class="permissionEmployeeRow" id="employeeFolderPermission_'.$employee_id.'" data-employee="'.$employee_id.'">';
            $html .= '<td><strong>'.$employee->full_name.'</strong></td>';
            $permission = DocumentRoleAndPermission::orderBy('id', 'ASC')->get();
            if(!empty($permission)):
                $dropDownHtml = '';
                $permissionHtml = '';
                $i = 1;
                foreach($permission as $per):
                    $dropDownHtml .= '<option '.($i == 1 ? 'Selected' : '').' value="'.$per->id.'">'.$per->display_name.'</option>';
                    if($i == 1):
                        $permissionHtml .= '<td class="text-center permissionCols">';
                            $permissionHtml .= '<div class="form-check m-0 inline-flex">';
                                $permissionHtml .= '<input disabled '.($per->create == 1 ? 'checked' : '').' id="create_'.$employee_id.'" name="create_'.$employee_id.'" class="form-check-input" type="checkbox" value="1">';
                            $permissionHtml .= '</div>';
                        $permissionHtml .= '</td>';
                        $permissionHtml .= '<td class="text-center permissionCols">';
                            $permissionHtml .= '<div class="form-check m-0 inline-flex">';
                                $permissionHtml .= '<input disabled '.($per->read == 1 ? 'checked' : '').' id="read_'.$employee_id.'" name="read_'.$employee_id.'" class="form-check-input" type="checkbox" value="1">';
                            $permissionHtml .= '</div>';
                        $permissionHtml .= '</td>';
                        $permissionHtml .= '<td class="text-center permissionCols">';
                            $permissionHtml .= '<div class="form-check m-0 inline-flex">';
                                $permissionHtml .= '<input disabled '.($per->update == 1 ? 'checked' : '').' id="update_'.$employee_id.'" name="update_'.$employee_id.'" class="form-check-input" type="checkbox" value="1">';
                            $permissionHtml .= '</div>';
                        $permissionHtml .= '</td>';
                        $permissionHtml .= '<td class="text-center permissionCols">';
                            $permissionHtml .= '<div class="form-check m-0 inline-flex">';
                                $permissionHtml .= '<input disabled '.($per->delete == 1 ? 'checked' : '').' id="delete_'.$employee_id.'" name="delete_'.$employee_id.'" class="form-check-input" type="checkbox" value="1">';
                            $permissionHtml .= '</div>';
                        $permissionHtml .= '</td>';
                    endif;
                    $i++;
                endforeach;

                $html .= '<td>';
                    $html .= '<select name="permission['.$employee_id.']" class="w-full form-control documentRoleAndPermission">';
                        $html .= $dropDownHtml;
                    $html .= '</select>';
                $html .= '</td>';
                $html .= $permissionHtml;
            else:
                $html .= '<td colspan="5">';
                    $html .= '<div class="alert alert-danger-soft show flex items-center mb-2" role="alert"><i data-lucide="alert-octagon" class="w-6 h-6 mr-2"></i> Permission set not found!</div>';
                $html .= '</td>';
            endif;
        $html .= '</tr>';

        return response()->json(['res' => $html], 200);
    }

    public function permissionSet(Request $request){
        $employee_id = $request->employee_id;
        $permission = DocumentRoleAndPermission::find($request->role_permission_id);

        $permissionHtml = '';
        $permissionHtml .= '<td class="text-center permissionCols">';
            $permissionHtml .= '<div class="form-check m-0 inline-flex">';
                $permissionHtml .= '<input disabled '.($permission->create == 1 ? 'checked' : '').' id="create_'.$employee_id.'" name="create_'.$employee_id.'" class="form-check-input" type="checkbox" value="1">';
            $permissionHtml .= '</div>';
        $permissionHtml .= '</td>';
        $permissionHtml .= '<td class="text-center permissionCols">';
            $permissionHtml .= '<div class="form-check m-0 inline-flex">';
                $permissionHtml .= '<input disabled '.($permission->read == 1 ? 'checked' : '').' id="read_'.$employee_id.'" name="read_'.$employee_id.'" class="form-check-input" type="checkbox" value="1">';
            $permissionHtml .= '</div>';
        $permissionHtml .= '</td>';
        $permissionHtml .= '<td class="text-center permissionCols">';
            $permissionHtml .= '<div class="form-check m-0 inline-flex">';
                $permissionHtml .= '<input disabled '.($permission->update == 1 ? 'checked' : '').' id="update_'.$employee_id.'" name="update_'.$employee_id.'" class="form-check-input" type="checkbox" value="1">';
            $permissionHtml .= '</div>';
        $permissionHtml .= '</td>';
        $permissionHtml .= '<td class="text-center permissionCols">';
            $permissionHtml .= '<div class="form-check m-0 inline-flex">';
                $permissionHtml .= '<input disabled '.($permission->delete == 1 ? 'checked' : '').' id="delete_'.$employee_id.'" name="delete_'.$employee_id.'" class="form-check-input" type="checkbox" value="1">';
            $permissionHtml .= '</div>';
        $permissionHtml .= '</td>';

        return response()->json(['res' => $permissionHtml], 200);
    }
}
