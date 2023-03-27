@extends('../layout/' . $layout)

@section('subhead')
    <title>{{ $title }}</title>
@endsection

@section('subcontent')
<div class="intro-y flex items-center mt-8">
        <h2 class="text-lg font-medium mr-auto">Academic Year Details</h2>
    </div>
    <!-- BEGIN: Profile Info -->
    <div class="intro-y box px-5 pt-5 mt-5">
        <div class="flex flex-col lg:flex-row border-b border-slate-200/60 dark:border-darkmode-400 pb-5 -mx-5">
            <div class="flex flex-1 px-5 items-center justify-center lg:justify-start">
                <div class="ml-auto mr-auto">
                    <div class="w-auto sm:w-full truncate text-primary sm:whitespace-normal font-bold text-3xl">{{ $academicyear->name }}</div>
                </div>
            </div>
            <div class="mt-6 lg:mt-0 flex-1 px-5 border-l border-slate-200/60 dark:border-darkmode-400 border-t lg:border-t-0 pt-5 lg:pt-0">
                <div class="font-medium text-center lg:text-left lg:mt-3">Academic Year Details</div>
                <div class="flex flex-col justify-center items-center lg:items-start mt-4">
                    <div class="truncate sm:whitespace-normal flex items-center">
                        <i data-lucide="disc" class="w-4 h-4 mr-2"></i> <span class="text-slate-500">Code:</span> <span class="font-medium ml-2">{{ $academicyear->code }}</span>
                    </div>
                    <div class="truncate sm:whitespace-normal flex items-center mt-3">
                        <i data-lucide="calendar" class="w-4 h-4 mr-2"></i> <span class="text-slate-500">From Date:</span> <span class="font-medium ml-2">{{ $academicyear->from_date }}</span>
                    </div>
                    <div class="truncate sm:whitespace-normal flex items-center mt-3">
                        <i data-lucide="calendar" class="w-4 h-4 mr-2"></i> <span class="text-slate-500">To Date:</span> <span class="font-medium ml-2">{{ $academicyear->to_date }}</span>
                    </div>
                </div>
            </div>
            <div class="mt-6 lg:mt-0 flex-1 px-5 pt-5 lg:pt-0">
                
            </div>
        </div>
        <ul class="nav nav-link-tabs flex-col sm:flex-row justify-center lg:justify-start text-center" role="tablist">
            <li id="bankholiday-tab" class="nav-item mr-5" role="presentation">
                <a href="javascript:void(0);" class="nav-link py-4 inline-flex px-0 active" data-tw-target="#bankholiday" aria-controls="bankholiday" aria-selected="true" role="tab" >
                    <i data-lucide="tablet" class="w-4 h-4 mr-2"></i> Bank Holidays
                </a>
            </li>
        </ul>
    </div>
    <div class="intro-y tab-content mt-5">
        <div id="bankholiday" class="tab-pane active" role="tabpanel" aria-labelledby="bankholiday-tab">
            @include('pages.academicyears.details.bankholiday')
        </div>
    </div>
    @include('pages.academicyears.details.bankholiday-modal')

    <!-- BEGIN: Success Modal Content -->
    <div id="successModal" class="modal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-body p-0">
                    <div class="p-5 text-center">
                        <i data-lucide="check-circle" class="w-16 h-16 text-success mx-auto mt-3"></i>
                        <div class="text-3xl mt-5 successModalTitle"></div>
                        <div class="text-slate-500 mt-2 successModalDesc"></div>
                    </div>
                    <div class="px-5 pb-8 text-center">
                        <button type="button" data-tw-dismiss="modal" class="btn btn-primary w-24">Ok</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection