<?php

namespace App\Http\Controllers\Reports\IntakePerformance\Concerns;

use App\Models\Semester;
use App\Support\ReportPdfLogo;
use Illuminate\Http\Request;

/**
 * Shared by the new intake reports (Continuation, Retention, Attendance,
 * Submission & Pass, Award): one or more intake semesters, from the form
 * (semester_ids[]) or the download URL ("130_132"), and the landscape PDF.
 */
trait ReadsIntakes
{
    /** The intake semester ids the form posted. A single semester_id is still accepted. */
    protected function postedIntakes(Request $request): array
    {
        if (!$request->has('semester_ids') && $request->filled('semester_id')) {
            $request->merge(['semester_ids' => [$request->input('semester_id')]]);
        }
        $request->validate([
            'semester_ids' => 'required|array|min:1',
            'semester_ids.*' => 'required|integer|exists:semesters,id',
        ], [
            'semester_ids.required' => 'Please select at least one intake semester.',
            'semester_ids.array' => 'Please select at least one intake semester.',
            'semester_ids.min' => 'Please select at least one intake semester.',
            'semester_ids.*.required' => 'Please select intake semesters from the list.',
            'semester_ids.*.integer' => 'Please select intake semesters from the list.',
            'semester_ids.*.exists' => 'Please select intake semesters from the list.',
        ]);

        return array_values(array_unique(array_map('intval', (array) $request->input('semester_ids'))));
    }

    /** Ids from a download URL segment such as "130_132"; an unknown id is a 404. */
    protected function pathIntakes(string $semesters): array
    {
        $ids = array_values(array_unique(array_map('intval', explode('_', $semesters))));
        abort_if(empty($ids) || Semester::whereIn('id', $ids)->count() !== count($ids), 404);

        return $ids;
    }

    protected function author(): string
    {
        $user = auth()->user();

        return (isset($user->employee->full_name) && !empty($user->employee->full_name) ? $user->employee->full_name : (string) optional($user)->name);
    }

    /**
     * A4 landscape download. Rendered twice: the first pass counts the pages,
     * so a report that runs past one page can say "Page 1 of 2".
     */
    protected function landscapePdf(string $view, array $data, string $fileName)
    {
        $data += [
            'author' => $this->author(),
            'created' => now()->format('j M Y, H:i'),
            'logo' => ReportPdfLogo::dataUri(),
        ];
        $make = function (int $pages) use ($view, $data) {
            return app('dompdf.wrapper')
                ->loadView($view, $data + ['pages' => $pages])
                ->setPaper('a4', 'landscape')
                ->setWarnings(false);
        };
        $first = $make(0);
        $first->render();

        return $make((int) $first->getDomPDF()->getCanvas()->get_page_count())->download($fileName);
    }
}
