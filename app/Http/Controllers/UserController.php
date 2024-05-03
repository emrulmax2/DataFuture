<?php

namespace App\Http\Controllers;

use App\Http\Requests\UserCreateRequest;
use App\Models\Role;
use App\Models\User;
use App\Models\UserRole;
use App\Models\PermissionTemplate;
use App\Models\PermissionCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Http\Requests\UserUpdateRequest;
use App\Models\ReferralCode;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class UserController extends Controller
{
    public function index()
    {
        return view('pages/users/index', [
            'title' => 'User Managment - LCC Data Future Managment',
            'breadcrumbs' => [
                ['label' => 'Users', 'href' => 'javascript:void(0);']
            ],
            'roles' => Role::all()
        ]);
    }
    public function useraccess($userId)
    {
        return view('pages/users/access/index', [
            'title' => 'User Access Dashboard - LCC Data Future Managment',
            'breadcrumbs' => [
                ['label' => 'User Access Dashboard', 'href' => 'javascript:void(0);']
            ],
            'user' => User::find($userId),
        ]);
    }

    public function useraccessStaff($userId,$roleId) {

        return view('pages/users/access/staff', [
            'title' => 'Potential Interviewee List',
            'breadcrumbs' => [
                ['label' => 'User Dashboard', 'href' => route('useraccess',$userId)],
                ['label' => 'Staff Dashboard', 'href' => 'javascript:void(0);'],
            ],
            'user' => User::find($userId),
            'role' => Role::where('id', $roleId)->get(),       
            
        ]);

    }

    public function list(Request $request) {

        $queryStr = (isset($request->querystr) && !empty($request->querystr) ? $request->querystr : '');
        $status = (isset($request->status) && $request->status > 0 ? $request->status : 1);

        $sorters = (isset($request->sorters) && !empty($request->sorters) ? $request->sorters : array(['field' => 'id', 'dir' => 'DESC']));
        $sorts = [];
        foreach($sorters as $sort):
            $sorts[] = $sort['field'].' '.$sort['dir'];
        endforeach;

        $query = User::orderByRaw(implode(',', $sorts));
        if(!empty($queryStr)):
            $query->where('name','LIKE','%'.$queryStr.'%');
            $query->where('email','LIKE','%'.$queryStr.'%');
        endif;
        if($status == 2):
            $query->onlyTrashed();
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
                $roles = '';
                if(!empty($list->userRole)):
                    foreach($list->userRole as $urole):
                        $roles .= '<span class="btn btn-secondary px-2 py-0 rounded-0 mr-1 mb-1">'.$urole->role->display_name.'</span>';
                    endforeach;
                endif;
                $data[] = [
                    'id' => $list->id,
                    'sl' => $i,
                    'name' => $list->name,
                    'email' => $list->email,
                    'gender' => $list->gender,
                    'photo_url' => $list->photo_url,
                    'roles' => $roles,
                    'deleted_at' => $list->deleted_at
                ];
                $i++;
            endforeach;
        endif;
        return response()->json(['last_page' => $last_page, 'data' => $data]);

    }

    public function store(UserCreateRequest $request) {

        $role_id = $request->role_id;
        $user = User::create([
            'name'=> $request->name,
            'email'=> $request->email,
            'password'=> Hash::make($request->password),
            'gender'=> $request->gender,
            'active'=> (isset($request->active) && $request->active > 0 ? $request->active : 0)
        ]);
        if($user):
            if($request->hasFile('photo')):
                $photo = $request->file('photo');
                $imageName = 'Avatar_'.$user->id.'_'.time() . '.' . $request->photo->getClientOriginalExtension();
                $path = $photo->storeAs('public/users/'.$user->id, $imageName, 's3');

                $userUpdate = User::where('id', $user->id)->update([
                    'photo' => $imageName
                ]);
            endif;
            if(!empty($role_id)):
                foreach($role_id as $role):
                    $userRole = UserRole::create([
                        'role_id' => $role,
                        'user_id' => $user->id,
                    ]);
                    $role = Role::find($role);
                    if($userRole && $role->type == 'Agent'):
                        $referralCode = Str::upper(Str::random(9));
                        $referral = ReferralCode::create([
                            'code' => $referralCode,
                            'type' => 'Agent',
                            'user_id' => $user->id,
                            'created_by' => auth()->user()->id,
                        ]);
                    endif;
                endforeach;
            endif;

            return response()->json(['message' => 'User successfully created'], 200);
        else:
            return response()->json(['message' => 'Something went wrong. Please try later.'], 422);
        endif;

    }

    public function edit($id) {

        $data = User::with('userRole')->find($id);

        $roles = [];

        if(isset($data->userRole) && !empty($data->userRole)):

            foreach($data->userRole as $ur):
                $roles[] = $ur->role_id;
            endforeach;

        endif;

        $data['roleIds'] = $roles;

        if($data) {

            return response()->json($data);

        }else{

            return response()->json(['message' => 'Something went wrong. Please try later'], 422);

        }

    }

    public function update(UserUpdateRequest $request, User $dataId) {   

        $userID = $request->id;
        $userOldRow = User::find($userID);
        
        $user = User::find($userID);
        $newData = [
            'name'=> $request->name,
            'email'=> $request->email,
            'gender'=> $request->gender,
            'active'=> (isset($request->active) && $request->active > 0 ? $request->active : 0)
        ];

        if(isset($request->password) && !empty($request->password)):
            $newData['password'] = Hash::make($request->password);
        endif;
        
        $newImageName = (isset($userOldRow->photo)) ? $userOldRow->photo : '';

        if($request->hasFile('photo')):

            $imageName = 'Avatar_'.$userID.'_'.time() . '.' . $request->photo->getClientOriginalExtension();
            $path = $request->file('photo')->storeAs('public/users/'.$userID, $imageName, 's3');
            if(isset($userOldRow->photo) && !empty($userOldRow->photo)):
                if (Storage::disk('s3')->exists('public/users/'.$userID.'/'.$userOldRow->photo)):
                    Storage::disk('s3')->delete('public/users/'.$userID.'/'.$userOldRow->photo);
                endif;
            endif;
            $newImageName = $imageName;

        endif;

        $user->fill($newData);
        $user->photo = $newImageName;
        $changes = $user->getDirty();
        $user->save();

        $isChanged = false;
        $role_id = $request->role_id;
        if(!empty($role_id)):
            $delUserRole = UserRole::where('user_id', $userID)->forceDelete();
            $isAgent = false;
            foreach($role_id as $role):
                $userRole = UserRole::create([
                    'role_id' => $role,
                    'user_id' => $userID,
                ]);
                $role = Role::find($role);
                if($userRole && $role->type == 'Agent'):
                    $isAgent = true;
                    $referralCode = Str::upper(Str::random(9));
                    $referral = ReferralCode::create([
                        'code' => $referralCode,
                        'type' => 'Agent',
                        'user_id' => $userID,
                        'created_by' => auth()->user()->id,
                    ]);
                endif;
            endforeach;
            if(!$isAgent):
                $userRole = ReferralCode::where('user_id', $userID)->forceDelete();
            endif;
        else:
            $userRole = ReferralCode::where('user_id', $userID)->forceDelete();
        endif;

        if($user->wasChanged() || $isChanged) {

            return response()->json(['message' => 'Data updated'], 200);

        } else {

            return response()->json(['message' => 'No data Modified'], 304);

        }

    }

    public function destroy($id){
        $data = User::find($id)->delete();
        return response()->json($data);
    }

    public function restore($id) {
        $data = User::where('id', $id)->withTrashed()->restore();

        response()->json($data);
    }
}
