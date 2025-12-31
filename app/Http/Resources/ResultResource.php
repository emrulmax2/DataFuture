<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ResultResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        // Support multidimensional array and include attemptData if present
        // Handle both single and multidimensional result arrays
        $result = $this->resource;
        // If it's a multidimensional array (collection of arrays)
        if (is_array($result) && isset($result[0]) && is_array($result[0])) {
            $output = [];
            foreach ($result as $item) {
                $output[] = [
                    'sn' => $item['sn'] ?? null,
                    //'id' => $item['id'] ?? null,
                    'term' => $item['term'] ?? null,
                    'module' => $item['module'] ?? null,
                    'code' => $item['code'] ?? null,
                    'body' => $item['body'] ?? null,
                    //'date' => $item['date'] ?? null,
                    //'grade' => $item['grade'] ?? null,
                    'merit' => $item['merit'] ?? null,
                    //'attempted' => $item['attempted'] ?? null,
                    //'updated_by' => $item['updated_by'] ?? null,
                    //'attemptData' => $item['attemptData'] ?? null,
                ];
            }
            return $output;
        }
        // Otherwise, treat as a single result array
        return [
            'sn' => $result['sn'] ?? null,
            //'id' => $result['id'] ?? null,
            'term' => $result['term'] ?? null,
            'module' => $result['module'] ?? null,
            'code' => $result['code'] ?? null,
            'body' => $result['body'] ?? null,
            //'date' => $result['date'] ?? null,
            //'grade' => $result['grade'] ?? null,
            'merit' => $result['merit'] ?? null,
            //'attempted' => $result['attempted'] ?? null,
            //'updated_by' => $result['updated_by'] ?? null,
            //'attemptData' => $result['attemptData'] ?? null,
        ];
    }
}
