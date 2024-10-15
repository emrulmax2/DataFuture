@extends('../layout/' . $layout)

@section('subhead')
    <title>{{ $title }}</title>
@endsection

@section('subcontent')
    <div class="intro-y flex flex-col sm:flex-row items-center mt-8">
        <h2 class="text-lg font-medium mr-auto">Class Status Report</h2>
        <div class="w-full sm:w-auto flex mt-4 sm:mt-0">
            <a href="{{ route('dashboard') }}" class="add_btn btn btn-primary shadow-md mr-2">Back to Dashboard</a>
        </div>
    </div>
    <!-- BEGIN: HTML Table Data -->
    <div class="intro-y box p-5 mt-5">
        <form action="#" method="post" id="classStatusForm">
            @csrf
            <div class="grid grid-cols-12 gap-0 gap-y-2 gap-x-4">
                <div class="col-span-12 sm:col-span-3">
                    <label for="attendance_semester" class="form-label">Attendance Term <span class="text-danger">*</span></label>
                    <select id="attendance_semester" class="w-full tom-selects" multiple name="attendance_semester">
                        <option value="">Please Select</option>
                        @if($terms->count() > 0)
                            @foreach($terms as $trm)
                                <option value="{{ $trm->id }}">{{ $trm->name }}</option>
                            @endforeach
                        @endif
                    </select>
                    <div class="acc__input-error error-attendance_semester text-danger mt-2"></div>
                </div>
                <div class="col-span-12 sm:col-span-9 ml-auto mt-auto text-right">
                    <button type="button" id="classStatusFormBtn" class="btn btn-success text-white ml-auto w-auto"><i class="w-4 h-4 mr-2" data-lucide="search"></i><i data-loading-icon="oval" data-color="white" class="w-4 h-4 mr-2 hidden loadingClass"></i> Search</button>
                </div>
            </div>
        </form>

        <div id="statusListTableWrap" class="overflow-x-auto scrollbar-hidden pt-5 statusReportListTableWrap" style="display: none;">
            
            <div id="statusListTable" class="mt-5 table-report table-report--tabulator"></div>
        </div>
    </div>

@endsection

@section('script')
   
    @vite('resources/js/student-class-status-reports.js')
@endsection