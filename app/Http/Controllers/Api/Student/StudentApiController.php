<?php

namespace App\Http\Controllers\Api\Student;

use App\Http\Controllers\Controller;
use App\Models\Student;
use Illuminate\Http\Request;

/**
 * Shared by the student app's Attendance excuse and Document requests
 * endpoints: who the request is for, and the shape of every answer.
 *
 * Answers are built here rather than thrown. On api/v1 the exception handler
 * turns anything thrown into {success, error} - and a failed validation into a
 * 500 - so these controllers validate by hand and return their own 4xx.
 */
abstract class StudentApiController extends Controller
{
    /**
     * The student the signed-in user is acting as: their newest record, or the
     * one named by ?selected_student_id when they have several - the same rule
     * as the rest of the student API. Never somebody else's record.
     */
    protected function student(Request $request)
    {
        $query = Student::where('student_user_id', $request->user()->id);
        $selectedStudentId = (int) $request->query('selected_student_id', 0);

        return ($selectedStudentId > 0 ? $query->where('id', $selectedStudentId)->first() : $query->orderBy('id', 'DESC')->first());
    }

    protected function ok($data, $status = 200, $meta = null)
    {
        $body = ['success' => true, 'data' => $data];
        if($meta !== null):
            $body['meta'] = $meta;
        endif;

        return response()->json($body, $status);
    }

    /**
     * "message" and "errors" are what the app's client reads; "error" repeats
     * the message under the key the older student endpoints use.
     */
    protected function fail($message, $status, array $errors = [])
    {
        $body = ['success' => false, 'message' => $message, 'error' => $message];
        if(!empty($errors)):
            $body['errors'] = $errors;
        endif;

        return response()->json($body, $status);
    }

    protected function noStudent()
    {
        return $this->fail('No student record found for this user.', 404);
    }

    /** A 422 from a failed validator, or from field errors worked out by hand. */
    protected function invalid($errors)
    {
        $errors = (is_array($errors) ? $errors : $errors->errors()->toArray());
        $first = reset($errors);

        return $this->fail((is_array($first) ? reset($first) : (string) $first), 422, $errors);
    }

    /** One page of a query, with the meta block every list carries. */
    protected function page($query, Request $request): array
    {
        $perPage = min(50, max(1, (int) $request->query('per_page', 15)));
        $page = max(1, (int) $request->query('page', 1));
        $rows = $query->paginate($perPage, ['*'], 'page', $page);

        return [$rows->getCollection(), [
            'current_page' => $rows->currentPage(),
            'per_page' => $rows->perPage(),
            'total' => $rows->total(),
            'last_page' => $rows->lastPage(),
        ]];
    }
}
