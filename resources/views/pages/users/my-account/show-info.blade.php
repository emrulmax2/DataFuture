<div class="intro-y box px-5 pt-5 mt-5">
    <div class="flex flex-col lg:flex-row border-b border-slate-200/60 dark:border-darkmode-400 pb-5 -mx-5">
        <div class="flex flex-1 px-5 items-center justify-center lg:justify-start">
            <div class="w-20 h-20 sm:w-24 sm:h-24 flex-none lg:w-32 lg:h-32 image-fit relative">
                <img alt="{{ $employee->title->name.' '.$employee->first_name.' '.$employee->last_name }}" class="rounded-full" src="{{ (isset($employee->photo) && !empty($employee->photo) && Storage::disk('local')->exists('public/employees/'.$employee->id.'/'.$employee->photo) ? Storage::disk('local')->url('public/employees/'.$employee->id.'/'.$employee->photo) : asset('build/assets/images/avater.png')) }}">
            </div>
            <div class="ml-5">
                <div class="w-24 sm:w-40 truncate sm:whitespace-normal font-medium text-lg"></div>
                <div class="w-24 sm:w-40 truncate sm:whitespace-normal font-medium text-lg">{{ $employee->title->name.' '.$employee->first_name }} <span class="font-black">{{ $employee->last_name }}</span></div>
                
            </div>
        </div>
        <div class="mt-6 lg:mt-0 flex-1 px-5 border-l border-r border-slate-200/60 dark:border-darkmode-400 border-t lg:border-t-0 pt-5 lg:pt-0">
            <div class="font-medium text-center lg:text-left lg:mt-3">Contact Details</div>
            <div class="flex flex-col justify-center items-center lg:items-start mt-4">
                <div class="truncate sm:whitespace-normal flex items-center">
                    <i data-lucide="mail" class="w-4 h-4 mr-2"></i> <span class="text-slate-500 mr-2">Email:</span> {{ $employee->email }}
                </div>
                <div class="truncate sm:whitespace-normal flex items-center mt-3">
                    <i data-lucide="phone" class="w-4 h-4 mr-2"></i> <span class="text-slate-500 mr-2">Phone:</span> {{ $employee->telephone }}
                </div>
                <div class="truncate sm:whitespace-normal flex items-center mt-3">
                    <i data-lucide="smartphone" class="w-4 h-4 mr-2"></i> <span class="text-slate-500 mr-2">Mobile:</span> {{ $employee->mobile }}
                </div>
                
                <div class="truncate sm:whitespace-normal flex items-center mt-3">
                    <i data-lucide="tent-tree" class="w-4 h-4 mr-2"></i> <span class="text-slate-500 mr-2">Expected Retirement :</span> {{ $employee->retire }}
                </div>
            </div>
        </div>
        <div class="mt-6 lg:mt-0 flex-1 px-5 border-t lg:border-0 border-slate-200/60 dark:border-darkmode-400 pt-5 lg:pt-0">
            <div class="font-medium text-center lg:text-left">Address <button data-tw-toggle="modal" data-tw-target="#editAddressUpdateModal" class="transition duration-200 border shadow-sm inline-flex items-center justify-center py-2 px-3 rounded-md font-medium cursor-pointer focus:ring-4 focus:ring-primary focus:ring-opacity-20 focus-visible:outline-none dark:focus:ring-slate-700 dark:focus:ring-opacity-50 [&amp;:hover:not(:disabled)]:bg-opacity-90 [&amp;:hover:not(:disabled)]:border-opacity-90 [&amp;:not(button)]:text-center disabled:opacity-70 disabled:cursor-not-allowed bg-secondary/70 border-secondary/70 text-slate-500 dark:border-darkmode-400 dark:bg-darkmode-400 dark:text-slate-300 [&amp;:hover:not(:disabled)]:bg-slate-100 [&amp;:hover:not(:disabled)]:border-slate-100 [&amp;:hover:not(:disabled)]:dark:border-darkmode-300/80 [&amp;:hover:not(:disabled)]:dark:bg-darkmode-300/80 mb-2 mr-1 ml-2"><i data-lucide="Pencil" width="24" height="24" class="stroke-1.5 h-4 w-4"></i></button>
            </div>
            <div class="flex flex-col justify-center items-center lg:items-start mt-4">
                <div class="truncate sm:whitespace-normal flex items-start">
                    <i data-lucide="map-pin" class="w-4 h-4 mr-2" style="padding-top: 3px;"></i> 
                    <span>
                        @if(isset($employee->address->address_line_1) && $employee->address->address_line_1 > 0)
                            @if(isset($employee->address->address_line_1) && !empty($employee->address->address_line_1))
                                <span class="font-medium">{{ $employee->address->address_line_1 }}</span><br/>
                            @endif
                            @if(isset($employee->address->address_line_2) && !empty($employee->address->address_line_2))
                                <span class="font-medium">{{ $employee->address->address_line_2 }}</span><br/>
                            @endif
                            @if(isset($employee->address->city) && !empty($employee->address->city))
                                <span class="font-medium">{{ $employee->address->city }}</span>,
                            @endif
                            @if(isset($employee->address->state) && !empty($employee->address->state))
                                <span class="font-medium">{{ $employee->address->state }}</span>, <br/>
                            @endif
                            @if(isset($employee->address->post_code) && !empty($employee->address->post_code))
                                <span class="font-medium">{{ $employee->address->post_code }}</span>,
                            @endif
                            @if(isset($employee->address->country) && !empty($employee->address->country))
                                <span class="font-medium">{{ $employee->address->country }}</span><br/>
                            @endif
                        @else 
                            <span class="font-medium text-warning">Not Set Yet!</span><br/>
                        @endif
                    </span>
                </div>
            </div>
        </div>
    </div>
    @include('pages.users.my-account.show-menu')
</div>