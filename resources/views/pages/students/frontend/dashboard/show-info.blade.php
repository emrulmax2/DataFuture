<!-- BEGIN: Show-info Report -->
@include('pages.students.frontend.modals.index')
<div class="col-span-12 flex flex-col sm:flex-row mt-2 sm:mt-6 py-3">
    <div class="flex justify-center items-center flex-wrap gap-2">
        <h2 class="text-lg font-medium sm:mr-auto text-center sm:text-left">
            Profile of 
        </h2>
        <h2 class="text-lg font-medium sm:mr-auto text-center sm:text-left">
            <u><strong>{{ $student->title->name.' '.$student->first_name.' '.$student->last_name }}</strong></u>
        </h2>
    </div>

    <div class="sm:ml-auto flex justify-center sm:justify-end mt-4 sm:mt-0">
        <button type="button" class="btn btn-success text-white w-auto sm:mr-1 mb-0">
            {{ $student->status->name }}
        </button>
        <div class="dropdown-menu w-52">
            <ul class="dropdown-content">
                @if(isset($student->children) && count($student->children) > 0)
                    @if(isset($student->descendants))
                        @foreach($student->descendants as $descendant)
                            <li>
                                <a href="{{ route('students.dashboard.student.select', $descendant->id) }}" class="dropdown-item">
                                    <i data-lucide="user" class="w-4 h-4 mr-2"></i> View {{ $descendant->course->semester->name }}
                                </a>
                            </li>
                        @endforeach
                    @else
                        @foreach($student->children as $child)
                            <li>
                                <a href="{{ route('students.dashboard.student.select', $child->id) }}" class="dropdown-item">
                                    <i data-lucide="user" class="w-4 h-4 mr-2"></i> View {{ $child->course->semester->name }}
                                </a>
                            </li>
                        @endforeach
                    @endif
                @elseif(isset($student->parent)  && is_object($student->parent))
                            
                    @if($student->ancestors->count())
                        @foreach($student->ancestors as $ancestor)
                            <li>
                                <a href="{{ route('students.dashboard.student.select', $ancestor->id) }}" class="dropdown-item">
                                    <i data-lucide="user" class="w-4 h-4 mr-2"></i> View {{ $ancestor->course->semester->name }}
                                </a>
                            </li>
                        @endforeach
                    @else
                        <li>
                            <span class="dropdown-item">
                                <i data-lucide="circle-slash-2" class="w-4 h-4 mr-2"></i> No Record
                            </span>
                        </li>
                    @endif
                @else
                    <li>
                        <span class="dropdown-item">
                            <i data-lucide="circle-slash-2" class="w-4 h-4 mr-2"></i> No Record
                        </span>
                    </li>
                @endif
            </ul>
        </div>
    </div>
</div>
<div class="col-span-12">
    <div class="grid grid-cols-12 gap-6">
        <div class="col-span-12 box">
            <div class="flex flex-col lg:flex-row border-b border-slate-200/60 dark:border-darkmode-400  pb-6 pt-6">
                <div class="flex flex-1 px-5 items-center justify-center lg:justify-start">
                    <div class="flex flex-1 px1 sm:px-5 items-center justify-center lg:justify-start">
                        <div class="w-20 h-20 sm:w-24 sm:h-24 flex-none lg:w-32 lg:h-32 image-fit relative">
                            <img alt="" class="rounded-full" src="{{ $student->photo_url }}">
                            
                        </div>
                        <div class="ml-1 sm:ml-10">
                            <div class="w-auto truncate font-medium text-lg"><span class="font-black">{{ $student->registration_no }}</span></div>
                            <div class="w-auto whitespace-normal font-medium text-lg">{{ $student->title->name.' '.$student->first_name }} {{ $student->last_name }}</div>
                            <div class="text-slate-500 mb-3">{{ isset($student->crel->creation->course->name) ? $student->crel->creation->course->name : '' }} - {{ isset($student->crel->propose->semester->name) ? $student->crel->propose->semester->name : '' }}</div>
                            
                        </div>
                    </div>
                </div>
                <div class="mt-6 lg:mt-0 flex-1 px-5 border-l border-slate-200/60 dark:border-darkmode-400 border-t lg:border-t-0 pt-5 lg:pt-0 phoneEmail" id="phoneEmail">
                    <div class="grid grid-cols-12 gap-4">
                        <div class="col-span-12 sm:col-span-6">
                            <div class="grid grid-cols-12 items-center gap-4">
                                <div class="col-span-6">
                                    <div class="font-normal text-left sm:text-center lg:text-left">
                                        Contact Details 
                                    </div>
                                </div>
                                <div class="col-span-6 text-right">
                                    <div class="dropdown w-1/2 sm:w-auto ml-auto">
                                        <button class="dropdown-toggle btn btn-primary w-auto" aria-expanded="false" data-tw-toggle="dropdown">
                                            <i data-lucide="grip" class="w-4 h-4"></i>
                                        </button>
                                        <div class="dropdown-menu w-40">
                                            <ul class="dropdown-content">
                                                <li>
                                                    <a id="tabulator-export-csv" data-tw-toggle="modal" data-tw-target="#confirmPersonalEmailUpdateModal" href="javascript:;" class="dropdown-item">
                                                        <i data-lucide="mail-question" class="w-4 h-4 mr-2"></i> Change Email
                                                    </a>
                                                </li>
                                                <li>
                                                    <a id="tabulator-export-xlsx" href="javascript:;"  data-tw-toggle="modal" data-tw-target="#confirmPersonalMobileUpdateModal" class="dropdown-item">
                                                        <i data-lucide="smartphone" class="w-4 h-4 mr-2"></i> Change Mobile
                                                    </a>
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                            </div>
                            </div>
                            <div class="ml-0 mt-0  mb-2">
                                @if($student->users->email)
                                <div class="truncate md:whitespace-normal flex font-normal text-slate-500">
                                    <div class="flex">
                                        <i data-lucide="mail" class="w-4 h-4 mr-2"></i> <span class="text-slate-500 mr-2">Email:</span> 
                                    </div>
                                    <div class=" mr-auto leading-6 px-2">
                                       <span> {{ $student->users->email }}</span> {!!  ($student->contact->personal_email) ? '<br />': "" !!} <span>{{ $student->contact->personal_email }}</span>
                                    </div>

                                </div>
                                @endif
                                @if($student->contact->home)
                                <div class="truncate sm:whitespace-normal flex items-center mt-1 font-normal text-slate-500">
                                    <i data-lucide="phone" class="w-4 h-4 mr-2"></i> <span class="text-slate-500 mr-2">Phone:</span> {{ $student->contact->home }}
                                </div>
                                @endif
                                @if($student->contact->mobile)
                                <div class="truncate sm:whitespace-normal flex items-center mt-1 font-normal text-slate-500">
                                    <i data-lucide="smartphone" class="w-4 h-4 mr-2"></i> <span class="text-slate-500 mr-2">Mobile:</span> {{ $student->contact->mobile }}
                                </div>
                                @endif
                            </div>
                        </div>
                        <div class="col-span-12 sm:col-span-6">
                            <div class="font-normal text-center lg:text-left inline-flex mb-4">Correspondence Address 
                            </div>
                            <div class="flex flex-col sm:justify-center sm:items-center lg:items-start">
                                <div class="flex items-start">
                                    <i data-lucide="map-pin" class="w-4 h-4 mr-2"></i> 
                                    <span class="uppercase addresses text-slate-500">
                                        <span>
                                            @if(isset($student->contact->term_time_address_id) && $student->contact->term_time_address_id > 0)
                                                @if(isset($student->contact->termaddress->address_line_1) && !empty($student->contact->termaddress->address_line_1))
                                                    <span class="font-normal">{{ $student->contact->termaddress->address_line_1 }}</span>
                                                @endif
                                                @if(isset($student->contact->termaddress->address_line_2) && !empty($student->contact->termaddress->address_line_2))
                                                    <span class="font-normal">{{ $student->contact->termaddress->address_line_2 }}</span>
                                                @endif
                                                @if(isset($student->contact->termaddress->city) && !empty($student->contact->termaddress->city))
                                                    <span class="font-normal">{{ $student->contact->termaddress->city }}</span>,
                                                @endif
                                                @if(isset($student->contact->termaddress->state) && !empty($student->contact->termaddress->state))
                                                    <span class="font-normal">{{ $student->contact->termaddress->state }}</span>, <br/>
                                                @endif
                                                @if(isset($student->contact->termaddress->post_code) && !empty($student->contact->termaddress->post_code))
                                                    <span class="font-normal">{{ $student->contact->termaddress->post_code }}</span>,
                                                @endif
                                                @if(isset($student->contact->termaddress->country) && !empty($student->contact->termaddress->country))
                                                    <span class="font-normal">{{ $student->contact->termaddress->country }}</span><br/>
                                                @endif
                                            @else 
                                                <span class="font-normal text-warning">Not Set Yet!</span><br/>
                                            @endif
                                        </span>
                                    </span>
                                    
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @include('pages.students.frontend.dashboard.show-menu')
        </div>
    </div>
</div>
<!-- END: Show-Info Report -->