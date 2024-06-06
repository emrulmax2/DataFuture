<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Http\Requests\ELearningActivitySettingsRequest;
use App\Http\Requests\ELearningActivitySettingsUpdateRequest;
use App\Models\ELearningActivitySetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ELearningActivitySettingController extends Controller
{
    public function index()
    {
        return view('pages.settings.activity.index', [
            'title' => 'E - Learning Activity Settings - London Churchill College',
            'subtitle' => 'E - Learning Activity Settings',
            'breadcrumbs' => [
                ['label' => 'Site Settings', 'href' => route('site.setting')],
                ['label' => 'E-Learning Activity Settings', 'href' => 'javascript:void(0);']
            ],
        ]);
    }

    public function list(Request $request){
        $queryStr = (isset($request->querystr) && !empty($request->querystr) ? $request->querystr : '');
        $status = (isset($request->status) ? $request->status : 1);

        $sorters = (isset($request->sorters) && !empty($request->sorters) ? $request->sorters : array(['field' => 'id', 'dir' => 'DESC']));
        $sorts = [];
        foreach($sorters as $sort):
            $sorts[] = $sort['field'].' '.$sort['dir'];
        endforeach;

        $query = ELearningActivitySetting::orderByRaw(implode(',', $sorts));
        if(!empty($queryStr)):
            $query->where('category','LIKE','%'.$queryStr.'%');
        endif;
        if($status == 2):
            $query->onlyTrashed();
        else:
            $query->where('active', $status);
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
                if ($list->logo !== null && Storage::disk('s3')->exists('public/activity/'.$list->logo)) {
                    $logoUrl = Storage::disk('s3')->temporaryUrl('public/activity/'.$list->logo, now()->addMinutes(120));
                } else {
                    $logoUrl = asset('build/assets/images/placeholders/200x200.jpg');
                }
                $data[] = [
                    'id' => $list->id,
                    'sl' => $i,
                    'category' => $list->category,
                    'name' => $list->name,
                    'logo_url' => $logoUrl,
                    'has_week' => (isset($list->has_week) && $list->has_week > 0 ? $list->has_week : 0),
                    'days_reminder'=> (isset($list->days_reminder) ? $list->days_reminder : NULL),
                    'is_mandatory'=> (isset($list->is_mandatory) && $list->is_mandatory > 0 ? $list->is_mandatory : 0),
                    'active' => $list->active,
                    'deleted_at' => $list->deleted_at
                ];
                $i++;
            endforeach;
        endif;
        return response()->json(['last_page' => $last_page, 'data' => $data]);
    }

    public function store(ELearningActivitySettingsRequest $request){
        $eLearning = ELearningActivitySetting::create([
            'category'=> $request->category,
            'name' => $request->name,
            'has_week'=> (isset($request->has_week) && $request->has_week > 0 ? $request->has_week : 0),
            'days_reminder'=> (isset($request->days_reminder) ? $request->days_reminder : NULL),
            'is_mandatory'=> (isset($request->is_mandatory) && $request->is_mandatory > 0 ? $request->is_mandatory : 0),
            'active'=> (isset($request->active) && $request->active > 0 ? $request->active : 0),
            'created_by' => auth()->user()->id
        ]);
        if($eLearning):
            if($request->hasFile('logo')):
                $logo = $request->file('logo');
                $imageName = 'activity_'.$eLearning->id.'_'.time() . '.' . $logo->getClientOriginalExtension();
                $path = $logo->storeAs('public/activity', $imageName, 's3');

                $activityUpdate = ELearningActivitySetting::where('id', $eLearning->id)->update([
                    'logo' => $imageName
                ]);
            endif;
        endif;
        return response()->json(['message' => 'Data successfully inserted'], 200);
    }

    public function edit(Request $request){
        $id = $request->editid;
        $rowData = ELearningActivitySetting::find($id);
        if ($rowData->logo !== null && Storage::disk('s3')->exists('public/activity/'.$rowData->logo)) {
            $logoUrl = Storage::disk('s3')->url('public/activity/'.$rowData->logo);
        } else {
            $logoUrl = asset('build/assets/images/placeholders/200x200.jpg');
        }
        $rowData['logoUrl'] = $logoUrl;

        if($rowData){
            return response()->json($rowData);
        }else{
            return response()->json(['message' => 'Something went wrong. Please try later'], 422);
        }
    }

    public function update(ELearningActivitySettingsUpdateRequest $request){     
        $oldRow = ELearningActivitySetting::find($request->id);

        $data = ELearningActivitySetting::where('id', $request->id)->update([
            'category'=> $request->category,
            'has_week'=> (isset($request->has_week) && $request->has_week > 0 ? $request->has_week : 0),
            'name' => $request->name,
            'days_reminder'=> (isset($request->days_reminder) ? $request->days_reminder : NULL),
            'is_mandatory'=> (isset($request->is_mandatory) && $request->is_mandatory > 0 ? $request->is_mandatory : 0),
            'active'=> (isset($request->active) && $request->active > 0 ? $request->active : 0),
            'updated_by' => auth()->user()->id
        ]);

        if($request->hasFile('logo')):
            $logo = $request->file('logo');
            $imageName = 'activity_'.$request->id.'_'.time() . '.' . $logo->getClientOriginalExtension();
            $path = $logo->storeAs('public/activity', $imageName, 's3');
            
            if(isset($oldRow->logo) && !empty($oldRow->logo)):
                if (Storage::disk('s3')->exists('public/activity/'.$oldRow->logo)):
                    Storage::disk('s3')->delete('public/activity/'.$oldRow->logo);
                endif;
            endif;

            $activityUpdate = ELearningActivitySetting::where('id', $request->id)->update([
                'logo' => $imageName
            ]);

        endif;

        return response()->json(['message' => 'Data updated'], 200);
    }

    public function destroy($id){
        $data = ELearningActivitySetting::find($id)->delete();
        return response()->json($data);
    }

    public function restore($id) {
        $data = ELearningActivitySetting::where('id', $id)->withTrashed()->restore();

        response()->json($data);
    }

    public function updateStatus($id){
        $activity = ELearningActivitySetting::find($id);
        $active = (isset($activity->active) && $activity->active == 1 ? 0 : 1);

        ELearningActivitySetting::where('id', $id)->update([
            'active'=> $active,
            'updated_by' => auth()->user()->id
        ]);

        return response()->json(['message' => 'Status successfully updated'], 200);
    }
}
