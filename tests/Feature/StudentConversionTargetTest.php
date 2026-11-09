<?php

namespace Tests\Feature;

use App\Jobs\ProcessStudentContact;
use App\Jobs\ProcessStudentKinDetail;
use App\Jobs\ProcessStudentOtherDetails;
use App\Jobs\ProcessStudents;
use App\Models\Applicant;
use App\Models\Student;
use App\Models\StudentUser;
use App\Services\Admission\StudentConversion;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * Every conversion step fills the student this conversion created (the newest one
 * carrying the applicant's id on its login), not the login's first student: a second
 * conversion of the same applicant used to fill the first record and leave the new
 * one empty, so its profile page failed on a missing contact.
 */
class StudentConversionTargetTest extends TestCase
{
    use DatabaseTransactions;

    private function applicant(): Applicant
    {
        $applicant = Applicant::with('users')->whereHas('users')->whereHas('course')->whereHas('contact')->whereHas('kin')->orderByDesc('id')->get()
            ->first(fn ($a) => StudentUser::where('email', $a->users->email)->exists());
        $this->assertNotNull($applicant, 'needs an applicant with a student login');
        Storage::fake('local');
        Storage::fake('s3');

        return $applicant;
    }

    public function test_a_second_conversion_fills_its_own_new_student(): void
    {
        $applicant = $this->applicant();
        $login = StudentUser::where('email', $applicant->users->email)->first();

        (new ProcessStudents($applicant))->handle();
        $first = Student::where('applicant_id', $applicant->id)->orderByDesc('id')->firstOrFail();
        (new ProcessStudentContact($applicant))->handle();

        // converted again: a new student record, and the copy steps must fill that one
        (new ProcessStudents($applicant))->handle();
        $second = Student::where('applicant_id', $applicant->id)->orderByDesc('id')->firstOrFail();
        $this->assertNotSame($first->id, $second->id);
        $this->assertSame($second->id, StudentConversion::studentFor($applicant, $login)->id);

        $before = DB::table('student_contacts')->where('student_id', $first->id)->count();
        (new ProcessStudentContact($applicant))->handle();
        (new ProcessStudentKinDetail($applicant))->handle();
        (new ProcessStudentOtherDetails($applicant))->handle();

        $this->assertSame(1, DB::table('student_contacts')->where('student_id', $second->id)->count());
        $this->assertSame(1, DB::table('student_kins')->where('student_id', $second->id)->count());
        $this->assertSame(1, DB::table('student_other_details')->where('student_id', $second->id)->count());
        $this->assertSame($before, DB::table('student_contacts')->where('student_id', $first->id)->count(), 'the earlier student is left alone');
    }

    public function test_without_a_student_for_this_applicant_it_falls_back_to_the_login_first_student(): void
    {
        $applicant = $this->applicant();
        $login = StudentUser::where('email', $applicant->users->email)->first();
        $firstOnLogin = Student::where('student_user_id', $login->id)->first();
        $mine = Student::where('student_user_id', $login->id)->where('applicant_id', $applicant->id)->exists();

        $found = StudentConversion::studentFor($applicant, $login);
        if ($mine) {
            $this->assertSame((int) $applicant->id, (int) $found->applicant_id);
        } else {
            $this->assertSame(optional($firstOnLogin)->id, optional($found)->id);
        }
        $this->assertNull(StudentConversion::studentFor($applicant, null));
    }
}
