<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ClassRoutineItemResource extends JsonResource
{
    public function toArray($request)
    {
        $row = (object) $this->resource;

        $startTime = !empty($row->start_time)
            ? date('h:i A', strtotime('1970-01-01 ' . $row->start_time))
            : null;

        $endTime = !empty($row->end_time)
            ? date('h:i A', strtotime('1970-01-01 ' . $row->end_time))
            : null;

        $venue = trim(
            ($row->venue_name ?? '') . (!empty($row->room_name) ? ', ' . $row->room_name : ''),
            ', '
        );

        return [
            'term_name'            => $row->term_name,
            'plan_date_list_id'   => $row->plan_date_list_id,
            'plan_id'             => $row->plan_id,
            'plan_date'           => $row->plan_date,
            'hr_date'             => date('F jS, Y', strtotime($row->plan_date)),
            'course'              => $row->course_name,
            'module'              => $row->module_name,
            'classType'           => !empty($row->class_type) ? $row->class_type : $row->module_class_type,
            'group'               => $row->group_name,
            'tutor'               => $row->tutor_name,
            'tutor_profile_photo' => !empty($row->tutor_photo)
                ? asset('storage/employees/' . $row->tutor_id . '/' . $row->tutor_photo)
                : null,
            'start_time'          => $startTime,
            'end_time'            => $endTime,
            'hr_time'             => trim(($startTime ?? '') . (!empty($endTime) ? ' - ' . $endTime : '')),
            'venue'               => $row->venue_name,
            'room'                => $row->room_name,
            'venue_room'          => $venue,
            'virtual_room'        => $row->virtual_room,
        ];
    }
}
