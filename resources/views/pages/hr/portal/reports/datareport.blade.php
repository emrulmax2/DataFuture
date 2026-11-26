@extends('../layout/' . $layout)

@section('subhead')
    <title>{{ $title }}</title>
    {{-- Inter, as in the design. --}}
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    @vite('resources/css/hr-data-report.css')
@endsection

@section('subcontent')
    {{-- Employee Data Report (user's employee-data-report design, 26 Nov 2026), replacing the old form.
         Step 1 counts the employees (Search), step 2 picks the fields (each becomes a column, in the order
         ticked), step 3 sums it up and generates the XLSX. Fields and export: App\Services\HR\EmployeeDataReport. --}}
    @php
        $edrFields = collect(\App\Services\HR\EmployeeDataReport::FIELDS)->map(fn ($f, $k) => ['id' => $k, 'group' => $f[0], 'label' => $f[1], 'col' => $f[2], 'sens' => $f[3]])->values();
        $edrGroups = collect(\App\Services\HR\EmployeeDataReport::GROUPS)->map(fn ($name, $id) => ['id' => $id, 'name' => $name])->values();
    @endphp
    <div class="edr" data-edr
         data-count-url="{{ route('hr.portal.reports.datareport.count') }}"
         data-export-url="{{ route('employee.hr.datareport') }}"
         data-fields='@json($edrFields)'
         data-groups='@json($edrGroups)'>
        <div class="edr-head">
            <div class="edr-head__lead">
                <span class="edr-head__icon" aria-hidden="true"><i data-lucide="table-2"></i></span>
                <div>
                    <h1 class="edr-head__title">Employee Data Report</h1>
                    <p class="edr-head__sub">London Churchill College · build a spreadsheet of employee data</p>
                </div>
            </div>
            <a class="edr-back" href="{{ route('hr.portal.employment.reports.show') }}">
                <svg aria-hidden="true" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 12H5M11 6l-6 6 6 6"/></svg>
                Back to Employment Reports
            </a>
        </div>

        <div class="edr-layout">
            <div class="edr-main">
                <section class="edr-panel" aria-labelledby="edrStep1">
                    <div class="edr-step"><i>1</i><div><h2 id="edrStep1">Choose the employees</h2><p>Filter, then select Search to count who is included.</p></div></div>
                    <form class="edr-form" data-edr-form>
                        <label class="edr-field"><span>Type</span>
                            <select class="edr-ctl" data-edr-f="type">
                                <option value="">All types</option>
                                @foreach($employeeWorkType->sortBy('name') as $t)<option value="{{ $t->id }}">{{ $t->name }}</option>@endforeach
                            </select>
                        </label>
                        <label class="edr-field"><span>Department</span>
                            <select class="edr-ctl" data-edr-f="department">
                                <option value="">All departments</option>
                                @foreach($departments as $d)<option value="{{ $d->id }}">{{ $d->name }}</option>@endforeach
                            </select>
                        </label>
                        <label class="edr-field"><span>Gender</span>
                            <select class="edr-ctl" data-edr-f="gender">
                                <option value="">All genders</option>
                                @foreach($gender as $g)<option value="{{ $g->id }}">{{ $g->name }}</option>@endforeach
                            </select>
                        </label>
                        <label class="edr-field"><span>Started from</span><input class="edr-ctl" type="text" data-hr-date data-edr-f="from"></label>
                        <label class="edr-field"><span>Started to</span><input class="edr-ctl" type="text" data-hr-date data-edr-f="to"></label>
                        <label class="edr-field"><span>Status</span>
                            <select class="edr-ctl" data-edr-f="status">
                                @foreach(\App\Services\HR\EmployeeDataReport::STATUSES as $v => $label)<option value="{{ $v }}">{{ $label }}</option>@endforeach
                            </select>
                        </label>
                        <div class="edr-form__end">
                            <div class="edr-found" data-edr-found role="status"></div>
                            <div class="edr-form__btns">
                                <button type="button" class="edr-btn" data-edr-reset>Reset</button>
                                <button type="submit" class="edr-btn edr-btn--primary">Search</button>
                            </div>
                        </div>
                    </form>
                </section>

                <section class="edr-panel" aria-labelledby="edrStep2">
                    <div class="edr-step"><i>2</i><div><h2 id="edrStep2">Choose the data fields</h2><p>Each field becomes a column in the spreadsheet.</p></div></div>
                    <div class="edr-picktools">
                        <input type="search" class="edr-find" data-edr-find placeholder="Find a field, such as mobile or NI number" aria-label="Find a field" autocomplete="off">
                        <div class="edr-presets" role="group" aria-label="Quick picks">
                            <span class="edr-hint">Quick picks</span>
                            <button type="button" class="edr-preset" data-preset="contact">Contact list</button>
                            <button type="button" class="edr-preset" data-preset="payroll">Payroll</button>
                            <button type="button" class="edr-preset" data-preset="rtw">Right to work</button>
                            <button type="button" class="edr-preset" data-preset="all">Everything</button>
                            <button type="button" class="edr-preset" data-preset="none">Clear all</button>
                        </div>
                    </div>
                    <div data-edr-groups></div>
                </section>
            </div>

            <aside class="edr-panel edr-sum" aria-labelledby="edrStep3">
                <div class="edr-step" style="margin-bottom: 0;"><i>3</i><div><h2 id="edrStep3">Your export</h2><p>Updates as you choose.</p></div></div>
                <div class="edr-sum__body" data-edr-sum></div>
            </aside>
        </div>

        <ul class="edr-notes">
            <li><b>Four field names repeat across sections</b> (Email, Telephone, Mobile and Address). The spreadsheet names them apart, for example "Work Email" and "Emergency Mobile", so the columns are not confused.</li>
            <li>Values come from each employee's latest employment, eligibility, emergency contact and payment records. Sort Code and Account Number are filled for people paid by Bank Transfer; Salary and Hourly Rate come from the current working pattern's pay.</li>
            <li>Started from and Started to filter on the start date of the latest employment.</li>
        </ul>
    </div>
@endsection

@section('script')
    @vite('resources/js/hr-data-report.js')
@endsection
