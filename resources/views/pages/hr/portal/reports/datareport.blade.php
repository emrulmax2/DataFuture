@extends('../layout/' . $layout)

@section('subhead')
    <title>{{ $title }}</title>
@endsection

@section('subcontent')
    <div class="intro-y flex flex-col sm:flex-row items-center mt-8">
        <h2 class="text-lg font-medium mr-auto">Employee Data Report</h2>
        <div class="w-full sm:w-auto flex mt-4 sm:mt-0">     
            <a href="{{ route('hr.portal.employment.reports.show') }}" class="add_btn btn btn-primary shadow-md">Back to Employment Reports</a>
        </div>
    </div>
    <form id="employeeDataReportForm" method="POST">
        <div class="intro-y box p-5 mt-5">
            <div class="grid grid-cols-12 gap-4">
                <div class="col-span-3">
                    <label class="form-label">Type</label>
                    <select id="employee_work_type_id-starter" class="lccTom lcc-tom-select w-full form-control" name="employee_work_type_id"> 
                        <option value="" selected>Please Select</option>
                        @if($employeeWorkType->count() > 0)
                            @foreach($employeeWorkType as $si)
                                <option {{ isset($employment->employee_work_type_id) && $employment->employee_work_type_id == $si->id }} value="{{ $si->id }}">{{ $si->name }}</option>
                            @endforeach
                        @endif
                    </select>
                </div>
                <div class="col-span-2">
                    <label class="form-label">Department</label>
                    <select id="department_id-starter" name="department_id" class="w-full lccTom lcc-tom-select form-control">     
                        <option value="" selected>Please Select</option>             
                        @foreach($departments as $si)
                            <option {{ isset($employment->department_id) && $employment->department_id == $si->id }} value="{{ $si->id }}">{{ $si->name }}</option>             
                        @endforeach
                    </select> 
                </div>
                <div class="col-span-2">
                    <label class="form-label">Startdate</label>
                    <input type="text" id="startdate-starter" name="startdate-starter" placeholder="DD-MM-YYYY" value="" data-format="DD-MM-YYYY"  data-single-mode="true" class="w-full datepicker form-control"/>
                </div>
                <div class="col-span-2">
                    <label class="form-label">Enddate</label>
                    <input type="text" id="enddate-starter" name="enddate-starter" placeholder="DD-MM-YYYY" value="" data-format="DD-MM-YYYY"  data-single-mode="true" class="w-full datepicker form-control"/>
                </div>
                <div class="col-span-2">
                    <label class="form-label">Gender</label>
                    <select id="sex_identifier_id" name="sex_identifier_id" class="lccTom lcc-tom-select w-full form-control">
                        <option value="" selected>Please Select</option>
                        @if(!empty($gender))
                            @foreach($gender as $n)
                                <option {{ isset($employee->sex_identifier_id) && $employee->sex_identifier_id == $n->id ? 'Selected' : '' }} value="{{ $n->id }}">{{ $n->name }}</option>
                            @endforeach 
                        @endif
                    </select>
                </div>
                <div class="col-span-2">
                    <label class="form-label">Status</label>
                    <select id="status_id-starter" name="status_id" class="w-full lccTom lcc-tom-select form-control">     
                        <option value="1">Active</option>
                        <option value="0">In Active</option>
                        <option value="2">All</option>
                    </select> 
                </div>
            </div>
        </div>
        <div class="intro-y box p-5 mt-5">
            <div class="grid grid-cols-12 gap-4 personalsRows">
                <div class="col-span-12">
                    <h3 class="empDatasTitle font-medium">Personal Info <a href="javascript: void(0);" data-parent="personalsRows" class="btn btn-sm btn-primary checkedAlls ml-1">All</a></h3>
                    
                    <input type="hidden" name="tables[employees]" value="Employee" />
                    
                </div>
                <div class="col-span-3">
                    <div class="singlefields relative mb-2">
                        <input id="s1" class="cus-check transition-all duration-100 ease-in-out shadow-sm border-slate-200 cursor-pointer rounded focus:ring-4 focus:ring-offset-0 focus:ring-primary focus:ring-opacity-20 dark:bg-darkmode-800 dark:border-transparent dark:focus:ring-slate-700 dark:focus:ring-opacity-50 [&[type='radio']]:checked:bg-primary [&[type='radio']]:checked:border-primary [&[type='radio']]:checked:border-opacity-10 [&[type='checkbox']]:checked:bg-primary [&[type='checkbox']]:checked:border-primary [&[type='checkbox']]:checked:border-opacity-10 [&:disabled:not(:checked)]:bg-slate-100 [&:disabled:not(:checked)]:cursor-not-allowed [&:disabled:not(:checked)]:dark:bg-darkmode-800/50 [&:disabled:checked]:opacity-70 [&:disabled:checked]:cursor-not-allowed [&:disabled:checked]:dark:bg-darkmode-800/50" type="checkbox" name="fields[employees][title_id]" value="1">
                        <label class= "cursor-pointer ml-2" for="s1">Title</label>
                    </div>
                    

                    <input type="hidden" name="labels[title_id]" value="Title">
                    <div class=" relative mb-2">
                        <input id="s2" class="cus-check transition-all duration-100 ease-in-out shadow-sm border-slate-200 cursor-pointer rounded focus:ring-4 focus:ring-offset-0 focus:ring-primary focus:ring-opacity-20 dark:bg-darkmode-800 dark:border-transparent dark:focus:ring-slate-700 dark:focus:ring-opacity-50 [&[type='radio']]:checked:bg-primary [&[type='radio']]:checked:border-primary [&[type='radio']]:checked:border-opacity-10 [&[type='checkbox']]:checked:bg-primary [&[type='checkbox']]:checked:border-primary [&[type='checkbox']]:checked:border-opacity-10 [&:disabled:not(:checked)]:bg-slate-100 [&:disabled:not(:checked)]:cursor-not-allowed [&:disabled:not(:checked)]:dark:bg-darkmode-800/50 [&:disabled:checked]:opacity-70 [&:disabled:checked]:cursor-not-allowed [&:disabled:checked]:dark:bg-darkmode-800/50" type="checkbox" name="fields[employees][last_name]" value="1">
                        <label class= "cursor-pointer ml-2" for="s2">Last Name</label>
                    </div>
                    <input type="hidden" name="labels[last_name]" value="Last Name">
                    <div class="singlefields relative mb-2">
                        <input id="s3" class="cus-check transition-all duration-100 ease-in-out shadow-sm border-slate-200 cursor-pointer rounded focus:ring-4 focus:ring-offset-0 focus:ring-primary focus:ring-opacity-20 dark:bg-darkmode-800 dark:border-transparent dark:focus:ring-slate-700 dark:focus:ring-opacity-50 [&[type='radio']]:checked:bg-primary [&[type='radio']]:checked:border-primary [&[type='radio']]:checked:border-opacity-10 [&[type='checkbox']]:checked:bg-primary [&[type='checkbox']]:checked:border-primary [&[type='checkbox']]:checked:border-opacity-10 [&:disabled:not(:checked)]:bg-slate-100 [&:disabled:not(:checked)]:cursor-not-allowed [&:disabled:not(:checked)]:dark:bg-darkmode-800/50 [&:disabled:checked]:opacity-70 [&:disabled:checked]:cursor-not-allowed [&:disabled:checked]:dark:bg-darkmode-800/50" type="checkbox" name="fields[employees][first_name]" value="1">
                        <label class= "cursor-pointer ml-2" for="s3">First name</label>
                    </div>
                    <input type="hidden" name="labels[first_name]" value="First Name">
                    

                    <div class="singlefields relative mb-2">
                        <input id="s12" class="cus-check transition-all duration-100 ease-in-out shadow-sm border-slate-200 cursor-pointer rounded focus:ring-4 focus:ring-offset-0 focus:ring-primary focus:ring-opacity-20 dark:bg-darkmode-800 dark:border-transparent dark:focus:ring-slate-700 dark:focus:ring-opacity-50 [&[type='radio']]:checked:bg-primary [&[type='radio']]:checked:border-primary [&[type='radio']]:checked:border-opacity-10 [&[type='checkbox']]:checked:bg-primary [&[type='checkbox']]:checked:border-primary [&[type='checkbox']]:checked:border-opacity-10 [&:disabled:not(:checked)]:bg-slate-100 [&:disabled:not(:checked)]:cursor-not-allowed [&:disabled:not(:checked)]:dark:bg-darkmode-800/50 [&:disabled:checked]:opacity-70 [&:disabled:checked]:cursor-not-allowed [&:disabled:checked]:dark:bg-darkmode-800/50" type="checkbox" name="fields[employees][date_of_birth]" value="1"> 
                        <label class= "cursor-pointer ml-2" for="s12">Date of Birth</label>
                    </div>
                    <input type="hidden" name="labels[date_of_birth]" value="Date of Birth">

                    <div class="singlefields relative mb-2">
                        <input id="s10" class="cus-check transition-all duration-100 ease-in-out shadow-sm border-slate-200 cursor-pointer rounded focus:ring-4 focus:ring-offset-0 focus:ring-primary focus:ring-opacity-20 dark:bg-darkmode-800 dark:border-transparent dark:focus:ring-slate-700 dark:focus:ring-opacity-50 [&[type='radio']]:checked:bg-primary [&[type='radio']]:checked:border-primary [&[type='radio']]:checked:border-opacity-10 [&[type='checkbox']]:checked:bg-primary [&[type='checkbox']]:checked:border-primary [&[type='checkbox']]:checked:border-opacity-10 [&:disabled:not(:checked)]:bg-slate-100 [&:disabled:not(:checked)]:cursor-not-allowed [&:disabled:not(:checked)]:dark:bg-darkmode-800/50 [&:disabled:checked]:opacity-70 [&:disabled:checked]:cursor-not-allowed [&:disabled:checked]:dark:bg-darkmode-800/50" type="checkbox" name="fields[employees][sex_identifier_id]" value="1"> 
                        <label class= "cursor-pointer ml-2" for="s10">Sex</label>
                    </div>
                    <input type="hidden" name="labels[sex_identifier_id]" value="Sex">
                    

                    

                </div>
                <div class="col-span-3">

                    <div class="singlefields relative mb-2">
                        <input id="s17" class="cus-check transition-all duration-100 ease-in-out shadow-sm border-slate-200 cursor-pointer rounded focus:ring-4 focus:ring-offset-0 focus:ring-primary focus:ring-opacity-20 dark:bg-darkmode-800 dark:border-transparent dark:focus:ring-slate-700 dark:focus:ring-opacity-50 [&[type='radio']]:checked:bg-primary [&[type='radio']]:checked:border-primary [&[type='radio']]:checked:border-opacity-10 [&[type='checkbox']]:checked:bg-primary [&[type='checkbox']]:checked:border-primary [&[type='checkbox']]:checked:border-opacity-10 [&:disabled:not(:checked)]:bg-slate-100 [&:disabled:not(:checked)]:cursor-not-allowed [&:disabled:not(:checked)]:dark:bg-darkmode-800/50 [&:disabled:checked]:opacity-70 [&:disabled:checked]:cursor-not-allowed [&:disabled:checked]:dark:bg-darkmode-800/50" type="checkbox" name="fields[employees][nationality_id]" value="1"> 
                        <label class= "cursor-pointer ml-2" for="s17">Nationality</label>
                    </div>
                    <input type="hidden" name="labels[nationality_id]" value="Nationality">

                    <div class="singlefields relative mb-2">
                        <input id="s18" class="cus-check transition-all duration-100 ease-in-out shadow-sm border-slate-200 cursor-pointer rounded focus:ring-4 focus:ring-offset-0 focus:ring-primary focus:ring-opacity-20 dark:bg-darkmode-800 dark:border-transparent dark:focus:ring-slate-700 dark:focus:ring-opacity-50 [&[type='radio']]:checked:bg-primary [&[type='radio']]:checked:border-primary [&[type='radio']]:checked:border-opacity-10 [&[type='checkbox']]:checked:bg-primary [&[type='checkbox']]:checked:border-primary [&[type='checkbox']]:checked:border-opacity-10 [&:disabled:not(:checked)]:bg-slate-100 [&:disabled:not(:checked)]:cursor-not-allowed [&:disabled:not(:checked)]:dark:bg-darkmode-800/50 [&:disabled:checked]:opacity-70 [&:disabled:checked]:cursor-not-allowed [&:disabled:checked]:dark:bg-darkmode-800/50" type="checkbox" name="fields[employees][ethnicity_id]" value="1"> 
                        <label class= "cursor-pointer ml-2" for="s18">Ethnic Origin</label>
                    </div>
                    <input type="hidden" name="labels[ethnicity_id]" value="Ethnic Origin">

                    <div class="singlefields relative mb-2">
                        <input id="s14" class="cus-check transition-all duration-100 ease-in-out shadow-sm border-slate-200 cursor-pointer rounded focus:ring-4 focus:ring-offset-0 focus:ring-primary focus:ring-opacity-20 dark:bg-darkmode-800 dark:border-transparent dark:focus:ring-slate-700 dark:focus:ring-opacity-50 [&[type='radio']]:checked:bg-primary [&[type='radio']]:checked:border-primary [&[type='radio']]:checked:border-opacity-10 [&[type='checkbox']]:checked:bg-primary [&[type='checkbox']]:checked:border-primary [&[type='checkbox']]:checked:border-opacity-10 [&:disabled:not(:checked)]:bg-slate-100 [&:disabled:not(:checked)]:cursor-not-allowed [&:disabled:not(:checked)]:dark:bg-darkmode-800/50 [&:disabled:checked]:opacity-70 [&:disabled:checked]:cursor-not-allowed [&:disabled:checked]:dark:bg-darkmode-800/50" type="checkbox" name="fields[employees][ni_number]" value="1"> 
                        <label class= "cursor-pointer ml-2" for="s14">NI Number</label>
                    </div>
                    <input type="hidden" name="labels[ni_number]" value="NI Number">

                    <div class="singlefields relative mb-2">
                        <input id="s16" class="cus-check transition-all duration-100 ease-in-out shadow-sm border-slate-200 cursor-pointer rounded focus:ring-4 focus:ring-offset-0 focus:ring-primary focus:ring-opacity-20 dark:bg-darkmode-800 dark:border-transparent dark:focus:ring-slate-700 dark:focus:ring-opacity-50 [&[type='radio']]:checked:bg-primary [&[type='radio']]:checked:border-primary [&[type='radio']]:checked:border-opacity-10 [&[type='checkbox']]:checked:bg-primary [&[type='checkbox']]:checked:border-primary [&[type='checkbox']]:checked:border-opacity-10 [&:disabled:not(:checked)]:bg-slate-100 [&:disabled:not(:checked)]:cursor-not-allowed [&:disabled:not(:checked)]:dark:bg-darkmode-800/50 [&:disabled:checked]:opacity-70 [&:disabled:checked]:cursor-not-allowed [&:disabled:checked]:dark:bg-darkmode-800/50" type="checkbox" name="fields[employees][disability_status]" value="1"> 
                        <label class= "cursor-pointer ml-2" for="s16">Disabled</label>
                    </div>
                    <input type="hidden" name="labels[disability_status]" value="Disabled">

                    
                </div>
                <div class="col-span-3">
                    
                    <div class="singlefields relative mb-2">
                        <input id="s9" class="cus-check transition-all duration-100 ease-in-out shadow-sm border-slate-200 cursor-pointer rounded focus:ring-4 focus:ring-offset-0 focus:ring-primary focus:ring-opacity-20 dark:bg-darkmode-800 dark:border-transparent dark:focus:ring-slate-700 dark:focus:ring-opacity-50 [&[type='radio']]:checked:bg-primary [&[type='radio']]:checked:border-primary [&[type='radio']]:checked:border-opacity-10 [&[type='checkbox']]:checked:bg-primary [&[type='checkbox']]:checked:border-primary [&[type='checkbox']]:checked:border-opacity-10 [&:disabled:not(:checked)]:bg-slate-100 [&:disabled:not(:checked)]:cursor-not-allowed [&:disabled:not(:checked)]:dark:bg-darkmode-800/50 [&:disabled:checked]:opacity-70 [&:disabled:checked]:cursor-not-allowed [&:disabled:checked]:dark:bg-darkmode-800/50" type="checkbox" name="fields[employees][email]" value="1"> 
                        <label class= "cursor-pointer ml-2" for="s9">Email</label>
                    </div>
                    <input type="hidden" name="labels[email]" value="Email">

                    <div class="singlefields relative mb-2">
                        <input id="s7" class="cus-check transition-all duration-100 ease-in-out shadow-sm border-slate-200 cursor-pointer rounded focus:ring-4 focus:ring-offset-0 focus:ring-primary focus:ring-opacity-20 dark:bg-darkmode-800 dark:border-transparent dark:focus:ring-slate-700 dark:focus:ring-opacity-50 [&[type='radio']]:checked:bg-primary [&[type='radio']]:checked:border-primary [&[type='radio']]:checked:border-opacity-10 [&[type='checkbox']]:checked:bg-primary [&[type='checkbox']]:checked:border-primary [&[type='checkbox']]:checked:border-opacity-10 [&:disabled:not(:checked)]:bg-slate-100 [&:disabled:not(:checked)]:cursor-not-allowed [&:disabled:not(:checked)]:dark:bg-darkmode-800/50 [&:disabled:checked]:opacity-70 [&:disabled:checked]:cursor-not-allowed [&:disabled:checked]:dark:bg-darkmode-800/50" type="checkbox" name="fields[employees][telephone]" value="1"> 
                        <label class= "cursor-pointer ml-2" for="s7">Telephone</label>
                    </div>
                    <input type="hidden" name="labels[telephone]" value="Telophone">
                    <div class="singlefields relative mb-2">
                        <input id="s8" class="cus-check transition-all duration-100 ease-in-out shadow-sm border-slate-200 cursor-pointer rounded focus:ring-4 focus:ring-offset-0 focus:ring-primary focus:ring-opacity-20 dark:bg-darkmode-800 dark:border-transparent dark:focus:ring-slate-700 dark:focus:ring-opacity-50 [&[type='radio']]:checked:bg-primary [&[type='radio']]:checked:border-primary [&[type='radio']]:checked:border-opacity-10 [&[type='checkbox']]:checked:bg-primary [&[type='checkbox']]:checked:border-primary [&[type='checkbox']]:checked:border-opacity-10 [&:disabled:not(:checked)]:bg-slate-100 [&:disabled:not(:checked)]:cursor-not-allowed [&:disabled:not(:checked)]:dark:bg-darkmode-800/50 [&:disabled:checked]:opacity-70 [&:disabled:checked]:cursor-not-allowed [&:disabled:checked]:dark:bg-darkmode-800/50" type="checkbox" name="fields[employees][mobile]" value="1"> 
                        <label class= "cursor-pointer ml-2" for="s8">Mobile</label>
                    </div>
                    <input type="hidden" name="labels[mobile]" value="Mobile">
                    
                </div>
                <div class="col-span-3">
                    
                    <div class="singlefields relative mb-2">
                        <input id="s5" class="cus-check transition-all duration-100 ease-in-out shadow-sm border-slate-200 cursor-pointer rounded focus:ring-4 focus:ring-offset-0 focus:ring-primary focus:ring-opacity-20 dark:bg-darkmode-800 dark:border-transparent dark:focus:ring-slate-700 dark:focus:ring-opacity-50 [&[type='radio']]:checked:bg-primary [&[type='radio']]:checked:border-primary [&[type='radio']]:checked:border-opacity-10 [&[type='checkbox']]:checked:bg-primary [&[type='checkbox']]:checked:border-primary [&[type='checkbox']]:checked:border-opacity-10 [&:disabled:not(:checked)]:bg-slate-100 [&:disabled:not(:checked)]:cursor-not-allowed [&:disabled:not(:checked)]:dark:bg-darkmode-800/50 [&:disabled:checked]:opacity-70 [&:disabled:checked]:cursor-not-allowed [&:disabled:checked]:dark:bg-darkmode-800/50" type="checkbox" name="fields[employees][address_id]" value="1"> 
                        <label class= "cursor-pointer ml-2" for="s5">Address</label>
                    </div>
                    <input type="hidden" name="labels[address_id]" value="Address">
                    <div class="singlefields relative mb-2">
                        <input id="s22" class="cus-check transition-all duration-100 ease-in-out shadow-sm border-slate-200 cursor-pointer rounded focus:ring-4 focus:ring-offset-0 focus:ring-primary focus:ring-opacity-20 dark:bg-darkmode-800 dark:border-transparent dark:focus:ring-slate-700 dark:focus:ring-opacity-50 [&[type='radio']]:checked:bg-primary [&[type='radio']]:checked:border-primary [&[type='radio']]:checked:border-opacity-10 [&[type='checkbox']]:checked:bg-primary [&[type='checkbox']]:checked:border-primary [&[type='checkbox']]:checked:border-opacity-10 [&:disabled:not(:checked)]:bg-slate-100 [&:disabled:not(:checked)]:cursor-not-allowed [&:disabled:not(:checked)]:dark:bg-darkmode-800/50 [&:disabled:checked]:opacity-70 [&:disabled:checked]:cursor-not-allowed [&:disabled:checked]:dark:bg-darkmode-800/50" type="checkbox" name="fields[employees][status]" value="1"> 
                        <label class= "cursor-pointer ml-2" for="s22">Status</label>
                        <input type="hidden" name="labels[status]" value="Status">
                    </div>
                </div>
            </div>
            <div class="grid grid-cols-12 gap-4 mt-10 employeementsRows">
                <div class="col-span-12">
                    <h3 class="empDatasTitle font-medium">Employment Info <a href="javascript: void(0);" data-parent="employeementsRows" class="btn btn-sm btn-primary checkedAlls ml-1">All</a></h3>
                    <input type="hidden" name="tables[employments]" value="Employment" />
                </div>
                <div class="col-span-3">
                    
                    <div class="singlefields relative mb-2">
                        <input id="s212" class="cus-check transition-all duration-100 ease-in-out shadow-sm border-slate-200 cursor-pointer rounded focus:ring-4 focus:ring-offset-0 focus:ring-primary focus:ring-opacity-20 dark:bg-darkmode-800 dark:border-transparent dark:focus:ring-slate-700 dark:focus:ring-opacity-50 [&[type='radio']]:checked:bg-primary [&[type='radio']]:checked:border-primary [&[type='radio']]:checked:border-opacity-10 [&[type='checkbox']]:checked:bg-primary [&[type='checkbox']]:checked:border-primary [&[type='checkbox']]:checked:border-opacity-10 [&:disabled:not(:checked)]:bg-slate-100 [&:disabled:not(:checked)]:cursor-not-allowed [&:disabled:not(:checked)]:dark:bg-darkmode-800/50 [&:disabled:checked]:opacity-70 [&:disabled:checked]:cursor-not-allowed [&:disabled:checked]:dark:bg-darkmode-800/50" type="checkbox" name="fields[employments][started_on]" value="1"> 
                        <label class= "cursor-pointer ml-2" for="s212">Started On</label>
                    </div>

                    <input type="hidden" name="labels[started_on]" value="Started On">

                    <div class="singlefields relative mb-2">
                        <input id="s23" class="cus-check transition-all duration-100 ease-in-out shadow-sm border-slate-200 cursor-pointer rounded focus:ring-4 focus:ring-offset-0 focus:ring-primary focus:ring-opacity-20 dark:bg-darkmode-800 dark:border-transparent dark:focus:ring-slate-700 dark:focus:ring-opacity-50 [&[type='radio']]:checked:bg-primary [&[type='radio']]:checked:border-primary [&[type='radio']]:checked:border-opacity-10 [&[type='checkbox']]:checked:bg-primary [&[type='checkbox']]:checked:border-primary [&[type='checkbox']]:checked:border-opacity-10 [&:disabled:not(:checked)]:bg-slate-100 [&:disabled:not(:checked)]:cursor-not-allowed [&:disabled:not(:checked)]:dark:bg-darkmode-800/50 [&:disabled:checked]:opacity-70 [&:disabled:checked]:cursor-not-allowed [&:disabled:checked]:dark:bg-darkmode-800/50" type="checkbox" name="fields[employments][ended_on]" value="1"> 
                        <label class= "cursor-pointer ml-2" for="s23">Ended On</label>
                    </div>
                    <input type="hidden" name="labels[ended_on]" value="Ended On">

                    <div class="singlefields relative mb-2">
                        <input id="s27" class="cus-check transition-all duration-100 ease-in-out shadow-sm border-slate-200 cursor-pointer rounded focus:ring-4 focus:ring-offset-0 focus:ring-primary focus:ring-opacity-20 dark:bg-darkmode-800 dark:border-transparent dark:focus:ring-slate-700 dark:focus:ring-opacity-50 [&[type='radio']]:checked:bg-primary [&[type='radio']]:checked:border-primary [&[type='radio']]:checked:border-opacity-10 [&[type='checkbox']]:checked:bg-primary [&[type='checkbox']]:checked:border-primary [&[type='checkbox']]:checked:border-opacity-10 [&:disabled:not(:checked)]:bg-slate-100 [&:disabled:not(:checked)]:cursor-not-allowed [&:disabled:not(:checked)]:dark:bg-darkmode-800/50 [&:disabled:checked]:opacity-70 [&:disabled:checked]:cursor-not-allowed [&:disabled:checked]:dark:bg-darkmode-800/50" type="checkbox" name="fields[employments][employee_work_type_id]" value="1"> 
                        <label class= "cursor-pointer ml-2" for="s27">Type</label>
                    </div>
                    <input type="hidden" name="labels[employee_work_type_id]" value="Type">
                    
                </div>
                <div class="col-span-3">
                    <div class="singlefields relative mb-2">
                        <input id="s24" class="cus-check transition-all duration-100 ease-in-out shadow-sm border-slate-200 cursor-pointer rounded focus:ring-4 focus:ring-offset-0 focus:ring-primary focus:ring-opacity-20 dark:bg-darkmode-800 dark:border-transparent dark:focus:ring-slate-700 dark:focus:ring-opacity-50 [&[type='radio']]:checked:bg-primary [&[type='radio']]:checked:border-primary [&[type='radio']]:checked:border-opacity-10 [&[type='checkbox']]:checked:bg-primary [&[type='checkbox']]:checked:border-primary [&[type='checkbox']]:checked:border-opacity-10 [&:disabled:not(:checked)]:bg-slate-100 [&:disabled:not(:checked)]:cursor-not-allowed [&:disabled:not(:checked)]:dark:bg-darkmode-800/50 [&:disabled:checked]:opacity-70 [&:disabled:checked]:cursor-not-allowed [&:disabled:checked]:dark:bg-darkmode-800/50" type="checkbox" name="fields[employments][works_number]" value="1"> 
                        <label class= "cursor-pointer ml-2" for="s24">Works Number</label>
                    </div>
                    <input type="hidden" name="labels[works_number]" value="Works Number">

                    <div class="singlefields relative mb-2">
                        <input id="s25" class="cus-check transition-all duration-100 ease-in-out shadow-sm border-slate-200 cursor-pointer rounded focus:ring-4 focus:ring-offset-0 focus:ring-primary focus:ring-opacity-20 dark:bg-darkmode-800 dark:border-transparent dark:focus:ring-slate-700 dark:focus:ring-opacity-50 [&[type='radio']]:checked:bg-primary [&[type='radio']]:checked:border-primary [&[type='radio']]:checked:border-opacity-10 [&[type='checkbox']]:checked:bg-primary [&[type='checkbox']]:checked:border-primary [&[type='checkbox']]:checked:border-opacity-10 [&:disabled:not(:checked)]:bg-slate-100 [&:disabled:not(:checked)]:cursor-not-allowed [&:disabled:not(:checked)]:dark:bg-darkmode-800/50 [&:disabled:checked]:opacity-70 [&:disabled:checked]:cursor-not-allowed [&:disabled:checked]:dark:bg-darkmode-800/50" type="checkbox" name="fields[employments][punch_number]" value="1"> 
                        <label class= "cursor-pointer ml-2" for="s25">Punch Number</label>
                    </div>
                    <input type="hidden" name="labels[punch_number]" value="Clock Number">
                    
                    <div class="singlefields relative mb-2">
                        <input id="s28" class="cus-check transition-all duration-100 ease-in-out shadow-sm border-slate-200 cursor-pointer rounded focus:ring-4 focus:ring-offset-0 focus:ring-primary focus:ring-opacity-20 dark:bg-darkmode-800 dark:border-transparent dark:focus:ring-slate-700 dark:focus:ring-opacity-50 [&[type='radio']]:checked:bg-primary [&[type='radio']]:checked:border-primary [&[type='radio']]:checked:border-opacity-10 [&[type='checkbox']]:checked:bg-primary [&[type='checkbox']]:checked:border-primary [&[type='checkbox']]:checked:border-opacity-10 [&:disabled:not(:checked)]:bg-slate-100 [&:disabled:not(:checked)]:cursor-not-allowed [&:disabled:not(:checked)]:dark:bg-darkmode-800/50 [&:disabled:checked]:opacity-70 [&:disabled:checked]:cursor-not-allowed [&:disabled:checked]:dark:bg-darkmode-800/50" type="checkbox" name="fields[employments][employee_job_title_id]" value="1"> 
                        <label class= "cursor-pointer ml-2" for="s28">Job Title</label>
                    </div>
                    <input type="hidden" name="labels[employee_job_title_id]" value="Job Title">

                </div>
                <div class="col-span-3">
                    <div class="singlefields relative mb-2">
                        <input id="s32" class="cus-check transition-all duration-100 ease-in-out shadow-sm border-slate-200 cursor-pointer rounded focus:ring-4 focus:ring-offset-0 focus:ring-primary focus:ring-opacity-20 dark:bg-darkmode-800 dark:border-transparent dark:focus:ring-slate-700 dark:focus:ring-opacity-50 [&[type='radio']]:checked:bg-primary [&[type='radio']]:checked:border-primary [&[type='radio']]:checked:border-opacity-10 [&[type='checkbox']]:checked:bg-primary [&[type='checkbox']]:checked:border-primary [&[type='checkbox']]:checked:border-opacity-10 [&:disabled:not(:checked)]:bg-slate-100 [&:disabled:not(:checked)]:cursor-not-allowed [&:disabled:not(:checked)]:dark:bg-darkmode-800/50 [&:disabled:checked]:opacity-70 [&:disabled:checked]:cursor-not-allowed [&:disabled:checked]:dark:bg-darkmode-800/50" type="checkbox" name="fields[employments][department_id]" value="1"> 
                        <label class= "cursor-pointer ml-2" for="s32">Department</label>
                    </div>
                    <input type="hidden" name="labels[department_id]" value="Department">

                    <div class="singlefields relative mb-2">
                        <input id="s26" class="cus-check transition-all duration-100 ease-in-out shadow-sm border-slate-200 cursor-pointer rounded focus:ring-4 focus:ring-offset-0 focus:ring-primary focus:ring-opacity-20 dark:bg-darkmode-800 dark:border-transparent dark:focus:ring-slate-700 dark:focus:ring-opacity-50 [&[type='radio']]:checked:bg-primary [&[type='radio']]:checked:border-primary [&[type='radio']]:checked:border-opacity-10 [&[type='checkbox']]:checked:bg-primary [&[type='checkbox']]:checked:border-primary [&[type='checkbox']]:checked:border-opacity-10 [&:disabled:not(:checked)]:bg-slate-100 [&:disabled:not(:checked)]:cursor-not-allowed [&:disabled:not(:checked)]:dark:bg-darkmode-800/50 [&:disabled:checked]:opacity-70 [&:disabled:checked]:cursor-not-allowed [&:disabled:checked]:dark:bg-darkmode-800/50" type="checkbox" name="fields[employments][site_location]" value="1"> 
                        <label class= "cursor-pointer ml-2" for="s26">Site Location</label>
                    </div>
                    <input type="hidden" name="labels[site_location]" value="Site Location">
                </div>
                <div class="col-span-3">
                    
                    
                    <div class="singlefields relative mb-2">
                        <input id="s35" class="cus-check transition-all duration-100 ease-in-out shadow-sm border-slate-200 cursor-pointer rounded focus:ring-4 focus:ring-offset-0 focus:ring-primary focus:ring-opacity-20 dark:bg-darkmode-800 dark:border-transparent dark:focus:ring-slate-700 dark:focus:ring-opacity-50 [&[type='radio']]:checked:bg-primary [&[type='radio']]:checked:border-primary [&[type='radio']]:checked:border-opacity-10 [&[type='checkbox']]:checked:bg-primary [&[type='checkbox']]:checked:border-primary [&[type='checkbox']]:checked:border-opacity-10 [&:disabled:not(:checked)]:bg-slate-100 [&:disabled:not(:checked)]:cursor-not-allowed [&:disabled:not(:checked)]:dark:bg-darkmode-800/50 [&:disabled:checked]:opacity-70 [&:disabled:checked]:cursor-not-allowed [&:disabled:checked]:dark:bg-darkmode-800/50" type="checkbox" name="fields[employments][office_telephone]" value="1"> 
                        <label class= "cursor-pointer ml-2" for="s35">Office Telephone</label>
                    </div>
                    <input type="hidden" name="labels[office_telephone]" value="O. Telephone">
                    <div class="singlefields relative mb-2">
                        <input id="s36" class="cus-check transition-all duration-100 ease-in-out shadow-sm border-slate-200 cursor-pointer rounded focus:ring-4 focus:ring-offset-0 focus:ring-primary focus:ring-opacity-20 dark:bg-darkmode-800 dark:border-transparent dark:focus:ring-slate-700 dark:focus:ring-opacity-50 [&[type='radio']]:checked:bg-primary [&[type='radio']]:checked:border-primary [&[type='radio']]:checked:border-opacity-10 [&[type='checkbox']]:checked:bg-primary [&[type='checkbox']]:checked:border-primary [&[type='checkbox']]:checked:border-opacity-10 [&:disabled:not(:checked)]:bg-slate-100 [&:disabled:not(:checked)]:cursor-not-allowed [&:disabled:not(:checked)]:dark:bg-darkmode-800/50 [&:disabled:checked]:opacity-70 [&:disabled:checked]:cursor-not-allowed [&:disabled:checked]:dark:bg-darkmode-800/50" type="checkbox" name="fields[employments][office_mobile]" value="1"> 
                        <label class= "cursor-pointer ml-2" for="s36">Office Mobile</label>
                    </div>
                    <input type="hidden" name="labels[office_mobile]" value="O. Mobile">
                    <div class="singlefields relative mb-2">
                        <input id="s37" class="cus-check transition-all duration-100 ease-in-out shadow-sm border-slate-200 cursor-pointer rounded focus:ring-4 focus:ring-offset-0 focus:ring-primary focus:ring-opacity-20 dark:bg-darkmode-800 dark:border-transparent dark:focus:ring-slate-700 dark:focus:ring-opacity-50 [&[type='radio']]:checked:bg-primary [&[type='radio']]:checked:border-primary [&[type='radio']]:checked:border-opacity-10 [&[type='checkbox']]:checked:bg-primary [&[type='checkbox']]:checked:border-primary [&[type='checkbox']]:checked:border-opacity-10 [&:disabled:not(:checked)]:bg-slate-100 [&:disabled:not(:checked)]:cursor-not-allowed [&:disabled:not(:checked)]:dark:bg-darkmode-800/50 [&:disabled:checked]:opacity-70 [&:disabled:checked]:cursor-not-allowed [&:disabled:checked]:dark:bg-darkmode-800/50" type="checkbox" name="fields[employments][office_email]" value="1"> 
                        <label class= "cursor-pointer ml-2" for="s37">Email</label>
                    </div>
                    <input type="hidden" name="labels[office_email]" value="O. Email">
                </div>
            </div>
            
            <div class="grid grid-cols-12 gap-4 mt-10 elegibilityRow">
                <div class="col-span-12">
                    <h3 class="empDatasTitle font-medium">Eligibility <a href="javascript: void(0);" data-parent="elegibilityRow" class="btn btn-sm btn-primary checkedAlls ml-1">All</a></h3>
                    <input type="hidden" name="tables[employee_eligibilites]" value="EmployeeEligibilites" />
                </div>
                <div class="col-span-3">
                    <div class="singlefields relative mb-2">
                        <input id="s57" class="cus-check transition-all duration-100 ease-in-out shadow-sm border-slate-200 cursor-pointer rounded focus:ring-4 focus:ring-offset-0 focus:ring-primary focus:ring-opacity-20 dark:bg-darkmode-800 dark:border-transparent dark:focus:ring-slate-700 dark:focus:ring-opacity-50 [&[type='radio']]:checked:bg-primary [&[type='radio']]:checked:border-primary [&[type='radio']]:checked:border-opacity-10 [&[type='checkbox']]:checked:bg-primary [&[type='checkbox']]:checked:border-primary [&[type='checkbox']]:checked:border-opacity-10 [&:disabled:not(:checked)]:bg-slate-100 [&:disabled:not(:checked)]:cursor-not-allowed [&:disabled:not(:checked)]:dark:bg-darkmode-800/50 [&:disabled:checked]:opacity-70 [&:disabled:checked]:cursor-not-allowed [&:disabled:checked]:dark:bg-darkmode-800/50" type="checkbox" name="fields[employee_eligibilites][eligible_to_work]" value="1"> 
                        <label class= "cursor-pointer ml-2" for="s57">Eligible to work in UK</label>
                    </div>
                    <input type="hidden" name="labels[eligible_to_work]" value="Eligible UK">

                    <div class="singlefields relative mb-2">
                        <input id="s07" class="cus-check transition-all duration-100 ease-in-out shadow-sm border-slate-200 cursor-pointer rounded focus:ring-4 focus:ring-offset-0 focus:ring-primary focus:ring-opacity-20 dark:bg-darkmode-800 dark:border-transparent dark:focus:ring-slate-700 dark:focus:ring-opacity-50 [&[type='radio']]:checked:bg-primary [&[type='radio']]:checked:border-primary [&[type='radio']]:checked:border-opacity-10 [&[type='checkbox']]:checked:bg-primary [&[type='checkbox']]:checked:border-primary [&[type='checkbox']]:checked:border-opacity-10 [&:disabled:not(:checked)]:bg-slate-100 [&:disabled:not(:checked)]:cursor-not-allowed [&:disabled:not(:checked)]:dark:bg-darkmode-800/50 [&:disabled:checked]:opacity-70 [&:disabled:checked]:cursor-not-allowed [&:disabled:checked]:dark:bg-darkmode-800/50" type="checkbox" name="fields[employee_eligibilites][employee_work_permit_type_id]" value="1"> 
                        <label class= "cursor-pointer ml-2" for="s07">Permit Type</label>
                    </div>
                    <input type="hidden" name="labels[employee_work_permit_type_id]" value="Permit Type">

                    <div class="singlefields relative mb-2">
                        <input id="s58" class="cus-check transition-all duration-100 ease-in-out shadow-sm border-slate-200 cursor-pointer rounded focus:ring-4 focus:ring-offset-0 focus:ring-primary focus:ring-opacity-20 dark:bg-darkmode-800 dark:border-transparent dark:focus:ring-slate-700 dark:focus:ring-opacity-50 [&[type='radio']]:checked:bg-primary [&[type='radio']]:checked:border-primary [&[type='radio']]:checked:border-opacity-10 [&[type='checkbox']]:checked:bg-primary [&[type='checkbox']]:checked:border-primary [&[type='checkbox']]:checked:border-opacity-10 [&:disabled:not(:checked)]:bg-slate-100 [&:disabled:not(:checked)]:cursor-not-allowed [&:disabled:not(:checked)]:dark:bg-darkmode-800/50 [&:disabled:checked]:opacity-70 [&:disabled:checked]:cursor-not-allowed [&:disabled:checked]:dark:bg-darkmode-800/50" type="checkbox" name="fields[employee_eligibilites][workpermit_number]" value="1"> 
                        <label class= "cursor-pointer ml-2" for="s58">Work Permit Number</label>
                    </div>
                    <input type="hidden" name="labels[workpermit_number]" value="Permit Number">
                </div>
                <div class="col-span-3">
                    <div class="singlefields relative mb-2">
                        <input id="s59" class="cus-check transition-all duration-100 ease-in-out shadow-sm border-slate-200 cursor-pointer rounded focus:ring-4 focus:ring-offset-0 focus:ring-primary focus:ring-opacity-20 dark:bg-darkmode-800 dark:border-transparent dark:focus:ring-slate-700 dark:focus:ring-opacity-50 [&[type='radio']]:checked:bg-primary [&[type='radio']]:checked:border-primary [&[type='radio']]:checked:border-opacity-10 [&[type='checkbox']]:checked:bg-primary [&[type='checkbox']]:checked:border-primary [&[type='checkbox']]:checked:border-opacity-10 [&:disabled:not(:checked)]:bg-slate-100 [&:disabled:not(:checked)]:cursor-not-allowed [&:disabled:not(:checked)]:dark:bg-darkmode-800/50 [&:disabled:checked]:opacity-70 [&:disabled:checked]:cursor-not-allowed [&:disabled:checked]:dark:bg-darkmode-800/50" type="checkbox" name="fields[employee_eligibilites][workpermit_expire]" value="1"> 
                        <label class= "cursor-pointer ml-2" for="s59">Work Permit Expire Date</label>
                    </div>
                    <input type="hidden" name="labels[workpermit_expire]" value="Work Permit Expire Date">
                    <div class="singlefields relative mb-2">
                        <input id="s60" class="cus-check transition-all duration-100 ease-in-out shadow-sm border-slate-200 cursor-pointer rounded focus:ring-4 focus:ring-offset-0 focus:ring-primary focus:ring-opacity-20 dark:bg-darkmode-800 dark:border-transparent dark:focus:ring-slate-700 dark:focus:ring-opacity-50 [&[type='radio']]:checked:bg-primary [&[type='radio']]:checked:border-primary [&[type='radio']]:checked:border-opacity-10 [&[type='checkbox']]:checked:bg-primary [&[type='checkbox']]:checked:border-primary [&[type='checkbox']]:checked:border-opacity-10 [&:disabled:not(:checked)]:bg-slate-100 [&:disabled:not(:checked)]:cursor-not-allowed [&:disabled:not(:checked)]:dark:bg-darkmode-800/50 [&:disabled:checked]:opacity-70 [&:disabled:checked]:cursor-not-allowed [&:disabled:checked]:dark:bg-darkmode-800/50" type="checkbox" name="fields[employee_eligibilites][document_type]" value="1"> 
                        <label class= "cursor-pointer ml-2" for="s60">Proof of ID Type</label>
                    </div>
                    <input type="hidden" name="labels[document_type]" value="Proof of ID Type">
                </div>
                <div class="col-span-3">
                    <div class="singlefields relative mb-2">
                        <input id="s61" class="cus-check transition-all duration-100 ease-in-out shadow-sm border-slate-200 cursor-pointer rounded focus:ring-4 focus:ring-offset-0 focus:ring-primary focus:ring-opacity-20 dark:bg-darkmode-800 dark:border-transparent dark:focus:ring-slate-700 dark:focus:ring-opacity-50 [&[type='radio']]:checked:bg-primary [&[type='radio']]:checked:border-primary [&[type='radio']]:checked:border-opacity-10 [&[type='checkbox']]:checked:bg-primary [&[type='checkbox']]:checked:border-primary [&[type='checkbox']]:checked:border-opacity-10 [&:disabled:not(:checked)]:bg-slate-100 [&:disabled:not(:checked)]:cursor-not-allowed [&:disabled:not(:checked)]:dark:bg-darkmode-800/50 [&:disabled:checked]:opacity-70 [&:disabled:checked]:cursor-not-allowed [&:disabled:checked]:dark:bg-darkmode-800/50" type="checkbox" name="fields[employee_eligibilites][doc_number]" value="1"> 
                        <label class= "cursor-pointer ml-2" for="s61">ID Number</label>
                    </div>
                    <input type="hidden" name="labels[doc_number]" value="ID Number">
                </div>
                <div class="col-span-3">
                    <div class="singlefields relative mb-2">
                        <input id="s62" class="cus-check transition-all duration-100 ease-in-out shadow-sm border-slate-200 cursor-pointer rounded focus:ring-4 focus:ring-offset-0 focus:ring-primary focus:ring-opacity-20 dark:bg-darkmode-800 dark:border-transparent dark:focus:ring-slate-700 dark:focus:ring-opacity-50 [&[type='radio']]:checked:bg-primary [&[type='radio']]:checked:border-primary [&[type='radio']]:checked:border-opacity-10 [&[type='checkbox']]:checked:bg-primary [&[type='checkbox']]:checked:border-primary [&[type='checkbox']]:checked:border-opacity-10 [&:disabled:not(:checked)]:bg-slate-100 [&:disabled:not(:checked)]:cursor-not-allowed [&:disabled:not(:checked)]:dark:bg-darkmode-800/50 [&:disabled:checked]:opacity-70 [&:disabled:checked]:cursor-not-allowed [&:disabled:checked]:dark:bg-darkmode-800/50" type="checkbox" name="fields[employee_eligibilites][doc_expire]" value="1"> 
                        <label class= "cursor-pointer ml-2" for="s62">Expiry Date</label>
                    </div>
                    <input type="hidden" name="labels[doc_expire]" value="Expiry Date">
                    <div class="singlefields relative mb-2">
                        <input id="s02" class="cus-check transition-all duration-100 ease-in-out shadow-sm border-slate-200 cursor-pointer rounded focus:ring-4 focus:ring-offset-0 focus:ring-primary focus:ring-opacity-20 dark:bg-darkmode-800 dark:border-transparent dark:focus:ring-slate-700 dark:focus:ring-opacity-50 [&[type='radio']]:checked:bg-primary [&[type='radio']]:checked:border-primary [&[type='radio']]:checked:border-opacity-10 [&[type='checkbox']]:checked:bg-primary [&[type='checkbox']]:checked:border-primary [&[type='checkbox']]:checked:border-opacity-10 [&:disabled:not(:checked)]:bg-slate-100 [&:disabled:not(:checked)]:cursor-not-allowed [&:disabled:not(:checked)]:dark:bg-darkmode-800/50 [&:disabled:checked]:opacity-70 [&:disabled:checked]:cursor-not-allowed [&:disabled:checked]:dark:bg-darkmode-800/50" type="checkbox" name="fields[employee_eligibilites][doc_issue_country]" value="1"> 
                        <label class= "cursor-pointer ml-2" for="s02">Issuing Country</label>
                    </div>
                    <input type="hidden" name="labels[doc_issue_country]" value="Issuing Country">
                </div>
                
            </div>
            <div class="grid grid-cols-12 gap-4 mt-10 emmContactRow">
                <div class="col-span-12">
                    <h3 class="empDatasTitle font-medium">Emergency Contacts <a href="javascript: void(0);" data-parent="emmContactRow" class="btn btn-sm btn-primary checkedAlls ml-1">All</a></h3>
                    <input type="hidden" name="tables[employee_emergency_contacts]" value="EmployeeEmergencyContact" />
                </div>
                <div class="col-span-3">
                    <div class="singlefields relative mb-2">
                        <input id="s64" class="cus-check transition-all duration-100 ease-in-out shadow-sm border-slate-200 cursor-pointer rounded focus:ring-4 focus:ring-offset-0 focus:ring-primary focus:ring-opacity-20 dark:bg-darkmode-800 dark:border-transparent dark:focus:ring-slate-700 dark:focus:ring-opacity-50 [&[type='radio']]:checked:bg-primary [&[type='radio']]:checked:border-primary [&[type='radio']]:checked:border-opacity-10 [&[type='checkbox']]:checked:bg-primary [&[type='checkbox']]:checked:border-primary [&[type='checkbox']]:checked:border-opacity-10 [&:disabled:not(:checked)]:bg-slate-100 [&:disabled:not(:checked)]:cursor-not-allowed [&:disabled:not(:checked)]:dark:bg-darkmode-800/50 [&:disabled:checked]:opacity-70 [&:disabled:checked]:cursor-not-allowed [&:disabled:checked]:dark:bg-darkmode-800/50" type="checkbox" name="fields[employee_emergency_contacts][emergency_contact_name]" value="1"> 
                        <label class= "cursor-pointer ml-2" for="s64">Emergency Contact Name</label>
                        <input type="hidden" name="labels[emergency_contact_name]" value="Emergency Contact Name">
                    </div>
                    <div class="singlefields relative mb-2">
                        <input id="s65" class="cus-check transition-all duration-100 ease-in-out shadow-sm border-slate-200 cursor-pointer rounded focus:ring-4 focus:ring-offset-0 focus:ring-primary focus:ring-opacity-20 dark:bg-darkmode-800 dark:border-transparent dark:focus:ring-slate-700 dark:focus:ring-opacity-50 [&[type='radio']]:checked:bg-primary [&[type='radio']]:checked:border-primary [&[type='radio']]:checked:border-opacity-10 [&[type='checkbox']]:checked:bg-primary [&[type='checkbox']]:checked:border-primary [&[type='checkbox']]:checked:border-opacity-10 [&:disabled:not(:checked)]:bg-slate-100 [&:disabled:not(:checked)]:cursor-not-allowed [&:disabled:not(:checked)]:dark:bg-darkmode-800/50 [&:disabled:checked]:opacity-70 [&:disabled:checked]:cursor-not-allowed [&:disabled:checked]:dark:bg-darkmode-800/50" type="checkbox" name="fields[employee_emergency_contacts][kins_relation_id]" value="1"> 
                        <label class= "cursor-pointer ml-2" for="s65">Kin Relation</label>
                        <input type="hidden" name="labels[kins_relation_id]" value="Emergency Contact Relation">
                    </div>
                </div>
                <div class="col-span-3">
                    <div class="singlefields relative mb-2">
                        <input id="s66" class="cus-check transition-all duration-100 ease-in-out shadow-sm border-slate-200 cursor-pointer rounded focus:ring-4 focus:ring-offset-0 focus:ring-primary focus:ring-opacity-20 dark:bg-darkmode-800 dark:border-transparent dark:focus:ring-slate-700 dark:focus:ring-opacity-50 [&[type='radio']]:checked:bg-primary [&[type='radio']]:checked:border-primary [&[type='radio']]:checked:border-opacity-10 [&[type='checkbox']]:checked:bg-primary [&[type='checkbox']]:checked:border-primary [&[type='checkbox']]:checked:border-opacity-10 [&:disabled:not(:checked)]:bg-slate-100 [&:disabled:not(:checked)]:cursor-not-allowed [&:disabled:not(:checked)]:dark:bg-darkmode-800/50 [&:disabled:checked]:opacity-70 [&:disabled:checked]:cursor-not-allowed [&:disabled:checked]:dark:bg-darkmode-800/50" type="checkbox" name="fields[employee_emergency_contacts][emergency_contact_telephone]" value="1"> 
                        <label class= "cursor-pointer ml-2" for="s66">Telephone</label>
                        <input type="hidden" name="labels[emergency_contact_telephone]" value="Emergency Contact Telephone">
                    </div>
                    <div class="singlefields relative mb-2">
                        <input id="s67" class="cus-check transition-all duration-100 ease-in-out shadow-sm border-slate-200 cursor-pointer rounded focus:ring-4 focus:ring-offset-0 focus:ring-primary focus:ring-opacity-20 dark:bg-darkmode-800 dark:border-transparent dark:focus:ring-slate-700 dark:focus:ring-opacity-50 [&[type='radio']]:checked:bg-primary [&[type='radio']]:checked:border-primary [&[type='radio']]:checked:border-opacity-10 [&[type='checkbox']]:checked:bg-primary [&[type='checkbox']]:checked:border-primary [&[type='checkbox']]:checked:border-opacity-10 [&:disabled:not(:checked)]:bg-slate-100 [&:disabled:not(:checked)]:cursor-not-allowed [&:disabled:not(:checked)]:dark:bg-darkmode-800/50 [&:disabled:checked]:opacity-70 [&:disabled:checked]:cursor-not-allowed [&:disabled:checked]:dark:bg-darkmode-800/50" type="checkbox" name="fields[employee_emergency_contacts][emergency_contact_mobile]" value="1"> 
                        <label class= "cursor-pointer ml-2" for="s67">Mobile</label>
                        <input type="hidden" name="labels[emergency_contact_mobile]" value="Emergency Contact Mobile">
                    </div>
                    
                </div>
                <div class="col-span-3">
                    
                    <div class="singlefields relative mb-2">
                        <input id="s68" class="cus-check transition-all duration-100 ease-in-out shadow-sm border-slate-200 cursor-pointer rounded focus:ring-4 focus:ring-offset-0 focus:ring-primary focus:ring-opacity-20 dark:bg-darkmode-800 dark:border-transparent dark:focus:ring-slate-700 dark:focus:ring-opacity-50 [&[type='radio']]:checked:bg-primary [&[type='radio']]:checked:border-primary [&[type='radio']]:checked:border-opacity-10 [&[type='checkbox']]:checked:bg-primary [&[type='checkbox']]:checked:border-primary [&[type='checkbox']]:checked:border-opacity-10 [&:disabled:not(:checked)]:bg-slate-100 [&:disabled:not(:checked)]:cursor-not-allowed [&:disabled:not(:checked)]:dark:bg-darkmode-800/50 [&:disabled:checked]:opacity-70 [&:disabled:checked]:cursor-not-allowed [&:disabled:checked]:dark:bg-darkmode-800/50" type="checkbox" name="fields[employee_emergency_contacts][emergency_contact_email]" value="1"> 
                        <label class= "cursor-pointer ml-2" for="s68">Email</label>
                        <input type="hidden" name="labels[emergency_contact_email]" value="Emergency Contact Email">
                    </div>
                    
                    <div class="singlefields relative mb-2">
                        <input id="s69" class="cus-check transition-all duration-100 ease-in-out shadow-sm border-slate-200 cursor-pointer rounded focus:ring-4 focus:ring-offset-0 focus:ring-primary focus:ring-opacity-20 dark:bg-darkmode-800 dark:border-transparent dark:focus:ring-slate-700 dark:focus:ring-opacity-50 [&[type='radio']]:checked:bg-primary [&[type='radio']]:checked:border-primary [&[type='radio']]:checked:border-opacity-10 [&[type='checkbox']]:checked:bg-primary [&[type='checkbox']]:checked:border-primary [&[type='checkbox']]:checked:border-opacity-10 [&:disabled:not(:checked)]:bg-slate-100 [&:disabled:not(:checked)]:cursor-not-allowed [&:disabled:not(:checked)]:dark:bg-darkmode-800/50 [&:disabled:checked]:opacity-70 [&:disabled:checked]:cursor-not-allowed [&:disabled:checked]:dark:bg-darkmode-800/50" type="checkbox" name="fields[employee_emergency_contacts][emergency_address_id]" value="1"> 
                        <label class= "cursor-pointer ml-2" for="s69">Address</label>
                        <input type="hidden" name="labels[emergency_address_id]" value="Emergency Contact Address">
                    </div>
                </div>
            </div> 
            <div class="grid grid-cols-12 gap-4 mt-10 banksRows">
                <div class="col-span-12">
                    <h3 class="empDatasTitle font-medium">Payments Info <a href="javascript: void(0);" data-parent="banksRows" class="btn btn-sm btn-primary checkedAlls ml-1">All</a></h3>
                    <input type="hidden" name="tables[employee_payment_settings]" value="EmployeePaymentSetting" />
                </div>
                <div class="col-span-3">
                    <div class="singlefields relative mb-2">
                        <input id="s46" class="cus-check transition-all duration-100 ease-in-out shadow-sm border-slate-200 cursor-pointer rounded focus:ring-4 focus:ring-offset-0 focus:ring-primary focus:ring-opacity-20 dark:bg-darkmode-800 dark:border-transparent dark:focus:ring-slate-700 dark:focus:ring-opacity-50 [&[type='radio']]:checked:bg-primary [&[type='radio']]:checked:border-primary [&[type='radio']]:checked:border-opacity-10 [&[type='checkbox']]:checked:bg-primary [&[type='checkbox']]:checked:border-primary [&[type='checkbox']]:checked:border-opacity-10 [&:disabled:not(:checked)]:bg-slate-100 [&:disabled:not(:checked)]:cursor-not-allowed [&:disabled:not(:checked)]:dark:bg-darkmode-800/50 [&:disabled:checked]:opacity-70 [&:disabled:checked]:cursor-not-allowed [&:disabled:checked]:dark:bg-darkmode-800/50" type="checkbox" name="fields[employee_payment_settings][pay_frequency]" value="1"> 
                        <label class= "cursor-pointer ml-2" for="s46">Pay Frequency</label>
                    </div>
                    <input type="hidden" name="labels[pay_frequency]" value="Pay Frequency">
                    <div class="singlefields relative mb-2">
                        <input id="s47" class="cus-check transition-all duration-100 ease-in-out shadow-sm border-slate-200 cursor-pointer rounded focus:ring-4 focus:ring-offset-0 focus:ring-primary focus:ring-opacity-20 dark:bg-darkmode-800 dark:border-transparent dark:focus:ring-slate-700 dark:focus:ring-opacity-50 [&[type='radio']]:checked:bg-primary [&[type='radio']]:checked:border-primary [&[type='radio']]:checked:border-opacity-10 [&[type='checkbox']]:checked:bg-primary [&[type='checkbox']]:checked:border-primary [&[type='checkbox']]:checked:border-opacity-10 [&:disabled:not(:checked)]:bg-slate-100 [&:disabled:not(:checked)]:cursor-not-allowed [&:disabled:not(:checked)]:dark:bg-darkmode-800/50 [&:disabled:checked]:opacity-70 [&:disabled:checked]:cursor-not-allowed [&:disabled:checked]:dark:bg-darkmode-800/50" type="checkbox" name="fields[employee_payment_settings][tax_code]" value="1"> 
                        <label class= "cursor-pointer ml-2" for="s47">Tax Code</label>
                    </div>
                    <input type="hidden" name="labels[tax_code]" value="Tax Code">
                    <div class="singlefields relative mb-2">
                        <input id="s48" class="cus-check transition-all duration-100 ease-in-out shadow-sm border-slate-200 cursor-pointer rounded focus:ring-4 focus:ring-offset-0 focus:ring-primary focus:ring-opacity-20 dark:bg-darkmode-800 dark:border-transparent dark:focus:ring-slate-700 dark:focus:ring-opacity-50 [&[type='radio']]:checked:bg-primary [&[type='radio']]:checked:border-primary [&[type='radio']]:checked:border-opacity-10 [&[type='checkbox']]:checked:bg-primary [&[type='checkbox']]:checked:border-primary [&[type='checkbox']]:checked:border-opacity-10 [&:disabled:not(:checked)]:bg-slate-100 [&:disabled:not(:checked)]:cursor-not-allowed [&:disabled:not(:checked)]:dark:bg-darkmode-800/50 [&:disabled:checked]:opacity-70 [&:disabled:checked]:cursor-not-allowed [&:disabled:checked]:dark:bg-darkmode-800/50" type="checkbox" name="fields[employee_payment_settings][payment_method]" value="1"> 
                        <label class= "cursor-pointer ml-2" for="s48">Payment Method</label>
                    </div>
                    <input type="hidden" name="labels[payment_method]" value="Payment Method">
                </div>
                <div class="col-span-3">
                    <input type="hidden" name="tables[employee_bank_details]" value="EmployeeBankDetail" />
                    <div class="singlefields relative mb-2">
                        <input id="s52" class="cus-check transition-all duration-100 ease-in-out shadow-sm border-slate-200 cursor-pointer rounded focus:ring-4 focus:ring-offset-0 focus:ring-primary focus:ring-opacity-20 dark:bg-darkmode-800 dark:border-transparent dark:focus:ring-slate-700 dark:focus:ring-opacity-50 [&[type='radio']]:checked:bg-primary [&[type='radio']]:checked:border-primary [&[type='radio']]:checked:border-opacity-10 [&[type='checkbox']]:checked:bg-primary [&[type='checkbox']]:checked:border-primary [&[type='checkbox']]:checked:border-opacity-10 [&:disabled:not(:checked)]:bg-slate-100 [&:disabled:not(:checked)]:cursor-not-allowed [&:disabled:not(:checked)]:dark:bg-darkmode-800/50 [&:disabled:checked]:opacity-70 [&:disabled:checked]:cursor-not-allowed [&:disabled:checked]:dark:bg-darkmode-800/50" type="checkbox" name="fields[employee_bank_details][sort_code]" value="1"> 
                        <label class= "cursor-pointer ml-2" for="s52">Sort Code</label>
                        <input type="hidden" name="labels[sort_code]" value="Sort Code">
                    </div>
                    <div class="singlefields relative mb-2">
                        <input id="s55" class="cus-check transition-all duration-100 ease-in-out shadow-sm border-slate-200 cursor-pointer rounded focus:ring-4 focus:ring-offset-0 focus:ring-primary focus:ring-opacity-20 dark:bg-darkmode-800 dark:border-transparent dark:focus:ring-slate-700 dark:focus:ring-opacity-50 [&[type='radio']]:checked:bg-primary [&[type='radio']]:checked:border-primary [&[type='radio']]:checked:border-opacity-10 [&[type='checkbox']]:checked:bg-primary [&[type='checkbox']]:checked:border-primary [&[type='checkbox']]:checked:border-opacity-10 [&:disabled:not(:checked)]:bg-slate-100 [&:disabled:not(:checked)]:cursor-not-allowed [&:disabled:not(:checked)]:dark:bg-darkmode-800/50 [&:disabled:checked]:opacity-70 [&:disabled:checked]:cursor-not-allowed [&:disabled:checked]:dark:bg-darkmode-800/50" type="checkbox" name="fields[employee_bank_details][ac_no]" value="1"> 
                        <label class= "cursor-pointer ml-2" for="s55">Account Number</label>
                    </div>
                    <input type="hidden" name="labels[ac_no]" value="Account Number">
                </div>
            </div>
            <div class="grid grid-cols-12 gap-4 mt-10 workingpatternsrows">
                <div class="col-span-12">
                    <h3 class="empDatasTitle font-medium">Working Patterns <a href="javascript: void(0);" data-parent="workingpatternsrows" class="btn btn-sm btn-primary checkedAlls ml-1">All</a></h3>
                    <input type="hidden" name="tables[employee_working_patterns]" value="EmployeeWorkingPattern" />
                </div>
                <div class="col-span-3">
                    <div class="singlefields relative mb-2">
                        <input id="s70" class="cus-check transition-all duration-100 ease-in-out shadow-sm border-slate-200 cursor-pointer rounded focus:ring-4 focus:ring-offset-0 focus:ring-primary focus:ring-opacity-20 dark:bg-darkmode-800 dark:border-transparent dark:focus:ring-slate-700 dark:focus:ring-opacity-50 [&[type='radio']]:checked:bg-primary [&[type='radio']]:checked:border-primary [&[type='radio']]:checked:border-opacity-10 [&[type='checkbox']]:checked:bg-primary [&[type='checkbox']]:checked:border-primary [&[type='checkbox']]:checked:border-opacity-10 [&:disabled:not(:checked)]:bg-slate-100 [&:disabled:not(:checked)]:cursor-not-allowed [&:disabled:not(:checked)]:dark:bg-darkmode-800/50 [&:disabled:checked]:opacity-70 [&:disabled:checked]:cursor-not-allowed [&:disabled:checked]:dark:bg-darkmode-800/50" type="checkbox" name="fields[employee_working_patterns][effective_from]" value="1"> 
                        <label class= "cursor-pointer ml-2" for="s70">Effective From</label>
                    </div>
                    <input type="hidden" name="labels[effective_from]" value="Working Pattern Effective From">
                </div>
                <div class="col-span-3">
                    <div class="singlefields relative mb-2">
                        <input id="s71" class="cus-check transition-all duration-100 ease-in-out shadow-sm border-slate-200 cursor-pointer rounded focus:ring-4 focus:ring-offset-0 focus:ring-primary focus:ring-opacity-20 dark:bg-darkmode-800 dark:border-transparent dark:focus:ring-slate-700 dark:focus:ring-opacity-50 [&[type='radio']]:checked:bg-primary [&[type='radio']]:checked:border-primary [&[type='radio']]:checked:border-opacity-10 [&[type='checkbox']]:checked:bg-primary [&[type='checkbox']]:checked:border-primary [&[type='checkbox']]:checked:border-opacity-10 [&:disabled:not(:checked)]:bg-slate-100 [&:disabled:not(:checked)]:cursor-not-allowed [&:disabled:not(:checked)]:dark:bg-darkmode-800/50 [&:disabled:checked]:opacity-70 [&:disabled:checked]:cursor-not-allowed [&:disabled:checked]:dark:bg-darkmode-800/50" type="checkbox" name="fields[employee_working_patterns][contracted_hour]" value="1"> 
                        <label class= "cursor-pointer ml-2" for="s71">Contracted Hour(s)</label>
                    </div>
                    <input type="hidden" name="labels[contracted_hour]" value="Contracted Hour">
                </div>

                <div class="col-span-3">
                    
                    <input type="hidden" name="tables[employee_working_pattern_pays]" value="EmployeeWorkingPatternPay" />
                    <div class="singlefields relative mb-2">
                        <input id="s72" class="cus-check transition-all duration-100 ease-in-out shadow-sm border-slate-200 cursor-pointer rounded focus:ring-4 focus:ring-offset-0 focus:ring-primary focus:ring-opacity-20 dark:bg-darkmode-800 dark:border-transparent dark:focus:ring-slate-700 dark:focus:ring-opacity-50 [&[type='radio']]:checked:bg-primary [&[type='radio']]:checked:border-primary [&[type='radio']]:checked:border-opacity-10 [&[type='checkbox']]:checked:bg-primary [&[type='checkbox']]:checked:border-primary [&[type='checkbox']]:checked:border-opacity-10 [&:disabled:not(:checked)]:bg-slate-100 [&:disabled:not(:checked)]:cursor-not-allowed [&:disabled:not(:checked)]:dark:bg-darkmode-800/50 [&:disabled:checked]:opacity-70 [&:disabled:checked]:cursor-not-allowed [&:disabled:checked]:dark:bg-darkmode-800/50" type="checkbox" name="fields[employee_working_pattern_pays][salary]" value="1"> 
                        <label class= "cursor-pointer ml-2" for="s72">Salary</label>
                    </div>
                    <input type="hidden" name="labels[salary]" value="Salary">
                </div>
            </div>

            {{-- <div class="grid grid-cols-12 gap-4 mt-10 payrollRow">
                <div class="col-span-12">
                    <h3 class="empDatasTitle font-medium">Payroll Info <a href="javascript: void(0);" data-parent="payrollRow" class="btn btn-sm btn-primary checkedAlls ml-1">All</a></h3>
                </div>
                <div class="col-span-3">
                    <div class="singlefields relative mb-2">
                        <input id="s39" class="cus-check transition-all duration-100 ease-in-out shadow-sm border-slate-200 cursor-pointer rounded focus:ring-4 focus:ring-offset-0 focus:ring-primary focus:ring-opacity-20 dark:bg-darkmode-800 dark:border-transparent dark:focus:ring-slate-700 dark:focus:ring-opacity-50 [&[type='radio']]:checked:bg-primary [&[type='radio']]:checked:border-primary [&[type='radio']]:checked:border-opacity-10 [&[type='checkbox']]:checked:bg-primary [&[type='checkbox']]:checked:border-primary [&[type='checkbox']]:checked:border-opacity-10 [&:disabled:not(:checked)]:bg-slate-100 [&:disabled:not(:checked)]:cursor-not-allowed [&:disabled:not(:checked)]:dark:bg-darkmode-800/50 [&:disabled:checked]:opacity-70 [&:disabled:checked]:cursor-not-allowed [&:disabled:checked]:dark:bg-darkmode-800/50" type="checkbox" name="fields[pay_effective_from]" value="1"> 
                        <label class= "cursor-pointer ml-2" for="s39">Effective From</label>
                    </div>
                    <input type="hidden" name="labels[pay_effective_from]" value="Effective From">
                    <div class="singlefields relative mb-2">
                        <input id="s40" class="cus-check transition-all duration-100 ease-in-out shadow-sm border-slate-200 cursor-pointer rounded focus:ring-4 focus:ring-offset-0 focus:ring-primary focus:ring-opacity-20 dark:bg-darkmode-800 dark:border-transparent dark:focus:ring-slate-700 dark:focus:ring-opacity-50 [&[type='radio']]:checked:bg-primary [&[type='radio']]:checked:border-primary [&[type='radio']]:checked:border-opacity-10 [&[type='checkbox']]:checked:bg-primary [&[type='checkbox']]:checked:border-primary [&[type='checkbox']]:checked:border-opacity-10 [&:disabled:not(:checked)]:bg-slate-100 [&:disabled:not(:checked)]:cursor-not-allowed [&:disabled:not(:checked)]:dark:bg-darkmode-800/50 [&:disabled:checked]:opacity-70 [&:disabled:checked]:cursor-not-allowed [&:disabled:checked]:dark:bg-darkmode-800/50" type="checkbox" name="fields[pay_frequecy]" value="1"> 
                        <label class= "cursor-pointer ml-2" for="s40">Pay Frequency</label>
                    </div>
                    <input type="hidden" name="labels[pay_frequecy]" value="Pay Frequency">
                </div>
                <div class="col-span-3">
                    <div class="singlefields relative mb-2">
                        <input id="s41" class="cus-check transition-all duration-100 ease-in-out shadow-sm border-slate-200 cursor-pointer rounded focus:ring-4 focus:ring-offset-0 focus:ring-primary focus:ring-opacity-20 dark:bg-darkmode-800 dark:border-transparent dark:focus:ring-slate-700 dark:focus:ring-opacity-50 [&[type='radio']]:checked:bg-primary [&[type='radio']]:checked:border-primary [&[type='radio']]:checked:border-opacity-10 [&[type='checkbox']]:checked:bg-primary [&[type='checkbox']]:checked:border-primary [&[type='checkbox']]:checked:border-opacity-10 [&:disabled:not(:checked)]:bg-slate-100 [&:disabled:not(:checked)]:cursor-not-allowed [&:disabled:not(:checked)]:dark:bg-darkmode-800/50 [&:disabled:checked]:opacity-70 [&:disabled:checked]:cursor-not-allowed [&:disabled:checked]:dark:bg-darkmode-800/50" type="checkbox" name="fields[pay_method]" value="1"> 
                        <label class= "cursor-pointer ml-2" for="s41">Pay Mehtod</label>
                    </div>
                    <input type="hidden" name="labels[pay_method]" value="Pay Mehtod">
                    <div class="singlefields relative mb-2">
                        <input id="s42" class="cus-check transition-all duration-100 ease-in-out shadow-sm border-slate-200 cursor-pointer rounded focus:ring-4 focus:ring-offset-0 focus:ring-primary focus:ring-opacity-20 dark:bg-darkmode-800 dark:border-transparent dark:focus:ring-slate-700 dark:focus:ring-opacity-50 [&[type='radio']]:checked:bg-primary [&[type='radio']]:checked:border-primary [&[type='radio']]:checked:border-opacity-10 [&[type='checkbox']]:checked:bg-primary [&[type='checkbox']]:checked:border-primary [&[type='checkbox']]:checked:border-opacity-10 [&:disabled:not(:checked)]:bg-slate-100 [&:disabled:not(:checked)]:cursor-not-allowed [&:disabled:not(:checked)]:dark:bg-darkmode-800/50 [&:disabled:checked]:opacity-70 [&:disabled:checked]:cursor-not-allowed [&:disabled:checked]:dark:bg-darkmode-800/50" type="checkbox" name="fields[pay_tax_code]" value="1"> 
                        <label class= "cursor-pointer ml-2" for="s42">Tax Code</label>
                    </div>
                    <input type="hidden" name="labels[pay_tax_code]" value="Tax Code">
                </div>
                <div class="col-span-3">
                    <div class="singlefields relative mb-2">
                        <input id="s43" class="cus-check transition-all duration-100 ease-in-out shadow-sm border-slate-200 cursor-pointer rounded focus:ring-4 focus:ring-offset-0 focus:ring-primary focus:ring-opacity-20 dark:bg-darkmode-800 dark:border-transparent dark:focus:ring-slate-700 dark:focus:ring-opacity-50 [&[type='radio']]:checked:bg-primary [&[type='radio']]:checked:border-primary [&[type='radio']]:checked:border-opacity-10 [&[type='checkbox']]:checked:bg-primary [&[type='checkbox']]:checked:border-primary [&[type='checkbox']]:checked:border-opacity-10 [&:disabled:not(:checked)]:bg-slate-100 [&:disabled:not(:checked)]:cursor-not-allowed [&:disabled:not(:checked)]:dark:bg-darkmode-800/50 [&:disabled:checked]:opacity-70 [&:disabled:checked]:cursor-not-allowed [&:disabled:checked]:dark:bg-darkmode-800/50" type="checkbox" name="fields[pay_contracted_hour]" value="1"> 
                        <label class= "cursor-pointer ml-2" for="s43">Contd. Hour</label>
                    </div>
                    <input type="hidden" name="labels[pay_contracted_hour]" value="Contd. Hour">
                    <div class="singlefields relative mb-2">
                        <input id="s44" class="cus-check transition-all duration-100 ease-in-out shadow-sm border-slate-200 cursor-pointer rounded focus:ring-4 focus:ring-offset-0 focus:ring-primary focus:ring-opacity-20 dark:bg-darkmode-800 dark:border-transparent dark:focus:ring-slate-700 dark:focus:ring-opacity-50 [&[type='radio']]:checked:bg-primary [&[type='radio']]:checked:border-primary [&[type='radio']]:checked:border-opacity-10 [&[type='checkbox']]:checked:bg-primary [&[type='checkbox']]:checked:border-primary [&[type='checkbox']]:checked:border-opacity-10 [&:disabled:not(:checked)]:bg-slate-100 [&:disabled:not(:checked)]:cursor-not-allowed [&:disabled:not(:checked)]:dark:bg-darkmode-800/50 [&:disabled:checked]:opacity-70 [&:disabled:checked]:cursor-not-allowed [&:disabled:checked]:dark:bg-darkmode-800/50" type="checkbox" name="fields[pay_salary]" value="1"> 
                        <label class= "cursor-pointer ml-2" for="s44">Salary</label>
                    </div>
                    <input type="hidden" name="labels[pay_salary]" value="Salary">
                </div>
                <div class="col-span-3">
                    <div class="singlefields relative mb-2">
                        <input id="s45" class="cus-check transition-all duration-100 ease-in-out shadow-sm border-slate-200 cursor-pointer rounded focus:ring-4 focus:ring-offset-0 focus:ring-primary focus:ring-opacity-20 dark:bg-darkmode-800 dark:border-transparent dark:focus:ring-slate-700 dark:focus:ring-opacity-50 [&[type='radio']]:checked:bg-primary [&[type='radio']]:checked:border-primary [&[type='radio']]:checked:border-opacity-10 [&[type='checkbox']]:checked:bg-primary [&[type='checkbox']]:checked:border-primary [&[type='checkbox']]:checked:border-opacity-10 [&:disabled:not(:checked)]:bg-slate-100 [&:disabled:not(:checked)]:cursor-not-allowed [&:disabled:not(:checked)]:dark:bg-darkmode-800/50 [&:disabled:checked]:opacity-70 [&:disabled:checked]:cursor-not-allowed [&:disabled:checked]:dark:bg-darkmode-800/50" type="checkbox" name="fields[pay_hourly_rate]" value="1"> 
                        <label class= "cursor-pointer ml-2" for="s45">Hourly Rate</label>
                    </div>
                    <input type="hidden" name="labels[pay_hourly_rate]" value="Hourly Rate">
                </div>
            </div>
            <div class="grid grid-cols-12 gap-4 mt-10 banksRows">
                <div class="col-span-12">
                    <h3 class="empDatasTitle font-medium">Bank Info <a href="javascript: void(0);" data-parent="banksRows" class="btn btn-sm btn-primary checkedAlls ml-1">All</a></h3>
                </div>
                <div class="col-span-3">
                    <div class="singlefields relative mb-2">
                        <input id="s46" class="cus-check transition-all duration-100 ease-in-out shadow-sm border-slate-200 cursor-pointer rounded focus:ring-4 focus:ring-offset-0 focus:ring-primary focus:ring-opacity-20 dark:bg-darkmode-800 dark:border-transparent dark:focus:ring-slate-700 dark:focus:ring-opacity-50 [&[type='radio']]:checked:bg-primary [&[type='radio']]:checked:border-primary [&[type='radio']]:checked:border-opacity-10 [&[type='checkbox']]:checked:bg-primary [&[type='checkbox']]:checked:border-primary [&[type='checkbox']]:checked:border-opacity-10 [&:disabled:not(:checked)]:bg-slate-100 [&:disabled:not(:checked)]:cursor-not-allowed [&:disabled:not(:checked)]:dark:bg-darkmode-800/50 [&:disabled:checked]:opacity-70 [&:disabled:checked]:cursor-not-allowed [&:disabled:checked]:dark:bg-darkmode-800/50" type="checkbox" name="fields[bank_name]" value="1"> 
                        <label class= "cursor-pointer ml-2" for="s46">Bank Name</label>
                    </div>
                    <input type="hidden" name="labels[bank_name]" value="Bank Name">
                    <div class="singlefields relative mb-2">
                        <input id="s47" class="cus-check transition-all duration-100 ease-in-out shadow-sm border-slate-200 cursor-pointer rounded focus:ring-4 focus:ring-offset-0 focus:ring-primary focus:ring-opacity-20 dark:bg-darkmode-800 dark:border-transparent dark:focus:ring-slate-700 dark:focus:ring-opacity-50 [&[type='radio']]:checked:bg-primary [&[type='radio']]:checked:border-primary [&[type='radio']]:checked:border-opacity-10 [&[type='checkbox']]:checked:bg-primary [&[type='checkbox']]:checked:border-primary [&[type='checkbox']]:checked:border-opacity-10 [&:disabled:not(:checked)]:bg-slate-100 [&:disabled:not(:checked)]:cursor-not-allowed [&:disabled:not(:checked)]:dark:bg-darkmode-800/50 [&:disabled:checked]:opacity-70 [&:disabled:checked]:cursor-not-allowed [&:disabled:checked]:dark:bg-darkmode-800/50" type="checkbox" name="fields[bank_address]" value="1"> 
                        <label class= "cursor-pointer ml-2" for="s47">Address</label>
                    </div>
                    <input type="hidden" name="labels[bank_address]" value="Address">
                    <div class="singlefields relative mb-2">
                        <input id="s48" class="cus-check transition-all duration-100 ease-in-out shadow-sm border-slate-200 cursor-pointer rounded focus:ring-4 focus:ring-offset-0 focus:ring-primary focus:ring-opacity-20 dark:bg-darkmode-800 dark:border-transparent dark:focus:ring-slate-700 dark:focus:ring-opacity-50 [&[type='radio']]:checked:bg-primary [&[type='radio']]:checked:border-primary [&[type='radio']]:checked:border-opacity-10 [&[type='checkbox']]:checked:bg-primary [&[type='checkbox']]:checked:border-primary [&[type='checkbox']]:checked:border-opacity-10 [&:disabled:not(:checked)]:bg-slate-100 [&:disabled:not(:checked)]:cursor-not-allowed [&:disabled:not(:checked)]:dark:bg-darkmode-800/50 [&:disabled:checked]:opacity-70 [&:disabled:checked]:cursor-not-allowed [&:disabled:checked]:dark:bg-darkmode-800/50" type="checkbox" name="fields[bank_post_code]" value="1"> 
                        <label class= "cursor-pointer ml-2" for="s48">Post Code</label>
                    </div>
                    <input type="hidden" name="labels[bank_post_code]" value="Post Code">
                </div>
                <div class="col-span-3">
                    <div class="singlefields relative mb-2">
                        <input id="s49" class="cus-check transition-all duration-100 ease-in-out shadow-sm border-slate-200 cursor-pointer rounded focus:ring-4 focus:ring-offset-0 focus:ring-primary focus:ring-opacity-20 dark:bg-darkmode-800 dark:border-transparent dark:focus:ring-slate-700 dark:focus:ring-opacity-50 [&[type='radio']]:checked:bg-primary [&[type='radio']]:checked:border-primary [&[type='radio']]:checked:border-opacity-10 [&[type='checkbox']]:checked:bg-primary [&[type='checkbox']]:checked:border-primary [&[type='checkbox']]:checked:border-opacity-10 [&:disabled:not(:checked)]:bg-slate-100 [&:disabled:not(:checked)]:cursor-not-allowed [&:disabled:not(:checked)]:dark:bg-darkmode-800/50 [&:disabled:checked]:opacity-70 [&:disabled:checked]:cursor-not-allowed [&:disabled:checked]:dark:bg-darkmode-800/50" type="checkbox" name="fields[bank_telephone]" value="1"> 
                        <label class= "cursor-pointer ml-2" for="s49">Bank Telephone</label>
                    </div>
                    <input type="hidden" name="labels[bank_telephone]" value="Bank Telephone">
                    <div class="singlefields relative mb-2">
                        <input id="s50" class="cus-check transition-all duration-100 ease-in-out shadow-sm border-slate-200 cursor-pointer rounded focus:ring-4 focus:ring-offset-0 focus:ring-primary focus:ring-opacity-20 dark:bg-darkmode-800 dark:border-transparent dark:focus:ring-slate-700 dark:focus:ring-opacity-50 [&[type='radio']]:checked:bg-primary [&[type='radio']]:checked:border-primary [&[type='radio']]:checked:border-opacity-10 [&[type='checkbox']]:checked:bg-primary [&[type='checkbox']]:checked:border-primary [&[type='checkbox']]:checked:border-opacity-10 [&:disabled:not(:checked)]:bg-slate-100 [&:disabled:not(:checked)]:cursor-not-allowed [&:disabled:not(:checked)]:dark:bg-darkmode-800/50 [&:disabled:checked]:opacity-70 [&:disabled:checked]:cursor-not-allowed [&:disabled:checked]:dark:bg-darkmode-800/50" type="checkbox" name="fields[bank_fax]" value="1"> 
                        <label class= "cursor-pointer ml-2" for="s50">Bank Fax</label>
                    </div>
                    <input type="hidden" name="labels[bank_fax]" value="Bank Fax">
                    <div class="singlefields relative mb-2">
                        <input id="s51" class="cus-check transition-all duration-100 ease-in-out shadow-sm border-slate-200 cursor-pointer rounded focus:ring-4 focus:ring-offset-0 focus:ring-primary focus:ring-opacity-20 dark:bg-darkmode-800 dark:border-transparent dark:focus:ring-slate-700 dark:focus:ring-opacity-50 [&[type='radio']]:checked:bg-primary [&[type='radio']]:checked:border-primary [&[type='radio']]:checked:border-opacity-10 [&[type='checkbox']]:checked:bg-primary [&[type='checkbox']]:checked:border-primary [&[type='checkbox']]:checked:border-opacity-10 [&:disabled:not(:checked)]:bg-slate-100 [&:disabled:not(:checked)]:cursor-not-allowed [&:disabled:not(:checked)]:dark:bg-darkmode-800/50 [&:disabled:checked]:opacity-70 [&:disabled:checked]:cursor-not-allowed [&:disabled:checked]:dark:bg-darkmode-800/50" type="checkbox" name="fields[bank_email]" value="1"> 
                        <label class= "cursor-pointer ml-2" for="s51">Bank Email</label>
                    </div>
                    <input type="hidden" name="labels[bank_email]" value="Bank Email">
                </div>
                <div class="col-span-3">
                    <div class="singlefields relative mb-2">
                        <input id="s52" class="cus-check transition-all duration-100 ease-in-out shadow-sm border-slate-200 cursor-pointer rounded focus:ring-4 focus:ring-offset-0 focus:ring-primary focus:ring-opacity-20 dark:bg-darkmode-800 dark:border-transparent dark:focus:ring-slate-700 dark:focus:ring-opacity-50 [&[type='radio']]:checked:bg-primary [&[type='radio']]:checked:border-primary [&[type='radio']]:checked:border-opacity-10 [&[type='checkbox']]:checked:bg-primary [&[type='checkbox']]:checked:border-primary [&[type='checkbox']]:checked:border-opacity-10 [&:disabled:not(:checked)]:bg-slate-100 [&:disabled:not(:checked)]:cursor-not-allowed [&:disabled:not(:checked)]:dark:bg-darkmode-800/50 [&:disabled:checked]:opacity-70 [&:disabled:checked]:cursor-not-allowed [&:disabled:checked]:dark:bg-darkmode-800/50" type="checkbox" name="fields[bank_account_type]" value="1"> 
                        <label class= "cursor-pointer ml-2" for="s52">AC Type</label>
                    </div>
                    <input type="hidden" name="labels[bank_account_type]" value="AC Type">
                    <div class="singlefields relative mb-2">
                        <input id="s53" class="cus-check transition-all duration-100 ease-in-out shadow-sm border-slate-200 cursor-pointer rounded focus:ring-4 focus:ring-offset-0 focus:ring-primary focus:ring-opacity-20 dark:bg-darkmode-800 dark:border-transparent dark:focus:ring-slate-700 dark:focus:ring-opacity-50 [&[type='radio']]:checked:bg-primary [&[type='radio']]:checked:border-primary [&[type='radio']]:checked:border-opacity-10 [&[type='checkbox']]:checked:bg-primary [&[type='checkbox']]:checked:border-primary [&[type='checkbox']]:checked:border-opacity-10 [&:disabled:not(:checked)]:bg-slate-100 [&:disabled:not(:checked)]:cursor-not-allowed [&:disabled:not(:checked)]:dark:bg-darkmode-800/50 [&:disabled:checked]:opacity-70 [&:disabled:checked]:cursor-not-allowed [&:disabled:checked]:dark:bg-darkmode-800/50" type="checkbox" name="fields[bank_roll_number]" value="1"> 
                        <label class= "cursor-pointer ml-2" for="s53">Roll Number</label>
                    </div>
                    <input type="hidden" name="labels[bank_roll_number]" value="Roll Number">
                    <div class="singlefields relative mb-2">
                        <input id="s54" class="cus-check transition-all duration-100 ease-in-out shadow-sm border-slate-200 cursor-pointer rounded focus:ring-4 focus:ring-offset-0 focus:ring-primary focus:ring-opacity-20 dark:bg-darkmode-800 dark:border-transparent dark:focus:ring-slate-700 dark:focus:ring-opacity-50 [&[type='radio']]:checked:bg-primary [&[type='radio']]:checked:border-primary [&[type='radio']]:checked:border-opacity-10 [&[type='checkbox']]:checked:bg-primary [&[type='checkbox']]:checked:border-primary [&[type='checkbox']]:checked:border-opacity-10 [&:disabled:not(:checked)]:bg-slate-100 [&:disabled:not(:checked)]:cursor-not-allowed [&:disabled:not(:checked)]:dark:bg-darkmode-800/50 [&:disabled:checked]:opacity-70 [&:disabled:checked]:cursor-not-allowed [&:disabled:checked]:dark:bg-darkmode-800/50" type="checkbox" name="fields[bank_account_name]" value="1"> 
                        <label class= "cursor-pointer ml-2" for="s54">Account Name</label>
                    </div>
                    <input type="hidden" name="labels[bank_account_name]" value="Account Name">
                </div>
                <div class="col-span-3">
                    <div class="singlefields relative mb-2">
                        <input id="s55" class="cus-check transition-all duration-100 ease-in-out shadow-sm border-slate-200 cursor-pointer rounded focus:ring-4 focus:ring-offset-0 focus:ring-primary focus:ring-opacity-20 dark:bg-darkmode-800 dark:border-transparent dark:focus:ring-slate-700 dark:focus:ring-opacity-50 [&[type='radio']]:checked:bg-primary [&[type='radio']]:checked:border-primary [&[type='radio']]:checked:border-opacity-10 [&[type='checkbox']]:checked:bg-primary [&[type='checkbox']]:checked:border-primary [&[type='checkbox']]:checked:border-opacity-10 [&:disabled:not(:checked)]:bg-slate-100 [&:disabled:not(:checked)]:cursor-not-allowed [&:disabled:not(:checked)]:dark:bg-darkmode-800/50 [&:disabled:checked]:opacity-70 [&:disabled:checked]:cursor-not-allowed [&:disabled:checked]:dark:bg-darkmode-800/50" type="checkbox" name="fields[bank_account_number]" value="1"> 
                        <label class= "cursor-pointer ml-2" for="s55">Account Number</label>
                    </div>
                    <input type="hidden" name="labels[bank_account_number]" value="Account Number">
                    <div class="singlefields relative mb-2">
                        <input id="s56" class="cus-check transition-all duration-100 ease-in-out shadow-sm border-slate-200 cursor-pointer rounded focus:ring-4 focus:ring-offset-0 focus:ring-primary focus:ring-opacity-20 dark:bg-darkmode-800 dark:border-transparent dark:focus:ring-slate-700 dark:focus:ring-opacity-50 [&[type='radio']]:checked:bg-primary [&[type='radio']]:checked:border-primary [&[type='radio']]:checked:border-opacity-10 [&[type='checkbox']]:checked:bg-primary [&[type='checkbox']]:checked:border-primary [&[type='checkbox']]:checked:border-opacity-10 [&:disabled:not(:checked)]:bg-slate-100 [&:disabled:not(:checked)]:cursor-not-allowed [&:disabled:not(:checked)]:dark:bg-darkmode-800/50 [&:disabled:checked]:opacity-70 [&:disabled:checked]:cursor-not-allowed [&:disabled:checked]:dark:bg-darkmode-800/50" type="checkbox" name="fields[bank_short_code]" value="1"> 
                        <label class= "cursor-pointer ml-2" for="s56">Short Code</label>
                    </div>
                    <input type="hidden" name="labels[bank_short_code]" value="Short Code">
                </div>
            </div>
            <div class="grid grid-cols-12 gap-4 mt-10 workingpatternsrows">
                <div class="col-span-12">
                    <h3 class="empDatasTitle font-medium">Working Patters <a href="javascript: void(0);" data-parent="workingpatternsrows" class="btn btn-sm btn-primary checkedAlls ml-1">All</a></h3>
                </div>
                <div class="col-span-3">
                    <div class="singlefields relative mb-2">
                        <input id="s63" class="cus-check transition-all duration-100 ease-in-out shadow-sm border-slate-200 cursor-pointer rounded focus:ring-4 focus:ring-offset-0 focus:ring-primary focus:ring-opacity-20 dark:bg-darkmode-800 dark:border-transparent dark:focus:ring-slate-700 dark:focus:ring-opacity-50 [&[type='radio']]:checked:bg-primary [&[type='radio']]:checked:border-primary [&[type='radio']]:checked:border-opacity-10 [&[type='checkbox']]:checked:bg-primary [&[type='checkbox']]:checked:border-primary [&[type='checkbox']]:checked:border-opacity-10 [&:disabled:not(:checked)]:bg-slate-100 [&:disabled:not(:checked)]:cursor-not-allowed [&:disabled:not(:checked)]:dark:bg-darkmode-800/50 [&:disabled:checked]:opacity-70 [&:disabled:checked]:cursor-not-allowed [&:disabled:checked]:dark:bg-darkmode-800/50" type="checkbox" name="fields[workingpattern]" value="1"> 
                        <label class= "cursor-pointer ml-2" for="s63">Working Patterns</label>
                    </div>
                    <input type="hidden" name="labels[workingpattern]" value="Working Patterns">
                </div>
            </div>
            <div class="grid grid-cols-12 gap-4 mt-10 emmContactRow">
                <div class="col-span-12">
                    <h3 class="empDatasTitle font-medium">Emergency Contacts <a href="javascript: void(0);" data-parent="emmContactRow" class="btn btn-sm btn-primary checkedAlls ml-1">All</a></h3>
                </div>
                <div class="col-span-3">
                    <div class="singlefields relative mb-2">
                        <input id="s64" class="cus-check transition-all duration-100 ease-in-out shadow-sm border-slate-200 cursor-pointer rounded focus:ring-4 focus:ring-offset-0 focus:ring-primary focus:ring-opacity-20 dark:bg-darkmode-800 dark:border-transparent dark:focus:ring-slate-700 dark:focus:ring-opacity-50 [&[type='radio']]:checked:bg-primary [&[type='radio']]:checked:border-primary [&[type='radio']]:checked:border-opacity-10 [&[type='checkbox']]:checked:bg-primary [&[type='checkbox']]:checked:border-primary [&[type='checkbox']]:checked:border-opacity-10 [&:disabled:not(:checked)]:bg-slate-100 [&:disabled:not(:checked)]:cursor-not-allowed [&:disabled:not(:checked)]:dark:bg-darkmode-800/50 [&:disabled:checked]:opacity-70 [&:disabled:checked]:cursor-not-allowed [&:disabled:checked]:dark:bg-darkmode-800/50" type="checkbox" name="fields[emergencycontact]" value="1"> 
                        <label class= "cursor-pointer ml-2" for="s64">Emergency Contacts</label>
                    </div>
                    <input type="hidden" name="labels[emergencycontact]" value="Emergency Contacts">
                </div>
            </div> --}}
        </div>
        <div class="intro-y box p-5 mt-5">
                <div class="grid grid-cols-12 gap-4">
                    <div class="col-span-3 mt-7 text-right">
                        <div class="w-full flex justify-start"> 
                            <button id="saveNote" href="javascript:;" type="submit" class="btn btn-outline-success text-success w-1/2  mr-2">
                                <i data-lucide="file-text" class="w-4 h-4 mr-2"></i> Generate XLSX <i data-loading-icon="oval" data-color="rgb(4,120,87)" class="w-4 h-4 ml-2 hidden"></i>
                            </button>                    
                        </div>
                    </div>
                </div>
        </div>
    </form>
@endsection
@section('script')
    @vite('resources/js/hr-portal-datareport.js')
@endsection
