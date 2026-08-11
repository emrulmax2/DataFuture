<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PlanDetailsResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        // Expect resource to be: ["module_details" => [...], "microsoft_teams_url" => ..., "course_content" => [...], "class_dates" => [...]]
        return [
            'module_details' => $this['module_details'] ?? null,
            'microsoft_teams_url' => $this['microsoft_teams_url'] ?? null,
            'course_content' => $this['course_content'] ?? [],
            'class_dates' => $this['class_dates'] ?? [],
        ];
    }
}
