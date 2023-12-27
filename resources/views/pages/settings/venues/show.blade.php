@extends('../layout/' . $layout)

@section('subhead')
    <title>{{ $title }}</title>
@endsection

@section('subcontent')
    <div class="intro-y flex flex-col sm:flex-row items-center mt-8">
        <h2 class="text-lg font-medium mr-auto">{{ $subtitle }}</h2>
        <div class="w-full sm:w-auto flex mt-4 sm:mt-0">
            <a href="{{ route('venues') }}" class="add_btn btn btn-primary shadow-md mr-2">Back To Venues</a>
        </div>
    </div>

    <!-- BEGIN: Settings Page Content -->
    <div class="grid grid-cols-12 gap-6">
        <div class="col-span-12 lg:col-span-4 2xl:col-span-3 flex lg:block flex-col-reverse">
            <!-- BEGIN: Profile Info -->
            @include('pages.settings.sidebar')
            <!-- END: Profile Info -->
        </div>

        <div class="col-span-12 lg:col-span-8 2xl:col-span-9">
            <div class="intro-y box px-5 pt-5 mt-5">
                <div class="flex flex-col lg:flex-row border-b border-slate-200/60 dark:border-darkmode-400 pb-5 -mx-5">
                    <div class="flex flex-1 px-5 items-center justify-center lg:justify-start">
                        <div class="ml-auto mr-auto">
                            <div class="w-auto sm:w-full truncate text-primary sm:whitespace-normal font-bold text-3xl">{{ $venue->name }}</div>
                        </div>
                    </div>
                    <div class="mt-6 lg:mt-0 flex-1 px-5 border-l border-slate-200/60 dark:border-darkmode-400 border-t lg:border-t-0 pt-5 lg:pt-0">
                        <div class="font-medium text-center lg:text-left lg:mt-3">Venue Details</div>
                        <div class="flex flex-col justify-center items-center lg:items-start mt-4">
                            <div class="truncate sm:whitespace-normal flex items-center">
                                <i data-lucide="user" class="w-4 h-4 mr-2"></i> <span class="text-slate-500">ID Number:</span> <span class="font-medium ml-2">{{ $venue->idnumber }}</span>
                            </div>
                            <div class="truncate sm:whitespace-normal flex items-center mt-3">
                                <i data-lucide="flag" class="w-4 h-4 mr-2"></i> <span class="text-slate-500">UKPRN:</span> <span class="font-medium ml-2">{{ $venue->ukprn }}</span>
                            </div>
                            <div class="truncate sm:whitespace-normal flex items-center mt-3">
                                <i data-lucide="mail" class="w-4 h-4 mr-2"></i> <span class="text-slate-500">Postal Code:</span> <span class="font-medium ml-2">{{ $venue->postcode }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="mt-6 lg:mt-0 flex-1 px-5 pt-5 lg:pt-0">
                        
                    </div>
                </div>
                <ul class="nav nav-link-tabs flex-col sm:flex-row justify-center lg:justify-start text-center" role="tablist">
                    <li id="room-tab" class="nav-item mr-5" role="presentation">
                        <a href="javascript:void(0);" class="nav-link py-4 inline-flex px-0 active" data-tw-target="#room" aria-controls="room" aria-selected="true" role="tab" >
                            <i data-lucide="tablet" class="w-4 h-4 mr-2"></i> Rooms
                        </a>
                    </li>
                    <li id="baseDataFuture-tab" class="nav-item mr-5" role="presentation">
                        <a href="javascript:void(0);" class="nav-link py-4 inline-flex px-0" data-tw-target="#baseDataFuture" aria-controls="baseDataFuture" aria-selected="true" role="tab" >
                            <i data-lucide="calendar" class="w-4 h-4 mr-2"></i> Datafuture
                        </a>
                    </li>
                </ul>
            </div>
            <div class="intro-y tab-content mt-5">
                <div id="room" class="tab-pane active" role="tabpanel" aria-labelledby="room-tab">
                    @include('pages.settings.venues.details.room')
                </div>
                <div id="baseDataFuture" class="tab-pane active" role="tabpanel" aria-labelledby="baseDataFuture-tab">
                    @include('pages.settings.venues.details.datafuture')
                </div>
            </div>
            @include('pages.settings.venues.details.room-modal')
            @include('pages.settings.venues.details.datafuture-modal')
        </div>
    </div>
    <!-- END: Settings Page Content -->
    

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
    <!-- END: Success Modal Content -->
@endsection

@section('script')
    @vite('resources/js/settings.js')
    @vite('resources/js/venues.js')
    @vite('resources/js/rooms.js')
    @vite('resources/js/venue-datafuture.js')
@endsection