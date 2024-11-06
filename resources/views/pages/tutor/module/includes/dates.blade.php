<div id="planClassDatesAccordion" class="accordion accordion-boxed pt-2">
    <div class="accordion-item">
        <div id="planClassDatesAccordion-1" class="accordion-header">
            <button class="accordion-button relative w-full text-lg font-semibold" type="button" data-tw-toggle="collapse" data-tw-target="#planClassDatesAccordion-collapse-1" aria-expanded="true" aria-controls="planClassDatesAccordion-collapse-1">
                Theory Class Dates
                <span class="accordionCollaps"></span>
            </button>
        </div>
        <div id="planClassDatesAccordion-collapse-1" class="accordion-collapse collapse show" aria-labelledby="planClassDatesAccordion-1" data-tw-parent="#planClassDatesAccordion">
            <div class="accordion-body">
                <div class="flex flex-col sm:flex-row sm:items-end xl:items-start">
                    <form id="tabulatorFilterForm-PD" class="xl:flex sm:mr-auto" >
                        <div class="sm:flex items-center sm:mr-4 mt-2 xl:mt-0">
                            <label class="w-12 flex-none xl:w-auto xl:flex-initial mr-2">Date</label>
                            <input id="dates-PD" name="dates" type="text" class="form-control sm:w-40 2xl:w-full mt-2 sm:mt-0 datepicker" data-format="DD-MM-YYYY" data-single-mode="true"  placeholder="DD-MM-YYYY">
                        </div>
                        <div class="sm:flex items-center sm:mr-4 mt-2 xl:mt-0">
                            <label class="w-12 flex-none xl:w-auto xl:flex-initial mr-2">Status</label>
                            <select id="status-PD" name="status" class="form-select w-full mt-2 sm:mt-0 sm:w-auto" >
                                <option value="1">Active</option>
                                <option value="2">Archived</option>
                            </select>
                        </div>
                        <div class="mt-2 xl:mt-0">
                            <button id="tabulator-html-filter-go-PD" type="button" class="btn btn-primary w-full sm:w-16" >Go</button>
                            <button id="tabulator-html-filter-reset-PD" type="button" class="btn btn-secondary w-full sm:w-16 mt-2 sm:mt-0 sm:ml-1" >Reset</button>
                        </div>
                    </form>
                    <div class="flex mt-5 sm:mt-0">
                        <button id="tabulator-print" class="btn btn-outline-secondary w-1/2 sm:w-auto mr-2">
                            <i data-lucide="printer" class="w-4 h-4 mr-2"></i> Print
                        </button>
                        <div class="dropdown w-1/2 sm:w-auto">
                            <button class="dropdown-toggle btn btn-outline-secondary w-full sm:w-auto" aria-expanded="false" data-tw-toggle="dropdown">
                                <i data-lucide="file-text" class="w-4 h-4 mr-2"></i> Export <i data-lucide="chevron-down" class="w-4 h-4 ml-auto sm:ml-2"></i>
                            </button>
                            <div class="dropdown-menu w-40">
                                <ul class="dropdown-content">
                                    <li>
                                        <a id="tabulator-export-csv" href="javascript:;" class="dropdown-item">
                                            <i data-lucide="file-text" class="w-4 h-4 mr-2"></i> Export CSV
                                        </a>
                                    </li>
                                    <li>
                                        <a id="tabulator-export-xlsx" href="javascript:;" class="dropdown-item">
                                            <i data-lucide="file-text" class="w-4 h-4 mr-2"></i> Export XLSX
                                        </a>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="overflow-x-auto scrollbar-hidden">
                    <div id="classPlanDateListsTutorTable" data-planid="{{ $plan->id }}" class="mt-5 table-report table-report--tabulator"></div>
                </div>
            </div>
        </div>
    </div>
    @if(isset($plan->tutorial->id) && $plan->tutorial->id > 0)
    <div class="accordion-item">
        <div id="planClassDatesAccordion-2" class="accordion-header">
            <button class="accordion-button collapsed relative w-full text-lg font-semibold" type="button" data-tw-toggle="collapse" data-tw-target="#planClassDatesAccordion-collapse-2" aria-expanded="false" aria-controls="planClassDatesAccordion-collapse-2">
                Tutorial Class Dates
                <span class="accordionCollaps"></span>
            </button>
        </div>
        <div id="planClassDatesAccordion-collapse-2" class="accordion-collapse collapse" aria-labelledby="planClassDatesAccordion-2" data-tw-parent="#planClassDatesAccordion">
            <div class="accordion-body">
                <div class="flex flex-col sm:flex-row sm:items-end xl:items-start">
                    <form id="tabulatorFilterForm-TPD" class="xl:flex sm:mr-auto" >
                        <div class="sm:flex items-center sm:mr-4 mt-2 xl:mt-0">
                            <label class="w-12 flex-none xl:w-auto xl:flex-initial mr-2">Date</label>
                            <input id="dates-TPD" name="dates" type="text" class="form-control sm:w-40 2xl:w-full mt-2 sm:mt-0 datepicker" data-format="DD-MM-YYYY" data-single-mode="true"  placeholder="DD-MM-YYYY">
                        </div>
                        <div class="sm:flex items-center sm:mr-4 mt-2 xl:mt-0">
                            <label class="w-12 flex-none xl:w-auto xl:flex-initial mr-2">Status</label>
                            <select id="status-TPD" name="status" class="form-select w-full mt-2 sm:mt-0 sm:w-auto" >
                                <option value="1">Active</option>
                                <option value="2">Archived</option>
                            </select>
                        </div>
                        <div class="mt-2 xl:mt-0">
                            <button id="tabulator-html-filter-go-TPD" type="button" class="btn btn-primary w-full sm:w-16" >Go</button>
                            <button id="tabulator-html-filter-reset-TPD" type="button" class="btn btn-secondary w-full sm:w-16 mt-2 sm:mt-0 sm:ml-1" >Reset</button>
                        </div>
                    </form>
                    <div class="flex mt-5 sm:mt-0">
                        <button id="tabulator-print-TPD" class="btn btn-outline-secondary w-1/2 sm:w-auto mr-2">
                            <i data-lucide="printer" class="w-4 h-4 mr-2"></i> Print
                        </button>
                        <div class="dropdown w-1/2 sm:w-auto">
                            <button class="dropdown-toggle btn btn-outline-secondary w-full sm:w-auto" aria-expanded="false" data-tw-toggle="dropdown">
                                <i data-lucide="file-text" class="w-4 h-4 mr-2"></i> Export <i data-lucide="chevron-down" class="w-4 h-4 ml-auto sm:ml-2"></i>
                            </button>
                            <div class="dropdown-menu w-40">
                                <ul class="dropdown-content">
                                    <li>
                                        <a id="tabulator-export-csv-TPD" href="javascript:;" class="dropdown-item">
                                            <i data-lucide="file-text" class="w-4 h-4 mr-2"></i> Export CSV
                                        </a>
                                    </li>
                                    <li>
                                        <a id="tabulator-export-xlsx-TPD" href="javascript:;" class="dropdown-item">
                                            <i data-lucide="file-text" class="w-4 h-4 mr-2"></i> Export XLSX
                                        </a>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="overflow-x-auto scrollbar-hidden">
                    <div id="classPlanDateListsTutorialTable" data-planid="{{ $plan->id }}" data-tutorialid="{{ $plan->tutorial->id }}" class="mt-5 table-report table-report--tabulator"></div>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>