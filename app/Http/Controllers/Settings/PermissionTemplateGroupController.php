<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Http\Requests\PermissionTemplateGroupRequest;
use App\Models\PermissionTemplateGroup;
use Illuminate\Http\Request;

class PermissionTemplateGroupController extends Controller
{
    public function store(PermissionTemplateGroupRequest $request){
        $permission_template_id = $request->permission_template_id;
        $name = $request->name;
        $R = (isset($request->R) && $request->R > 0 ? $request->R : '0');
        $W = (isset($request->W) && $request->W > 0 ? $request->W : '0');
        $D = (isset($request->D) && $request->D > 0 ? $request->D : '0');

        $data = [];
        $data['permission_template_id'] = $permission_template_id;
        $data['name'] = $name;
        $data['R'] = $R;
        $data['W'] = $W;
        $data['D'] = $D;
        $data['created_by'] = auth()->user()->id;

        $group = PermissionTemplateGroup::create($data);
        return response()->json(['message' => 'Data successfully inserted.'], 200);
    }
}
