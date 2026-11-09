<?php

namespace App\Http\Controllers\AttendanceIntegrity;

use App\Services\AttendanceIntegrity\Filters;
use App\Services\AttendanceIntegrity\IntegrityAccess;
use App\Services\AttendanceIntegrity\Lookup;
use App\Services\AttendanceIntegrity\Metrics;
use App\Services\AttendanceIntegrity\Scoring;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/** Attendance Integrity > Staff Analysis and Student Analysis. */
class AnalysisController extends IntegrityBaseController
{
    public function staff(Request $request)
    {
        IntegrityAccess::authorize('view_staff');
        $f = Filters::fromRequest($request);
        IntegrityAccess::log('view', 'staff_list', null, $f->query());

        return $this->shell($request, 'pages.attendance-integrity.staff', 'Staff Analysis', ['filters' => $f]);
    }

    public function staffData(Request $request)
    {
        IntegrityAccess::authorize('view_staff');

        return response()->json(['data' => Metrics::staffRows(Filters::fromRequest($request))]);
    }

    public function staffShow(Request $request, int $user)
    {
        IntegrityAccess::authorize('view_staff');
        abort_unless(DB::table('users')->where('id', $user)->exists(), 404);
        $f = Filters::fromRequest($request);
        IntegrityAccess::log('view', 'staff', $user, $f->query());

        // Compared with everyone under the filters (peers), then this person's row.
        $all = clone $f;
        $all->staff = null;
        $row = collect(Metrics::staffRows($all))->firstWhere('user_id', $user);
        $person = Lookup::staff([$user])[$user] ?? ['name' => 'User #'.$user, 'job_title' => '', 'department' => '', 'employee_id' => null];

        return $this->shell($request, 'pages.attendance-integrity.staff-show', $person['name'], [
            'filters' => $f,
            'subjectId' => $user,
            'person' => $person,
            'row' => $row,
            'students' => $this->staffStudents(clone $f, $user),
        ]);
    }

    /** Students whose attendance this person entered or changed, most findings first. */
    private function staffStudents(Filters $f, int $user): array
    {
        return Metrics::uk(function () use ($f, $user) {
            $staff = $f->staff;
            $f->staff = null;
            [$w, $b] = $f->attendanceWhere();
            $f->staff = $staff;
            $rows = DB::select('SELECT a.student_id, SUM(a.created_by = ?) AS entered, SUM(a.updated_by = ?) AS changed, COUNT(DISTINCT p.id) AS classes
                '.Metrics::FROM.' WHERE a.deleted_at IS NULL AND (a.created_by = ? OR a.updated_by = ?) '.$w.'
                GROUP BY a.student_id', array_merge([$user, $user, $user, $user], $b));
            [$fj, $fw, $fb] = $f->findingWhere(false);
            $flags = [];
            foreach (DB::select("SELECT f.student_id, COUNT(*) AS n, SUM(f.weight) AS pts,
                    COUNT(DISTINCT CASE WHEN f.type IN ('absent_to_present', 'absent_to_present_notice', 'absent_to_present_own_class') THEN f.attendance_id END) AS positive
                FROM aim_findings f $fj WHERE f.student_id IS NOT NULL AND f.status <> 'dismissed' $fw GROUP BY f.student_id", $fb) as $x) {
                $flags[(int) $x->student_id] = $x;
            }
            $students = Lookup::students(array_map(fn ($r) => $r->student_id, $rows));
            $out = array_map(fn ($r) => [
                'student_id' => (int) $r->student_id,
                'registration_no' => $students[(int) $r->student_id]['registration_no'] ?? '',
                'name' => $students[(int) $r->student_id]['name'] ?? '',
                'entered' => (int) $r->entered,
                'changed' => (int) $r->changed,
                'classes' => (int) $r->classes,
                'positive' => (int) ($flags[(int) $r->student_id]->positive ?? 0),
                'findings' => (int) ($flags[(int) $r->student_id]->n ?? 0),
                'points' => (int) ($flags[(int) $r->student_id]->pts ?? 0),
            ], $rows);
            usort($out, fn ($x, $y) => [$y['points'], $y['changed']] <=> [$x['points'], $x['changed']]);

            return $out;
        });
    }

    public function students(Request $request)
    {
        IntegrityAccess::authorize('view_students');
        $f = Filters::fromRequest($request);
        IntegrityAccess::log('view', 'student_list', null, $f->query());

        return $this->shell($request, 'pages.attendance-integrity.students', 'Student Analysis', ['filters' => $f]);
    }

    public function studentsData(Request $request)
    {
        IntegrityAccess::authorize('view_students');

        return response()->json(['data' => Metrics::studentRows(Filters::fromRequest($request))]);
    }

    public function studentShow(Request $request, int $student)
    {
        IntegrityAccess::authorize('view_students');
        abort_unless(DB::table('students')->where('id', $student)->exists(), 404);
        $f = Filters::fromRequest($request);
        IntegrityAccess::log('view', 'student', $student, $f->query());

        // Queries run on a copy so the filter bar keeps showing only what was chosen.
        $one = clone $f;
        $one->student = $student;
        $row = collect(Metrics::studentRows($one))->firstWhere('student_id', $student);
        $timeline = Metrics::studentTimeline(clone $one, $student);
        $score = Scoring::subjects('student', $one)[$student] ?? null;

        return $this->shell($request, 'pages.attendance-integrity.student-show', Lookup::studentLabel($student), [
            'filters' => $f,
            'subjectId' => $student,
            'student' => Lookup::students([$student])[$student] ?? ['registration_no' => '', 'name' => ''],
            'row' => $row,
            'score' => $score,
            'timeline' => $timeline,
        ]);
    }
}
