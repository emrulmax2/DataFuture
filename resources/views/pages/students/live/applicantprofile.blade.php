
<!DOCTYPE html>
<html lang="en" class="">
    <!-- BEGIN: Head -->
    <head>
        <meta charset="utf-8">
        <!-- BEGIN: CSS Assets-->
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">
        <!-- END: CSS Assets-->
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/js/bootstrap.min.js" integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl" crossorigin="anonymous"></script>
        <script src="https://code.jquery.com/jquery-3.2.1.slim.min.js" integrity="sha384-KJ3o2DKtIkvYIK3UENzmM7KCkRr/rE9/Qpg6aAZGJwFDMVNA/GpGFF93hXpG5KkN" crossorigin="anonymous"></script>
        <script src="https://cdn.jsdelivr.net/npm/popper.js@1.12.9/dist/umd/popper.min.js" integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q" crossorigin="anonymous"></script>
        <style>
            html { margin: 0px}
            @page { margin: 0px; }
            @font-face {
                font-family: 'Roboto-Regular';
                src: url({{ storage_path('fonts\Roboto-Regular.ttf') }}) format("truetype"); 
                font-style: normal;
            }
            
            @font-face {
                font-family: 'Roboto-Medium';
                src: url({{ storage_path('fonts\Roboto-Medium.ttf') }}) format("truetype"); 
                font-style: normal;
            }

            @font-face {
                font-family: 'Roboto-Bold';
                src: url({{ storage_path('fonts\Roboto-Bold.ttf') }}) format("truetype"); 
                font-style: normal;
            }

            body {
                font-size:10px;
            }
        </style>
    </head>
    <!-- END: Head -->

    <body style="margin:0; background-color: white; color:rgb(30, 41, 59); padding: 30px;">
        <div>
            <span style="font-size:24px; font-family:Roboto-Medium; margin-top:10px">Profile Review of</span> <span style="font-size:24px;font-family:Roboto-Bold;margin-top:10px"><u>{{ $applicant->title->name.' '.$applicant->first_name.' '.$applicant->last_name }}</u></span><span style="display:inline-block;margin-top:10px; float:right; font-family:Roboto-Bold"><button type="button" class="btn btn-sm btn-dark">{{ $applicant->status->name }}</button></span>
        </div> 
        <!-- BEGIN: Profile Info -->
        
        <div class="width: 100%;" style="margin-top:10px">
            <div style="float:left; width: 55%; height: 200px; background-color: rgb(241, 245, 249); border-radius:4px;">
                <div style="float:left;  margin:10px 0 10px 10px;">
                    <img style="width: 80px; height: 80px; border-radius: 50%;" src="{{ public_path('build/assets/images/' . $fakers[0]['photos'][0]) }}">
                </div>
                
                <div style="margin-left: 90px;">
                    <div style="font-size:16px;font-family:Roboto-Bold;margin-top:10px;margin-left:50px;margin-top:10px">{{ $applicant->title->name.' '.$applicant->first_name.' '.$applicant->last_name }}</div>   
                
                    <div style="font-size:16px;color:#696969;font-family:Roboto-Regular;margin-left:50px;margin-top:5px">{{ $applicant->course->creation->course->name.' - '.$applicant->course->semester->name }}</div>
                
                    <div style="font-size:16px;"><span style="color:#696969; font-family:Roboto-Medium;display:inline-block;margin-left:50px;margin-top:5px">Email:</span><span style="font-family:Roboto-Bold;display:inline-block;margin-top:5px">{{ $applicant->users->email }}</span></div>
                    <div style="font-size:16px;"><span style="color:#696969; font-family:Roboto-Medium;display:inline-block;margin-left:50px;margin-top:5px">Phone:</span><span style="font-family:Roboto-Bold;display:inline-block;margin-top:5px">{{ $applicant->contact->home }}</span></div>
                    <div style="font-size:16px;"><span style="color:#696969; font-family:Roboto-Medium;display:inline-block;margin-left:50px;margin-top:5px">Mobile:</span><span style="font-family:Roboto-Bold;display:inline-block;margin-top:5px">{{ $applicant->contact->mobile }}</span></div>
                </div>
                <div style="clear:both"></div>
            </div>
            {{-- Right Box --}}
            <div style="float:right; width: 40%; height: 200px; background-color: rgb(241, 245, 249); border-radius:4px;">
                <div style="font-size: 16px;font-family:Roboto-Bold;">
                    <div style="margin-left: 10px;margin-top:10px">Work in Progress</div>
                <div style="margin-left: 10px;margin-top:5px;font-family:Roboto-Bold"><span style="display:inline-block; margin-right: 90px;margin-top:20px;">Pending Task</span><span style="display:inline-block; margin-top:20px;">20%</span></div>
                <div style="margin-left: 10px;margin-top:5px;font-family:Roboto-Bold"><span style="display:inline-block; margin-right: 70px;margin-top:10px;">Completed Task</span><span style="display:inline-block; margin-top:10px;">2/20</span></div>
                </div>
            </div>
            <div style="clear:both"></div>
        </div>
        {{-- Information Details --}}
        <div class="width: 100%;" style="margin-top: 20px">
            <div style="float:left; width: 100%; height:50px; font-family: Roboto-Bold; background-color: #249D93; border-radius:4px;">
                <div style="float:left; margin-left: 10px; color:white;padding:5px;">
                    <span style="font-size:16px;">Information </span><span style="font-size:14px;">Details</span>
                </div>
            </div>
            <div style="clear:both"></div>
        </div>
        {{-- Personal Details --}}
        <div class="card" style="margin-top: 20px; width:100%;">
            <div class="card-body">
                <div class="card-title" style="font-size:14px;font-family: Roboto-Bold;">Personal Details</div>
                <div style="float:left; width: 15%; color:#696969;font-family: Roboto-Medium;">Name</div>
                <div style="float:left; width: 18%; margin-left: 5px;  margin-right: 5px;font-family: Roboto-Bold;">{{ $applicant->title->name.' '.$applicant->first_name.' '.$applicant->last_name }}</div>                      
                
                <div style="float:left; width: 15%; color:#696969;font-family: Roboto-Medium;">Date of Birth</div>
                <div style="float:left; width: 18%; margin-left: 5px;  margin-right: 5px;font-family: Roboto-Bold;">{{ $applicant->date_of_birth }}</div>                      

                <div style="float:left; width: 15%; color:#696969;font-family: Roboto-Medium;">Gender</div>
                <div style="float:left; width: 18%; margin-left: 5px; font-family: Roboto-Bold;">{{ $applicant->gender }}</div> 
                <div style="clear:both"></div>
                <div style="float:left; width: 15%; margin-top:10px; color:#696969;font-family: Roboto-Medium;">Nationality</div>
                <div style="float:left; width: 18%; margin-top:10px; margin-left: 5px;margin-right: 5px;font-family: Roboto-Bold;">{{ $applicant->nation->name }}</div>

                <div style="float:left; width: 15%; margin-top:10px; color:#696969;font-family: Roboto-Medium;">Country of Birth</div>
                <div style="float:left; width: 18%; margin-top:10px; margin-left: 5px; margin-right: 5px;font-family: Roboto-Bold;">{{ $applicant->country->name }}</div> 
                
                <div style="float:left; width: 15%; margin-top:10px; color:#696969;font-family: Roboto-Medium;">Ethnicity</div>
                <div style="float:left; width: 18%; margin-top:10px; margin-left: 5px; font-family: Roboto-Bold;">{{ $applicant->other->ethnicity->name }}</div>
                <div style="clear:both"></div>
                <div style="float:left; width: 15%; margin-top:10px; color:#696969;font-family: Roboto-Medium;">Disability Status</div>
                <div style="float:left; width: 18%; margin-top:14px; margin-left: 5px; margin-right: 5px;font-family: Roboto-Bold;">
                    {!! (isset($applicant->other->disability_status) && $applicant->other->disability_status == 1 ? '<h6><span class="badge badge-success">Yes</span></h6>' : '<h6><span class="badge badge-danger">No</span></h6>') !!}
                </div>

                @if(isset($applicant->other->disability_status) && $applicant->other->disability_status == 1)
                <div style="float:left; width: 15%; margin-top:10px; color:#696969;font-family: Roboto-Medium;">Allowance Claimed?</div>
                <div style="float:left; width: 18%; margin-left: 5px; margin-top:14px; font-family: Roboto-Bold;">
                    {!! (isset($applicant->other->disabilty_allowance) && $applicant->other->disabilty_allowance == 1 ? '<h6><span class="badge badge-success">Yes</span></h6>' : '<h6><span class="badge badge-danger">No</span></h6>') !!}
                </div> 
                @endif
                <div style="clear:both"></div>
                @if(isset($applicant->other->disability_status) && $applicant->other->disability_status == 1)    
                    <div style="float:left; width: 15%; margin-top:10px; color:#696969;font-family: Roboto-Medium;">Disabilities</div>
                    <div style="clear:both"></div>
                    <div style="float:left;">
                        @if(isset($applicant->disability) && !empty($applicant->disability))
                            @foreach($applicant->disability as $dis)
                                <div style="float:left; font-family: Roboto-Bold;">{{ $dis->disabilities->name  }}</div>
                            @endforeach
                        @endif
                    </div>  
                    <div style="clear:both"></div>   
                @endif
            </div>
        </div>
        <div class="card" style="margin-top: 20px; width:100%;">
            <div class="card-body">
                <div class="card-title" style="font-size:14px;font-family: Roboto-Bold;">Contact Details</div>
                <div style="float:left; width: 15%; color:#696969;font-family: Roboto-Medium;">Email</div>
                <div style="float:left; width: 18%; margin-left: 5px; font-family: Roboto-Bold;">{{ $applicant->users->email }}</div>
                <div style="float:left; width: 18%; margin-left: 5px; margin-right: 10px;font-family: Roboto-Bold; margin-top:4px">
                    @if ($applicant->users->email_verified_at == NULL)
                        <h6><span class="badge badge-danger">Unverified</span></h6>
                    @else
                        @if(isset($tempEmail->applicant_id) && $tempEmail->applicant_id > 0 && (isset($tempEmail->status) && $tempEmail->status == 'Pending'))
                            <h6><span class="badge badge-warning">Awaiting Verification</span></h6>
                        @else
                            <h6><span class="badge badge-success">Awaiting Verification</span></h6>   
                        @endif
                    @endif
                </div>                      
                
                <div style="float:left; width: 15%; color:#696969;font-family: Roboto-Medium;">Home Phone</div>
                <div style="float:left; width: 18%; margin-left: 5px;  margin-right: 5px;font-family: Roboto-Bold;">{{ $applicant->contact->home }}</div>                      
                <div style="clear:both"></div>
                <div style="float:left; width: 15%; margin-top:10px; color:#696969;font-family: Roboto-Medium;">Mobile</div>
                <div style="float:left; width: 18%; margin-top:10px; margin-left: 5px; font-family: Roboto-Bold;">{{ $applicant->contact->mobile }}</div>
                <div style="float:left; width: 15%; margin-left: 5px; font-family: Roboto-Bold; margin-top:14px;">                   
                    @if($applicant->contact->mobile_verification == 1)
                        <h6><span class="badge badge-success">Verified</span></h6>   
                    @else
                        <h6><span class="badge badge-danger">Unverified</span></h6>
                    @endif
                </div>
                <div style="clear:both"></div>
                <div style="float:left; width: 15%; margin-top:10px; color:#696969;font-family: Roboto-Medium;">Address</div>
                <div style="clear:both"></div>
                <div style="float:left; font-family: Roboto-Bold;">
                    @if(isset($applicant->contact->address_line_1) && !empty($applicant->contact->address_line_1))
                        {{ $applicant->contact->address_line_1 }}
                    @endif
                    @if(isset($applicant->contact->address_line_2) && !empty($applicant->contact->address_line_2))
                        , {{ $applicant->contact->address_line_2 }}
                    @endif
                    @if(isset($applicant->contact->city) && !empty($applicant->contact->city))
                        , {{ $applicant->contact->city }}
                    @endif
                    @if(isset($applicant->contact->state) && !empty($applicant->contact->state))
                        , {{ $applicant->contact->state }}
                    @endif
                    @if(isset($applicant->contact->post_code) && !empty($applicant->contact->post_code))
                        , {{ $applicant->contact->post_code }}
                    @endif
                    @if(isset($applicant->contact->country) && !empty($applicant->contact->country))
                        , {{ $applicant->contact->country }}
                    @endif 
                </div>
                <div style="clear:both"></div>
            </div>
        </div>
        {{-- Next of Kin --}}
        <div class="card" style="margin-top: 20px; width:100%;">
            <div class="card-body">
                <div class="card-title" style="font-size:14px;font-family: Roboto-Bold;">Next of Kin</div>
                <div style="float:left; width: 15%; color:#696969;font-family: Roboto-Medium;">Name</div>
                <div style="float:left; width: 18%; margin-left: 5px;  margin-right: 5px;font-family: Roboto-Bold;">{{ $applicant->kin->name }}</div>                      
                
                <div style="float:left; width: 15%; color:#696969;font-family: Roboto-Medium;">Relation</div>
                <div style="float:left; width: 18%; margin-left: 5px;  margin-right: 5px;font-family: Roboto-Bold;">{{ $applicant->kin->relation->name }}</div>                      

                <div style="float:left; width: 15%; color:#696969;font-family: Roboto-Medium;">Mobile</div>
                <div style="float:left; width: 18%; margin-left: 5px; font-family: Roboto-Bold;">{{ $applicant->kin->mobile }}</div> 
                <div style="clear:both"></div>
                <div style="float:left; width: 15%; margin-top:10px; color:#696969;font-family: Roboto-Medium;">Email</div>
                <div style="float:left; width: 18%; margin-top:10px; margin-left: 5px; font-family: Roboto-Bold;">{{ (isset($applicant->kin->email) && !empty($applicant->kin->email) ? $applicant->kin->email : '---') }}</div> 
                <div style="clear:both"></div>
                <div style="float:left; width: 15%; margin-top:10px; color:#696969;font-family: Roboto-Medium;">Address</div>
                <div style="clear:both"></div>
                <div style="float:left; font-family: Roboto-Bold;">
                    @if(isset($applicant->kin->address_line_1) && !empty($applicant->kin->address_line_1))
                        {{ $applicant->kin->address_line_1 }}
                    @endif
                    @if(isset($applicant->kin->address_line_2) && !empty($applicant->kin->address_line_2))
                        , {{ $applicant->kin->address_line_2 }}
                    @endif
                    @if(isset($applicant->kin->city) && !empty($applicant->kin->city))
                        , {{ $applicant->kin->city }}
                    @endif
                    @if(isset($applicant->kin->state) && !empty($applicant->kin->state))
                        , {{ $applicant->kin->state }}
                    @endif
                    @if(isset($applicant->kin->post_code) && !empty($applicant->kin->post_code))
                        , {{ $applicant->kin->post_code }}
                    @endif
                    @if(isset($applicant->kin->country) && !empty($applicant->kin->country))
                        , {{ $applicant->kin->country }}
                    @endif
                </div>
                <div style="clear:both"></div>
            </div>
        </div>
        {{-- Proposed Course & Programme --}}
        <div class="card" style="margin-top: 20px; width:100%;">
            <div class="card-body">
                <div class="card-title" style="font-size:14px;font-family: Roboto-Bold;">Proposed Course & Programme</div>
                <div style="float:left; width: 50%; color:#696969;font-family:Roboto-Medium">Course & Semester</div>
                <div style="float:left; width: 30%; margin-left: 15px;font-family:Roboto-Bold">{{ $applicant->course->creation->course->name.' - '.$applicant->course->semester->name }}</div>
                <div style="clear:both"></div>
                <div style="float:left; width: 50%; margin-top:10px; color:#696969;font-family:Roboto-Medium">How are you funding your education at London Churchill College?</div>
                <div style="float:left; width: 30%; margin-top:10px; margin-left: 15px;font-family:Roboto-Bold">{{ $applicant->course->student_loan }}</div>
                <div style="clear:both"></div>

                @if($applicant->course->student_loan == 'Student Loan')
                    <div style="float:left; width: 50%; margin-top:10px; color:#696969;font-family:Roboto-Medium">If your funding is through Student Finance England, please choose from the following. Have you applied for the proposed course?</div>
                    <div style="float:left; width: 30%; margin-top:10px; margin-left: 15px;font-family:Roboto-Bold">{!! (isset($applicant->course->student_finance_england) && $applicant->course->student_finance_england == 1 ? '<h6><span class="badge badge-success">Yes</span></h6>' : '<h6><span class="badge badge-danger">No</span></h6>') !!}</div>
                    <div style="clear:both"></div>
                    @if(isset($applicant->course->student_finance_england) && $applicant->course->student_finance_england == 1)
                        <div style="float:left; width: 50%; margin-top:10px; color:#696969;font-family:Roboto-Medium">Are you already in receipt of funds?</div>
                        <div style="float:left; width: 30%; margin-top:10px; margin-left: 15px;font-family:Roboto-Bold">{!! (isset($applicant->course->fund_receipt) && $applicant->course->fund_receipt == 1 ? '<h6><span class="badge badge-success">Yes</span></h6>' : '<h6><span class="badge badge-danger">No</span></h6>') !!}</div>
                        <div style="clear:both"></div>
                    @endif
                    <div style="float:left; width: 50%; margin-top:10px; color:#696969;font-family:Roboto-Medium">Have you ever apply/Received any fund/Loan from SLC/government Loan for any other programme/institution?</div>
                    <div style="float:left; width: 30%; margin-top:10px; margin-left: 15px;font-family:Roboto-Bold">{!! (isset($applicant->course->applied_received_fund) && $applicant->course->applied_received_fund == 1 ? '<h6><span class="badge badge-success">Yes</span></h6>' : '<h6><span class="badge badge-danger">No</span></h6>') !!}</div>
                    <div style="clear:both"></div>
                @elseif($applicant->course->student_loan == 'Others')
                    <div style="float:left; width: 50%; margin-top:10px; color:#696969;font-family:Roboto-Medium">Other Funding</div>
                    <div style="float:left; width: 30%; margin-top:10px; margin-left: 15px;font-family:Roboto-Bold">{{ (isset($applicant->course->other_funding) && $applicant->course->other_funding != '' ? $applicant->course->other_funding : '') }}</div>
                    <div style="clear:both"></div>
                @endif
                <div style="float:left; width: 50%; margin-top:10px; color:#696969;font-family:Roboto-Medium">Are you applying for evening and weekend classes (Full Time)</div>
                <div style="float:left; width: 30%; margin-top:10px; margin-left: 15px;font-family:Roboto-Bold">{!! (isset($applicant->course->full_time) && $applicant->course->full_time == 1 ? '<h6><span class="badge badge-success">Yes</span></h6>' : '<h6><span class="badge badge-danger">No</span></h6>') !!}</div>
                <div style="clear:both"></div>
            </div>
        </div>

        {{-- Education Qualification --}}
        <div class="card" style="margin-top: 20px; width:100%;">
            <div class="card-body">
                <div class="card-title">
                    <div style="float:left; width: 40%; font-size:14px; font-family:Roboto-Bold">Education Qualification</div>
                    <div style="float:left; width: 50%; margin-left: 45px;  font-family:Roboto-Regular; margin-top:4px">Do you have any formal academic qualification?</div>
                    <div style="float:left; width: 10%; font-family:Roboto-Bold; margin-top:5px">
                        {!! (isset($applicant->other->is_edication_qualification) && $applicant->other->is_edication_qualification == 1 ? '<h6><span class="badge badge-success">Yes</span></h6>' : '<h6><span class="badge badge-danger">No</span></h6>') !!}
                    </div>  
                </div>
                <div style="clear:both"></div>
                @if($applicant->other->is_edication_qualification == 1)
                <div class="" data-pattern="priority-columns" style="margin-top: 20px">
                    <table style="width: 100%;" class="table table-striped">
                        <thead style="font-family:Roboto-Bold">
                            <tr>
                                <th data-priority="1" scope="col">Awarding Body</th>
                                <th data-priority="2" scope="col">Highest Academic Qualification</th>
                                <th data-priority="3" scope="col">Subjects</th>
                                <th data-priority="4" scope="col">Result</th>
                                <th data-priority="5" scope="col">Award Date</th>
                            </tr>
                        </thead>
                        <tbody style="font-family:Roboto-Regular">
                            @foreach ( $noteList as $notes )
                            <tr>
                                <td>{{ $notes['awarding_body'] }}</td>
                                <td>{{ $notes['highest_academic'] }}</td>
                                <td>{{ $notes['subjects'] }}</td>
                                <td>{{ $notes['result'] }}</td>
                                <td>{{ $notes['degree_award_date'] }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div style="clear:both"></div>
                @elseif ($applicant->other->is_edication_qualification == 0)
                <div class="" data-pattern="priority-columns">
                    <table style="width: 100%; margin-top:10px;" class="table table-striped">
                        <thead style="font-family:Roboto-Bold">
                            <tr>
                                <th data-priority="1" scope="col">Awarding Body</th>
                                <th data-priority="2" scope="col">Highest Academic Qualification</th>
                                <th data-priority="3" scope="col">Subjects</th>
                                <th data-priority="4" scope="col">Result</th>
                                <th data-priority="5" scope="col">Award Date</th>
                            </tr>
                        </thead>
                        <tbody style="font-family:Roboto-Regular">
                            <tr>
                                <p style="text-align: left">No Data Found !</p>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div style="clear:both"></div>
                @endif
            </div>
        </div>

        {{-- Empoyment History --}}
        <div class="card" style="margin-top: 20px; width:100%;">
            <div class="card-body">
                <div class="card-title">
                    <div style="float:left; width: 40%; font-size:14px; font-family:Roboto-Bold">Empoyment History</div>
                    <div style="float:left; width: 45%; margin-left: 45px;  font-family:Roboto-Regular; margin-top:4px">What is your current employment status?</div>
                    <div style="float:left; width: 15%; font-family:Roboto-Medium; margin-top:6px">           
                        <h6><span class="badge badge-primary">{{ $applicant->other->employment_status }}</span></h6>            
                    </div>  
                </div>
                <div style="clear:both"></div>
                {{-- Empoyment History Table --}}
                @if(isset($applicant->other->employment_status) || ($applicant->other->employment_status == 'Unemployed' || $applicant->other->employment_status == 'Contractor' || $applicant->other->employment_status == 'Consultant' || $applicant->other->employment_status == 'Office Holder'))
                <div class="" data-pattern="priority-columns" style="margin-top: 20px">
                    <table style="width: 100%; margin-top:10px;" class="table table-striped">
                        <thead style="font-family:Roboto-Bold">
                            <tr>
                                <th data-priority="1" scope="col">Company</th>
                                <th data-priority="2" scope="col">Phone</th>
                                <th data-priority="3" scope="col">Position</th>
                                <th data-priority="4" scope="col">Start</th>
                                <th data-priority="5" scope="col">End</th>
                                <th data-priority="6" scope="col">Address</th>
                                <th data-priority="7" scope="col">Contact Person</th>
                                <th data-priority="8" scope="col">Position</th>
                                <th data-priority="9" scope="col">Phone</th>
                            </tr>
                        </thead>
                        <tbody style="font-family:Roboto-Regular">
                            @foreach ( $noteList as $notes )
                            <tr>
                                <td>{{ $notes['company_name'] }}</td>
                                <td>{{ $notes['company_phone'] }}</td>
                                <td>{{ $notes['position'] }}</td>
                                <td>{{ $notes['start_date'] }}</td>
                                <td>{{ $notes['end_date'] }}</td>
                                <td>{{ $notes['address'] }}</td>
                                <td>{{ $notes['reference'] }}</td>
                                <td>{{ $notes['reference_position'] }}</td>
                                <td>{{ $notes['reference_phone'] }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>  
                <div style="clear:both"></div>      
                @endif
            </div>
        </div>

        {{-- Others --}}
        <div class="card" style="margin-top: 20px; width:100%;">
            <div class="card-body">
                <div class="card-title" style="font-size:14px;font-family: Roboto-Bold;">Others</div>
                <div style="float:left; width: 50%; color:#696969;font-family:Roboto-Medium">If you referred by Somone/ Agent, Please enter the Referral Code.</div>
                <div style="float:left; width: 30%; margin-left: 15px;font-family:Roboto-Bold">
                    {!! ($applicant->referral_code != '' ? $applicant->referral_code : '<h6><span class="badge badge-danger">No</span></h6>') !!}</div>
                    <div style="clear:both"></div>
                </div>               
            </div>
            <div style="clear:both"></div>
        </div>

        {{-- Communication --}}
        <div class="width: 100%;" style="margin-top: 20px">
            <div style="float:left; width: 100%; height:50px; font-family: Roboto-Bold; background-color: #249D93; border-radius:4px;">
                <div style="float:left; margin-left: 10px; color:white;padding:5px;">
                    <span style="font-size:16px;">Communication</span>
                </div>
            </div>
            <div style="clear:both"></div>
        </div>
        <div class="card" style="margin-top: 20px; width:100%;">
            <div class="card-body">
                <div class="card-title" style="font-size:14px;font-family: Roboto-Bold;">Email</div>
                <div style="clear:both"></div>
                {{-- Email Table --}}
                <div class="" data-pattern="priority-columns" style="margin-top: 20px">
                    <table style="width: 100%; margin-top:10px;" class="table table-striped"> 
                        <thead style="font-family:Roboto-Bold">
                            <tr>
                                <th data-priority="1" scope="col">Subject</th>
                                <th data-priority="2" scope="col">From</th>
                                <th data-priority="3" scope="col">Issued By</th>
                            </tr>
                        </thead>
                        <tbody style="font-family:Roboto-Regular">
                            @foreach ( $noteList as $notes )
                            <tr>
                                <td>{{ $notes['email_subject'] }}</td>
                                <td>{{ $notes['smtp_user'] }}</td>
                                <td>{{ $notes['email_by'] }}</td>                               
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div style="clear:both"></div>
            </div>
        </div>
        <div class="card" style="margin-top: 20px; width:100%;">
            <div class="card-body">
                <div class="card-title" style="font-size:14px;font-family: Roboto-Bold;">SMS</div>
                <div style="clear:both"></div>
                {{-- SMS Table --}}
                <div class="" data-pattern="priority-columns" style="margin-top: 20px">
                    <table style="width: 100%; margin-top:10px;" class="table table-striped"> 
                        <thead style="font-family:Roboto-Bold">
                            <tr>
                                <th data-priority="1" scope="col">Subject</th>
                                <th data-priority="3" scope="col">Issued By</th>
                            </tr>
                        </thead>
                        <tbody style="font-family:Roboto-Regular">
                            @foreach ( $noteList as $notes )
                            <tr>
                                <td>{{ $notes['sms_subject'] }}</td>
                                <td>{{ $notes['sms_by'] }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div style="clear:both"></div>
            </div>
        </div>
        {{-- Uploaded Files --}}
        <div class="width: 100%;" style="margin-top: 20px">
            <div style="float:left; width: 100%; height:50px; font-family: Roboto-Bold; background-color: #249D93; border-radius:4px;">
                <div style="float:left; margin-left: 10px; color:white;padding:5px;">
                    <span style="font-size:16px;">Uploaded Files</span>
                </div>
            </div>
            <div style="clear:both"></div>
        </div>
        <div class="card" style="margin-top: 20px; width:100%;">
            <div class="card-body">
                <div class="card-title" style="font-size:14px;font-family: Roboto-Bold;">Documents</div>
                <div style="clear:both"></div>
                {{-- Documents Table --}}
                <div class="" data-pattern="priority-columns" style="margin-top: 20px">
                    <table style="width: 100%; margin-top:10px;" class="table table-striped"> 
                        <thead style="font-family:Roboto-Bold">
                            <tr>
                                <th data-priority="1" scope="col">Name</th>
                                <th data-priority="2" scope="col">Checked</th>
                                <th data-priority="3" scope="col">Uploaded By</th>
                                <th data-priority="4" scope="col"></th>
                            </tr>
                        </thead>
                        <tbody style="font-family:Roboto-Regular">
                            @foreach ( $noteList as $notes )
                            <tr>
                                <td>{{ $notes['document_name'] }}</td>
                                <td>
                                    @if($notes['hard_copy_check']==1)
                                    <h6><span class="badge badge-success">Yes</span></h6>
                                    @else
                                    <h6><span class="badge badge-danger">No</span></h6>
                                    @endif
                                </td>
                                <td>{{ $notes['created_by'] }}</td>
                                <td><a target="_blank" href="{{ asset('storage/applicants/' . $notes['applicant_id'].'/'.$notes['document_link']) }}"><img style="width: 30px; height: 30px; border-radius: 6px; margin-top:10px;" src="{{ public_path('build/assets/images/fileicon.png') }}"></a></td>
                            </tr>
                            @endforeach 
                        </tbody>
                    </table>
                </div>
                <div style="clear:both"></div>
            </div>
        </div>

        {{-- Notes --}}
        <div class="width: 100%;" style="margin-top: 20px">
            <div style="float:left; width: 100%; height:50px; font-family: Roboto-Bold; background-color: #249D93; border-radius:4px;">
                <div style="float:left; margin-left: 10px; color:white;padding:5px;">
                    <span style="font-size:16px;">Notes</span>
                </div>
            </div>
            <div style="clear:both"></div>
        </div>
        <div class="card" style="margin-top: 20px; width:100%;">
            <div class="card-body">
                <div class="card-title" style="font-size:14px;font-family: Roboto-Bold;">Notes</div>
                <div style="clear:both"></div>
                {{-- Notes Table --}}
                <div class="" data-pattern="priority-columns" style="margin-top: 20px">
                    <table style="width: 100%; margin-top:10px;" class="table table-striped">
                        <thead style="font-family:Roboto-Bold">
                            <tr>
                                <th data-priority="1" scope="col">Note</th>
                                <th data-priority="2" scope="col">Created By</th>
                            </tr>
                        </thead>
                        <tbody style="font-family:Roboto-Regular">
                            @foreach ( $noteList as $notes )
                            <tr>
                                <td><?php echo strip_tags ($notes['note'])?></td>
                                <td>{{ $notes['created_by'] }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div style="clear:both"></div>
            </div>
        </div>

        {{-- Processes --}}
        <div class="width: 100%;" style="margin-top: 20px">
            <div style="float:left; width: 100%; height:50px; font-family: Roboto-Bold; background-color: #249D93; border-radius:4px;">
                <div style="float:left; margin-left: 10px; color:white;padding:5px;">
                    <span style="font-size:16px;">Processes</span>
                </div>
            </div>
            <div style="clear:both"></div>
        </div>
        <div class="card" style="margin-top: 20px; width:100%; height:490px">
            <div class="card-body">
                <div class="card-title" style="font-size:14px;font-family: Roboto-Bold;">My Task</div>
                <div style="clear:both"></div>
                <div class="card-subtitle" style="font-size:12px;font-family: Roboto-Medium; color:#696969;"><u>In Progress</u></div>
                <div style="clear:both"></div>
                @if($applicantPendingTask->count() > 0)
                    @foreach($applicantPendingTask as $task)
                        <div style="float:left; width: 30%; margin-top:10px;">
                            <span style="font-family:Roboto-Medium">{{ $task->task->name }}</span>
                            @if($task->task_status_id > 0 && isset($task->applicatnTaskStatus->name) && !empty($task->applicatnTaskStatus->name))
                                (<span style="font-family:Roboto-Medium"><u>Outcome: {{ $task->applicatnTaskStatus->name }}</u></span>)
                            @endif 
                            <div style="clear:both"></div>
                            @if(isset($task->task->short_description) && !empty($task->task->short_description))
                                <span style="font-family:Roboto-Regular">{{ $task->task->short_description }}</span>
                            @endif
                            <div style="clear:both"></div>
                            @if(isset($task->documents) && !empty($task->documents))
                                @foreach($task->documents as $tdoc)
                                    @if($tdoc->doc_type == 'jpg' || $tdoc->doc_type == 'jpeg' || $tdoc->doc_type == 'png' || $tdoc->doc_type == 'gif')
                                    <a target="_blank" href="{{ asset('storage/applicants/' . $tdoc->applicant_id.'/'.$tdoc->current_file_name) }}"><img style="width: 30px; height: 30px; border-radius: 6px; margin-top:10px;" src="{{ public_path('storage/applicants/' . $tdoc->applicant_id.'/'.$tdoc->current_file_name) }}" alt="{{ $task->task->name }}"></a>
                                    @else
                                        {{-- <i class="fa fa-file-archive-o" style=" margin-top:10px;" src="{{ public_path('storage/applicants/' . $tdoc->applicant_id.'/'.$tdoc->current_file_name) }}"></i> --}}
                                        <a target="_blank" href="{{ asset('storage/applicants/' . $tdoc->applicant_id.'/'.$tdoc->current_file_name) }}"><img style="width: 30px; height: 30px; border-radius: 6px; margin-top:10px;" src="{{ public_path('build/assets/images/fileicon.png') }}"></a>
                                    @endif
                                @endforeach
                            @endif 
                        </div>
                        <div style="float:left; width: 10%;  margin-top:10px; color:#696969; font-size:10px;font-family:Roboto-Regular">{{ date('h:i a', strtotime($task->created_at)) }}</div>         
                        <div style="float:left; width: 10%; margin-top:10px;font-family:Roboto-Bold">Assigned To:</div> 
                        <span style="float:left; width: 40%;  margin-top:10px; font-size:10px"> 
                            @if(isset($task->task->users) && !empty($task->task->users))
                                @foreach($task->task->users as $userser)
                                    @if($loop->first)
                                        <span style="font-family:Roboto-Medium">{{ $userser->user->name }}</span>
                                    @elseif(!$loop->last)                               
                                        <span style="font-family:Roboto-Medium">, {{ $userser->user->name }}</span>
                                        <div style="clear:both"></div>
                                    @endif
                                @endforeach
                            @else 
                                <div style="float:left; width: 40%; margin-top:10px;font-family:Roboto-Medium">No assigned user found!</div>
                            @endif                                    
                        </span>
                        <div style="clear:both"></div>
                    @endforeach
                @else 
                    <div style="float:left; width: 40%; margin-top:10px;font-family:Roboto-Medium">Oops! There are no pending process found for this applicant.</div>
                @endif
                <div style="clear:both"></div>
           
                <div class="card-subtitle" style="font-size:12px;font-family: Roboto-Medium; color:#696969; margin-top:20px"><u>Completed</u></div>
                <div style="clear:both"></div>
                @if($applicantCompletedTask->count() > 0)
                    @foreach($applicantCompletedTask as $task)
                        <div style="float:left; width: 30%; margin-top:10px;">
                            <span style="font-family:Roboto-Medium">{{ $task->task->name }}</span>
                            @if($task->task_status_id > 0 && isset($task->applicatnTaskStatus->name) && !empty($task->applicatnTaskStatus->name))
                                (<span style="font-family:Roboto-Medium"><u>Outcome: {{ $task->applicatnTaskStatus->name }}</u></span>)
                            @endif
                            
                            <div style="clear:both"></div>
                            @if(isset($task->task->short_description) && !empty($task->task->short_description))
                                <span style="font-family:Roboto-Regular">{{ $task->task->short_description }}</span>
                            @endif
                            <div style="clear:both"></div>
                            @if(isset($task->documents) && !empty($task->documents))
                                @foreach($task->documents as $tdoc)
                                    @if($tdoc->doc_type == 'jpg' || $tdoc->doc_type == 'jpeg' || $tdoc->doc_type == 'png' || $tdoc->doc_type == 'gif')
                                        <a target="_blank" href="{{ asset('storage/applicants/' . $tdoc->applicant_id.'/'.$tdoc->current_file_name) }}"><img style="width: 30px; height: 30px; border-radius: 6px;  margin-top:10px;" src="{{ public_path('storage/applicants/' . $tdoc->applicant_id.'/'.$tdoc->current_file_name) }}" alt="{{ $task->task->name }}"></a>
                                    @else
                                        
                                        <a target="_blank" href="{{ asset('storage/applicants/' . $tdoc->applicant_id.'/'.$tdoc->current_file_name) }}"><img style="width: 30px; height: 30px; border-radius: 6px; margin-top:10px;" src="{{ public_path('build/assets/images/fileicon.png') }}"></a>
                                    @endif
                                @endforeach    
                            @endif
                        </div>
                        <div style="float:left; width: 10%;  margin-top:10px; color:#696969; font-size:10px;font-family:Roboto-Regular">{{ date('h:i a', strtotime($task->created_at)) }}</div>
                        <div style="float:left; width: 10%; margin-top:10px;font-family:Roboto-Bold">Assigned To:</div> 
                        <span style="float:left; width: 40%;  margin-top:10px; font-size:10px">
                            @if(isset($task->task->users) && !empty($task->task->users))
                                @foreach($task->task->users as $userser)
                                    @if($loop->first)                               
                                        <span style="font-family:Roboto-Medium">{{ $userser->user->name }}</span>
                                        
                                        
                                    @elseif(!$loop->last)                             
                                        <span style="font-family:Roboto-Medium">, {{ $userser->user->name }}</span>
                                        <div style="clear:both"></div>
                             
                                    @endif
                                @endforeach  
                            @else 
                                <div style="float:left; width: 40%; margin-top:10px;font-family:Roboto-Medium">No assigned user found!</div>
                            @endif
                        </span>
                    @endforeach
                @endif
            </div>
        </div>
    </body>
</html>