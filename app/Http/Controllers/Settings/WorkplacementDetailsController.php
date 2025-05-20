<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Http\Requests\LearningHoursStoreRequest;
use App\Http\Requests\LearningHoursUpdateRequest;
use App\Http\Requests\LevelHoursStoreRequest;
use App\Http\Requests\LevelHoursUpdateRequest;
use App\Http\Requests\WorkplacementDetailsStoreRequest;
use App\Http\Requests\WorkplacementDetailsUpdateRequest;
use App\Models\Course;
use App\Models\LearningHours;
use App\Models\LevelHours;
use App\Models\WorkplacementDetails;
use Illuminate\Support\Facades\Auth;

class WorkplacementDetailsController extends Controller
{
    public function wp_details()
    {
        return view('pages.settings.workplacement.wp-details', [
            'title' => 'Workplacement Settings - London Churchill College',
            'subtitle' => 'Workplacement Settings',
            'breadcrumbs' => [
                ['label' => 'Workplacement Settings', 'href' => 'javascript:void(0);']
            ],
            'courses' => Course::where('active', 1)->get(),
            'workplacement_details' => WorkplacementDetails::with(['level_hours', 'level_hours.learning_hours'])->get()
        ]);
    }

    public function wp_store(WorkplacementDetailsStoreRequest $request)
    {
        WorkplacementDetails::create([
            'name' => (isset($request->name) && !empty($request->name) ? $request->name : null),
            'hours' => (isset($request->hours) && !empty($request->hours) ? $request->hours : null),
            'course_id' => (isset($request->course_id) && !empty($request->course_id) ? $request->course_id : null),
            'start_date' => (isset($request->start_date) && !empty($request->start_date) ? $request->start_date : null),
            'end_date' => (isset($request->end_date) && !empty($request->end_date) ? $request->end_date : null),
            'created_by' => Auth::user()->id
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Workplacement created successfully!',
            'request' => $request->all(),
            'created_data' => WorkplacementDetails::latest()->first()
        ]);
    }

    public function wp_edit($id){
        $data = WorkplacementDetails::findOrFail($id);

        return response()->json($data);
    }

    public function wp_update(WorkplacementDetailsUpdateRequest $request, $id){
        $workplacementDetails = WorkplacementDetails::findOrFail($id);

        $workplacementDetails->update([
            'name' => (isset($request->name) && !empty($request->name) ? $request->name : null),
            'hours' => (isset($request->hours) && !empty($request->hours) ? $request->hours : null),
            'course_id' => (isset($request->course_id) && !empty($request->course_id) ? $request->course_id : null),
            'start_date' => (isset($request->start_date) && !empty($request->start_date) ? $request->start_date : null),
            'end_date' => (isset($request->end_date) && !empty($request->end_date) ? $request->end_date : null),
            'updated_by' => Auth::user()->id
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Workplacement updated successfully!'
        ]);
    }

    public function wp_delete($id){
        WorkplacementDetails::where('id', $id)->delete();
        return response()->json([
            'success' => true,
            'message' => 'Workplacement deleted successfully!'
        ]);
    }

    public function level_hours_store(LevelHoursStoreRequest $request){
        LevelHours::create([
            'name' => (isset($request->name) && !empty($request->name) ? $request->name : null),
            'hours' => (isset($request->hours) && !empty($request->hours) ? $request->hours : null),
            'workplacement_details_id' => (isset($request->workplacement_id) && !empty($request->workplacement_id) ? $request->workplacement_id : null),
            'created_by' => Auth::user()->id
        ]);
    
        return response()->json([
            'success' => true,
            'message' => 'Level hours created successfully!'
        ], 201);
    }

    public function level_hours_edit($id){
        $data = LevelHours::findOrFail($id);

        return response()->json($data);
    }

    public function level_hours_update(LevelHoursUpdateRequest $request, $id){

        $levelHours = LevelHours::findOrFail($id);
        $levelHours->update([
            'name' => (isset($request->name) && !empty($request->name) ? $request->name : $levelHours->name),
            'hours' => (isset($request->hours) && !empty($request->hours) ? $request->hours : $levelHours->hours),
            'workplacement_details_id' => (isset($request->workplacement_id) && !empty($request->workplacement_id) ? $request->workplacement_id : $levelHours->workplacement_details_id),
            'updated_by' => Auth::user()->id
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Level hours updated successfully!'
        ], 200);
    }
    public function learning_hours_store(LearningHoursStoreRequest $request){
        LearningHours::create([
            'name' => (isset($request->name) && !empty($request->name) ? $request->name : null),
            'hours' => (isset($request->hours) && !empty($request->hours) ? $request->hours : null),
            'level_hours_id' => (isset($request->level_hours_id) && !empty($request->level_hours_id) ? $request->level_hours_id : null),
            'created_by' => Auth::user()->id
        ]);
    
        return response()->json([
            'success' => true,
            'message' => 'Learning hours created successfully!'
        ], 201);
    }

    public function learning_hours_edit($id){
        $data = LearningHours::findOrFail($id);

        return response()->json($data);
    }

    public function learning_hours_update(LearningHoursUpdateRequest $request, $id){

        $learningHours = LearningHours::findOrFail($id);
        $learningHours->update([
            'name' => (isset($request->name) && !empty($request->name) ? $request->name : $learningHours->name),
            'hours' => (isset($request->hours) && !empty($request->hours) ? $request->hours : $learningHours->hours),
            'level_hours_id' => (isset($request->level_hours_id) && !empty($request->level_hours_id) ? $request->level_hours_id : $learningHours->level_hours_id),
            'updated_by' => Auth::user()->id
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Learning hours updated successfully!'
        ], 200);
    }

    public function learning_hours_delete($id){
        $learningHours = LearningHours::findOrFail($id);
        $learningHours->delete();

        return response()->json([
            'success' => true,
            'message' => 'Learning hours deleted successfully!'
        ], 200);
    }

    public function level_hours_delete($id){
        $levelHours = LevelHours::findOrFail($id);
        $levelHours->delete();

        return response()->json([
            'success' => true,
            'message' => 'Level hours deleted successfully!'
        ], 200);
    }
}
