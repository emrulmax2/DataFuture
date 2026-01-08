@extends('../layout/print')

@section('subhead')
    <title>{{ $title }} - Print</title>
    <style>
          /* Reserve top margin for header on every printed page */
          @page { margin: 0; }

        @media print {
            .no-print { display: none !important; }
            /* fixed header that repeats on every printed page */
            .print-header {
                position: relative;
                top: 0;
                left: 0;
                right: 0;
                height: 140px;
                background: #fff;
                padding: 0;
                border-bottom: 1px solid #e5e7eb;
                z-index: 9999;
                display:flex;
                flex-direction:column;
                align-items:center;
                justify-content:space-between;
                gap:12px;
                -webkit-print-color-adjust: exact;
            }
            .print-header { page-break-inside: avoid; }
            /* keep attendance-block from being split across pages */
            /* .attendance-block { page-break-inside: avoid; } */
            /* ensure body has no extra margins when printing */
            body { margin: 0; }
        }
        body { font-family: Arial, Helvetica, sans-serif; color: #000; }
        /* Header uses two rows when printing: title full-width on top, left/right below */
        .print-header { margin-bottom: 10px; display:flex; flex-direction:column; gap:4px; }
        .print-header .left { display:flex; align-items:center; gap:12px; }
        .print-header .right { display:flex; align-items:center; justify-content:flex-end; }
        .print-header .print-header-bottom { display:flex; align-items:flex-start; justify-content:space-between; gap:12px; }
        .logo__image { height:48px; width:auto; display:block; }
        .student-photo { width:64px; height:64px; border-radius:9999px; object-fit:cover; border:1px solid #e5e7eb; }
        /* Allow term blocks to flow across pages so header doesn't push them to the next page */
        .term-block { margin-bottom: 24px; page-break-inside: auto; -webkit-column-break-inside: auto; break-inside: auto; }
        .print-header h1 { margin: 0 0 6px 0; font-size: 1.25rem; }
        .small { font-size: 0.9rem; }
        table.print-table { width:100%; border-collapse: collapse; margin-top:8px; }
        table.print-table th, table.print-table td { border:1px solid #000; padding:6px; text-align:left; }
        .meta { margin-top:6px; }
        .badge { display:inline-block; padding:4px 8px; border-radius:4px; background:#eee; }
        /* Term summary design block */
        .term-summary { padding: 4px; 
            /* border-radius: 4px; 
            background: #fbfdff; 
            border: 1px solid #e6eef6; 
            box-shadow: 0 1px 2px 
            rgba(16,24,40,0.04); 
            margin-bottom:8px;  */
            
            border-bottom: 1px solid #e6eef6; 
        }
        .term-summary h2 { margin:0 0 4px 0; display:flex; align-items:center; gap:6px; }
        .term-summary .meta-inline { display:flex; gap:6px; align-items:center; flex-wrap:wrap; }
        .term-summary .badge { background:#e6f7ff; color:#0369a1; font-weight:600; padding:2px 8px; border-radius:4px; }
        
        @media print {
            .term-summary { background: #fff; box-shadow: none; }
        }
    </style>
@endsection

@section('subcontent')
    
    <div class="print-header mb-4">
        <div class="print-header-top w-full text-center">
            <h1 class="text-lg font-semibold">{{ $title }}</h1>
            <div class="text-sm text-gray-600">Generated on: {{ date('jS F, Y') }}</div>
            <div class="no-print flex w-60 items-center justify-center gap-2" style="margin-top:18px;">
                <button onclick="window.print()" class="btn btn-outline-secondary btn-sm">Print</button>
                <button onclick="window.location.href='{{ route('student.attendance',$student->id) }}'" class="btn btn-outline-primary btn-sm">Back to Attendances</button>
            </div>
        </div>
        <div class="print-header-bottom w-full">
            <div class="left">
                 <img alt="London Churchill College" class="logo__image w-auto h-12" src="{{ asset("build/assets/images/L1_logo.svg") }}">
            </div>
            <div class="right">
                {{-- <img src="{{ (isset($student->photo_url) && $student->photo_url) ? $student->photo_url : asset('images/default-profile.png') }}" alt="Student" class="student-photo mr-2" /> --}}
                <div>
                    <h2 class="text-sm font-semibold">{{ $student->full_name ?? ($student->name ?? 'Student') }}</h2>
                    <div class="text-sm text-gray-700">ID: {{ $student->registration_no }}</div>
                    <div class="text-sm text-gray-700">Semester: {{ $student->crel->semester->name ?? '' }}</div>
                    <div class="text-sm text-gray-700 break-words whitespace-normal">Course : {{ $student->crel->propose->creation->course->name ?? '' }}</div>
                </div>
            </div>
        </div>
    </div>
    <div class="secondary-printheader grid grid-cols-12 gap-3 my-2">
        <div class="col-span-6">
            <div class="text-sm text-gray-700">Date of birth: {{ !empty($student->date_of_birth) ? date("jS F, Y",strtotime($student->date_of_birth)) : 'N/A' }} </div>
            <div class="text-sm text-gray-700">Address: 
                <span class="">
                    @if(isset($student->contact->term_time_address_id) && $student->contact->term_time_address_id > 0)
                        @if(isset($student->contact->termaddress->address_line_1) && !empty($student->contact->termaddress->address_line_1))
                            <span >{{ $student->contact->termaddress->address_line_1 }}</span> <br/>
                        @endif
                        @if(isset($student->contact->termaddress->address_line_2) && !empty($student->contact->termaddress->address_line_2))
                            <span >{{ $student->contact->termaddress->address_line_2 }}</span> <br/>
                        @endif
                        @if(isset($student->contact->termaddress->city) && !empty($student->contact->termaddress->city))
                            <span>{{ $student->contact->termaddress->city }}</span>,
                        @endif
                        @if(isset($student->contact->termaddress->state) && !empty($student->contact->termaddress->state))
                            <span >{{ $student->contact->termaddress->state }}</span>, <br/>
                        @endif
                        @if(isset($student->contact->termaddress->post_code) && !empty($student->contact->termaddress->post_code))
                            <span >{{ $student->contact->termaddress->post_code }}</span>,
                        @endif
                        @if(isset($student->contact->termaddress->country) && !empty($student->contact->termaddress->country))
                            <span >{{ $student->contact->termaddress->country }}</span>
                        @endif
                    @else 
                        <span class="font-medium text-warning">Not Set Yet!</span><br/>
                    @endif
                </span>    
            </div>
        </div>
        <div class="col-span-6">
            <div class="text-sm text-gray-700">Awarding Body: {{ (isset($student->crel->creation->course->body->name) ? $student->crel->creation->course->body->name : 'Unknown')}}</div>
            <div class="text-sm text-gray-700">Awarding Body Registration No: {{ (isset($student->crel->abody->reference) ? $student->crel->abody->reference : '') }}</div>
            <div class="text-sm text-gray-700">Date of Award: {{ (isset($student->awarded) ? $student->awarded->date_of_award : '') }}</div>
        </div>
    </div>
    <div class="print-content">
    @php $termstart=0 @endphp
    
    @foreach($dataSet as $termId =>$dataStartPoint)
        @php $termstart++; $planId=1; @endphp
        @if(isset($term_id) && $term_id>0)
        
            @if($term_id == $termId)
                @include('pages.students.live.attendance.print-partial')
                
                @break
            @endif
        @else
            @include('pages.students.live.attendance.print-partial')    
        @endif
        
        
    @endforeach
    </div>
    

@endsection

@section('script')
    @vite('resources/js/student-global.js')
    @vite('resources/js/student-attendance-term-status.js')
    <script type="module">
        
        (function () {
        // Auto-trigger print when opening the print view in a new tab/window.
        window.addEventListener('load', function(){ setTimeout(function(){ window.print(); }, 250); });
            $(".tablepoint-toggle").on('click', function(e) {
                e.preventDefault();
                var $t = $(this);
                // Try to find plus/minus icon elements; support both original <i> and replaced SVG from Lucide
                var $icons = $t.find('.plusminus');
                if ($icons.length === 0) {
                    // fallback: any element with data-lucide (lucide replaced svg or original i)
                    $icons = $t.find('[data-lucide]');
                }

                if ($icons.length >= 2) {
                    // toggle visibility between the two icons
                    $icons.eq(0).toggleClass('hidden');
                    $icons.eq(1).toggleClass('hidden');
                } else if ($icons.length === 1) {
                    $icons.eq(0).toggleClass('hidden');
                }

                // Toggle the related dataset; prefer nearest matching .tabledataset
                var $dataset = $t.closest('.relative').find('div.tabledataset').first();
                if ($dataset.length === 0) {
                    $dataset = $t.parent().siblings('div.tabledataset');
                }
                if ($dataset.length) {
                    $dataset.slideToggle();
                }
            });

            $(".toggle-heading").on('click', function(e) {
                e.preventDefault();
                var $t = $(this);
                var $toggle = $t.siblings('div.tablepoint-toggle');
                if ($toggle.length === 0) {
                    $toggle = $t.closest('.relative').find('div.tablepoint-toggle').first();
                }
                if ($toggle.length) $toggle.trigger('click');
            });
        })()
    </script>
@endsection
