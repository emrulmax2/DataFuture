<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ClassRoutineResource extends JsonResource
{
    public function toArray($request)
    {
        $data    = $this->resource;
        $rows    = $data['rows'] ?? [];

        $classes = collect($rows)
            ->map(fn ($row) => (new ClassRoutineItemResource($row))->toArray($request))
            ->values();

        return [
            'student_id'    => $data['student_id'],
            'from_date'     => $data['from_date'],
            'total_classes' => $classes->count(),
            'term_name'     => $classes->firstWhere('plan_date', $data['from_date'])['term_name'] ?? null,
            'classes'       => $classes,

            //'date_wise'     => $classes->groupBy('plan_date'),
        ];
    }
}
