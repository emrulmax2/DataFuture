<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class StudentUserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'email_verified_at' => $this->email_verified_at,
            'gender' => $this->gender,
            'active' => $this->active,
            "first_login" => $this->first_login,
            'last_login_ip' => $this->last_login_ip,
            'last_login_at' => $this->last_login_at,
            'photo_url' => $this->photo_url,
            // Add more fields as needed
        ];
    }
}
