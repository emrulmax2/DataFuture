@extends('../layout/' . $layout)

@section('subhead')
    <title>{{ $title }}- </title>
@endsection

@section('subcontent')

    <div class="intro-y flex items-center mt-8">
        <h2 class="text-lg font-medium mr-auto">Profile of <u><strong>{{ $employee->title->name.' '.$employee->first_name.' '.$employee->last_name }}</strong></u></h2>
    </div>

    <!-- BEGIN: Profile Info -->
    @include('pages.agent.profile.show-info')
    <!-- END: Profile Info -->

    <div class="intro-y mt-5">
        <div class="intro-y box p-5">
            <div class="grid grid-cols-12 gap-0 items-center">
                <div class="col-span-6">
                    <div class="font-medium text-base">Terms Details</div>
                </div>

            </div>
            
            <div class="mt-5 pt-5 border-t border-slate-200/60 dark:border-darkmode-400"></div>
            <div class="grid grid-cols-12 gap-4"> 
                <div class="col-span-12">
                    <div class="overflow-x-auto scrollbar-hidden">
                        <div id="agentTableId" class="mt-5 table-report table-report--tabulator"></div>
                    </div>
                </div>
                
            </div>
        </div>
    </div>



    @include('pages.agent.profile.show-modals')

@endsection

@section('script')
    @vite('resources/js/agent-profile.js')
@endsection