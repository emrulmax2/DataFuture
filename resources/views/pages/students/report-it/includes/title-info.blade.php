@php
    //conveert btn-warning to style class
    $statusClasses = [
        "Pending" => "btn-warning w-auto ml-1 mb-0",
        "In Progress" => "btn-linkedin w-auto ml-1 mb-0",
        "Resolved" => "btn-success w-auto ml-1 mb-0 text-white",
        "Rejected" => "btn-danger w-auto ml-1 mb-0",
    ];

@endphp
<div class="intro-y flex items-center mt-8">
    <h2 class="text-lg font-medium mr-auto">Profile of <u><strong>{{ isset($employee) ? $employee->title->name.' '.$employee->full_name : 
    $student->title->name.' '.$student->full_name }}</strong></u></h2>
    <div class="ml-auto flex justify-end">
        <a href="{{ route('report.it.all') }}" class="btn btn-primary text-white w-auto mr-1 mb-0">
            <i data-lucide="arrow-left" class="w-4 h-4 mr-2"></i> Back 
        </a>
        <div class="flex items-center ">
            <div class="dropdown ml-auto">
                <a class="dropdown-toggle btn {{ $statusClasses[$reportItAll->status] ?? 'btn btn-default text-dark w-auto ml-1 mb-0' }}" href="javascript:;" aria-expanded="false" data-tw-toggle="dropdown">
                    {{ $reportItAll->status }} 
                    @if($reportItAll->status != 'Resolved')
                        <i data-lucide="chevron-down" class="w-4 h-4 ml-2"></i>
                    @endif
                </a>
                <div class="dropdown-menu w-40">
                    <ul class="dropdown-content">
                        @if($reportItAll->status != 'Resolved')
                        <li >
                            <a href="javascript:;" class="click-close dropdown-item flex" data-id="{{ $reportItAll->id }}">
                                Close/Resolved 
                            </a>
                        </li>
                        @endif
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>