<!-- BEGIN: Show-info Report -->
<div class="col-span-12 mt-5">
    <div class="grid grid-cols-12 gap-6 mt-5">
        <div class="col-span-12 box">
            <div class="flex flex-col lg:flex-row border-b border-slate-200/60 dark:border-darkmode-400  pb-6 pt-6">
                <div class="flex flex-1 px-5 items-center justify-center lg:justify-start">
                    <div class="flex flex-1 px-5 items-center justify-center lg:justify-start">
                        <div class="w-20 h-20 sm:w-24 sm:h-24 flex-none lg:w-32 lg:h-32 image-fit relative">
                            <img alt="" class="rounded-full" src="{{ (isset($student->photo) && !empty($student->photo) && Storage::disk('google')->exists('public/applicants/'.$student->applicant_id.'/'.$student->photo) ? Storage::disk('google')->url('public/applicants/'.$student->applicant_id.'/'.$student->photo) : asset('build/assets/images/avater.png')) }}">
                            <button data-tw-toggle="modal" data-tw-target="#addApplicantPhotoModal" type="button" class="absolute mb-1 mr-1 flex items-center justify-center bottom-0 right-0 bg-primary rounded-full p-2">
                                <i class="w-4 h-4 text-white" data-lucide="camera"></i>
                            </button>
                        </div>
                        <div class="ml-10">
                            <div class="w-auto truncate sm:whitespace-normal font-medium text-lg">{{ $student->title->name.' '.$student->first_name }} {{ $student->last_name }} - <span class="font-black">{{ $student->registration_no }}</span></div>
                            <div class="text-slate-500 mb-3">{{ isset($student->crel->creation->course->name) ? $student->crel->creation->course->name : '' }} - {{ isset($student->crel->propose->semester->name) ? $student->crel->propose->semester->name : '' }}</div>
                            <div class="truncate sm:whitespace-normal flex items-center font-medium">
                                <i data-lucide="mail" class="w-4 h-4 mr-2"></i> <span class="text-slate-500 mr-2">Email:</span> xyz@gmail.com<br/>{{ $student->users->email }}
                            </div>
                            
                            <div class="truncate sm:whitespace-normal flex items-center mt-1 font-medium">
                                <i data-lucide="phone" class="w-4 h-4 mr-2"></i> <span class="text-slate-500 mr-2">Phone:</span> {{ $student->contact->home }}
                            </div>
                            <div class="truncate sm:whitespace-normal flex items-center mt-1 font-medium">
                                <i data-lucide="smartphone" class="w-4 h-4 mr-2"></i> <span class="text-slate-500 mr-2">Mobile:</span> {{ $student->contact->mobile }}
                            </div>
                        </div>
                    </div>
                </div>
                <div class="mt-6 lg:mt-0 flex-1 px-5 border-l border-slate-200/60 dark:border-darkmode-400 border-t lg:border-t-0 pt-5 lg:pt-0 addressWrap" id="employeeAddress">
                    <div class="font-medium text-center lg:text-left">Correspondence Address 
                        <button data-id="1018" data-type="employee" data-tw-toggle="modal" data-tw-target="#addressModal" class="addressPopupToggler transition duration-200 border shadow-sm inline-flex items-center justify-center py-2 px-3 rounded-md font-medium cursor-pointer focus:ring-4 focus:ring-primary focus:ring-opacity-20 focus-visible:outline-none dark:focus:ring-slate-700 dark:focus:ring-opacity-50 [&amp;:hover:not(:disabled)]:bg-opacity-90 [&amp;:hover:not(:disabled)]:border-opacity-90 [&amp;:not(button)]:text-center disabled:opacity-70 disabled:cursor-not-allowed bg-secondary/70 border-secondary/70 text-slate-500 dark:border-darkmode-400 dark:bg-darkmode-400 dark:text-slate-300 [&amp;:hover:not(:disabled)]:bg-slate-100 [&amp;:hover:not(:disabled)]:border-slate-100 [&amp;:hover:not(:disabled)]:dark:border-darkmode-300/80 [&amp;:hover:not(:disabled)]:dark:bg-darkmode-300/80 mb-2 mr-1 ml-2"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" data-lucide="Pencil" class="lucide lucide-Pencil stroke-1.5 h-4 w-4"><path d="M17 3a2.85 2.83 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5Z"></path><path d="m15 5 4 4"></path></svg></button>
                    </div>
                    <div class="flex flex-col justify-center items-center lg:items-start mt-4">
                        <div class="truncate sm:whitespace-normal flex items-start">
                            <i data-lucide="location" class="w-4 h-4 mr-2"></i> 
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