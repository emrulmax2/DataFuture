@extends('../layout/' . $layout)

@section('subhead')
    <title>{{ $title }}</title>
@endsection

@section('subcontent')
    <div class="intro-y flex flex-col sm:flex-row items-center mt-8">
        <h2 class="text-lg font-medium mr-auto">Term Performance Reports</h2>
        <div class="w-full sm:w-auto flex mt-4 sm:mt-0">
            <a href="{{ route('reports') }}" class="add_btn btn btn-primary shadow-md mr-2">Back to Reports</a>
        </div>
    </div>
    <!-- BEGIN: HTML Table Data -->
    <div class="intro-y box p-5 mt-5">
        <form method="post" action="{{ route('reports.term.performance') }}" id="attendanceRateSearchForm">
            @csrf
            <div class="grid grid-cols-12 gap-4">
                <div class="col-span-3">
                    <label for="term_declaration_id" class="form-label semesterLabel inline-flex items-center">Term Declaration <span class="text-danger">*</span></label>
                    <select name="term_declaration_id" class="tom-selects w-full" id="term_declaration_id">
                        <option value="">Please Select</option>
                        @if($terms->count() > 0)
                            @foreach($terms as $trm)
                                <option {{ ($searched_terms && $searched_terms == $trm->id ? 'Selected' : '') }} value="{{ $trm->id }}">{{ $trm->name }}</option>
                            @endforeach
                        @endif
                    </select>
                    <div class="acc__input-error error-term_declaration_id text-danger mt-2"></div>
                </div>
                <div class="col-span-9 text-right" style="padding-top: 31px;">
                    <div class="flex justify-end items-center">
                        <button type="submit" id="IntakeAttnRateBtn" class="btn btn-primary text-white w-auto ml-2">
                            Generate Report
                        </button>
                        {{--<a href="javascript:void(0);" style="display: none;" id="printPdfAtnRateBtn" class="btn btn-linkedin text-white ml-2"><i data-lucide="printer" class="w-4 h-4 mr-2"></i> Download PDF</a>--}}
                    </div>
                </div>
            </div>
        </form>

        @if($searched_terms)
            @php 
                $overAll = 0;
                $row = 1;
                if($result && !empty($result)):
                    $perticipents = $result->sum('TOTAL');
                    $attendances = $result->sum('P') + $result->sum('O') + $result->sum('E') + $result->sum('M') + $result->sum('H') + $result->sum('L');
                    $overAll = round($attendances * 100 / $perticipents, 2);
                endif;
            @endphp
            <div class="overflow-x-auto scrollbar-hidden mt-5" id="attendanceRateWrap">
                <div class="chartWrap mb-7">
                    <canvas height="40" id="attendanceRateBarChart"></canvas>
                </div>
                <table class="table table-bordered table-sm" id="attendanceRateOvTable" data-title="{{ (isset($theTerm->name) && !empty($theTerm->name) ? $theTerm->name : 'Undefined') }}">
                    <tbody>
                        @if($result && !empty($result))
                            <tr class="rateRow" data-label="Overall" data-rate="{{ ($overAll > 0 ? $overAll : 0) }}">
                                <td class="w-20">
                                    <div class="form-check m-0 justify-center">
                                        <input checked id="rateRowCheck_0" class="form-check-input rateRowCheck" type="checkbox" name="rateRowCheck[]" value="1">
                                    </div>
                                </td>
                                <th>Overall</th>
                                <th>
                                    {{ $overAll > 0 ? $overAll.'%' : '0.00%'}}
                                </th>
                            </tr>
                            @foreach($result as $res)
                                <tr class="rateRow" data-label="{{ $res->course_name }}" data-rate="{{ ($res->percentage_withexcuse > 0 ? round($res->percentage_withexcuse, 2) : 0) }}">
                                    <td class="w-20">
                                        <div class="form-check m-0 justify-center">
                                            <input checked id="rateRowCheck_{{ $row }}" class="form-check-input rateRowCheck" type="checkbox" name="rateRowCheck[]" value="1">
                                        </div>
                                    </td>    
                                    <th>{{ $res->course_name }}</th>
                                    <th>{{ ($res->percentage_withexcuse > 0 ? round($res->percentage_withexcuse, 2).'%' : '0.00%') }}</th>
                                </tr>
                                @php $row++; @endphp
                            @endforeach
                        @endif
                    </tbody>
                </table>
            </div>
        @endif
    </div>

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

    <!-- BEGIN: Success Modal Content -->
    <div id="warningModal" class="modal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-body p-0">
                    <div class="p-5 text-center">
                        <i data-lucide="x-circle" class="w-16 h-16 text-danger mx-auto mt-3"></i>
                        <div class="text-3xl mt-5 warningModalTitle"></div>
                        <div class="text-slate-500 mt-2 warningModalDesc"></div>
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
    @vite('resources/js/term-performance-reports.js')
@endsection