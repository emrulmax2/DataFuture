<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Http\Requests\OtherPersonalInformationRequest;
use Illuminate\Http\Request;

class OtherPersonalInformationController extends Controller
{
    public function update(OtherPersonalInformationRequest $request){
        $student_id = $request->student_id;
    }
}
