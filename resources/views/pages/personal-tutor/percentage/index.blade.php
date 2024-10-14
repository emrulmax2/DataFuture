@extends('../layout/' . $layout)

@section('subhead')
    <title>{{ $title }}</title>
@endsection

@section('subcontent')
    <div class="intro-y flex flex-col sm:flex-row items-center mt-8">
        <h2 class="text-lg font-medium mr-auto">Students Has Bellow 60% Attendance</h2>
        <div class="w-full sm:w-auto flex mt-4 sm:mt-0">
            <a href="{{ route('dashboard') }}" class="add_btn btn btn-primary shadow-md mr-2">Back to Dashboard</a>
        </div>
    </div>
    <!-- BEGIN: HTML Table Data -->
    <div class="intro-y box p-5 mt-5">
        <div class="flex flex-col sm:flex-row sm:items-end xl:items-start">
            <form id="tabulatorFilterForm-LS" class="xl:flex sm:mr-auto" >
                <div class="sm:flex items-center sm:mr-4 mt-2 xl:mt-0">
                    <label class="w-12 flex-none xl:w-auto xl:flex-initial mr-2">Student</label>
                    <select id="student_ids" name="student_ids" class="tom-selects w-72 mt-2 sm:mt-0" >
                        <option value="">All</option>
                        @if(!empty($students))
                            @foreach($students as $student_id => $student_name)
                                <option value="{{ $student_id }}">{{ $student_name }}</option>
                            @endforeach
                        @endif
                    </select>
                </div>
                <div class="mt-2 xl:mt-0">
                    <button id="tabulator-html-filter-go" type="button" class="btn btn-primary w-full sm:w-16" >Go</button>
                    <button id="tabulator-html-filter-reset" type="button" class="btn btn-secondary w-full sm:w-16 mt-2 sm:mt-0 sm:ml-1" >Reset</button>
                </div>
            </form>
            <div class="flex mt-5 sm:mt-0">
                <div id="studentCountShow" class=" font-medium text-primary flex items-center justify-end">0 student found</div>
            </div>
        </div>
        <div class="overflow-x-auto scrollbar-hidden">
            <div id="studentBellow60PercentList" data-term="{{ $terms->id }}" data-tutor="{{ $tutor->id }}" class="mt-5 table-report table-report--tabulator"></div>
        </div>
    </div>
@endsection

@section('script')
    @vite('resources/js/student-attendance-percentage.js')
@endsection