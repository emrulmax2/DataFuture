<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Models\HrCondition;
use Illuminate\Http\Request;

class HrConditionController extends Controller
{
    public function index(){
        return view('pages.settings.hr-condition.index', [
            'title' => 'HR Conditions - London Churchill College',
            'subtitle' => 'HR Settings',
            'breadcrumbs' => [
                ['label' => 'Site Settings', 'href' => route('site.setting')],
                ['label' => 'HR Conditions', 'href' => 'javascript:void(0);']
            ],
            'clockIn_1' => HrCondition::where('type', 'Clock In')->where('time_frame', 1)->get()->first(),
            'clockIn_2' => HrCondition::where('type', 'Clock In')->where('time_frame', 2)->get()->first(),
            'clockIn_3' => HrCondition::where('type', 'Clock In')->where('time_frame', 3)->get()->first(),
            'clockIn_4' => HrCondition::where('type', 'Clock In')->where('time_frame', 4)->get()->first(),

            'clockOut_1' => HrCondition::where('type', 'Clock Out')->where('time_frame', 1)->get()->first(),
            'clockOut_2' => HrCondition::where('type', 'Clock Out')->where('time_frame', 2)->get()->first(),
            'clockOut_3' => HrCondition::where('type', 'Clock Out')->where('time_frame', 3)->get()->first(),
        ]);
    }

    public function store(Request $request){
        $clockin = (isset($request->clockin) && !empty($request->clockin) ? $request->clockin : []);
        $clockout = (isset($request->clockout) && !empty($request->clockout) ? $request->clockout : []);

        if(!empty($clockin)):
            foreach($clockin as $time_frame => $options):
                $type = 'Clock In';
                $data = [];
                $data['type'] = $type;
                $data['time_frame'] = $time_frame;
                $data['minutes'] = (isset($options['time']) && !empty($options['time']) ? $options['time'] : 0);
                $data['notify'] = (isset($options['notify']) && !empty($options['notify']) ? $options['notify'] : 0);
                $data['action'] = (isset($options['action']) && !empty($options['action']) ? $options['action'] : 0);

                $existCondition = HrCondition::where('type', $type)->where('time_frame', $time_frame)->get()->first();
                if(isset($existCondition->id) && $existCondition->id > 0):
                    $data['updated_by'] = auth()->user()->id;
                    HrCondition::where('type', $type)->where('time_frame', $time_frame)->update($data);
                else:
                    $data['created_by'] = auth()->user()->id;
                    HrCondition::create($data);
                endif;
            endforeach;
        else:
            $type = 'Clock In';
            HrCondition::where('type', $type)->forceDelete();
        endif;
        if(!empty($clockout)):
            foreach($clockout as $time_frame => $options):
                $type = 'Clock Out';
                $data = [];
                $data['type'] = $type;
                $data['time_frame'] = $time_frame;
                $data['minutes'] = (isset($options['time']) && !empty($options['time']) ? $options['time'] : 0);
                $data['notify'] = (isset($options['notify']) && !empty($options['notify']) ? $options['notify'] : 0);
                $data['action'] = (isset($options['action']) && !empty($options['action']) ? $options['action'] : 0);

                $existCondition = HrCondition::where('type', $type)->where('time_frame', $time_frame)->get()->first();
                if(isset($existCondition->id) && $existCondition->id > 0):
                    $data['updated_by'] = auth()->user()->id;
                    HrCondition::where('type', $type)->where('time_frame', $time_frame)->update($data);
                else:
                    $data['created_by'] = auth()->user()->id;
                    HrCondition::create($data);
                endif;
            endforeach;
        else:
            $type = 'Clock Out';
            HrCondition::where('type', $type)->forceDelete();
        endif;

        return response()->json(['res' => 'HR Condition Successfully updated!'], 200);
    }
}
