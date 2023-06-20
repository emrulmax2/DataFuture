<div class="grid grid-cols-12 gap-x-4 gap-y-0 mt-5">
    <div class="col-span-8">
        <div class="intro-y box px-5 pt-5">
            <div class="flex flex-col lg:flex-row border-b border-slate-200/60 dark:border-darkmode-400 pb-5 -mx-5">
                <div class="flex flex-1 px-5 items-center justify-center lg:justify-start">
                    <div class="w-20 h-20 sm:w-24 sm:h-24 flex-none lg:w-32 lg:h-32 image-fit relative">
                        <img alt="Midone - HTML Admin Template" class="rounded-full" src="{{ asset('build/assets/images/' . $fakers[0]['photos'][0]) }}">
                        <div class="absolute mb-1 mr-1 flex items-center justify-center bottom-0 right-0 bg-primary rounded-full p-2">
                            <i class="w-4 h-4 text-white" data-lucide="camera"></i>
                        </div>
                    </div>
                    <div class="ml-10">
                        <div class="w-24 sm:w-40 truncate sm:whitespace-normal font-medium text-lg">{{ $applicant->title->name.' '.$applicant->first_name.' '.$applicant->last_name }}</div>
                        <div class="text-slate-500 mb-3">{{ $applicant->course->creation->course->name.' - '.$applicant->course->semester->name }}</div>
                        <div class="truncate sm:whitespace-normal flex items-center font-medium">
                            <i data-lucide="mail" class="w-4 h-4 mr-2"></i> <span class="text-slate-500 mr-2">Email:</span> {{ $applicant->users->email }}
                        </div>
                        <div class="truncate sm:whitespace-normal flex items-center mt-1 font-medium">
                            <i data-lucide="phone" class="w-4 h-4 mr-2"></i> <span class="text-slate-500 mr-2">Phone:</span> {{ $applicant->contact->home }}
                        </div>
                        <div class="truncate sm:whitespace-normal flex items-center mt-1 font-medium">
                            <i data-lucide="smartphone" class="w-4 h-4 mr-2"></i> <span class="text-slate-500 mr-2">Mobile:</span> {{ $applicant->contact->mobile }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-span-4">
        <div class="intro-y box p-5 pt-3">
            <div class="grid grid-cols-12 gap-0 items-center">
                <div class="col-span-6">
                    <div class="font-medium text-base">Work in Progress</div>
                </div>
                <div class="col-span-6 text-right">
                    <button type="button" class="btn btn-primary w-auto mr-1 mb-0">
                        {{ $applicant->status->name }}
                    </button>
                </div>
            </div>
            <div class="mt-3 mb-4 border-t border-slate-200/60 dark:border-darkmode-400"></div>
            <div class="progressBarWrap">
                <div class="singleProgressBar mb-3">
                    <div class="flex justify-between mb-1">
                        <div class="font-medium">Pending Task</div>
                        <div class="font-medium">20%</div>
                    </div>
                    <div class="progress h-1">
                        <div class="progress-bar w-1/2 bg-warning" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
                    </div>
                </div>
                <div class="singleProgressBar">
                    <div class="flex justify-between mb-1">
                        <div class="font-medium">Completed Task</div>
                        <div class="font-medium">2/20</div>
                    </div>
                    <div class="progress h-1">
                        <div class="progress-bar w-2/5" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>