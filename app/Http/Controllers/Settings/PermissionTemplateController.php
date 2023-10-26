<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Models\PermissionTemplate;
use App\Models\Role;
use App\Models\Department;
use App\Models\PermissionCategory;
use Illuminate\Http\Request;

class PermissionTemplateController extends Controller
{
    
    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request){
        $permission_category_ids = $request->permission_category_ids;
        $role_id = $request->role_id;

        if(!empty($permission_category_ids) && count($permission_category_ids) > 0):
            $existingCategoryIds = PermissionTemplate::where('role_id', $role_id)->pluck('permission_category_id')->toArray();
            $existingDiff = array_diff($existingCategoryIds, $permission_category_ids);
            $permissionCategoryListDiff = array_diff($permission_category_ids, $existingCategoryIds);

            $numInsert = 0;
            $numDelete = 0;
            if(!empty($permissionCategoryListDiff)):
                foreach($permissionCategoryListDiff as $permissionCategory):
                    $withTrashed = PermissionTemplate::where('role_id', $role_id)->where('permission_category_id', $permissionCategory)->onlyTrashed()->get();
                    if(!empty($withTrashed) && $withTrashed->count() > 0):
                        $restorePC = PermissionTemplate::where('role_id', $role_id)->where('permission_category_id', $permissionCategory)->withTrashed()->restore();
                    else:
                        $data = [];
                        $data['permission_category_id'] = $permissionCategory;
                        $data['role_id'] = $role_id;
                        $data['created_by'] = auth()->user()->id;
                        $insertTemplate = PermissionTemplate::create($data);
                    endif;
                    $numInsert += 1;
                endforeach;
            endif;

            if(!empty($existingDiff)):
                foreach($existingDiff as $permissionCategory):
                    $deletePT = PermissionTemplate::where('role_id', $role_id)->where('permission_category_id', $permissionCategory)->delete();
                    $numDelete += 1;
                endforeach;
            endif;

            if($numInsert > 0):
                $message = 'Permission Template Category list '.$numInsert.' item success fully inserted.';
                $message .= ($numDelete > 0 ? ' Previously inserted '.$numDelete.' item deleted.' : '');
            else:
                $message = 'No new Permission Template Category selected. ';
                $message .= ($numDelete > 0 ? ' Previously inserted '.$numDelete.' item deleted.' : '');
            endif;
            return response()->json(['message' => $message], 200);
        else:
            return response()->json(['message' => 'Something went wrong. Please try later or contact administrator.'], 422);
        endif;
    }


    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\PermissionTemplate  $permissionTemplate
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request){
        $permissionTemplateId = $request->recordID;

        $permissionTemplate = PermissionTemplate::where('id', $permissionTemplateId)->get()->first();
        if(!empty($permissionTemplate) && isset($permissionTemplate->id) && $permissionTemplate->id > 0):
            $deletedPT = PermissionTemplate::find($permissionTemplate->id)->delete();
            return response()->json(['res' => 2], 200);
        else:
            $trashedPT = PermissionTemplate::withTrashed()->where('id', $permissionTemplateId)->get()->first();
            if(!empty($trashedPT) && isset($trashedPT->id) && $trashedPT->id > 0):
                $restoredPT = PermissionTemplate::where('id', $trashedPT->id)->withTrashed()->restore();
            endif;
            return response()->json(['res' => 1], 200);
        endif;
    }

}
