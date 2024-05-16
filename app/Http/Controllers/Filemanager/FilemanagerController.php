<?php

namespace App\Http\Controllers\Filemanager;

use App\Http\Controllers\Controller;
use App\Http\Requests\CreateFolderRequest;
use App\Http\Requests\UpdateFolderPermissionRequest;
use App\Http\Requests\UpdateFolderRequest;
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
        $inherit = (isset($request->permission_inheritence) && $request->permission_inheritence > 0 ? $request->permission_inheritence : 0);
        $employee_ids = (isset($request->employee_ids) && !empty($request->employee_ids) ? $request->employee_ids : []);
        $permission = (isset($request->permission) && !empty($request->permission) ? $request->permission : []);

        $data = [];
        $data['parent_id'] = $parent_id;
        $data['name'] = $folderName;
        $data['created_by'] = auth()->user()->id;

        $folder = DocumentFolder::create($data);
        if($inherit == 1 && $folder->id && $parent_id > 0):
            $parentPermission = DocumentFolderPermission::where('document_folder_id', $parent_id)->orderBy('id', 'ASC')->get();
            if($parentPermission->count() > 0):
                foreach($parentPermission as $prmsn):
                    $data = [];
                    $data['document_role_and_permission_id'] = $prmsn->document_role_and_permission_id;
                    $data['document_folder_id'] = $folder->id;
                    $data['employee_id'] = $prmsn->employee_id;

                    DocumentFolderPermission::create($data);
                endforeach;
            endif;
        elseif($folder->id && !empty($employee_ids)):
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
        $folder_id = (isset($request->folder_id) && $request->folder_id > 0 ? $request->folder_id : 0);
        $employee = Employee::find($employee_id);

        $preSelectedRole = 0;
        if($folder_id > 0):
            $folderPermission = DocumentFolderPermission::where('document_folder_id', $folder_id)->where('employee_id', $employee_id)->get()->first();
            $preSelectedRole = (isset($folderPermission->document_role_and_permission_id) && $folderPermission->document_role_and_permission_id > 0 ? $folderPermission->document_role_and_permission_id : 0);
        endif;

        $html = '';
        $html .= '<tr class="permissionEmployeeRow" id="employeeFolderPermission_'.$employee_id.'" data-employee="'.$employee_id.'">';
            $html .= '<td><strong>'.$employee->full_name.'</strong></td>';
            $permission = DocumentRoleAndPermission::orderBy('id', 'ASC')->get();
            if(!empty($permission)):
                $dropDownHtml = '';
                $permissionHtml = '';
                $i = 1;
                foreach($permission as $per):
                    $dropDownHtml .= '<option '.(($i == 1 && $preSelectedRole == 0) || $preSelectedRole == $per->id ? 'Selected' : '').' value="'.$per->id.'">'.$per->display_name.'</option>';
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

    public function editFolder(Request $request){
        $row_id = $request->row_id;
        $row = DocumentFolder::find($row_id);

        return response()->json(['res' => $row], 200);
    }

    public function updateFolder(UpdateFolderRequest $request){
        $folder_id = $request->folder_id;
        $name = $request->name;

        $data = [];
        $data['name'] = $name;
        $data['updated_by'] = auth()->user()->id;
        $data['updated_at'] = date('Y-m-d H:i:s');

        DocumentFolder::where('id', $folder_id)->update($data);
        return response()->json(['res' => 'Folder successfully updated.'], 200);
    }

    public function editFolderPermission(Request $request){
        $row_id = $request->row_id;

        $employee_ids = [];
        $html = '';

        $allPermission = DocumentRoleAndPermission::orderBy('id', 'ASC')->get();
        $folderPermission = DocumentFolderPermission::where('document_folder_id', $row_id)->orderBy('id', 'ASC')->get();
        if($folderPermission->count()):
            foreach($folderPermission as $perm):
                $employee_ids[] = $perm->employee_id;
                $html .= '<tr class="permissionEmployeeRow" id="employeeFolderPermission_'.$perm->employee_id.'" data-employee="'.$perm->employee_id.'">';
                    $html .= '<td><strong>'.(isset($perm->employee->full_name) ? $perm->employee->full_name : '').'</strong></td>';
                    $html .= '<td>';
                        $html .= '<select name="permission['.$perm->employee_id.']" class="w-full form-control documentRoleAndPermission">';
                            if($allPermission->count() > 0):
                                foreach($allPermission as $pms):
                                    $html .= '<option '.($pms->id == $perm->document_role_and_permission_id ? 'Selected' : '').' value="'.$pms->id.'">'.$pms->display_name.'</option>';
                                endforeach;
                            else:
                                $html .= '<option value="">Select Permission</option>';
                            endif;
                        $html .= '</select>';
                    $html .= '</td>';
                    $html .= '<td class="text-center permissionCols">';
                        $html .= '<div class="form-check m-0 inline-flex">';
                            $html .= '<input disabled '.($perm->role->create == 1 ? 'checked' : '').' id="create_'.$perm->employee_id.'" name="create_'.$perm->employee_id.'" class="form-check-input" type="checkbox" value="1">';
                        $html .= '</div>';
                    $html .= '</td>';
                    $html .= '<td class="text-center permissionCols">';
                        $html .= '<div class="form-check m-0 inline-flex">';
                            $html .= '<input disabled '.($perm->role->read == 1 ? 'checked' : '').' id="read_'.$perm->employee_id.'" name="read_'.$perm->employee_id.'" class="form-check-input" type="checkbox" value="1">';
                        $html .= '</div>';
                    $html .= '</td>';
                    $html .= '<td class="text-center permissionCols">';
                        $html .= '<div class="form-check m-0 inline-flex">';
                            $html .= '<input disabled '.($perm->role->update == 1 ? 'checked' : '').' id="update_'.$perm->employee_id.'" name="update_'.$perm->employee_id.'" class="form-check-input" type="checkbox" value="1">';
                        $html .= '</div>';
                    $html .= '</td>';
                    $html .= '<td class="text-center permissionCols">';
                        $html .= '<div class="form-check m-0 inline-flex">';
                            $html .= '<input disabled '.($perm->role->delete == 1 ? 'checked' : '').' id="delete_'.$perm->employee_id.'" name="delete_'.$perm->employee_id.'" class="form-check-input" type="checkbox" value="1">';
                        $html .= '</div>';
                    $html .= '</td>';
                $html .= '</tr>';
            endforeach;
        endif;

        return response()->json(['emp' => $employee_ids, 'htm' => $html], 200);
    }

    public function updateFolderPermission(UpdateFolderPermissionRequest $request){
        $employee_ids = $request->employee_ids;
        $folder_id = $request->folder_id;

        $existingEmployeeIds = DocumentFolderPermission::where('document_folder_id', $folder_id)->pluck('employee_id')->unique()->toArray();
        $removedEmpIds = array_diff($existingEmployeeIds, $employee_ids);
        if(!empty($removedEmpIds)):
            DocumentFolderPermission::where('document_folder_id', $folder_id)->whereIn('employee_id', $removedEmpIds)->forceDelete();
        endif;

        $permission = (isset($request->permission) && !empty($request->permission) ? $request->permission : []);
        if($folder_id && !empty($employee_ids)):
            foreach($employee_ids as $employee_id):
                if(isset($permission[$employee_id]) && $permission[$employee_id] > 0):
                    $data = [];
                    $data['document_role_and_permission_id'] = $permission[$employee_id];
                    $data['document_folder_id'] = $folder_id;
                    $data['employee_id'] = $employee_id;

                    $existRow = DocumentFolderPermission::where('document_folder_id', $folder_id)->where('employee_id', $employee_id)->get()->first();
                    if(isset($existRow->id) && $existRow->id > 0):
                        DocumentFolderPermission::where('id', $existRow->id)->update($data);
                    else:
                        DocumentFolderPermission::create($data);
                    endif;
                endif;
            endforeach;
        endif;

        return response()->json(['res' => 'Document Folder Permission successfully updated.'], 200);
    }
}
