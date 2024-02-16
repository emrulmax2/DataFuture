@extends('../layout/' . $layout)

@section('subhead')
    <title>{{ $title }}- </title>
@endsection

@section('subcontent')

    <div class="intro-y flex items-center mt-8">
        <h2 class="text-lg font-medium mr-auto">My Account</h2>
    </div>

    <!-- BEGIN: Profile Info -->
    @include('pages.users.my-account.show-info')
    <!-- END: Profile Info -->

    <div class="intro-y mt-5">
        <div class="intro-y box p-5">
            <div class="grid grid-cols-12 gap-0 items-center">
                <div class="col-span-6">
                    <div class="font-medium text-base">Personal Details</div>
                </div>
            </div>
            
            <div class="mt-5 pt-5 border-t border-slate-200/60 dark:border-darkmode-400"></div>
            <div class="grid grid-cols-12 gap-4"> 
                <div class="col-span-12 sm:col-span-3">
                    <div class="grid grid-cols-12 gap-0">
                        <div class="col-span-4 text-slate-500 font-medium">Name</div>
                        <div class="col-span-8 font-medium">{{ $employee->title->name.' '.$employee->full_name }}</div>
                    </div>
                </div>
                <div class="col-span-12 sm:col-span-3">
                    <div class="grid grid-cols-12 gap-0">
                        <div class="col-span-4 text-slate-500 font-medium">Date of Birth</div>
                        <div class="col-span-8 font-medium">{{ (isset($employee->date_of_birth) && !empty($employee->date_of_birth) ? date('jS M, Y', strtotime($employee->date_of_birth)) : '') }}</div>
                    </div>
                </div>
                <div class="col-span-12 sm:col-span-3">
                    <div class="grid grid-cols-12 gap-0">
                        <div class="col-span-4 text-slate-500 font-medium">Age</div>
                        <div class="col-span-8 font-medium">{{ (isset($employee->age) ? $employee->age: '') }}</div>
                    </div>
                </div>
                
                <div class="col-span-12 sm:col-span-3"></div>
                <div class="col-span-12 sm:col-span-3">
                    <div class="grid grid-cols-12 gap-0">
                        <div class="col-span-4 text-slate-500 font-medium">Sex Identifier/Gender</div>
                        <div class="col-span-8 font-medium">{{ (isset($employee->sex->name) ? $employee->sex->name : '') }}</div>
                    </div>
                </div>
                <div class="col-span-12 sm:col-span-3">
                    <div class="grid grid-cols-12 gap-0">
                        <div class="col-span-4 text-slate-500 font-medium">Nationality</div>
                        <div class="col-span-8 font-medium">{{ isset($employee->nationality->name) ? $employee->nationality->name : '' }}</div>
                    </div>
                </div>
                <div class="col-span-12 sm:col-span-3">
                    <div class="grid grid-cols-12 gap-0">
                        <div class="col-span-4 text-slate-500 font-medium">Ethnicity</div>
                        <div class="col-span-8 font-medium">{{ isset($employee->ethnicity->name) ? $employee->ethnicity->name : '' }}</div>
                    </div>
                </div>
                
                <div class="col-span-12 sm:col-span-3">
                    <div class="grid grid-cols-12 gap-0">
                        <div class="col-span-4 text-slate-500 font-medium">NI Number</div>
                        <div class="col-span-8 font-medium">{{ isset($employee->ni_number) ? $employee->ni_number : '' }}</div>
                    </div>
                </div>
                <div class="col-span-12 sm:col-span-6">
                    <div class="grid grid-cols-12 gap-0">
                        <div class="col-span-4 text-slate-500 font-medium">Is this employee has disabilites?</div>
                        <div class="col-span-8 font-medium">{{ isset($employee->disability_status) ? $employee->disability_status : '' }}</div>
                    </div>
                </div>
                
                <div class="col-span-12 sm:col-span-3">
                    <div class="grid grid-cols-12 gap-0">
                        <div class="col-span-4 text-slate-500 font-medium">Car Reg Number</div>
                        <div class="col-span-8 font-medium">{{ isset($employee->car_reg_number	) ? $employee->car_reg_number	 : '' }}</div>
                    </div>
                </div>
                <div class="col-span-12 sm:col-span-3">
                    <div class="grid grid-cols-12 gap-0">
                        <div class="col-span-4 text-slate-500 font-medium">Driving License</div>
                        <div class="col-span-8 font-medium">{{ isset($employee->drive_license_number) ? $employee->drive_license_number : '' }}</div>
                    </div>
                </div>
              
                @if(isset($employee->disability_status) && $employee->disability_status == "Yes")
                    <div class="col-span-12">
                        <div class="grid grid-cols-12 gap-0">
                            <div class="col-span-12 text-slate-500 font-medium">Disabilities :</div>
                            <div class="col-span-12 font-medium">
                                @if(isset($employee->disability) && !empty($employee->disability))
                                    <ul class="m-0 p-0"> 
                                        @foreach($employee->disability as $dis)
                                            <li class="text-left font-normal mb-1 flex pl-5 relative"><i data-lucide="check-circle" class="w-3 h-3 text-success absolute" style="left: 0; top: 4px;"></i>{{ $dis->name }}</li>
                                        @endforeach
                                    </ul>
                                @endif
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
    
    <div class="intro-y mt-5">
        <div class="intro-y box p-5">
            <div class="grid grid-cols-12 gap-0 items-center">
                <div class="col-span-6">
                    <div class="font-medium text-base">Emergency Contacts</div>
                </div>
            </div>
            
            <div class="mt-5 pt-5 border-t border-slate-200/60 dark:border-darkmode-400"></div>
            <div class="grid grid-cols-12 gap-4"> 
                <div class="col-span-6">
                    <div class="col-span-12 sm:col-span-4">
                        <div class="grid grid-cols-12 gap-0">
                            <div class="col-span-4 text-slate-500 font-medium">Name</div>
                            <div class="col-span-8 font-medium">{{ isset($emergencyContacts->emergency_contact_name) ? $emergencyContacts->emergency_contact_name : '' }}</div>
                        </div>
                    </div>
                    <div class="col-span-12 sm:col-span-4">
                        <div class="grid grid-cols-12 gap-0">
                            <div class="col-span-4 text-slate-500 font-medium">Relation</div>
                            <div class="col-span-8 font-medium">{{ (isset($emergencyContacts->kin->name) && !empty($emergencyContacts->kin->name) ? $emergencyContacts->kin->name : '') }}</div>
                        </div>
                    </div>
                    <div class="col-span-12 sm:col-span-4">
                        <div class="grid grid-cols-12 gap-0">
                            <div class="col-span-4 text-slate-500 font-medium">Telephone</div>
                            <div class="col-span-8 font-medium">{{ isset($emergencyContacts->emergency_contact_telephone) ? $emergencyContacts->emergency_contact_telephone : '' }}</div>
                        </div>
                    </div>
                    <div class="col-span-12 sm:col-span-6">
                        <div class="grid grid-cols-12 gap-0">
                            <div class="col-span-4 text-slate-500 font-medium">Mobile</div>
                            <div class="col-span-8 font-medium">{{ isset($emergencyContacts->emergency_contact_mobile) ? $emergencyContacts->emergency_contact_mobile : '' }}</div>
                        </div>
                    </div>
                    <div class="col-span-12 sm:col-span-6">
                        <div class="grid grid-cols-12 gap-0">
                            <div class="col-span-4 text-slate-500 font-medium">Email </div>
                            <div class="col-span-8 font-medium">{{ isset($emergencyContacts->emergency_contact_email) ? $emergencyContacts->emergency_contact_email : '' }}</div>
                        </div>
                    </div>
                </div>
                <div class="col-span-6">
                    <div class="col-span-12">
                        <div class="grid grid-cols-12 gap-0">
                            <div class="flex flex-col justify-center items-center lg:items-start col-span-12">
                                <div class="truncate sm:whitespace-normal flex items-start">
                                    <i data-lucide="map-pin" class="w-4 h-4 mr-2" style="padding-top: 3px;"></i> 
                                    <span>
                                        @if(isset($emergencyContacts->address->address_line_1) && $emergencyContacts->address->address_line_1 > 0)
                                            @if(isset($emergencyContacts->address->address_line_1) && !empty($emergencyContacts->address->address_line_1))
                                                <span class="font-medium">{{ $emergencyContacts->address->address_line_1 }}</span><br/>
                                            @endif
                                            @if(isset($emergencyContacts->address->address_line_2) && !empty($emergencyContacts->address->address_line_2))
                                                <span class="font-medium">{{ $emergencyContacts->address->address_line_2 }}</span><br/>
                                            @endif
                                            @if(isset($emergencyContacts->address->city) && !empty($emergencyContacts->address->city))
                                                <span class="font-medium">{{ $emergencyContacts->address->city }}</span>,
                                            @endif
                                            @if(isset($emergencyContacts->address->state) && !empty($emergencyContacts->address->state))
                                                <span class="font-medium">{{ $emergencyContacts->address->state }}</span>, 
                                            @endif
                                            @if(isset($emergencyContacts->address->post_code) && !empty($emergencyContacts->address->post_code))
                                                <span class="font-medium">{{ $emergencyContacts->address->post_code }}</span>,<br/>
                                            @endif
                                            @if(isset($employee->address->country) && !empty($emergencyContacts->address->country))
                                                <span class="font-medium">{{ strtoupper($emergencyContacts->address->country) }}</span><br/>
                                            @endif
                                        @else 
                                            <span class="font-medium text-warning">Not Set Yet!</span><br/>
                                        @endif
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection