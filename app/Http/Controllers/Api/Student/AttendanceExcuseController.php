<?php

namespace App\Http\Controllers\Api\Student;

use App\Models\AttendanceExcuse;
use App\Services\StudentAttendanceExcuses;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AttendanceExcuseController extends StudentApiController
{
    public function __construct(private StudentAttendanceExcuses $excuses)
    {
    }

    /** The Missed and Upcoming tabs. */
    public function sessions(Request $request)
    {
        $student = $this->student($request);
        if(!$student):
            return $this->noStudent();
        endif;

        $excusable = $this->excuses->excusable($student->id);

        return $this->ok([
            'missed' => $excusable['missed']->map(function($dateList){
                return $this->excuses->session($dateList, 'absent');
            })->all(),
            'upcoming' => $excusable['upcoming']->map(function($dateList){
                return $this->excuses->session($dateList, 'upcoming');
            })->all(),
        ]);
    }

    /** The excuses the student has sent, newest first. */
    public function index(Request $request)
    {
        $student = $this->student($request);
        if(!$student):
            return $this->noStudent();
        endif;

        $validator = Validator::make($request->query(), [
            'status' => 'nullable|in:'.implode(',', StudentAttendanceExcuses::STATUSES),
            'page' => 'nullable|integer|min:1',
            'per_page' => 'nullable|integer|min:1|max:50',
        ]);
        if($validator->fails()):
            return $this->invalid($validator);
        endif;

        $query = AttendanceExcuse::with($this->excuses->withDetails())->where('student_id', $student->id)->orderBy('id', 'DESC');
        if(!empty($request->query('status'))):
            $query->where('status', array_search($request->query('status'), StudentAttendanceExcuses::STATUSES));
        endif;

        [$rows, $meta] = $this->page($query, $request);

        return $this->ok($rows->map(function($excuse){
            return $this->excuses->present($excuse);
        })->all(), 200, $meta);
    }

    /** The Submit excuse button. */
    public function store(Request $request)
    {
        $student = $this->student($request);
        if(!$student):
            return $this->noStudent();
        endif;

        $validator = Validator::make($request->all(), [
            'session_ids' => 'required|array|min:1',
            'session_ids.*' => 'required|integer|distinct',
            'reason' => 'required|string|max:500',
            'documents' => 'nullable|array',
            'documents.*' => 'file|mimes:docx,doc,pdf,jpg,jpeg,png|max:5120',
        ], [
            'session_ids.required' => 'Select at least one session to excuse.',
            'session_ids.array' => 'Select at least one session to excuse.',
            'session_ids.min' => 'Select at least one session to excuse.',
            'reason.required' => 'Tell us why you were absent.',
            'reason.max' => 'Keep your reason to 500 characters or fewer.',
            'documents.*.file' => 'The file could not be uploaded.',
            'documents.*.uploaded' => 'The file could not be uploaded. It may be larger than 5 MB.',
            'documents.*.mimes' => 'The file must be a docx, doc, pdf, jpg or png of 5 MB or less.',
            'documents.*.max' => 'The file must be a docx, doc, pdf, jpg or png of 5 MB or less.',
        ]);
        if($validator->fails()):
            return $this->invalid($validator);
        endif;

        /* An id is only good if it is one this student could tick right now:
           theirs, absent or still to come, and not already excused. */
        $excusable = $this->excuses->excusable($student->id);
        $open = $excusable['missed']->merge($excusable['upcoming'])->keyBy('id');

        $errors = [];
        $dateLists = collect();
        foreach(array_values($request->input('session_ids')) as $index => $sessionId):
            if($open->has((int) $sessionId)):
                $dateLists->push($open->get((int) $sessionId));
            else:
                $errors['session_ids.'.$index] = ['This session can no longer be excused.'];
            endif;
        endforeach;
        if(!empty($errors)):
            return $this->invalid($errors);
        endif;

        $files = $request->file('documents', []);
        $excuse = $this->excuses->submit($student, $request->user()->id, $dateLists, $request->input('reason'), (is_array($files) ? $files : [$files]));
        if(!$excuse):
            return $this->fail('Your excuse could not be saved. Please try again.', 500);
        endif;

        return $this->ok($this->excuses->present($excuse), 201);
    }

    public function show(Request $request, $excuseId)
    {
        $student = $this->student($request);
        if(!$student):
            return $this->noStudent();
        endif;

        $excuse = AttendanceExcuse::where('student_id', $student->id)->where('id', (int) $excuseId)->first();
        if(!$excuse):
            return $this->fail('Attendance excuse not found.', 404);
        endif;

        return $this->ok($this->excuses->present($excuse));
    }
}
