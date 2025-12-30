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
            'name' => $student->title->name . ' ' . $student->first_name . ' ' . $student->last_name,
            'status' => $student->status->name,
            'photo_url' => $student->photo_url,
            'registration_no' => $student->registration_no,
            'course' => $student->crel->creation->course->name ?? '',
            'intake_semester' => $student->crel->propose->semester->name ?? '',
            'college_email' => $student->users->email ?? '',
            'personal_email' => $student->contact->personal_email ?? '',
            'mobile' => $student->contact->mobile ?? '',
            'phone' => $student->contact->phone ?? '',
            'term_time_address_line_1' => $student->contact->termaddress->address_line_1 ?? '',
            'term_time_address_line_2' => $student->contact->termaddress->address_line_2 ?? '',
            'term_time_city' => $student->contact->termaddress->city ?? '',
            'term_time_state' => $student->contact->termaddress->state ?? '',
            'term_time_postcode' => $student->contact->termaddress->post_code ?? '',
            'term_time_country' => $student->contact->termaddress->country ?? '',
            'term_time_accommodation_type' => $student->contact->ttacom->name ?? '',
            'permanent_address_line_1' => $student->contact->permaddress->address_line_1 ?? '',
            'permanent_address_line_2' => $student->contact->permaddress->address_line_2 ?? '',
            'permanent_city' => $student->contact->permaddress->city ?? '',
            'permanent_state' => $student->contact->permaddress->state ?? '',
            'permanent_postcode' => $student->contact->permaddress->post_code ?? '',
            'permanent_country' => $student->contact->permaddress->country ?? '',
        ];
    }
}
