<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Student;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Student lookup, for the Operations Service Desk.
 *
 * Staff there tag a student on a ticket to record who it concerns — a support
 * referral, an attendance intervention, a document request. Students have no
 * account in Operations, so it asks here as the name is typed rather than
 * holding a copy of 10,000 enrolments it would then have to keep current.
 *
 * Read-only and deliberately narrow. It returns what is needed to tell two
 * students apart and nothing more: the id to join on, the registration number
 * staff actually quote, the name, the course and where the enrolment stands.
 * No dates of birth, no addresses, no contact details — a search box on
 * another system is not the place to hand those out.
 */
class StudentSearchController extends Controller
{
    /* A number in a search box is a registration or application number far more
       often than it is part of a name, so digits are matched against those
       first — otherwise "2026" returns most of the college. */
    private const MAX_LIMIT = 50;

    public function search(Request $request): JsonResponse
    {
        $data = $request->validate([
            'q'     => ['required', 'string', 'min:2', 'max:64'],
            'limit' => ['nullable', 'integer', 'min:1', 'max:'.self::MAX_LIMIT],
        ]);

        $term = trim($data['q']);
        $like = '%'.str_replace(['%', '_'], ['\%', '\_'], $term).'%';

        $students = Student::query()
            ->with([
                'status:id,name',
                'activeCR:id,student_id,course_creation_id',
                /* Qualified: course() reaches through course_creations, and an
                   unqualified `id` is ambiguous across that join. */
                'activeCR.course:courses.id,courses.name',
            ])
            ->where(function ($query) use ($like) {
                $query->where('registration_no', 'LIKE', $like)
                    ->orWhere('application_no', 'LIKE', $like)
                    ->orWhere('df_sid_number', 'LIKE', $like)
                    ->orWhere('first_name', 'LIKE', $like)
                    ->orWhere('last_name', 'LIKE', $like)
                    /* Typed in full — "Lidia Rossi" matches neither column on
                       its own. */
                    ->orWhereRaw("CONCAT(COALESCE(first_name, ''), ' ', COALESCE(last_name, '')) LIKE ?", [$like]);
            })
            /* Most recent enrolments first: a name being searched for today is
               far more likely to be a current student than one from 2019. */
            ->orderByDesc('id')
            ->limit((int) ($data['limit'] ?? 20))
            ->get();

        return response()->json([
            'data' => $students->map(fn (Student $student) => [
                'id'     => $student->id,
                'ref'    => $student->registration_no ?: $student->application_no,
                'name'   => trim($student->first_name.' '.$student->last_name),
                'course' => $student->activeCR?->course?->name,
                'status' => $student->status?->name,
            ])->values(),
        ]);
    }
}
