@extends('../layout/' . $layout)

@section('subhead')
    <title>{{ $title }}</title>
@endsection

@section('subcontent')
    <div class="intro-y flex flex-col sm:flex-row items-center mt-8">
        <h2 class="text-lg font-medium mr-auto">Accounts Reports</h2>
        <div class="w-full sm:w-auto flex mt-4 sm:mt-0">
            <a href="{{ route('reports') }}" class="add_btn btn btn-primary shadow-md mr-2">Back to Reports</a>
        </div>
    </div>
    <!-- BEGIN: HTML Table Data -->
    <div class="intro-y box p-5 mt-5">
        <div id="accountsReportsAccordion" class="accordion accordion-boxed pt-2">
            <div class="accordion-item">
                <div id="accountsReportsAccordion-1" class="accordion-header">
                    <button id="studentSearchBtn" class="accordion-button collapsed relative w-full text-lg font-semibold" type="button" data-tw-toggle="collapse" data-tw-target="#accountsReportsAccordion-collapse-1" aria-expanded="false" aria-controls="accountsReportsAccordion-collapse-1">
                        Collection Reports
                        <span class="accordionCollaps"></span>
                    </button>
                </div>
                <div id="accountsReportsAccordion-collapse-1" class="accordion-collapse collapse" aria-labelledby="accountsReportsAccordion-1" data-tw-parent="#accountsReportsAccordion">
                    <div class="accordion-body">
                        <form action="{{ route('reports.account.collection.export') }}" method="post" id="collectionReportForm">
                            @csrf
                            <div class="grid grid-cols-12 gap-0 gap-y-2 gap-x-4">
                                <div class="col-span-12 sm:col-span-3">
                                    <label for="from_date" class="form-label">From Date <span class="text-danger">*</span></label>
                                    <input type="text" name="from_date" class="w-full form-control datepicker" id="from_date" data-format="DD-MM-YYYY" data-single-mode="true">
                                    <div class="acc__input-error error-from_date text-danger mt-2">{{ ($errors->has('from_date') ? $errors->first('from_date') : '')}}</div>
                                </div>
                                <div class="col-span-12 sm:col-span-3">
                                    <label for="to_date" class="form-label">To Date <span class="text-danger">*</span></label>
                                    <input type="text" name="to_date" class="w-full form-control datepicker" id="to_date" data-format="DD-MM-YYYY" data-single-mode="true">
                                    <div class="acc__input-error error-to_date text-danger mt-2">{{ ($errors->has('to_date') ? $errors->first('to_date') : '')}}</div>
                                </div>
                                <div class="col-span-12 sm:col-span-3">
                                    <div class="flex flex-col sm:flex-row pt-10">
                                        <div class="form-check mr-5">
                                            <input id="date_type_1" checked class="form-check-input" type="radio" name="date_type" value="entry_date">
                                            <label class="form-check-label" for="date_type_1">Entry Date</label>
                                        </div>
                                        <div class="form-check mr-2 mt-2 sm:mt-0">
                                            <input id="date_type_2" class="form-check-input" type="radio" name="date_type" value="payment_date">
                                            <label class="form-check-label" for="date_type_2">Payment Date</label>
                                        </div>
                                    </div>
                                    <div class="acc__input-error error-date_type text-danger mt-2">{{ ($errors->has('date_type') ? $errors->first('date_type') : '')}}</div>
                                </div>
                                <div class="col-span-12 sm:col-span-3 ml-auto mt-auto text-right">
                                    <button type="submit" class="btn btn-success text-white ml-auto w-auto"><i class="w-4 h-4 mr-2" data-lucide="file-text"></i> Export Excel</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            <div class="accordion-item">
                <div id="accountsReportsAccordion-2" class="accordion-header">
                    <button id="studentSearchBtn" class="accordion-button collapsed relative w-full text-lg font-semibold" type="button" data-tw-toggle="collapse" data-tw-target="#accountsReportsAccordion-collapse-2" aria-expanded="false" aria-controls="accountsReportsAccordion-collapse-2">
                        Due Reports
                        <span class="accordionCollaps"></span>
                    </button>
                </div>
                <div id="accountsReportsAccordion-collapse-2" class="accordion-collapse collapse" aria-labelledby="accountsReportsAccordion-2" data-tw-parent="#accountsReportsAccordion">
                    <div class="accordion-body">
                        <div class="alert alert-success-soft show flex items-center mb-2 font-medium" role="alert">
                            <i data-lucide="alert-triangle" class="w-6 h-6 mr-2"></i> Comming Soon...
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('script')
    @vite('resources/js/accounts-reports.js')
@endsection