<?php

namespace App\Http\Resources;

use App\Models\AttendanceCriteria;
use App\Models\TermPerformanceCriteria;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StudentAPIPerformanceResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $payload = is_array($this->resource) ? $this->resource : [];

        $termSet = $payload['termSet'] ?? [];
        $results = $payload['results'] ?? [];
        $perTermTopSet = $payload['perTermTopSet'] ?? [];
        $topAcademicCriteria = (float) ($payload['TopAcademicCriteria'] ?? 0);
        $termAttendanceCount = $payload['termAttendanceCount'] ?? [];
        $topAttendanceCriteria = (float) ($payload['TopAttendanceCriteria'] ?? 0);
        $perTermModuleCriteria = $payload['perTermModuleCriteria'] ?? [];

        $attendanceCriteriaList = AttendanceCriteria::orderBy('range_from')->get();
        $termPerformanceCriteriaList = TermPerformanceCriteria::orderBy('range_from')->get();

        $terms = [];
        foreach ($termSet as $term) {
            $termId = $term->id;
            $averageAttendance = isset($termAttendanceCount[$termId]['avg']) ? (float) $termAttendanceCount[$termId]['avg'] : 0.0;
                        
            $attendanceCriteriaFound = $attendanceCriteriaList
                ->first(function ($criteria) use ($averageAttendance) {
                    return $criteria->range_from <= $averageAttendance && $criteria->range_to >= $averageAttendance;
                });

            $attendanceCriteriaPoint = (float) ($attendanceCriteriaFound->point ?? 0);

            $achievedResult = isset($perTermModuleCriteria[$termId]) ? (float) $perTermModuleCriteria[$termId] : 0.0;
            $expectedResult = isset($perTermTopSet[$termId]) ? (float) $perTermTopSet[$termId] : 0.0;

            $achievedPerformance = $attendanceCriteriaPoint + $achievedResult;
            $expectedPerformance = $topAttendanceCriteria + $expectedResult;

            $avgPerformance = $expectedPerformance > 0
                ? (float) number_format(($achievedPerformance / $expectedPerformance) * 100, 2, '.', '')
                : 0.0;

            $performanceOutput = $termPerformanceCriteriaList
                ->first(function ($criteria) use ($avgPerformance) {
                    return $criteria->range_from <= $avgPerformance && $criteria->range_to >= $avgPerformance;
                });

            $attendancePercentOfExpected = $topAttendanceCriteria > 0
                ? (float) number_format(($attendanceCriteriaPoint / $topAttendanceCriteria) * 100, 2, '.', '')
                : 0.0;

            $resultPercentOfExpected = $expectedResult > 0
                ? (float) number_format(($achievedResult / $expectedResult) * 100, 2, '.', '')
                : 0.0;

            $termModules = [];
            if (isset($results[$termId]) && is_array($results[$termId])) {
                foreach ($results[$termId] as $moduleName => $resultRow) {
                    $termModules[] = [
                        'module' => $resultRow['module'] ?? $moduleName,
                        'grade' => $resultRow['grade'] ?? '',
                        'academic_criteria' => (float) ($resultRow['academic_criteria'] ?? 0),
                    ];
                }
            }

            $terms[] = [
                'id' => $termId,
                'name' => $term->name,
                'attendance' => [
                    'average_percent' => (float) round($averageAttendance),
                    'achieved_point' => $attendanceCriteriaPoint,
                    'expected_point' => $topAttendanceCriteria,
                    //'achieved_percent_of_expected' => $attendancePercentOfExpected,
                ],
                'academic' => [
                    'achieved_result' => $achievedResult,
                    'expected_result' => $expectedResult,
                    //'achieved_percent_of_expected' => $resultPercentOfExpected,
                ],
                'performance' => [
                    'achieved' => $achievedPerformance,
                    'expected' => $expectedPerformance,
                    'average_percent' => $avgPerformance,
                    'label' => $performanceOutput->label ?? 'N/A',
                    'color' => $performanceOutput->color ?? 'secondary',
                ],
                'modules' => $termModules,
            ];
        }

        return [
            'terms' => $terms,
            'meta' => [
                'top_academic_criteria' => $topAcademicCriteria,
                'top_attendance_criteria' => $topAttendanceCriteria,
            ],
        ];
    }
}
