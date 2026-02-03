<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Http\Requests\StudentResidencyAndCriminalConvictionRequest;
use App\Models\StudentCriminalConviction;
use App\Models\StudentResidency;

class ResidencyCriminalController extends Controller
{
    public function update(StudentResidencyAndCriminalConvictionRequest $request)
    {
        $studentId = $request->student_id;

        $residency = StudentResidency::updateOrCreate(
            ['student_id' => $studentId],
            [
                'residency_status_id' => $request->residency_status_id,
                'created_by' => auth()->user()->id,
                'updated_by' => auth()->user()->id,
            ]
        );

        if ($residency) {
            StudentCriminalConviction::updateOrCreate(
                ['student_id' => $studentId],
                [
                    'have_you_been_convicted' => $request->have_you_been_convicted,
                    'criminal_conviction_details' =>
                        $request->have_you_been_convicted == 1
                            ? $request->criminal_conviction_details
                            : null,
                    'criminal_declaration' =>
                        $request->has('criminal_declaration') &&
                        $request->criminal_declaration > 0
                            ? 1
                            : 0,
                    'created_by' => auth()->user()->id,
                    'updated_by' => auth()->user()->id,
                ]
            );

            return response()->json(
                [
                    'msg' =>
                        'Residency and Criminal Conviction details successfully updated.',
                ],
                200
            );
        }

        return response()->json(
            ['msg' => 'Something went wrong. Please try later.'],
            422
        );
    }
}
