@extends('../layout/' . $layout)

@section('subhead')
    <title>{{ $title }}- </title>
@endsection

@section('subcontent')

    <div class="agm-page agm-profile-page">
        @include('pages.agent.profile.show-info')

        <section class="agm-profile-panel">
            <div class="agm-profile-panel__header">
                <div class="agm-section-title">
                    <span aria-hidden="true"></span>
                    <h2>Applicant / Student Details</h2>
                </div>
            </div>

            <div id="studentSearchAccordionWrap" class="agm-profile-search">
                <div id="studentSearchAccordion" class="accordion">
                    <div class="accordion-item">
                        <div id="studentSearchAccordion-1" class="accordion-header">
                            <button id="studentGroupSearchBtn" class="agm-profile-search__toggle accordion-button collapsed" type="button" data-tw-toggle="collapse" data-tw-target="#studentSearchAccordion-collapse-1" aria-expanded="false" aria-controls="studentSearchAccordion-collapse-1">
                                <span>
                                    <i data-lucide="search"></i>
                                    Search
                                </span>
                                <i data-lucide="plus" class="agm-profile-search__open"></i>
                                <i data-lucide="minus" class="agm-profile-search__close"></i>
                            </button>
                        </div>
                        <div id="studentSearchAccordion-collapse-1" class="accordion-collapse collapse" aria-labelledby="studentSearchAccordion-1" data-tw-parent="#studentSearchAccordion">
                            <div class="accordion-body agm-profile-search__body">
                                <div class="agm-profile-filter-grid">
                                    <div class="agm-profile-field">
                                        <label for="application_no">Reference No</label>
                                        <div class="autoCompleteField" data-table="students">
                                            <input type="text" autocomplete="off" id="application_no" name="application_no" class="form-control registration_no" value="" placeholder=""/>
                                            <ul class="autoFillDropdown"></ul>
                                        </div>
                                    </div>
                                    <div class="agm-profile-field">
                                        <label for="query-CNTR">Search By Name</label>
                                        <div class="autoCompleteField" data-table="students">
                                            <input id="query-CNTR" autocomplete="off" name="query" type="text" class="form-control" placeholder="Search by Name">
                                            <ul class="autoFillDropdown"></ul>
                                        </div>
                                    </div>
                                    <div class="agm-profile-field">
                                        <label for="applicantEmail">Applicant Email</label>
                                        <div class="autoCompleteField" data-table="students">
                                            <input type="text" autocomplete="off" id="applicantEmail" name="applicantEmail" class="form-control email" value="" placeholder=""/>
                                            <ul class="autoFillDropdown"></ul>
                                        </div>
                                    </div>
                                    <div class="agm-profile-field">
                                        <label for="applicantPhone">Applicant Phone</label>
                                        <div class="autoCompleteField" data-table="students">
                                            <input type="text" autocomplete="off" id="applicantPhone" name="applicantPhone" class="form-control phone" value="" placeholder=""/>
                                            <ul class="autoFillDropdown"></ul>
                                        </div>
                                    </div>
                                    <div class="agm-profile-field">
                                        <label for="semesters">Intake Semester</label>
                                        <select id="semesters" class="w-full tom-selects" name="semesters[]" multiple>
                                            <option value="">Please Select</option>
                                            @if(!empty($semesters))
                                                @foreach($semesters as $sem)
                                                    <option value="{{ $sem->id }}">{{ $sem->name }}</option>
                                                @endforeach
                                            @endif
                                        </select>
                                        <div class="acc__input-error error-semesters text-danger"></div>
                                    </div>
                                    <div class="agm-profile-field">
                                        <label for="courses">Course</label>
                                        <select id="courses" class="w-full tom-selects" name="courses[]" multiple>
                                            <option value="">Please Select</option>
                                            @if(!empty($courses))
                                                @foreach($courses as $crs)
                                                    <option value="{{ $crs->id }}">{{ $crs->name }}</option>
                                                @endforeach
                                            @endif
                                        </select>
                                        <div class="acc__input-error error-course text-danger"></div>
                                    </div>
                                    <div class="agm-profile-field">
                                        <label for="statuses">Status</label>
                                        <select id="statuses" class="w-full tom-selects" name="statuses[]" multiple>
                                            <option value="">Please Select</option>
                                            @if(!empty($statuses))
                                                @foreach($statuses as $crs)
                                                    <option value="{{ $crs->id }}">{{ $crs->name }}</option>
                                                @endforeach
                                            @endif
                                        </select>
                                    </div>
                                    <div class="agm-profile-field">
                                        <label for="agents">Agent/SubAgents</label>
                                        <select id="agents" class="w-full tom-selects" name="agents[]" multiple>
                                            <option value="">Please Select</option>
                                            @if(!empty($agents))
                                                @foreach($agents as $crs)
                                                    <option value="{{ $crs->agent_user_id }}">{{ $crs->full_name }} [{{ $crs->code }}]</option>
                                                @endforeach
                                            @endif
                                        </select>
                                    </div>
                                </div>
                                <div class="agm-profile-search__actions">
                                    <button id="studentGroupSearchSubmitBtn" type="button" class="agm-btn agm-btn--primary">
                                        <i data-lucide="search"></i>
                                        Search
                                        <i data-loading-icon="oval" data-color="white" class="w-4 h-4 searchLoading hidden"></i>
                                    </button>
                                    <button id="studentGroupSearchResetBtn" type="button" class="agm-btn agm-btn--muted">
                                        <i data-lucide="refresh-ccw"></i>
                                        Reset
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="agm-profile-table-wrap">
                <div id="applicantApplicantionList" class="agm-profile-table agm-agent-table"></div>
            </div>
        </section>
    </div>



    @include('pages.agent.profile.show-modals')

@endsection

@section('script')
    @vite('resources/js/agent-global.js')
    @vite('resources/js/agent-profile.js')
@endsection
