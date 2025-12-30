<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class StudentResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $student = $this;
        return [
            'id' => $student->id,
            'name' => optional($student->title)->name . ' ' . $student->first_name . ' ' . $student->last_name,
            'status' => optional($student->status)->name,
            'photo_url' => $student->photo_url,
            'registration_no' => $student->registration_no,
            'course' => optional(optional(optional($student->crel)->creation)->course)->name ?? '',
            'intake_semester' => optional(optional(optional($student->crel)->propose)->semester)->name ?? '',
            'college_email' => optional($student->users)->email ?? '',
            'personal_email' => optional(optional($student->contact)->personal_email),
            'mobile' => optional(optional($student->contact)->mobile),
            'phone' => optional(optional($student->contact)->phone),
            'term_time_address_line_1' => optional(optional(optional($student->contact)->termaddress)->address_line_1),
            'term_time_address_line_2' => optional(optional(optional($student->contact)->termaddress)->address_line_2),
            'term_time_city' => optional(optional(optional($student->contact)->termaddress)->city),
            'term_time_state' => optional(optional(optional($student->contact)->termaddress)->state),
            'term_time_postcode' => optional(optional(optional($student->contact)->termaddress)->post_code),
            'term_time_country' => optional(optional(optional($student->contact)->termaddress)->country),
            'term_time_accommodation_type' => optional(optional(optional($student->contact)->ttacom)->name),
            'permanent_address_line_1' => optional(optional(optional($student->contact)->permaddress)->address_line_1),
            'permanent_address_line_2' => optional(optional(optional($student->contact)->permaddress)->address_line_2),
            'permanent_city' => optional(optional(optional($student->contact)->permaddress)->city),
            'permanent_state' => optional(optional(optional($student->contact)->permaddress)->state),
            'permanent_postcode' => optional(optional(optional($student->contact)->permaddress)->post_code),
            'permanent_country' => optional(optional(optional($student->contact)->permaddress)->country),
        ];
    }
}
