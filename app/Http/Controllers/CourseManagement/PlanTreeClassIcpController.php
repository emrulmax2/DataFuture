<?php

namespace App\Http\Controllers\CourseManagement;

use App\Http\Controllers\Controller;
use App\Models\Group;
use App\Models\Student;
use App\Models\TermDeclaration;
use App\Services\ClassRoutine\ClassRoutineBuilder;
use App\Services\ClassRoutine\ClassRoutineGroupPdf;
use App\Services\ClassRoutine\ClassRoutineSender;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Course Management › Plan tree › "Send Class ICP" (user, 30 Oct 2026): the class routine PDF
 * emailed to every student of the group, with no letter and no copy in their Uploads.
 *
 *   prepare  who is in the group and which PDF each gets (one per routine, so classmates share
 *            one; a student whose plans differ gets their own), with a preview of each;
 *   send     one student at a time, so the dialog can show each result as it goes.
 *
 * "The group" is the plan tree's: every same-named group of the course in the term, as the
 * panel's table lists its plans; its students are the ones assigned to those plans. Routines
 * are built from the tree's term, not each student's newest.
 */
class PlanTreeClassIcpController extends Controller
{
    private const COORDS = [
        'yearid' => 'required|integer',
        'termid' => 'required|integer',
        'courseid' => 'required|integer',
        'groupid' => 'required|integer',
    ];

    public function prepare(Request $request, ClassRoutineGroupPdf $pdfs)
    {
        $request->validate(self::COORDS);
        $termId = (int) $request->termid;
        $group = Group::find((int) $request->groupid);
        $term = TermDeclaration::find($termId);
        $ids = $this->studentIds($request);

        $title = trim(($group->name ?? 'Group').' · '.($term->name ?? ''), ' ·');
        if (!$ids) {
            return response()->json(['message' => 'No students are assigned to this group\'s class plans yet, so there is nobody to send the class routine to.', 'title' => $title], 422);
        }

        $sorted = $pdfs->groups($ids, $termId);
        // the biggest set first: it is the group's own routine; any other is a student whose plans differ
        usort($sorted['groups'], fn ($a, $b) => count($b['students']) <=> count($a['students']));
        foreach ($sorted['groups'] as $i => &$set) {
            $set['count'] = count($set['students']);
            $set['variant'] = $i > 0;
            $set['preview_url'] = route('plans.tree.class.icp.preview', ['student_id' => $set['sample_student_id'], 'termid' => $termId]);
            usort($set['students'], fn ($a, $b) => strcmp((string) $a['registration_no'], (string) $b['registration_no']));
        }
        unset($set);

        $redirect = config('mail.redirect_to') && !app()->environment('production') ? (string) config('mail.redirect_to') : null;

        return response()->json($sorted + ['total' => count($ids), 'title' => $title, 'redirect_to' => $redirect]);
    }

    /** One of the prepared PDFs, opened in the browser to check before sending. */
    public function preview(Request $request, ClassRoutineBuilder $builder, ClassRoutineGroupPdf $pdfs)
    {
        $request->validate(['student_id' => 'required|integer', 'termid' => 'required|integer']);
        $routine = $builder->forStudent((int) $request->student_id, (int) $request->termid);
        abort_unless($routine, 404, 'No class routine for this student in this term.');

        return response($pdfs->bytes($routine), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="'.ClassRoutineGroupPdf::fileName($routine).'"',
        ]);
    }

    /** Email one student of the group their routine. */
    public function send(Request $request, ClassRoutineSender $sender)
    {
        $request->validate(self::COORDS + ['student_id' => 'required|integer']);
        $studentId = (int) $request->student_id;
        if (!in_array($studentId, $this->studentIds($request), true)) {
            return response()->json(['message' => 'This student is no longer assigned to this group\'s class plans.'], 422);
        }
        $student = Student::find($studentId);
        if (!$student) {
            return response()->json(['message' => 'Student not found.'], 422);
        }

        $result = $sender->emailOnly($student, (int) auth()->id(), (int) $request->termid);

        return response()->json($result, $result['status'] === ClassRoutineSender::DONE ? 200 : 422);
    }

    /**
     * The students assigned to the group's plans, as the tree panel finds them (PlanTreeController::getModule):
     * every same-named group of the course in the term.
     *
     * @return int[]
     */
    private function studentIds(Request $request): array
    {
        $group = Group::find((int) $request->groupid);
        if (!$group) {
            return [];
        }
        $groupIds = Group::where('term_declaration_id', (int) $request->termid)->where('course_id', (int) $request->courseid)
            ->where('name', $group->name)->pluck('id');

        return DB::table('assigns as a')
            ->join('plans as p', 'p.id', '=', 'a.plan_id')
            ->join('students as s', 's.id', '=', 'a.student_id')
            ->where('p.course_id', (int) $request->courseid)
            ->where('p.term_declaration_id', (int) $request->termid)
            ->where('p.academic_year_id', (int) $request->yearid)
            ->whereIn('p.group_id', $groupIds)
            ->whereNull('a.deleted_at')->whereNull('p.deleted_at')->whereNull('s.deleted_at')
            ->distinct()->orderBy('a.student_id')
            ->pluck('a.student_id')->map(fn ($id) => (int) $id)->all();
    }
}
