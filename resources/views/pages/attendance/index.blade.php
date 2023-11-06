@extends('../layout/' . $layout)

@section('subhead')
    <title>{{ $title }}</title>
@endsection

@section('subcontent')
    <div class="intro-y flex flex-col sm:flex-row items-center mt-8">
        <h2 class="text-lg font-medium mr-auto">Attendance</h2>
        <div class="w-full sm:w-auto flex mt-4 sm:mt-0">
            {{-- <a href="{{ route('dashboard') }}" class="add_btn btn btn-primary shadow-md mr-2">Back to Dashboard</a> --}}
        </div>
    </div>
    <!-- BEGIN: HTML Table Data -->
    <div class="intro-y box p-5 mt-5">
        <form id="attendance_search" method="post" >
            <div class="grid grid-cols-12 gap-0 gap-x-4">
                <div class="col-span-12 sm:col-span-4">
                    <div class="grid grid-cols-12 gap-0 gap-x-4">
                        <label class="col-span-12 sm:col-span-4 form-label pt-2">Plan Date Search</label>
                        <div class="col-span-12 sm:col-span-8">
                            <div class="">
                                <input type="text" value="" placeholder="DD-MM-YYYY" id="plan_date" class="form-control datepicker" name="plan_date" data-format="DD-MM-YYYY" data-single-mode="true">
                                <ul class="autoFillDropdown"></ul>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-span-12 sm:col-span-4 text-right"></div>
                <div class="col-span-12 sm:col-span-4 text-right">
                    <div class="flex justify-end items-center">
                        <button id="planDateSearchBtn" type="submit" class="btn btn-success text-white ml-2 w-auto"><i class="w-4 h-4 mr-2" data-lucide="search"></i> Search</button>
                        <button id="resetPlanDateSearch" type="button" class="btn btn-danger w-auto ml-2" ><i class="w-4 h-4 mr-2" data-lucide="rotate-cw"></i> Reset</button>
                    </div>
                </div>
            </div>
        </form>

        <div class="overflow-x-auto scrollbar-hidden">
            <div id="attendanceListTable" class="mt-5 table-report table-report--tabulator"></div>
        </div>
    </div>
@endsection

@section('script')
    @vite('resources/js/attendance.js')
@endsection