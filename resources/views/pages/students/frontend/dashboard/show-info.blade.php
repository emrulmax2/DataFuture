<!-- BEGIN: Show-info Report -->
@include('pages.students.frontend.modals.index')
<div class="col-span-12 flex">
    <h2 class="text-lg font-medium mr-auto">Profile Review of <u><strong>{{ $student->title->name.' '.$student->first_name.' '.$student->last_name }}</strong></u></h2>
    <div class="ml-auto flex justify-end">
        <button type="button" class="btn btn-success text-white w-auto mr-1 mb-0">
            {{ $student->status->name }}
        </button>
        <button data-tw-toggle="modal" data-tw-target="#changeStudentModal" type="button" class="btn btn-primary w-auto text-white tooltip" title="Change Status">
            <i data-lucide="check-circle" class="w-4 h-4"></i>
        </button>
        <!-- <a style="float: right;" href="{{ route('applicantprofile.print',$student->id) }}" data-id="{{ $student->id }}" class="btn btn-success text-white w-auto">Download Pdf</a> -->
        <input type="hidden" name="applicant_id" value="{{ $student->id }}"/>
    </div>
</div>
<div class="col-span-12 mt-5">
    <div class="grid grid-cols-12 gap-6 mt-5">
        <div class="col-span-12 box">
            <div class="flex flex-col lg:flex-row border-b border-slate-200/60 dark:border-darkmode-400  pb-6 pt-6">
                <div class="flex flex-1 px-5 items-center justify-center lg:justify-start">
                    <div class="flex flex-1 px-5 items-center justify-center lg:justify-start">
                        <div class="w-20 h-20 sm:w-24 sm:h-24 flex-none lg:w-32 lg:h-32 image-fit relative">
                            <img alt="" class="rounded-full" src="{{ $student->photo_url }}">
                            
                        </div>
                        <div class="ml-10">
                            <div class="w-auto truncate sm:whitespace-normal font-medium text-lg">{{ $student->title->name.' '.$student->first_name }} {{ $student->last_name }} - <span class="font-black">{{ $student->registration_no }}</span></div>
                            <div class="text-slate-500 mb-3">{{ isset($student->crel->creation->course->name) ? $student->crel->creation->course->name : '' }} - {{ isset($student->crel->propose->semester->name) ? $student->crel->propose->semester->name : '' }}</div>
                            
                        </div>
                    </div>
                </div>
                <div class="mt-6 lg:mt-0 flex-1 px-5 border-l border-slate-200/60 dark:border-darkmode-400 border-t lg:border-t-0 pt-5 lg:pt-0 phoneEmail" id="phoneEmail">
                    <div class="font-medium text-center lg:text-left mb-2 inline-flex"><i data-lucide="contact" class="w-4 h-4 mr-2"></i> Contact Details
                    </div>
                    <div class="ml-0 mt-0  mb-2">
                        @if($student->users->email)
                        <div class="truncate sm:whitespace-normal flex items-center font-medium ml-6">
                            <div class="flex">
                                <i data-lucide="mail" class="w-4 h-4 mr-2"></i> <span class="text-slate-500 mr-2">Email:</span> 
                            </div>
                            <div class="flex mr-auto leading-6 px-2">
                                {{ $student->users->email }} {!!  ($student->contact->personal_email) ? '<br />': "" !!} {{ $student->contact->personal_email }}
                            </div>

                        </div>
                        @endif
                        @if($student->contact->home)
                        <div class="truncate sm:whitespace-normal flex items-center mt-1 font-medium ml-6">
                            <i data-lucide="phone" class="w-4 h-4 mr-2"></i> <span class="text-slate-500 mr-2">Phone:</span> {{ $student->contact->home }}
                        </div>
                        @endif
                        @if($student->contact->mobile)
                        <div class="truncate sm:whitespace-normal flex items-center mt-1 font-medium ml-6">
                            <i data-lucide="smartphone" class="w-4 h-4 mr-2"></i> <span class="text-slate-500 mr-2">Mobile:</span> {{ $student->contact->mobile }}
                        </div>
                        @endif
                    </div>
                    <div class="font-medium text-center lg:text-left inline-flex mb-2 mt-4"> <i data-lucide="globe" class="w-4 h-4 mr-2"></i>  Correspondence Address 
                    </div>
                    <div class="flex flex-col justify-center items-center lg:items-start ml-6">
                        <div class="truncate sm:whitespace-normal flex items-start">
                            <i data-lucide="map-pin" class="w-4 h-4 mr-2"></i> 
                            <span class="uppercase addresses">
                                <span>
                                    @if(isset($student->contact->term_time_address_id) && $student->contact->term_time_address_id > 0)
                                        @if(isset($student->contact->termaddress->address_line_1) && !empty($student->contact->termaddress->address_line_1))
                                            <span class="font-medium">{{ $student->contact->termaddress->address_line_1 }}</span><br/>
                                        @endif
                                        @if(isset($student->contact->termaddress->address_line_2) && !empty($student->contact->termaddress->address_line_2))
                                            <span class="font-medium">{{ $student->contact->termaddress->address_line_2 }}</span><br/>
                                        @endif
                                        @if(isset($student->contact->termaddress->city) && !empty($student->contact->termaddress->city))
                                            <span class="font-medium">{{ $student->contact->termaddress->city }}</span>,
                                        @endif
                                        @if(isset($student->contact->termaddress->state) && !empty($student->contact->termaddress->state))
                                            <span class="font-medium">{{ $student->contact->termaddress->state }}</span>, <br/>
                                        @endif
                                        @if(isset($student->contact->termaddress->post_code) && !empty($student->contact->termaddress->post_code))
                                            <span class="font-medium">{{ $student->contact->termaddress->post_code }}</span>,
                                        @endif
                                        @if(isset($student->contact->termaddress->country) && !empty($student->contact->termaddress->country))
                                            <span class="font-medium">{{ $student->contact->termaddress->country }}</span><br/>
                                        @endif
                                    @else 
                                        <span class="font-medium text-warning">Not Set Yet!</span><br/>
                                    @endif
                                </span>
                            </span>
                            
                        </div>
                    </div>
                </div>
            </div>
            @include('pages.students.frontend.dashboard.show-menu')
        </div>
    </div>
</div>
<!-- END: Show-Info Report -->