@extends('../layout/' . $layout)

@section('subhead')
    <title>{{ $title }}</title>
@endsection

@section('subcontent')
    <div class="intro-y flex flex-col sm:flex-row items-center mt-8">
        <h2 class="text-lg font-medium mr-auto">{{ $subtitle }}</h2>
        <div class="w-full sm:w-auto flex mt-4 sm:mt-0">
            <a href="{{ route('dashboard') }}" class="add_btn btn btn-primary shadow-md mr-2">Back To Dashboard</a>
        </div>
    </div>

    <!-- BEGIN: Settings Page Content -->
    <div class="grid grid-cols-12 gap-6">
        <div class="col-span-12 lg:col-span-4 2xl:col-span-3 flex lg:block flex-col-reverse">
            <!-- BEGIN: Profile Info -->
            @include('pages.settings.sidebar')
            <!-- END: Profile Info -->
        </div>

        <div class="col-span-12 lg:col-span-8 2xl:col-span-9">
            <div class="intro-y grid grid-cols-12 gap-6 mt-5">
                <div class="col-span-12">
                    <div class="intro-y box optionBox">
                        <div class="flex flex-col optionBoxHeader sm:flex-row items-center p-5 border-b border-slate-200/60 dark:border-darkmode-400">
                            <h2 class="font-medium optionBoxTitle text-base mr-auto cursor-pointer">Countries</h2>
                            <div class="w-full sm:w-auto sm:ml-auto mt-3 sm:mt-0">
                                <button data-tw-toggle="modal" data-tw-target="#addCountryModal" type="button" class="add_btn btn btn-primary shadow-md mr-0 d-inline-flex items-center"><i data-lucide="plus-circle" class="w-4 h-4 mr-1"></i> Add New Country</button>
                                <i data-lucide="chevron-down" class="w-8 h-8 text-slate-600 arrowNavigation"></i>
                            </div>
                        </div>
                        <div class="optionBoxBody p-5" data-tableid="countryListTable">
                            @include('pages.settings.studentoption.country.index')
                        </div>
                    </div>
                </div>
                <div class="col-span-12">
                    <div class="intro-y box optionBox">
                        <div class="flex flex-col optionBoxHeader sm:flex-row items-center p-5 border-b border-slate-200/60 dark:border-darkmode-400">
                            <h2 class="font-medium optionBoxTitle text-base mr-auto cursor-pointer">Country of permanent address</h2>
                            <div class="w-full sm:w-auto sm:ml-auto mt-3 sm:mt-0">
                                <button data-tw-toggle="modal" data-tw-target="#addPermaddcountryModal" type="button" class="add_btn btn btn-primary shadow-md mr-0 d-inline-flex items-center"><i data-lucide="plus-circle" class="w-4 h-4 mr-1"></i> Add New</button>
                                <i data-lucide="chevron-down" class="w-8 h-8 text-slate-600 arrowNavigation"></i>
                            </div>
                        </div>
                        <div class="optionBoxBody p-5" data-tableid="PermaddcountryListTable">
                            @include('pages.settings.studentoption.permaddcountry.index')
                        </div>
                    </div>
                </div>
                <div class="col-span-12">
                    <div class="intro-y box optionBox">
                        <div class="flex flex-col optionBoxHeader sm:flex-row items-center p-5 border-b border-slate-200/60 dark:border-darkmode-400">
                            <h2 class="font-medium optionBoxTitle text-base mr-auto cursor-pointer">Disabilities</h2>
                            <div class="w-full sm:w-auto sm:ml-auto mt-3 sm:mt-0">
                                <button data-tw-toggle="modal" data-tw-target="#addDisabilityModal" type="button" class="add_btn btn btn-primary shadow-md mr-0 d-inline-flex items-center"><i data-lucide="plus-circle" class="w-4 h-4 mr-1"></i> Add New Disability</button>
                                <i data-lucide="chevron-down" class="w-8 h-8 text-slate-600 arrowNavigation"></i>
                            </div>
                        </div>
                        <div class="optionBoxBody p-5" data-tableid="disabilityListTable">
                            @include('pages.settings.studentoption.disability.index')
                        </div>
                    </div>
                </div>
                <div class="col-span-12">
                    <div class="intro-y box optionBox">
                        <div class="flex flex-col optionBoxHeader sm:flex-row items-center p-5 border-b border-slate-200/60 dark:border-darkmode-400">
                            <h2 class="font-medium optionBoxTitle text-base mr-auto cursor-pointer">Disable Allowance</h2>
                            <div class="w-full sm:w-auto sm:ml-auto mt-3 sm:mt-0">
                                <button data-tw-toggle="modal" data-tw-target="#addDisableAllowanceModal" type="button" class="add_btn btn btn-primary shadow-md mr-0 d-inline-flex items-center"><i data-lucide="plus-circle" class="w-4 h-4 mr-1"></i> Add New</button>
                                <i data-lucide="chevron-down" class="w-8 h-8 text-slate-600 arrowNavigation"></i>
                            </div>
                        </div>
                        <div class="optionBoxBody p-5" data-tableid="DisableAllowanceListTable">
                            @include('pages.settings.studentoption.disableAllowance.index')
                        </div>
                    </div>
                </div>
                <div class="col-span-12">
                    <div class="intro-y box optionBox">
                        <div class="flex flex-col optionBoxHeader sm:flex-row items-center p-5 border-b border-slate-200/60 dark:border-darkmode-400">
                            <h2 class="font-medium optionBoxTitle text-base mr-auto cursor-pointer">Equivalent or Lower qualification</h2>
                            <div class="w-full sm:w-auto sm:ml-auto mt-3 sm:mt-0">
                                <button data-tw-toggle="modal" data-tw-target="#addEqvOrLwQfModal" type="button" class="add_btn btn btn-primary shadow-md mr-0 d-inline-flex items-center"><i data-lucide="plus-circle" class="w-4 h-4 mr-1"></i> Add New</button>
                                <i data-lucide="chevron-down" class="w-8 h-8 text-slate-600 arrowNavigation"></i>
                            </div>
                        </div>
                        <div class="optionBoxBody p-5" data-tableid="EqvOrLwQfListTable">
                            @include('pages.settings.studentoption.eqvorlwqf.index')
                        </div>
                    </div>
                </div>
                <div class="col-span-12">
                    <div class="intro-y box optionBox">
                        <div class="flex flex-col optionBoxHeader sm:flex-row items-center p-5 border-b border-slate-200/60 dark:border-darkmode-400">
                            <h2 class="font-medium optionBoxTitle text-base mr-auto cursor-pointer">Ethnicities</h2>
                            <div class="w-full sm:w-auto sm:ml-auto mt-3 sm:mt-0">
                                <button data-tw-toggle="modal" data-tw-target="#addEthnicityModal" type="button" class="add_btn btn btn-primary shadow-md mr-0 d-inline-flex items-center"><i data-lucide="plus-circle" class="w-4 h-4 mr-1"></i> Add New Ethnicity</button>
                                <i data-lucide="chevron-down" class="w-8 h-8 text-slate-600 arrowNavigation"></i>
                            </div>
                        </div>
                        <div class="optionBoxBody p-5" data-tableid="ethnicityListTable">
                            @include('pages.settings.studentoption.ethnicity.index')
                        </div>
                    </div>
                </div>
                <div class="col-span-12">
                    <div class="intro-y box optionBox">
                        <div class="flex flex-col optionBoxHeader sm:flex-row items-center p-5 border-b border-slate-200/60 dark:border-darkmode-400">
                            <h2 class="font-medium optionBoxTitle text-base mr-auto cursor-pointer">Exchange Programme</h2>
                            <div class="w-full sm:w-auto sm:ml-auto mt-3 sm:mt-0">
                                <button data-tw-toggle="modal" data-tw-target="#addExchangeProgrammeModal" type="button" class="add_btn btn btn-primary shadow-md mr-0 d-inline-flex items-center"><i data-lucide="plus-circle" class="w-4 h-4 mr-1"></i> Add New</button>
                                <i data-lucide="chevron-down" class="w-8 h-8 text-slate-600 arrowNavigation"></i>
                            </div>
                        </div>
                        <div class="optionBoxBody p-5" data-tableid="ExchangeProgrammeListTable">
                            @include('pages.settings.studentoption.exchangeProgramme.index')
                        </div>
                    </div>
                </div>
                <div class="col-span-12">
                    <div class="intro-y box optionBox">
                        <div class="flex flex-col optionBoxHeader sm:flex-row items-center p-5 border-b border-slate-200/60 dark:border-darkmode-400">
                            <h2 class="font-medium optionBoxTitle text-base mr-auto cursor-pointer">Fee Eligibilities</h2>
                            <div class="w-full sm:w-auto sm:ml-auto mt-3 sm:mt-0">
                                <button data-tw-toggle="modal" data-tw-target="#addFeeEligibilityModal" type="button" class="add_btn btn btn-primary shadow-md mr-0 d-inline-flex items-center"><i data-lucide="plus-circle" class="w-4 h-4 mr-1"></i> Add New</button>
                                <i data-lucide="chevron-down" class="w-8 h-8 text-slate-600 arrowNavigation"></i>
                            </div>
                        </div>
                        <div class="optionBoxBody p-5" data-tableid="feeEligibilitiesListTable">
                            @include('pages.settings.studentoption.feeeligibility.index')
                        </div>
                    </div>
                </div>
                <div class="col-span-12">
                    <div class="intro-y box optionBox">
                        <div class="flex flex-col optionBoxHeader sm:flex-row items-center p-5 border-b border-slate-200/60 dark:border-darkmode-400">
                            <h2 class="font-medium optionBoxTitle text-base mr-auto cursor-pointer">Funding Completion</h2>
                            <div class="w-full sm:w-auto sm:ml-auto mt-3 sm:mt-0">
                                <button data-tw-toggle="modal" data-tw-target="#addFundingCompletionModal" type="button" class="add_btn btn btn-primary shadow-md mr-0 d-inline-flex items-center"><i data-lucide="plus-circle" class="w-4 h-4 mr-1"></i> Add New</button>
                                <i data-lucide="chevron-down" class="w-8 h-8 text-slate-600 arrowNavigation"></i>
                            </div>
                        </div>
                        <div class="optionBoxBody p-5" data-tableid="FundingCompletionListTable">
                            @include('pages.settings.studentoption.fundingcompletion.index')
                        </div>
                    </div>
                </div>
                <div class="col-span-12">
                    <div class="intro-y box optionBox">
                        <div class="flex flex-col optionBoxHeader sm:flex-row items-center p-5 border-b border-slate-200/60 dark:border-darkmode-400">
                            <h2 class="font-medium optionBoxTitle text-base mr-auto cursor-pointer">Funding Length</h2>
                            <div class="w-full sm:w-auto sm:ml-auto mt-3 sm:mt-0">
                                <button data-tw-toggle="modal" data-tw-target="#addFundingLengthModal" type="button" class="add_btn btn btn-primary shadow-md mr-0 d-inline-flex items-center"><i data-lucide="plus-circle" class="w-4 h-4 mr-1"></i> Add New</button>
                                <i data-lucide="chevron-down" class="w-8 h-8 text-slate-600 arrowNavigation"></i>
                            </div>
                        </div>
                        <div class="optionBoxBody p-5" data-tableid="FundingLengthListTable">
                            @include('pages.settings.studentoption.fundinglength.index')
                        </div>
                    </div>
                </div>
                <div class="col-span-12">
                    <div class="intro-y box optionBox">
                        <div class="flex flex-col optionBoxHeader sm:flex-row items-center p-5 border-b border-slate-200/60 dark:border-darkmode-400">
                            <h2 class="font-medium optionBoxTitle text-base mr-auto cursor-pointer">Genders</h2>
                            <div class="w-full sm:w-auto sm:ml-auto mt-3 sm:mt-0">
                                <button data-tw-toggle="modal" data-tw-target="#addHgenModal" type="button" class="add_btn btn btn-primary shadow-md mr-0 d-inline-flex items-center"><i data-lucide="plus-circle" class="w-4 h-4 mr-1"></i> Add New Gender</button>
                                <i data-lucide="chevron-down" class="w-8 h-8 text-slate-600 arrowNavigation"></i>
                            </div>
                        </div>
                        <div class="optionBoxBody p-5" data-tableid="hgenListTable">
                            @include('pages.settings.studentoption.hesagender.index')
                        </div>
                    </div>
                </div>
                <div class="col-span-12">
                    <div class="intro-y box optionBox">
                        <div class="flex flex-col optionBoxHeader sm:flex-row items-center p-5 border-b border-slate-200/60 dark:border-darkmode-400">
                            <h2 class="font-medium optionBoxTitle text-base mr-auto cursor-pointer">Heapes Population</h2>
                            <div class="w-full sm:w-auto sm:ml-auto mt-3 sm:mt-0">
                                <button data-tw-toggle="modal" data-tw-target="#addHeapesPopulationModal" type="button" class="add_btn btn btn-primary shadow-md mr-0 d-inline-flex items-center"><i data-lucide="plus-circle" class="w-4 h-4 mr-1"></i> Add New</button>
                                <i data-lucide="chevron-down" class="w-8 h-8 text-slate-600 arrowNavigation"></i>
                            </div>
                        </div>
                        <div class="optionBoxBody p-5" data-tableid="HeapesPopulationListTable">
                            @include('pages.settings.studentoption.heapesPopulation.index')
                        </div>
                    </div>
                </div>
                <div class="col-span-12">
                    <div class="intro-y box optionBox">
                        <div class="flex flex-col optionBoxHeader sm:flex-row items-center p-5 border-b border-slate-200/60 dark:border-darkmode-400">
                            <h2 class="font-medium optionBoxTitle text-base mr-auto cursor-pointer">Hesa Qualification Award</h2>
                            <div class="w-full sm:w-auto sm:ml-auto mt-3 sm:mt-0">
                                <button data-tw-toggle="modal" data-tw-target="#addHesaQualificationAwardModal" type="button" class="add_btn btn btn-primary shadow-md mr-0 d-inline-flex items-center"><i data-lucide="plus-circle" class="w-4 h-4 mr-1"></i> Add New</button>
                                <i data-lucide="chevron-down" class="w-8 h-8 text-slate-600 arrowNavigation"></i>
                            </div>
                        </div>
                        <div class="optionBoxBody p-5" data-tableid="HesaQualificationAwardListTable">
                            @include('pages.settings.studentoption.hesaQualificationAward.index')
                        </div>
                    </div>
                </div>
                <div class="col-span-12">
                    <div class="intro-y box optionBox">
                        <div class="flex flex-col optionBoxHeader sm:flex-row items-center p-5 border-b border-slate-200/60 dark:border-darkmode-400">
                            <h2 class="font-medium optionBoxTitle text-base mr-auto cursor-pointer">Hesa Qualification Subject</h2>
                            <div class="w-full sm:w-auto sm:ml-auto mt-3 sm:mt-0">
                                <button data-tw-toggle="modal" data-tw-target="#addHesaQualSubModal" type="button" class="add_btn btn btn-primary shadow-md mr-0 d-inline-flex items-center"><i data-lucide="plus-circle" class="w-4 h-4 mr-1"></i> Add New</button>
                                <i data-lucide="chevron-down" class="w-8 h-8 text-slate-600 arrowNavigation"></i>
                            </div>
                        </div>
                        <div class="optionBoxBody p-5" data-tableid="HesaQualSubListTable">
                            @include('pages.settings.studentoption.hesaqualsub.index')
                        </div>
                    </div>
                </div>
                <div class="col-span-12">
                    <div class="intro-y box optionBox">
                        <div class="flex flex-col optionBoxHeader sm:flex-row items-center p-5 border-b border-slate-200/60 dark:border-darkmode-400">
                            <h2 class="font-medium optionBoxTitle text-base mr-auto cursor-pointer">Highest qualification on entry</h2>
                            <div class="w-full sm:w-auto sm:ml-auto mt-3 sm:mt-0">
                                <button data-tw-toggle="modal" data-tw-target="#addHighestqoeModal" type="button" class="add_btn btn btn-primary shadow-md mr-0 d-inline-flex items-center"><i data-lucide="plus-circle" class="w-4 h-4 mr-1"></i> Add New</button>
                                <i data-lucide="chevron-down" class="w-8 h-8 text-slate-600 arrowNavigation"></i>
                            </div>
                        </div>
                        <div class="optionBoxBody p-5" data-tableid="HighestqoeListTable">
                            @include('pages.settings.studentoption.highestqoe.index')
                        </div>
                    </div>
                </div>
                <div class="col-span-12">
                    <div class="intro-y box optionBox">
                        <div class="flex flex-col optionBoxHeader sm:flex-row items-center p-5 border-b border-slate-200/60 dark:border-darkmode-400">
                            <h2 class="font-medium optionBoxTitle text-base mr-auto cursor-pointer">Kins Relation</h2>
                            <div class="w-full sm:w-auto sm:ml-auto mt-3 sm:mt-0">
                                <button data-tw-toggle="modal" data-tw-target="#addKinsModal" type="button" class="add_btn btn btn-primary shadow-md mr-0 d-inline-flex items-center"><i data-lucide="plus-circle" class="w-4 h-4 mr-1"></i> Add New Relation</button>
                                <i data-lucide="chevron-down" class="w-8 h-8 text-slate-600 arrowNavigation"></i>
                            </div>
                        </div>
                        <div class="optionBoxBody p-5" data-tableid="kinsListTable">
                            @include('pages.settings.studentoption.kins-relation.index')
                        </div>
                    </div>
                </div>
                <div class="col-span-12">
                    <div class="intro-y box optionBox">
                        <div class="flex flex-col optionBoxHeader sm:flex-row items-center p-5 border-b border-slate-200/60 dark:border-darkmode-400">
                            <h2 class="font-medium optionBoxTitle text-base mr-auto cursor-pointer">Location Of Study</h2>
                            <div class="w-full sm:w-auto sm:ml-auto mt-3 sm:mt-0">
                                <button data-tw-toggle="modal" data-tw-target="#addLocationOfStudyModal" type="button" class="add_btn btn btn-primary shadow-md mr-0 d-inline-flex items-center"><i data-lucide="plus-circle" class="w-4 h-4 mr-1"></i> Add New</button>
                                <i data-lucide="chevron-down" class="w-8 h-8 text-slate-600 arrowNavigation"></i>
                            </div>
                        </div>
                        <div class="optionBoxBody p-5" data-tableid="LocationOfStudyListTable">
                            @include('pages.settings.studentoption.locationOfStudy.index')
                        </div>
                    </div>
                </div>
                <div class="col-span-12">
                    <div class="intro-y box optionBox">
                        <div class="flex flex-col optionBoxHeader sm:flex-row items-center p-5 border-b border-slate-200/60 dark:border-darkmode-400">
                            <h2 class="font-medium optionBoxTitle text-base mr-auto cursor-pointer">Major Source Of Tuition Fee</h2>
                            <div class="w-full sm:w-auto sm:ml-auto mt-3 sm:mt-0">
                                <button data-tw-toggle="modal" data-tw-target="#addMajorSourceOfTuitionFeeModal" type="button" class="add_btn btn btn-primary shadow-md mr-0 d-inline-flex items-center"><i data-lucide="plus-circle" class="w-4 h-4 mr-1"></i> Add New</button>
                                <i data-lucide="chevron-down" class="w-8 h-8 text-slate-600 arrowNavigation"></i>
                            </div>
                        </div>
                        <div class="optionBoxBody p-5" data-tableid="MajorSourceOfTuitionFeeListTable">
                            @include('pages.settings.studentoption.majorSourceOfTuitionFee.index')
                        </div>
                    </div>
                </div>
                <div class="col-span-12">
                    <div class="intro-y box optionBox">
                        <div class="flex flex-col optionBoxHeader sm:flex-row items-center p-5 border-b border-slate-200/60 dark:border-darkmode-400">
                            <h2 class="font-medium optionBoxTitle text-base mr-auto cursor-pointer">Module Outcome</h2>
                            <div class="w-full sm:w-auto sm:ml-auto mt-3 sm:mt-0">
                                <button data-tw-toggle="modal" data-tw-target="#addModuleOutcomeModal" type="button" class="add_btn btn btn-primary shadow-md mr-0 d-inline-flex items-center"><i data-lucide="plus-circle" class="w-4 h-4 mr-1"></i> Add New</button>
                                <i data-lucide="chevron-down" class="w-8 h-8 text-slate-600 arrowNavigation"></i>
                            </div>
                        </div>
                        <div class="optionBoxBody p-5" data-tableid="ModuleOutcomeTable">
                            @include('pages.settings.studentoption.moduleOutcome.index')
                        </div>
                    </div>
                </div>
                <div class="col-span-12">
                    <div class="intro-y box optionBox">
                        <div class="flex flex-col optionBoxHeader sm:flex-row items-center p-5 border-b border-slate-200/60 dark:border-darkmode-400">
                            <h2 class="font-medium optionBoxTitle text-base mr-auto cursor-pointer">Module Result</h2>
                            <div class="w-full sm:w-auto sm:ml-auto mt-3 sm:mt-0">
                                <button data-tw-toggle="modal" data-tw-target="#addModuleResultModal" type="button" class="add_btn btn btn-primary shadow-md mr-0 d-inline-flex items-center"><i data-lucide="plus-circle" class="w-4 h-4 mr-1"></i> Add New</button>
                                <i data-lucide="chevron-down" class="w-8 h-8 text-slate-600 arrowNavigation"></i>
                            </div>
                        </div>
                        <div class="optionBoxBody p-5" data-tableid="ModuleResultTable">
                            @include('pages.settings.studentoption.moduleResult.index')
                        </div>
                    </div>
                </div>
                <div class="col-span-12">
                    <div class="intro-y box optionBox">
                        <div class="flex flex-col optionBoxHeader sm:flex-row items-center p-5 border-b border-slate-200/60 dark:border-darkmode-400">
                            <h2 class="font-medium optionBoxTitle text-base mr-auto cursor-pointer">Non Regulated Fee Flag</h2>
                            <div class="w-full sm:w-auto sm:ml-auto mt-3 sm:mt-0">
                                <button data-tw-toggle="modal" data-tw-target="#addNonRegFFlgModal" type="button" class="add_btn btn btn-primary shadow-md mr-0 d-inline-flex items-center"><i data-lucide="plus-circle" class="w-4 h-4 mr-1"></i> Add New</button>
                                <i data-lucide="chevron-down" class="w-8 h-8 text-slate-600 arrowNavigation"></i>
                            </div>
                        </div>
                        <div class="optionBoxBody p-5" data-tableid="NonRegFFlgListTable">
                            @include('pages.settings.studentoption.nonregfflg.index')
                        </div>
                    </div>
                </div>
                <div class="col-span-12">
                    <div class="intro-y box optionBox">
                        <div class="flex flex-col optionBoxHeader sm:flex-row items-center p-5 border-b border-slate-200/60 dark:border-darkmode-400">
                            <h2 class="font-medium optionBoxTitle text-base mr-auto cursor-pointer">Previous provider</h2>
                            <div class="w-full sm:w-auto sm:ml-auto mt-3 sm:mt-0">
                                <button data-tw-toggle="modal" data-tw-target="#addPreviousproviderModal" type="button" class="add_btn btn btn-primary shadow-md mr-0 d-inline-flex items-center"><i data-lucide="plus-circle" class="w-4 h-4 mr-1"></i> Add New</button>
                                <i data-lucide="chevron-down" class="w-8 h-8 text-slate-600 arrowNavigation"></i>
                            </div>
                        </div>
                        <div class="optionBoxBody p-5" data-tableid="PreviousproviderListTable">
                            @include('pages.settings.studentoption.previousprovider.index')
                        </div>
                    </div>
                </div>
                <div class="col-span-12">
                    <div class="intro-y box optionBox">
                        <div class="flex flex-col optionBoxHeader sm:flex-row items-center p-5 border-b border-slate-200/60 dark:border-darkmode-400">
                            <h2 class="font-medium optionBoxTitle text-base mr-auto cursor-pointer">Qualification type identifier</h2>
                            <div class="w-full sm:w-auto sm:ml-auto mt-3 sm:mt-0">
                                <button data-tw-toggle="modal" data-tw-target="#addQaualtypeidModal" type="button" class="add_btn btn btn-primary shadow-md mr-0 d-inline-flex items-center"><i data-lucide="plus-circle" class="w-4 h-4 mr-1"></i> Add New</button>
                                <i data-lucide="chevron-down" class="w-8 h-8 text-slate-600 arrowNavigation"></i>
                            </div>
                        </div>
                        <div class="optionBoxBody p-5" data-tableid="QaualtypeidListTable">
                            @include('pages.settings.studentoption.qaualtypeid.index')
                        </div>
                    </div>
                </div>
                <div class="col-span-12">
                    <div class="intro-y box optionBox">
                        <div class="flex flex-col optionBoxHeader sm:flex-row items-center p-5 border-b border-slate-200/60 dark:border-darkmode-400">
                            <h2 class="font-medium optionBoxTitle text-base mr-auto cursor-pointer">Reason for Ending course session</h2>
                            <div class="w-full sm:w-auto sm:ml-auto mt-3 sm:mt-0">
                                <button data-tw-toggle="modal" data-tw-target="#addRsForEndCrsSModal" type="button" class="add_btn btn btn-primary shadow-md mr-0 d-inline-flex items-center"><i data-lucide="plus-circle" class="w-4 h-4 mr-1"></i> Add New</button>
                                <i data-lucide="chevron-down" class="w-8 h-8 text-slate-600 arrowNavigation"></i>
                            </div>
                        </div>
                        <div class="optionBoxBody p-5" data-tableid="RsForEndCrsSListTable">
                            @include('pages.settings.studentoption.rsfendcrss.index')
                        </div>
                    </div>
                </div>
                <div class="col-span-12">
                    <div class="intro-y box optionBox">
                        <div class="flex flex-col optionBoxHeader sm:flex-row items-center p-5 border-b border-slate-200/60 dark:border-darkmode-400">
                            <h2 class="font-medium optionBoxTitle text-base mr-auto cursor-pointer">Reason for Engagement ending</h2>
                            <div class="w-full sm:w-auto sm:ml-auto mt-3 sm:mt-0">
                                <button data-tw-toggle="modal" data-tw-target="#addRsnengendModal" type="button" class="add_btn btn btn-primary shadow-md mr-0 d-inline-flex items-center"><i data-lucide="plus-circle" class="w-4 h-4 mr-1"></i> Add New</button>
                                <i data-lucide="chevron-down" class="w-8 h-8 text-slate-600 arrowNavigation"></i>
                            </div>
                        </div>
                        <div class="optionBoxBody p-5" data-tableid="RsnengendListTable">
                            @include('pages.settings.studentoption.rsnengend.index')
                        </div>
                    </div>
                </div>
                <div class="col-span-12">
                    <div class="intro-y box optionBox">
                        <div class="flex flex-col optionBoxHeader sm:flex-row items-center p-5 border-b border-slate-200/60 dark:border-darkmode-400">
                            <h2 class="font-medium optionBoxTitle text-base mr-auto cursor-pointer">Religions</h2>
                            <div class="w-full sm:w-auto sm:ml-auto mt-3 sm:mt-0">
                                <button data-tw-toggle="modal" data-tw-target="#addRelgnModal" type="button" class="add_btn btn btn-primary shadow-md mr-0 d-inline-flex items-center"><i data-lucide="plus-circle" class="w-4 h-4 mr-1"></i> Add New Religion</button>
                                <i data-lucide="chevron-down" class="w-8 h-8 text-slate-600 arrowNavigation"></i>
                            </div>
                        </div>
                        <div class="optionBoxBody p-5" data-tableid="relgnListTable">
                            @include('pages.settings.studentoption.religion.index')
                        </div>
                    </div>
                </div>
                <div class="col-span-12">
                    <div class="intro-y box optionBox">
                        <div class="flex flex-col optionBoxHeader sm:flex-row items-center p-5 border-b border-slate-200/60 dark:border-darkmode-400">
                            <h2 class="font-medium optionBoxTitle text-base mr-auto cursor-pointer">Sexual Orientation</h2>
                            <div class="w-full sm:w-auto sm:ml-auto mt-3 sm:mt-0">
                                <button data-tw-toggle="modal" data-tw-target="#addSexoModal" type="button" class="add_btn btn btn-primary shadow-md mr-0 d-inline-flex items-center"><i data-lucide="plus-circle" class="w-4 h-4 mr-1"></i> Add New Orientation</button>
                                <i data-lucide="chevron-down" class="w-8 h-8 text-slate-600 arrowNavigation"></i>
                            </div>
                        </div>
                        <div class="optionBoxBody p-5" data-tableid="sexoListTable">
                            @include('pages.settings.studentoption.sexual-orientation.index')
                        </div>
                    </div>
                </div>
                <div class="col-span-12">
                    <div class="intro-y box optionBox">
                        <div class="flex flex-col optionBoxHeader sm:flex-row items-center p-5 border-b border-slate-200/60 dark:border-darkmode-400">
                            <h2 class="font-medium optionBoxTitle text-base mr-auto cursor-pointer">Sex Identifier</h2>
                            <div class="w-full sm:w-auto sm:ml-auto mt-3 sm:mt-0">
                                <button data-tw-toggle="modal" data-tw-target="#addStudentidentifierModal" type="button" class="add_btn btn btn-primary shadow-md mr-0 d-inline-flex items-center"><i data-lucide="plus-circle" class="w-4 h-4 mr-1"></i> Add New Student Identifier</button>
                                <i data-lucide="chevron-down" class="w-8 h-8 text-slate-600 arrowNavigation"></i>
                            </div>
                        </div>
                        <div class="optionBoxBody p-5" data-tableid="studentidentifierListTable">
                            @include('pages.settings.studentoption.sexidentifier.index')
                        </div>
                    </div>
                </div>
                <div class="col-span-12">
                    <div class="intro-y box optionBox">
                        <div class="flex flex-col optionBoxHeader sm:flex-row items-center p-5 border-b border-slate-200/60 dark:border-darkmode-400">
                            <h2 class="font-medium optionBoxTitle text-base mr-auto cursor-pointer">Student Support Eligibility</h2>
                            <div class="w-full sm:w-auto sm:ml-auto mt-3 sm:mt-0">
                                <button data-tw-toggle="modal" data-tw-target="#addStudentSupportEligibilityModal" type="button" class="add_btn btn btn-primary shadow-md mr-0 d-inline-flex items-center"><i data-lucide="plus-circle" class="w-4 h-4 mr-1"></i> Add New</button>
                                <i data-lucide="chevron-down" class="w-8 h-8 text-slate-600 arrowNavigation"></i>
                            </div>
                        </div>
                        <div class="optionBoxBody p-5" data-tableid="StudentSupportEligibilityListTable">
                            @include('pages.settings.studentoption.studentSupportEligibility.index')
                        </div>
                    </div>
                </div>
                <div class="col-span-12">
                    <div class="intro-y box optionBox">
                        <div class="flex flex-col optionBoxHeader sm:flex-row items-center p-5 border-b border-slate-200/60 dark:border-darkmode-400">
                            <h2 class="font-medium optionBoxTitle text-base mr-auto cursor-pointer">Suspension Of Active Study</h2>
                            <div class="w-full sm:w-auto sm:ml-auto mt-3 sm:mt-0">
                                <button data-tw-toggle="modal" data-tw-target="#addSuspensionOfActiveStudyModal" type="button" class="add_btn btn btn-primary shadow-md mr-0 d-inline-flex items-center"><i data-lucide="plus-circle" class="w-4 h-4 mr-1"></i> Add New</button>
                                <i data-lucide="chevron-down" class="w-8 h-8 text-slate-600 arrowNavigation"></i>
                            </div>
                        </div>
                        <div class="optionBoxBody p-5" data-tableid="SuspensionOfActiveStudyListTable">
                            @include('pages.settings.studentoption.suspensionOfActiveStudy.index')
                        </div>
                    </div>
                </div>
                <div class="col-span-12">
                    <div class="intro-y box optionBox">
                        <div class="flex flex-col optionBoxHeader sm:flex-row items-center p-5 border-b border-slate-200/60 dark:border-darkmode-400">
                            <h2 class="font-medium optionBoxTitle text-base mr-auto cursor-pointer">Term Time Accommodation Type</h2>
                            <div class="w-full sm:w-auto sm:ml-auto mt-3 sm:mt-0">
                                <button data-tw-toggle="modal" data-tw-target="#addTTACCOMModal" type="button" class="add_btn btn btn-primary shadow-md mr-0 d-inline-flex items-center"><i data-lucide="plus-circle" class="w-4 h-4 mr-1"></i> Add New Term Time Accommodation Type</button>
                                <i data-lucide="chevron-down" class="w-8 h-8 text-slate-600 arrowNavigation"></i>
                            </div>
                        </div>
                        <div class="optionBoxBody p-5" data-tableid="termtimeaccommodationtypeListTable">
                            @include('pages.settings.studentoption.termtimeaccommodationtype.index')
                        </div>
                    </div>
                </div>
                <div class="col-span-12">
                    <div class="intro-y box optionBox">
                        <div class="flex flex-col optionBoxHeader sm:flex-row items-center p-5 border-b border-slate-200/60 dark:border-darkmode-400">
                            <h2 class="font-medium optionBoxTitle text-base mr-auto cursor-pointer">Titles</h2>
                            <div class="w-full sm:w-auto sm:ml-auto mt-3 sm:mt-0 flex justify-end items-center">
                                <button data-tw-toggle="modal" data-tw-target="#addTitleModal" type="button" class="add_btn btn btn-primary shadow-md mr-0 d-inline-flex items-center"><i data-lucide="plus-circle" class="w-4 h-4 mr-1"></i> Add New Title</button>
                                <i data-lucide="chevron-down" class="w-8 h-8 text-slate-600 arrowNavigation"></i>
                            </div>
                        </div>
                        <div class="optionBoxBody p-5" data-tableid="titleListTable">
                            @include('pages.settings.studentoption.title.index')
                        </div>
                    </div>
                </div>
                <div class="col-span-12">
                    <div class="intro-y box optionBox">
                        <div class="flex flex-col optionBoxHeader sm:flex-row items-center p-5 border-b border-slate-200/60 dark:border-darkmode-400">
                            <h2 class="font-medium optionBoxTitle text-base mr-auto cursor-pointer">Work Placement Companies</h2>
                            <div class="w-full sm:w-auto sm:ml-auto mt-3 sm:mt-0 flex justify-end items-center">
                                <button data-tw-toggle="modal" data-tw-target="#addWPCompanyModal" type="button" class="add_btn btn btn-primary shadow-md mr-0 d-inline-flex items-center"><i data-lucide="plus-circle" class="w-4 h-4 mr-1"></i> Add Company</button>
                                <i data-lucide="chevron-down" class="w-8 h-8 text-slate-600 arrowNavigation"></i>
                            </div>
                        </div>
                        <div class="optionBoxBody p-5" data-tableid="wpCompanyListTable">
                            @include('pages.settings.studentoption.workplacement-company.index')
                        </div>
                    </div>
                </div>
                
            </div>
        </div>
    </div>
    <!-- END: Settings Page Content -->

    <!-- BEGIN: Success Modal Content -->
    <div id="successModal" class="modal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-body p-0">
                    <div class="p-5 text-center">
                        <i data-lucide="check-circle" class="w-16 h-16 text-success mx-auto mt-3"></i>
                        <div class="text-3xl mt-5 successModalTitle">Success</div>
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
    <!-- BEGIN: Delete Confirm Modal Content -->
    <div id="confirmModal" class="modal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-body p-0">
                    <div class="p-5 text-center">
                        <i data-lucide="x-circle" class="w-16 h-16 text-danger mx-auto mt-3"></i>
                        <div class="text-3xl mt-5 confModTitle">Are you sure?</div>
                        <div class="text-slate-500 mt-2 confModDesc"></div>
                    </div>
                    <div class="px-5 pb-8 text-center">
                        <button type="button" data-tw-dismiss="modal" class="btn btn-outline-secondary w-24 mr-1">No, Cancel</button>
                        <button type="button" data-id="0" data-action="none" class="agreeWith btn btn-danger w-auto">Yes, I agree</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- END: Delete Confirm Modal Content -->
    
@endsection

@section('script')
    @vite('resources/js/settings.js')
    @vite('resources/js/student-option.js')
    @vite('resources/js/title.js')
    @vite('resources/js/ethnicity.js')
    @vite('resources/js/kins-relation.js')
    @vite('resources/js/sexual-rientation.js')
    @vite('resources/js/religion.js')
    @vite('resources/js/hesagender.js')
    @vite('resources/js/country.js')
    @vite('resources/js/disabilities.js')
    @vite('resources/js/feeeligibilities.js')
    @vite('resources/js/funding-completion.js')
    @vite('resources/js/funding-length.js')
    @vite('resources/js/module-outcome.js')
    @vite('resources/js/module-result.js')
    @vite('resources/js/apelcredit.js')
    @vite('resources/js/highest-qualification-on-entry.js')
    @vite('resources/js/hesa-qualification-subject.js')
    @vite('resources/js/country-fo-permanent-address.js')
    @vite('resources/js/previous-provider.js')
    @vite('resources/js/qualification-type-identifier.js')
    @vite('resources/js/reason-for-ending-course-session.js')
    @vite('resources/js/equivalent-or-lower-qualification.js')
    @vite('resources/js/non-regulated-fee-flag.js')
    @vite('resources/js/reason-for-engagement-ending.js')
    @vite('resources/js/termtimeaccommodationtype.js')     
    @vite('resources/js/sexidentifier.js')
    @vite('resources/js/wp-company.js')
    @vite('resources/js/disable-allowance.js')
    @vite('resources/js/exchange-programme.js')
    @vite('resources/js/heapes-population.js')
    @vite('resources/js/hesa-qualification-award.js')
    @vite('resources/js/location-of-study.js')
    @vite('resources/js/major-source-of-tuition-fee.js')
    @vite('resources/js/student-support-eligibility.js')
    @vite('resources/js/suspension-of-active-study.js')
@endsection