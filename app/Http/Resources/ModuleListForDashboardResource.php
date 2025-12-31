<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ModuleListForDashboardResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        //dd($this['termList']);
        // ["termList" =>$termData,
        //     "data" => $data,
        //     "currenTerm" => $currentTerm ];
        //From Request I will get termList, data, currenTerm
        // I need current term name and each module data for the current term

        // this below is the set of data
        
        // Expect resource to be: ["termList" => $termData, "data" => $data, "currenTerm" => $currentTerm]
        $termList = $this['termList'] ?? [];
        $data = $this['data'] ?? [];
        $currentTerm = $this['currenTerm'] ?? null;

        // Get current term name
        $currentTermName = $currentTerm && isset($termList[$currentTerm]) ? $termList[$currentTerm]->name : null;
        // Get module data for current term 
                // "tutor_photo": "",
                // "personal_tutor_photo": "/storage/employees/261/1707242641_Muktiben_Soni2_thumb.jpg",
                // "classType": "Seminar",
                // "module": "GROUP TUTORIAL (RQF)",
                // "group": "MAY23-HM-X",
        //Please fix the data structure to return only modules for current term

        $currentTermModules = $currentTerm && isset($data[$currentTerm]) ? $data[$currentTerm] : [];
        foreach ($currentTermModules as $index => $module) {
            $currentTermModules[$index] = [
                'id' => $module->id,
                'parent_id' => $module->parent_id,
                //'course' => $module->course,
                'tutor_photo' => $module->tutor_photo,
                'personal_tutor_photo' => $module->personal_tutor_photo,
                'classType' => $module->classType,
                'module' => $module->module,
                'group' => $module->group,
                'venue' => $module->venue->name,
                'room' => $module->room->name,
                'virtual_room' => $module->virtual_room,
                //'plan_dates' => $module->plan_dates,
                //'start_time' => $module->start_time,
                //'end_time' => $module->end_time,
                'has_tutorial' => $module->has_tutorial,
                'p_tutor_photo' => $module->p_tutor_photo,
            ];
        }
        return [
            'currentTermName' => $currentTermName,
            'current_modules' => $currentTermModules,
            'termList' => array_values($termList), // optional: flatten for frontend
        ];
                    
    }
}
