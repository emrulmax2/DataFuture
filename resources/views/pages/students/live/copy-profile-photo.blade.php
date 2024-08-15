@extends('../layout/' . $layout)

@section('subhead')
    <title>{{ $title }}</title>
@endsection

@section('subcontent')
    <div class="intro-y flex flex-col sm:flex-row items-center mt-8">
        <h2 class="text-lg font-medium mr-auto">Copy Student Profile Photos</h2>
        <div class="w-full sm:w-auto flex mt-4 sm:mt-0">
            <a href="{{ route('dashboard') }}" class="add_btn btn btn-primary shadow-md mr-2">Back to Dashboard</a>
        </div>
    </div>
    <!-- BEGIN: HTML Table Data -->
    <div class="intro-y box p-5 mt-5">
        <div class="grid grid-cols-12 gap-4">
            <div class="col-span-6">
                <p class="font-medium mt-2">Total Student : {{ $student }}</p>
                <p class="font-medium mt-2">Student Left : {{ $student - ($page * $limit) }}</p>
                <p class="font-medium mt-2">Per Page : {{ $limit }}</p>
                <p class="font-medium mt-2">Current Page : {{ $page }}</p>
            </div>
            <div class="col-span-3">
                <a href="{{ route('student.copy.profile.photo', [($page + 1), $limit]) }}" class="btn btn-success text-white">Next Page</a>
            </div>
        </div>
    </div>
@endsection