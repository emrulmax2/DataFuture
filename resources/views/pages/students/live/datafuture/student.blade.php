<div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-5 gap-4 gap-y-2">
    <div class="grid-column">
        <div class="col-span-4 text-slate-500 uppercase">SID</div>
        <div class="col-span-8 font-medium">{{ (isset($student->laststuload->sid_number) ? $student->laststuload->sid_number : '---') }}</div>
    </div>
    <div class="grid-column">
        <div class="col-span-4 text-slate-500 uppercase">BIRTHDTE</div>
        <div class="col-span-8 font-medium">{{ (!empty($student->date_of_birth) ? date('Y-m-d', strtotime($student->date_of_birth)) : '---') }}</div>
    </div>
    <div class="grid-column">
        <div class="col-span-4 text-slate-500 uppercase">ETHNIC</div>
        <div class="col-span-8 font-medium">
            {{ (isset($student->other->ethnicity->name) && !empty($student->other->ethnicity->name) ? ($student->other->ethnicity->name) : '---') }}
            {{ (isset($student->other->ethnicity->is_hesa) && $student->other->ethnicity->is_hesa == 1 && !empty($student->other->ethnicity->hesa_code) ? ' ['.$student->other->ethnicity->hesa_code.']' : '') }}
            {{ (isset($student->other->ethnicity->is_df) && $student->other->ethnicity->is_df == 1 && !empty($student->other->ethnicity->df_code) ? ' ['.$student->other->ethnicity->df_code.']' : '') }}
        </div>
    </div>
    <div class="grid-column">
        <div class="col-span-4 text-slate-500 uppercase">FNAMES</div>
        <div class="col-span-8 font-medium">{{ (!empty($student->first_name) ? $student->first_name : '---') }}</div>
    </div>
    <div class="grid-column">
        <div class="col-span-4 text-slate-500 uppercase">GENDERID</div>
        <div class="col-span-8 font-medium">
            {{ (isset($student->other->gender->name) && !empty($student->other->gender->name) ? ($student->other->gender->name) : '---') }}
            {{ (isset($student->other->gender->is_hesa) && $student->other->gender->is_hesa == 1 && !empty($student->other->gender->hesa_code) ? ' ['.$student->other->gender->hesa_code.']' : '') }}
            {{ (isset($student->other->gender->is_df) && $student->other->gender->is_df == 1 && !empty($student->other->gender->df_code) ? ' ['.$student->other->gender->df_code.']' : '') }}
        </div>
    </div>
    <div class="grid-column">
        <div class="col-span-4 text-slate-500 uppercase">NATION</div>
        <div class="col-span-8 font-medium">
            {{ (isset($student->nation->name) && !empty($student->nation->name) ? ($student->nation->name) : '---') }}
            {{ (isset($student->nation->is_hesa) && $student->nation->is_hesa == 1 && !empty($student->nation->hesa_code) ? ' ['.$student->nation->hesa_code.']' : '') }}
            {{ (isset($student->nation->is_df) && $student->nation->is_df == 1 && !empty($student->nation->df_code) ? ' ['.$student->nation->df_code.']' : '') }}
        </div>
    </div>
    <div class="grid-column">
        <div class="col-span-4 text-slate-500 uppercase">OWNSTU</div>
        <div class="col-span-8 font-medium">{{ (!empty($student->registration_no) ? $student->registration_no : '---') }}</div>
    </div>
    <div class="grid-column">
        <div class="col-span-4 text-slate-500 uppercase">RELIGION</div>
        <div class="col-span-8 font-medium">
            {{ (isset($student->other->religion->name) && !empty($student->other->religion->name) ? ($student->other->religion->name) : '---') }}
            {{ (isset($student->other->religion->is_hesa) && $student->other->religion->is_hesa == 1 && !empty($student->other->religion->hesa_code) ? ' ['.$student->other->religion->hesa_code.']' : '') }}
            {{ (isset($student->other->religion->is_df) && $student->other->religion->is_df == 1 && !empty($student->other->religion->df_code) ? ' ['.$student->other->religion->df_code.']' : '') }}
        </div>
    </div>
    <div class="grid-column">
        <div class="col-span-4 text-slate-500 uppercase">SEXID</div>
        <div class="col-span-8 font-medium">
            {{ (isset($student->sexid->name) && !empty($student->sexid->name) ? ($student->sexid->name) : '---') }}
            {{ (isset($student->sexid->is_hesa) && $student->sexid->is_hesa == 1 && !empty($student->sexid->hesa_code) ? ' ['.$student->sexid->hesa_code.']' : '') }}
            {{ (isset($student->sexid->is_df) && $student->sexid->is_df == 1 && !empty($student->sexid->df_code) ? ' ['.$student->sexid->df_code.']' : '') }}
        </div>
    </div>
    <div class="grid-column">
        <div class="col-span-4 text-slate-500 uppercase">SEXORT</div>
        <div class="col-span-8 font-medium">
            {{ (isset($student->other->sexori->name) && !empty($student->other->sexori->name) ? ($student->other->sexori->name) : '---') }}
            {{ (isset($student->other->sexori->is_hesa) && $student->other->sexori->is_hesa == 1 && !empty($student->other->sexori->hesa_code) ? ' ['.$student->other->sexori->hesa_code.']' : '') }}
            {{ (isset($student->other->sexori->is_df) && $student->other->sexori->is_df == 1 && !empty($student->other->sexori->df_code) ? ' ['.$student->other->sexori->df_code.']' : '') }}
        </div>
    </div>
    <div class="grid-column">
        <div class="col-span-4 text-slate-500 uppercase">SSN</div>
        <div class="col-span-8 font-medium">{{ (!empty($student->ssn_no) ? $student->ssn_no : '---') }}</div>
    </div>
    <div class="grid-column">
        <div class="col-span-4 text-slate-500 uppercase">SURNAME</div>
        <div class="col-span-8 font-medium">{{ (!empty($student->last_name) ? $student->last_name : '---') }}</div>
    </div>
    <div class="grid-column">
        <div class="col-span-4 text-slate-500 uppercase">TTACCOM</div>
        <div class="col-span-8 font-medium">
            {{ (isset($student->contact->ttacom->name) && !empty($student->contact->ttacom->name) ? ($student->contact->ttacom->name) : '---') }}
            {{ (isset($student->contact->ttacom->is_hesa) && $student->contact->ttacom->is_hesa == 1 && !empty($student->contact->ttacom->hesa_code) ? ' ['.$student->contact->ttacom->hesa_code.']' : '') }}
            {{ (isset($student->contact->ttacom->is_df) && $student->contact->ttacom->is_df == 1 && !empty($student->contact->ttacom->df_code) ? ' ['.$student->contact->ttacom->df_code.']' : '') }}
        </div>
    </div>
    <div class="grid-column">
        <div class="col-span-4 text-slate-500 uppercase">TTPCODE</div>
        <div class="col-span-8 font-medium">{{ (isset($student->contact->term_time_post_code) ? $student->contact->term_time_post_code : '') }}</div>
    </div>
</div>

@php 
    $disability_ids = (isset($student->disability) && $student->disability->count() > 0 ? $student->disability->pluck('disability_id')->unique()->toArray() : []);
@endphp
<!-- BEGIN: Entry Qualification Subject -->
<div id="df-accordion-EQS" class="lcc-accordion lcc-accordion-boxed mt-5">
    <div class="lcc-accordion-item">
        <div id="df-accr-EQS-content-1" class="lcc-accordion-header">
            <button class="lcc-accordion-button bg_color_2" type="button">
                Disability
                <span class="accordionCollaps"></span>
            </button>
        </div>
        <div id="df-accr-EQS-collapse-1" class="lcc-accordion-collapse lcc-show" style="display: block;">
            <div class="lcc-accordion-body text-slate-600 dark:text-slate-500 leading-relaxed">
                <div class="grid grid-cols-1 gap-4 gap-y-2">
                    <div class="grid-column readonlyBlock">
                        <div class="col-span-4 text-slate-500 uppercase">DISABILITY</div>
                        @if(isset($student->other->disability_status) && $student->other->disability_status == 1 && isset($student->disability) && $student->disability->count() > 0)
                            <ul class="m-0 p-0">
                            @foreach($student->disability as $disability)
                                <li class="text-left font-normal mb-1 flex pl-5 relative">
                                    <i data-lucide="check-circle" class="w-3 h-3 text-success absolute" style="left: 0; top: 4px;"></i>
                                    {{ (isset($disability->disabilities->name) && !empty($disability->disabilities->name) ? ($disability->disabilities->name) : '---') }}
                                    {{ (isset($disability->disabilities->is_hesa) && $disability->disabilities->is_hesa == 1 && !empty($disability->disabilities->hesa_code) ? ' ['.$disability->disabilities->hesa_code.']' : '') }}
                                    {{ (isset($disability->disabilities->is_df) && $disability->disabilities->is_df == 1 && !empty($disability->disabilities->df_code) ? ' ['.$disability->disabilities->df_code.']' : '') }}
                                </li>
                            @endforeach
                            </ul>
                        @else
                            <div class="col-span-8 font-medium">---</div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- END: Entry Qualification Subject -->

<!-- BEGIN: Engagement -->
<div id="df-accordion-Engagement" class="lcc-accordion lcc-accordion-boxed mt-5">
    <div class="lcc-accordion-item">
        <div id="df-accr-Engagement-content-1" class="lcc-accordion-header">
            <button class="lcc-accordion-button bg_color_2" type="button">
                Engagement
                <span class="accordionCollaps"></span>
            </button>
        </div>
        <div id="df-accr-Engagement-collapse-1" class="lcc-accordion-collapse lcc-show" style="display: block;">
            <div class="lcc-accordion-body text-slate-600 dark:text-slate-500 leading-relaxed">
                <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-5 gap-4 gap-y-2">
                    <div class="grid-column">
                        <div class="col-span-4 text-slate-500 uppercase">NUMHUS</div>
                        <div class="col-span-8 font-medium">{{ (isset($student->df->NUMHUS) && !empty($student->df->NUMHUS) ? $student->df->NUMHUS : '1') }}</div>
                    </div>
                    <div class="grid-column">
                        <div class="col-span-4 text-slate-500 uppercase">ENGEXPECTEDENDDATE</div>
                        <div class="col-span-8 font-medium">{{ (isset($student->crel->course_end_date) && !empty($student->crel->course_end_date) ? date('Y-m-d', strtotime($student->crel->course_end_date)) : (isset($student->crel->creation->available->course_end_date) && !empty($student->crel->creation->available->course_end_date) ? date('Y-m-d', strtotime($student->crel->creation->available->course_end_date)) : '---')) }}</div>
                    </div>
                    <div class="grid-column">
                        <div class="col-span-4 text-slate-500 uppercase">ENGSTARTDATE</div>
                        <div class="col-span-8 font-medium">{{(isset($student->crel->course_start_date) && !empty($student->crel->course_start_date) ? date('Y-m-d', strtotime($student->crel->course_start_date)) : (isset($student->crel->creation->available->course_start_date) && !empty($student->crel->creation->available->course_start_date) ? date('Y-m-d', strtotime($student->crel->creation->available->course_start_date)) : '---')) }}</div>
                    </div>
                    <div class="grid-column">
                        <div class="col-span-4 text-slate-500 uppercase">OWNENGID</div>
                        <div class="col-span-8 font-medium">{{ (isset($student->crel->creation->semester->name) && !empty($student->crel->creation->semester->name) ? $student->crel->creation->semester->name : '---') }}</div>
                    </div>
                    <div class="grid-column">
                        <div class="col-span-4 text-slate-500 uppercase">OWNENGID</div>
                        <div class="col-span-8 font-medium">{{ (isset($student->crel->creation->semester->name) && !empty($student->crel->creation->semester->name) ? $student->crel->creation->semester->name : '---') }}</div>
                    </div>
                    <div class="grid-column">
                        <div class="col-span-4 text-slate-500 uppercase">FEEELIG</div>
                        <div class="col-span-8 font-medium">
                            {{ (isset($student->crel->feeeligibility->elegibility->name) && !empty($student->crel->feeeligibility->elegibility->name) ? ($student->crel->feeeligibility->elegibility->name) : '---') }}
                            {{ (isset($student->crel->feeeligibility->elegibility->is_hesa) && $student->crel->feeeligibility->elegibility->is_hesa == 1 && !empty($student->crel->feeeligibility->elegibility->hesa_code) ? ' ['.$student->crel->feeeligibility->elegibility->hesa_code.']' : '') }}
                            {{ (isset($student->crel->feeeligibility->elegibility->is_df) && $student->crel->feeeligibility->elegibility->is_df == 1 && !empty($student->crel->feeeligibility->elegibility->df_code) ? ' ['.$student->crel->feeeligibility->elegibility->df_code.']' : '') }}
                        </div>
                    </div>
                </div>

                <!-- BEGIN: Entry Profile -->
                <div id="df-accordion-EntryProfile" class="lcc-accordion lcc-accordion-boxed mt-5">
                    <div class="lcc-accordion-item">
                        <div id="df-accr-EntryProfile-content-1" class="lcc-accordion-header">
                            <button class="lcc-accordion-button bg_color_3" type="button">
                                Entry Profile
                                <span class="accordionCollaps"></span>
                            </button>
                        </div>
                        <div id="df-accr-EntryProfile-collapse-1" class="lcc-accordion-collapse lcc-show" style="display: block;">
                            <div class="lcc-accordion-body text-slate-600 dark:text-slate-500 leading-relaxed">
                                <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-5 gap-4 gap-y-2">
                                    <div class="grid-column">
                                        <div class="col-span-4 text-slate-500 uppercase">CARELEAVER</div>
                                        <div class="col-span-8 font-medium">{{ (isset($student->df->CARELEAVER) && !empty($student->df->CARELEAVER) ? $student->df->CARELEAVER : '---') }}</div>
                                    </div>
                                    <div class="grid-column">
                                        <div class="col-span-4 text-slate-500 uppercase">PERMADDCOUNTRY</div>
                                        <div class="col-span-8 font-medium">
                                            {{ (isset($student->contact->pcountry->name) && !empty($student->contact->pcountry->name) ? ($student->contact->pcountry->name) : '---') }}
                                            {{ (isset($student->contact->pcountry->is_hesa) && $student->contact->pcountry->is_hesa == 1 && !empty($student->contact->pcountry->hesa_code) ? ' ['.$student->contact->pcountry->hesa_code.']' : '') }}
                                            {{ (isset($student->contact->pcountry->is_df) && $student->contact->pcountry->is_df == 1 && !empty($student->contact->pcountry->df_code) ? ' ['.$student->contact->pcountry->df_code.']' : '') }}
                                        </div>
                                    </div>
                                    <div class="grid-column">
                                        <div class="col-span-4 text-slate-500 uppercase">PERMADDPOSTCODE</div>
                                        <div class="col-span-8 font-medium">{{ (isset($student->contact->permanent_post_code) && !empty($student->contact->permanent_post_code) ? $student->contact->permanent_post_code : '---') }}</div>
                                    </div>
                                    <div class="grid-column">
                                        <div class="col-span-4 text-slate-500 uppercase">PREVIOUSPROVIDER</div>
                                        <div class="col-span-8 font-medium">
                                            @if(isset($student->other->is_education_qualification) && $student->other->is_education_qualification == 1)
                                                {{ (isset($student->qualHigest->previous_providers->name) && !empty($student->qualHigest->previous_providers->name) ? ($student->qualHigest->previous_providers->name) : '---') }}
                                                {{ (isset($student->qualHigest->previous_providers->is_hesa) && $student->qualHigest->previous_providers->is_hesa == 1 && !empty($student->qualHigest->previous_providers->hesa_code) ? ' ['.$student->qualHigest->previous_providers->hesa_code.']' : '') }}
                                                {{ (isset($student->qualHigest->previous_providers->is_df) && $student->qualHigest->previous_providers->is_df == 1 && !empty($student->qualHigest->previous_providers->df_code) ? ' ['.$student->qualHigest->previous_providers->df_code.']' : '') }}
                                            @else 
                                                {{ '---' }}
                                            @endif
                                        </div>
                                    </div>
                                    <div class="grid-column">
                                        <div class="col-span-4 text-slate-500 uppercase">HIGHESTQOE</div>
                                        <div class="col-span-8 font-medium">
                                            @if(isset($student->other->is_education_qualification) && $student->other->is_education_qualification == 1)
                                                {{ (isset($student->qualHigest->highest_qualification_on_entries->name) && !empty($student->qualHigest->highest_qualification_on_entries->name) ? ($student->qualHigest->highest_qualification_on_entries->name) : '---') }}
                                                {{ (isset($student->qualHigest->highest_qualification_on_entries->is_hesa) && $student->qualHigest->highest_qualification_on_entries->is_hesa == 1 && !empty($student->qualHigest->highest_qualification_on_entries->hesa_code) ? ' ['.$student->qualHigest->highest_qualification_on_entries->hesa_code.']' : '') }}
                                                {{ (isset($student->qualHigest->highest_qualification_on_entries->is_df) && $student->qualHigest->highest_qualification_on_entries->is_df == 1 && !empty($student->qualHigest->highest_qualification_on_entries->df_code) ? ' ['.$student->qualHigest->highest_qualification_on_entries->df_code.']' : '') }}
                                            @else 
                                                {{ '---' }}
                                            @endif
                                        </div>
                                    </div>

                                    <!-- <div class="grid-column">
                                        <label class="form-label uppercase">RELIGIOUSBGROUND</label>
                                        <select name="RELIGIOUSBGROUND" class="w-full tom-selects df-tom-selects">
                                            <option value="">Please Select</option>
                                            @if($religion->count() > 0)
                                                @foreach($religion as $opt)
                                                    <option {{ (isset($student->other->religion_id) && $student->other->religion_id == $opt->id ? 'Selected' : '') }} value="{{ $opt->id }}">{{ $opt->name }} {{ ($opt->is_hesa == 1 && !empty($opt->hesa_code) ? ' ['.$opt->hesa_code.']' : '') }} {{ ($opt->is_df == 1 && !empty($opt->df_code) ? ' ['.$opt->df_code.']' : '') }}</option>
                                                @endforeach
                                            @endif
                                        </select>
                                    </div> -->
                                </div>

                                <!-- BEGIN: Entry Qualification Award -->
                                <div id="df-accordion-EntryQualificationAward" class="lcc-accordion lcc-accordion-boxed mt-5">
                                    <div class="lcc-accordion-item">
                                        <div id="df-accr-EntryQualificationAward-content-1" class="lcc-accordion-header">
                                            <button class="lcc-accordion-button bg_color_6" type="button">
                                                Entry Qualification Award
                                                <span class="accordionCollaps"></span>
                                            </button>
                                        </div>
                                        <div id="df-accr-EntryQualificationAward-collapse-1" class="lcc-accordion-collapse lcc-show" style="display: block;">
                                            <div class="lcc-accordion-body text-slate-600 dark:text-slate-500 leading-relaxed">
                                                <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-5 gap-4 gap-y-2">
                                                    <div class="grid-column">
                                                        <div class="col-span-4 text-slate-500 uppercase">ENTRYQUALAWARDID</div>
                                                        <div class="col-span-8 font-medium">
                                                            @if(isset($student->other->is_education_qualification) && $student->other->is_education_qualification == 1)
                                                                {{ (isset($student->qualHigest->qualification->name) && !empty($student->qualHigest->qualification->name) ? ($student->qualHigest->qualification->name) : '---') }}
                                                                {{ (isset($student->qualHigest->qualification->is_hesa) && $student->qualHigest->qualification->is_hesa == 1 && !empty($student->qualHigest->qualification->hesa_code) ? ' ['.$student->qualHigest->qualification->hesa_code.']' : '') }}
                                                                {{ (isset($student->qualHigest->qualification->is_df ) && $student->qualHigest->qualification->is_df == 1 && !empty($student->qualHigest->qualification->df_code) ? ' ['.$student->qualHigest->qualification->df_code.']' : '') }}
                                                            @else 
                                                                {{ '---' }}
                                                            @endif
                                                        </div>
                                                    </div>
                                                    <div class="grid-column">
                                                        <div class="col-span-4 text-slate-500 uppercase">ENTRYQUALAWARDRESULT</div>
                                                        <div class="col-span-8 font-medium">
                                                            @if(isset($student->other->is_education_qualification) && $student->other->is_education_qualification == 1)
                                                                {{ (isset($student->qualHigest->grade->name) && !empty($student->qualHigest->grade->name) ? ($student->qualHigest->grade->name) : '---') }}
                                                                {{ (isset($student->qualHigest->grade->is_hesa) && $student->qualHigest->grade->is_hesa == 1 && !empty($student->qualHigest->grade->hesa_code) ? ' ['.$student->qualHigest->grade->hesa_code.']' : '') }}
                                                                {{ (isset($student->qualHigest->grade->is_df) && $student->qualHigest->grade->is_df == 1 && !empty($student->qualHigest->grade->df_code) ? ' ['.$student->qualHigest->grade->df_code.']' : '') }}
                                                            @else 
                                                                {{ '---' }}
                                                            @endif
                                                        </div>
                                                    </div>
                                                    <div class="grid-column">
                                                        <div class="col-span-4 text-slate-500 uppercase">QUALTYPEID</div>
                                                        <div class="col-span-8 font-medium">
                                                            @if(isset($student->other->is_education_qualification) && $student->other->is_education_qualification == 1)
                                                                {{ (isset($student->qualHigest->qualification_type_identifiers->name) && !empty($student->qualHigest->qualification_type_identifiers->name) ? ($student->qualHigest->qualification_type_identifiers->name) : '---') }}
                                                                {{ (isset($student->qualHigest->qualification_type_identifiers->is_hesa) && $student->qualHigest->qualification_type_identifiers->is_hesa == 1 && !empty($student->qualHigest->qualification_type_identifiers->hesa_code) ? ' ['.$student->qualHigest->qualification_type_identifiers->hesa_code.']' : '') }}
                                                                {{ (isset($student->qualHigest->qualification_type_identifiers->is_df) && $student->qualHigest->qualification_type_identifiers->is_df == 1 && !empty($student->qualHigest->qualification_type_identifiers->df_code) ? ' ['.$student->qualHigest->qualification_type_identifiers->df_code.']' : '') }}
                                                            @else 
                                                                {{ '---' }}
                                                            @endif
                                                        </div>
                                                    </div>
                                                    <div class="grid-column">
                                                        <div class="col-span-4 text-slate-500 uppercase">QUALYEAR</div>
                                                        <div class="col-span-8 font-medium">{{ (isset($student->other->is_education_qualification) && $student->other->is_education_qualification == 1 && isset($student->qualHigest->degree_award_date) && !empty($student->qualHigest->degree_award_date) ? date('Y', strtotime($student->qualHigest->degree_award_date)) : '---') }}</div>
                                                    </div>
                                                </div>

                                                <!-- BEGIN: Student Entry Qualification Subject -->
                                                <div id="df-accordion-EntryQualificationSubject" class="lcc-accordion lcc-accordion-boxed mt-5">
                                                    <div class="lcc-accordion-item">
                                                        <div id="df-accr-EntryQualificationSubject-content-1" class="lcc-accordion-header">
                                                            <button class="lcc-accordion-button bg_color_5" type="button">
                                                                Entry Qualification Subject
                                                                <span class="accordionCollaps"></span>
                                                            </button>
                                                        </div>
                                                        <div id="df-accr-EntryQualificationSubject-collapse-1" class="lcc-accordion-collapse lcc-show" style="display: block;">
                                                            <div class="lcc-accordion-body text-slate-600 dark:text-slate-500 leading-relaxed">
                                                                <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-5 gap-4 gap-y-2">
                                                                    <div class="grid-column">
                                                                        <div class="col-span-4 text-slate-500 uppercase">QUALTYPEID</div>
                                                                        <div class="col-span-8 font-medium">
                                                                            @if(isset($student->other->is_education_qualification) && $student->other->is_education_qualification == 1)
                                                                                {{ (isset($student->qualHigest->hesa_qualification_subjects->name) && !empty($student->qualHigest->hesa_qualification_subjects->name) ? ($student->qualHigest->hesa_qualification_subjects->name) : '---') }}
                                                                                {{ (isset($student->qualHigest->hesa_qualification_subjects->is_hesa) && $student->qualHigest->hesa_qualification_subjects->is_hesa == 1 && !empty($student->qualHigest->hesa_qualification_subjects->hesa_code) ? ' ['.$student->qualHigest->hesa_qualification_subjects->hesa_code.']' : '') }}
                                                                                {{ (isset($student->qualHigest->hesa_qualification_subjects->is_df) && $student->qualHigest->hesa_qualification_subjects->is_df == 1 && !empty($student->qualHigest->hesa_qualification_subjects->df_code) ? ' ['.$student->qualHigest->hesa_qualification_subjects->df_code.']' : '') }}
                                                                            @else 
                                                                                {{ '---' }}
                                                                            @endif
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <!-- END: Entry Qualification Subject -->

                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <!-- END: Entry Qualification Subject -->
                            </div>
                        </div>
                    </div>
                </div>
                <!-- END: Entry Profile -->

                <!-- BEGIN: Leaver -->
                <div id="df-accordion-Leaver" class="lcc-accordion lcc-accordion-boxed mt-5">
                    <div class="lcc-accordion-item">
                        <div id="df-accr-Leaver-content-1" class="lcc-accordion-header">
                            <button class="lcc-accordion-button bg_color_3" type="button">
                                Leaver
                                <span class="accordionCollaps"></span>
                            </button>
                        </div>
                        @php 
                            $ENGENDDATE = '';
                            $RSNENGEND = '';
                            $QUALRESULT = '';

                            if(isset($student->crel->active) && $student->crel->active == 1):
                                $endStatuses = [21, 26, 27, 31, 42];
                                $student_status_id = (isset($student->status_id) && $student->status_id > 0 ? $student->status_id : '');
                                $termStatusId = (isset($student->termStatus->status_id) && !empty($student->termStatus->status_id) ? $student->termStatus->status_id : '');

                                if($student_status_id == $termStatusId && in_array($student_status_id, $endStatuses)):
                                    $ENGENDDATE = (isset($student->termStatus->status_end_date) && !empty($student->termStatus->status_end_date) ? date('Y-m-d', strtotime($student->termStatus->status_end_date)) : '');
                                    $RSNENGEND = (isset($student->termStatus->reason_for_engagement_ending_id) && !empty($student->termStatus->reason_for_engagement_ending_id) ? $student->termStatus->reason_for_engagement_ending_id : '');
                                    $QUALRESULT = (isset($student->termStatus->other_academic_qualification_id) && !empty($student->termStatus->other_academic_qualification_id) ? $student->termStatus->other_academic_qualification_id : '');
                                endif;
                            endif;
                        @endphp
                        <div id="df-accr-Leaver-collapse-1" class="lcc-accordion-collapse lcc-show" style="display: block;">
                            <div class="lcc-accordion-body text-slate-600 dark:text-slate-500 leading-relaxed">
                                <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-5 gap-4 gap-y-2">
                                    <div class="grid-column">
                                        <div class="col-span-4 text-slate-500 uppercase">ENGENDDATE</div>
                                        <div class="col-span-8 font-medium">{{ !empty($ENGENDDATE) ? $ENGENDDATE : '---' }}</div>
                                    </div>
                                    <div class="grid-column">
                                        <div class="col-span-4 text-slate-500 uppercase">RSNENGEND</div>
                                        <div class="col-span-8 font-medium">
                                            @if($endreasons->count() > 0 && $RSNENGEND > 0)
                                                @foreach($endreasons as $opt)
                                                    @if($RSNENGEND == $opt->id)
                                                        {{ $opt->name }} 
                                                        {{ ($opt->is_hesa == 1 && !empty($opt->hesa_code) ? ' ['.$opt->hesa_code.']' : '') }} 
                                                        {{ ($opt->is_df == 1 && !empty($opt->df_code) ? ' ['.$opt->df_code.']' : '') }}
                                                    @endif
                                                @endforeach
                                            @else 
                                                {{ '---' }}
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- END: Entry Qualification Subject -->

                @php 
                    $QUALAWARDID = '';
                    $QUALID = '';
                    if(!empty($df_qualification_fields) && $df_qualification_fields->count() > 0):
                        foreach($df_qualification_fields as $qf):
                            if(isset($qf->field->name) && $qf->field->name == 'QUALAWARDID'):
                                $QUALAWARDID = (isset($qf->field_value) && !empty($qf->field_value) ? trim($qf->field_value) : '');
                            elseif(isset($qf->field->name) && $qf->field->name == 'QUALID'):
                                $QUALID = (isset($qf->field_value) && !empty($qf->field_value) ? trim($qf->field_value) : '');
                            endif;
                        endforeach;
                    endif;
                @endphp
                <!-- BEGIN: Qualification Awarded -->
                <div id="df-accordion-QualificationAwarded" class="lcc-accordion lcc-accordion-boxed mt-5">
                    <div class="lcc-accordion-item">
                        <div id="df-accr-QualificationAwarded-content-1" class="lcc-accordion-header">
                            <button class="lcc-accordion-button bg_color_3" type="button">
                                Qualification Awarded
                                <span class="accordionCollaps"></span>
                            </button>
                        </div>
                        <div id="df-accr-QualificationAwarded-collapse-1" class="lcc-accordion-collapse lcc-show" style="display: block;">
                            <div class="lcc-accordion-body text-slate-600 dark:text-slate-500 leading-relaxed">
                                <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-5 gap-4 gap-y-2">
                                    <div class="grid-column">
                                        <div class="col-span-4 text-slate-500 uppercase">QUALAWARDID</div>
                                        <div class="col-span-8 font-medium">
                                            {{ (isset($student->awarded->qual_award_type) && !empty($student->awarded->qual_award_type) ? $student->awarded->qual_award_type : '---') }}
                                        </div>
                                    </div>
                                    <div class="grid-column">
                                        <div class="col-span-4 text-slate-500 uppercase">QUALID</div>
                                        <div class="col-span-8 font-medium">{{ (!empty($QUALID) && isset($student->awarded->qual_award_type) && !empty($student->awarded->qual_award_type) ? $QUALID : '---') }}</div>
                                    </div>
                                    <div class="grid-column">
                                        <div class="col-span-4 text-slate-500 uppercase">QUALAWARDRESULT</div>
                                        <div class="col-span-8 font-medium">
                                            {{ (isset($student->awarded->qual->name) && !empty($student->awarded->qual->name) ? ($student->awarded->qual->name) : '---') }}
                                            {{ (isset($student->awarded->qual->is_hesa) && $student->awarded->qual->is_hesa == 1 && !empty($student->awarded->qual->hesa_code) ? ' ['.$student->awarded->qual->hesa_code.']' : '') }}
                                            {{ (isset($student->awarded->qual->is_df) && $student->awarded->qual->is_df == 1 && !empty($student->awarded->qual->df_code) ? ' ['.$student->awarded->qual->df_code.']' : '') }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- END: Qualification Awarded -->

                <!-- BEGIN: Student Course Sessions -->
                @if($stuloads->count() > 0)
                    <div id="df-accordion-Student-Course-Session" class="lcc-accordion lcc-accordion-boxed mt-5">
                        @php 
                            $i = 1; 
                        @endphp
                        @foreach($stuloads as $stu)
                            @php 
                                $instanceStart = (isset($stu->instance->start_date) && !empty($stu->instance->start_date) ? date('Y-m-d', strtotime($stu->instance->start_date)) : '');
                                $instanceEnd = (isset($stu->instance->end_date) && !empty($stu->instance->end_date) ? date('Y-m-d', strtotime($stu->instance->end_date)) : '');
                                $hesaEndDate = (isset($stu->enddate) && !empty($stu->enddate) ? date('Y-m-d', strtotime($stu->enddate)) : '');
                                $periodEndDate = (isset($stu->periodend) && !empty($stu->periodend) && $stu->periodend != '0000-00-00' ? date('Y-m-d', strtotime($stu->periodend)) : '');
                                $periodStartDate = (isset($stu->periodstart) && !empty($stu->periodstart) && $stu->periodstart != '0000-00-00' ? date('Y-m-d', strtotime($stu->periodstart)) : '');

                                //$SCSMODE = (isset($stu->mode_id) && $stu->mode_id > 0 ? $stu->mode_id : '');
                                $SCSMODE = (isset($student->other->study_mode_id) && $student->other->study_mode_id > 0 ? $student->other->study_mode_id : 1);
                                $SCSEXPECTEDENDDATE = $instanceEnd;
                                $SCSENDDATE = $hesaEndDate;
                                if(!empty($ENGENDDATE) && ($ENGENDDATE > $periodStartDate &&  $ENGENDDATE < $periodEndDate) && $ENGENDDATE < $instanceEnd):
                                    $SCSENDDATE = $ENGENDDATE;
                                    //$SCSMODE = (!empty($SCSMODE) ? 2 : $SCSMODE);
                                elseif(empty($hesaEndDate) && (!empty($SCSEXPECTEDENDDATE) && $SCSEXPECTEDENDDATE < date('Y-m-d'))):
                                    $SCSENDDATE = $SCSEXPECTEDENDDATE;
                                    //$SCSMODE = (!empty($SCSMODE) ? 4 : $SCSMODE);
                                endif;

                                $RSNSCSEND = '';
                                if(($hesaEndDate == '' && $instanceEnd <= date('Y-m-d')) || ($hesaEndDate != '' && $hesaEndDate == $instanceEnd) || ($hesaEndDate != '' && $hesaEndDate > $instanceEnd && $instanceEnd <= date('Y-m-d'))):
                                    $RSNSCSEND = 4;
                                elseif($hesaEndDate != '' && $hesaEndDate > $instanceStart && $hesaEndDate < $instanceEnd):
                                    $RSNSCSEND = 2;
                                else:
                                    $RSNSCSEND = '';
                                endif;
                                $RSNSCSEND = (isset($stu->df->RSNSCSEND) && !empty($stu->df->RSNSCSEND) ? $stu->df->RSNSCSEND : $RSNSCSEND);
                                $FUNDCOMP = (!empty($periodEndDate) && $periodEndDate < date('Y-m-d') ? 1 : (!empty($periodStartDate) && $periodStartDate <= date('Y-m-d') && !empty($periodEndDate) && $periodEndDate > date('Y-m-d') ? 3 : 2));
                                $FUNDLENGTH = 3;

                                $REFPERIOD_INC = ($i < 10 ? '0'.$i : $i);
                            @endphp
                            <div class="lcc-accordion-item">
                                <div id="df-accr-Student-Course-Session-content-{{ $i }}" class="lcc-accordion-header relative">
                                    <button class="lcc-accordion-button bg_color_3" type="button" style="padding-left: 95px;">
                                        Student Course Session {{ (isset($stu->periodstart) && !empty($stu->periodstart) ? date('d-m-Y', strtotime($stu->periodstart)) : '')}} - {{ (isset($stu->periodend) && !empty($stu->periodend) ? date('d-m-Y', strtotime($stu->periodend)) : '')}}
                                        <span class="accordionCollaps"></span>
                                    </button>
                                    <div class="absolute l-0 t-0 b-0 m-auto ml-4 inline-flex justify-start items-center">
                                        <button type="button" data-tw-toggle="modal" data-tw-target="#editStudentStuloadModal" data-student-id="{{ $student->id }}" data-id="{{ $stu->id }}" class="editStudentLoadBtn btn btn-success w-[30px] h-[30px] p-0 items-center justify-center rounded-full text-white">
                                            <i data-lucide="pencil" class="w-4 h-4"></i>
                                        </button>
                                        <button type="button" data-student-id="{{ $student->id }}" data-id="{{ $stu->id }}" class="deleteStudentLoadBtn btn btn-danger w-[30px] h-[30px] p-0 items-center justify-center rounded-full text-white ml-1">
                                            <i data-lucide="trash-2" class="w-4 h-4"></i>
                                        </button>
                                    </div>
                                    <div class="absolute right-[50px] top-0 bottom-0 mt-auto mb-auto">
                                        <div class="form-check form-switch m-0">
                                            <input {{ isset($stu->report_visibility) && $stu->report_visibility == 1 ? 'Checked' : '' }} id="report_visibility_{{$stu->id}}" class="form-check-input report_visibility" data-student-id="{{ $student->id }}" data-id="{{ $stu->id }}" type="checkbox" value="{{ $stu->report_visibility }}" name="report_visibility">
                                        </div>
                                    </div>
                                </div>
                                <div id="df-accr-Student-Course-Session-collapse-{{ $i }}" class="lcc-accordion-collapse lcc-show" style="display: block;">
                                    <div class="lcc-accordion-body text-slate-600 dark:text-slate-500 leading-relaxed">
                                        <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-5 gap-4 gap-y-2">
                                            <div class="grid-column">
                                                <div class="col-span-4 text-slate-500 uppercase">SCSESSIONID</div>
                                                <div class="col-span-8 font-medium">{{ $stu->course_creation_instance_id }}</div>
                                            </div>
                                            <div class="grid-column">
                                                <div class="col-span-4 text-slate-500 uppercase">COURSEID</div>
                                                <div class="col-span-8 font-medium">{{ $stu->courseaim_id }}</div>
                                            </div>
                                            <div class="grid-column">
                                                <div class="col-span-4 text-slate-500 uppercase">INVOICEFEEAMOUNT</div>
                                                <div class="col-span-8 font-medium">{{ $stu->gross_fee }}</div>
                                            </div>
                                            <!-- <div class="grid-column">
                                                <div class="col-span-4 text-slate-500 uppercase">INVOICEHESAID</div>
                                                <div class="col-span-8 font-medium">5026</div>
                                            </div> -->
                                            <div class="grid-column">
                                                <label class="form-label uppercase">INVOICEHESAID</label>
                                                <input value="{{ (isset($stu->df->INVOICEHESAID) && !empty($stu->df->INVOICEHESAID) ? $stu->df->INVOICEHESAID : '5026' ) }}" type="text" name="SCS[{{ $stu->id }}][INVOICEHESAID]" class="w-full form-control" placeholder="INVOICEHESAID"/>
                                            </div>
                                            <div class="grid-column">
                                                <div class="col-span-4 text-slate-500 uppercase">SCSEXPECTEDENDDATE</div>
                                                <div class="col-span-8 font-medium">{{ (isset($stu->instance->end_date) && !empty($stu->instance->end_date) ? date('Y-m-d', strtotime($stu->instance->end_date)) : '---') }}</div>
                                            </div>
                                            <div class="grid-column">
                                                <div class="col-span-4 text-slate-500 uppercase">SCSENDDATE</div>
                                                <div class="col-span-8 font-medium">{{ (!empty($SCSENDDATE) ? date('Y-m-d', strtotime($SCSENDDATE)) : '---') }}</div>
                                            </div>
                                            <div class="grid-column">
                                                <div class="col-span-4 text-slate-500 uppercase">SCSFEEAMOUNT</div>
                                                <div class="col-span-8 font-medium">{{ (isset($stu->netfee) && $stu->netfee > 0 ? $stu->netfee : '') }}</div>
                                            </div>
                                            <div class="grid-column">
                                                <div class="col-span-4 text-slate-500 uppercase">SCSMODE</div>
                                                <div class="col-span-8 font-medium">
                                                    @if($modes->count() > 0 && !empty($SCSMODE))
                                                        @foreach($modes as $opt)
                                                            @if($SCSMODE == $opt->id)
                                                                {{ $opt->name }} 
                                                                {{ ($opt->is_hesa == 1 && !empty($opt->hesa_code) ? ' ['.$opt->hesa_code.']' : '') }} 
                                                                {{ ($opt->is_df == 1 && !empty($opt->df_code) ? ' ['.$opt->df_code.']' : '') }}
                                                            @endif
                                                        @endforeach
                                                    @else 
                                                        {{ '---' }}
                                                    @endif
                                                </div>
                                            </div>
                                            <div class="grid-column">
                                                <div class="col-span-4 text-slate-500 uppercase">SCSSTARTDATE</div>
                                                <div class="col-span-8 font-medium">{{ (isset($stu->periodstart) && !empty($stu->periodstart) ? date('Y-m-d', strtotime($stu->periodstart)) : '---') }}</div>
                                            </div>
                                            <div class="grid-column">
                                                <div class="col-span-4 text-slate-500 uppercase">SESSIONYEARID</div>
                                                <div class="col-span-8 font-medium">{{ $stu->course_creation_instance_id }}</div>
                                            </div>
                                            <!-- <div class="grid-column">
                                                <div class="col-span-4 text-slate-500 uppercase">YEARPRG</div>
                                                <div class="col-span-8 font-medium">
                                                    {{ ($stu->yearprg > 0 ? $stu->yearprg : '---') }}
                                                </div>
                                            </div> -->
                                            <div class="grid-column">
                                                <label class="form-label uppercase">YEARPRG</label>
                                                <input value="{{ ($stu->yearprg > 0 ? $stu->yearprg : '') }}" type="text" name="SCS[{{ $stu->id }}][YEARPRG]" class="w-full form-control" placeholder="YEARPRG"/>
                                            </div>

                                            <div class="grid-column">
                                                <!-- <div class="col-span-4 text-slate-500 uppercase">RSNSCSEND</div>
                                                <div class="col-span-8 font-medium">
                                                    @if($rsnscsends->count() > 0 && !empty($RSNSCSEND))
                                                        @foreach($rsnscsends as $opt)
                                                            @if($RSNSCSEND == $opt->id)
                                                                {{ $opt->name }} 
                                                                {{ ($opt->is_hesa == 1 && !empty($opt->hesa_code) ? ' ['.$opt->hesa_code.']' : '') }} 
                                                                {{ ($opt->is_df == 1 && !empty($opt->df_code) ? ' ['.$opt->df_code.']' : '') }}
                                                            @endif
                                                        @endforeach
                                                    @else 
                                                        {{ '---' }}
                                                    @endif
                                                </div> -->

                                                <label class="form-label uppercase">RSNSCSEND</label>
                                                <select name="SCS[{{ $stu->id }}][RSNSCSEND]" class="w-full tom-selects df-tom-selects">
                                                    <option value="">Please Select</option>
                                                    @if($rsnscsends->count() > 0)
                                                        @foreach($rsnscsends as $opt)
                                                            <option {{ ($RSNSCSEND == $opt->id ? 'Selected' : '') }} value="{{ $opt->id }}">{{ $opt->name }}</option>
                                                        @endforeach
                                                    @endif
                                                </select>
                                            </div>
                                        </div>

                                        <!-- BEGIN: Funding & Monitoring -->
                                        <div id="df-accordion-FundingAndMonitoring-{{$stu->id}}" class="lcc-accordion lcc-accordion-boxed mt-5">
                                            <div class="lcc-accordion-item">
                                                <div id="df-accr-FundingAndMonitoring-content-{{$stu->id}}" class="lcc-accordion-header">
                                                    <button class="lcc-accordion-button bg_color_6" type="button">
                                                        Funding And Monitoring
                                                        <span class="accordionCollaps"></span>
                                                    </button>
                                                </div>
                                                <div id="df-accr-FundingAndMonitoring-collapse-{{$stu->id}}" class="lcc-accordion-collapse lcc-show" style="display: block;">
                                                    <div class="lcc-accordion-body text-slate-600 dark:text-slate-500 leading-relaxed">
                                                        <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-5 gap-4 gap-y-2">
                                                            <div class="grid-column">
                                                                <label class="form-label uppercase">ELQ</label>
                                                                <select name="SCS[{{ $stu->id }}][ELQ]" class="w-full tom-selects df-tom-selects">
                                                                    <option value="">Please Select</option>
                                                                    @if($elqs->count() > 0)
                                                                        @foreach($elqs as $opt)
                                                                            <option {{ (isset($stu->df->ELQ) && $stu->df->ELQ == $opt->id ? 'Selected' : '') }} value="{{ $opt->id }}">{{ $opt->name }} {{ ($opt->is_hesa == 1 && !empty($opt->hesa_code) ? ' ['.$opt->hesa_code.']' : '') }} {{ ($opt->is_df == 1 && !empty($opt->df_code) ? ' ['.$opt->df_code.']' : '') }}</option>
                                                                        @endforeach
                                                                    @endif
                                                                </select>
                                                            </div>
                                                            <div class="grid-column">
                                                                <label class="form-label uppercase">FUNDCOMP</label>
                                                                <select name="SCS[{{ $stu->id }}][FUNDCOMP]" class="w-full tom-selects df-tom-selects">
                                                                    <option value="">Please Select</option>
                                                                    @if($fundcomps->count() > 0)
                                                                        @foreach($fundcomps as $opt)
                                                                            <option {{ (isset($stu->df->FUNDCOMP) && $stu->df->FUNDCOMP == $opt->id ? 'Selected' : ($FUNDCOMP == $opt->id ? 'Selected' : '')) }} value="{{ $opt->id }}">{{ $opt->name }} {{ ($opt->is_hesa == 1 && !empty($opt->hesa_code) ? ' ['.$opt->hesa_code.']' : '') }} {{ ($opt->is_df == 1 && !empty($opt->df_code) ? ' ['.$opt->df_code.']' : '') }}</option>
                                                                        @endforeach
                                                                    @endif
                                                                </select>
                                                            </div>
                                                            <div class="grid-column">
                                                                <label class="form-label uppercase">FUNDLENGTH</label>
                                                                <select name="SCS[{{ $stu->id }}][FUNDLENGTH]" class="w-full tom-selects df-tom-selects">
                                                                    <option value="">Please Select</option>
                                                                    @if($fundLengths->count() > 0)
                                                                        @foreach($fundLengths as $opt)
                                                                            <option {{ (isset($stu->df->FUNDLENGTH) && $stu->df->FUNDLENGTH == $opt->id ? 'Selected' : ($FUNDLENGTH == $opt->id ? 'Selected' : '')) }} value="{{ $opt->id }}">{{ $opt->name }} {{ ($opt->is_hesa == 1 && !empty($opt->hesa_code) ? ' ['.$opt->hesa_code.']' : '') }} {{ ($opt->is_df == 1 && !empty($opt->df_code) ? ' ['.$opt->df_code.']' : '') }}</option>
                                                                        @endforeach
                                                                    @endif
                                                                </select>
                                                            </div>
                                                            <div class="grid-column">
                                                                <label class="form-label uppercase">NONREGFEE</label>
                                                                <select name="SCS[{{ $stu->id }}][NONREGFEE]" class="w-full tom-selects df-tom-selects">
                                                                    <option value="">Please Select</option>
                                                                    @if($nonregfees->count() > 0)
                                                                        @foreach($nonregfees as $opt)
                                                                            <option {{ (isset($stu->df->NONREGFEE) && $stu->df->NONREGFEE == $opt->id ? 'Selected' : '') }} value="{{ $opt->id }}">{{ $opt->name }} {{ ($opt->is_hesa == 1 && !empty($opt->hesa_code) ? ' ['.$opt->hesa_code.']' : '') }} {{ ($opt->is_df == 1 && !empty($opt->df_code) ? ' ['.$opt->df_code.']' : '') }}</option>
                                                                        @endforeach
                                                                    @endif
                                                                </select>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <!-- END: Funding & Monitoring -->

                                        <!-- BEGIN: Module Instance -->
                                        <div id="df-accordion-ModuleInstance-{{$stu->id}}" class="lcc-accordion lcc-accordion-boxed mt-5">
                                            <div class="lcc-accordion-item">
                                                <div id="df-accr-ModuleInstance-content-{{$stu->id}}" class="lcc-accordion-header">
                                                    <button class="lcc-accordion-button  bg_color_6" type="button">
                                                        Module Instance
                                                        <span class="accordionCollaps"></span>
                                                    </button>
                                                </div>
                                                <div id="df-accr-ModuleInstance-collapse-{{$stu->id}}" class="lcc-accordion-collapse lcc-show" style="display: block;">
                                                    <div class="lcc-accordion-body text-slate-600 dark:text-slate-500 leading-relaxed">
                                                        @if(isset($moduleInstances[$stu->id]) && !empty($moduleInstances[$stu->id]))
                                                            <div id="df-accordion-modTerms-{{$stu->id}}" class="lcc-accordion lcc-accordion-boxed">
                                                                @foreach($moduleInstances[$stu->id] as $term_id => $termDetails)
                                                                    <div class="lcc-accordion-item">
                                                                        <div id="df-accr-modTerms-content-{{$stu->id}}-{{ $term_id }}" class="lcc-accordion-header">
                                                                            <button class="lcc-accordion-button bg_color_5" type="button">
                                                                                {{ $termDetails['name'] }}
                                                                                <span class="accordionCollaps"></span>
                                                                            </button>
                                                                        </div>
                                                                        <div id="df-accr-modTerms-collapse-{{$stu->id}}-{{ $term_id }}" class="lcc-accordion-collapse lcc-show" style="display: block;">
                                                                            <div class="lcc-accordion-body text-slate-600 dark:text-slate-500 leading-relaxed">
                                                                                <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-5 gap-4 gap-y-2 mb-5">
                                                                                    <div class="grid-column">
                                                                                        <label class="form-label uppercase">STULOAD</label>
                                                                                        <input value="{{ (isset($termDetails['student_load']) && $termDetails['student_load'] > 0 ? $termDetails['student_load'] : '') }}" type="number" name="SCS[{{ $stu->id }}][LOADS][{{$term_id}}][student_load]" class="form-control w-full"/>
                                                                                    </div>
                                                                                    <div class="grid-column pt-7">
                                                                                        <div class="form-check form-switch">
                                                                                            <input {{ (isset($termDetails['auto_stuload']) && $termDetails['auto_stuload'] == 1 ? 'Checked' : '') }} id="auto_stuload_{{$term_id}}" class="form-check-input stuloadMethodChecker" type="checkbox" name="SCS[{{ $stu->id }}][LOADS][{{$term_id}}][auto_stuload]" value="1">
                                                                                            <label class="form-check-label ml-4" for="auto_stuload_{{$term_id}}">{{ (isset($termDetails['auto_stuload']) && $termDetails['auto_stuload'] == 1 ? 'Auto Load' : 'Manual Load') }}</label>
                                                                                        </div>
                                                                                    </div>
                                                                                </div>
                                                                                        
                                                                                @if(isset($termDetails['modules']) && !empty($termDetails['modules']))
                                                                                    @foreach($termDetails['modules'] as $sl => $module)
                                                                                        <input type="hidden" name="SCS[{{ $stu->id }}][SCSM][{{$module['MODINSTID']}}][instnce_term_id]" value="{{$term_id}}"/>
                                                                                        <fieldset class="modInstSet mb-5">
                                                                                            <legend class="font-medium">Instance {{ $sl }}</legend>
                                                                                            <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-5 gap-4 gap-y-2">
                                                                                                <div class="grid-column">
                                                                                                    <label class="form-label uppercase">MODINSTID</label>
                                                                                                    <input value="{{ $module['MODINSTID'] }}" type="text" name="SCS[{{ $stu->id }}][SCSM][{{$module['MODINSTID']}}][MODINSTID]" class="w-full form-control" placeholder="MODINSTID"/>
                                                                                                </div>
                                                                                                <div class="grid-column">
                                                                                                    <label class="form-label uppercase">MODID</label>
                                                                                                    <select name="SCS[{{ $stu->id }}][SCSM][{{$module['MODINSTID']}}][MODID]" class="w-full tom-selects df-tom-selects">
                                                                                                        <option value="">Please Select</option>
                                                                                                        @if($modules->count() > 0)
                                                                                                            @foreach($modules as $opt)
                                                                                                                <option {{ ($module['MODINS_MODID'] == $opt->id ? 'Selected' : '') }} value="{{ $opt->id }}">{{ $opt->name }}</option>
                                                                                                            @endforeach
                                                                                                        @endif
                                                                                                    </select>
                                                                                                </div>
                                                                                                <div class="grid-column">
                                                                                                    <label class="form-label uppercase">MODINSTENDDATE</label>
                                                                                                    <input value="{{ $module['MODINSTENDDATE'] }}" type="text" name="SCS[{{ $stu->id }}][SCSM][{{$module['MODINSTID']}}][MODINSTENDDATE]" class="w-full form-control df-datepicker" placeholder="MODINSTENDDATE"/>
                                                                                                </div>
                                                                                                <div class="grid-column">
                                                                                                    <label class="form-label uppercase">MODINSTSTARTDATE</label>
                                                                                                    <input value="{{ $module['MODINSTSTARTDATE'] }}" type="text" name="SCS[{{ $stu->id }}][SCSM][{{$module['MODINSTID']}}][MODINSTSTARTDATE]" class="w-full form-control df-datepicker" placeholder="MODINSTSTARTDATE"/>
                                                                                                </div> 
                                                                                                <div class="grid-column">
                                                                                                    <label class="form-label uppercase">MODULEOUTCOME</label>
                                                                                                    <select name="SCS[{{ $stu->id }}][SCSM][{{$module['MODINSTID']}}][MODULEOUTCOME]" class="w-full tom-selects df-tom-selects">
                                                                                                        <option value="">Please Select</option>
                                                                                                        @if($modoutcom->count() > 0)
                                                                                                            @foreach($modoutcom as $opt)
                                                                                                                <option {{ ($module['MODULEOUTCOME'] == $opt->id ? 'Selected' : '') }} value="{{ $opt->id }}">{{ $opt->name }}</option>
                                                                                                            @endforeach
                                                                                                        @endif
                                                                                                    </select>
                                                                                                </div>
                                                                                                <div class="grid-column">
                                                                                                    <label class="form-label uppercase">MODULERESULT</label>
                                                                                                    <select name="SCS[{{ $stu->id }}][SCSM][{{$module['MODINSTID']}}][MODULERESULT]" class="w-full tom-selects df-tom-selects">
                                                                                                        <option value="">Please Select</option>
                                                                                                        @if($modresult->count() > 0)
                                                                                                            @foreach($modresult as $opt)
                                                                                                                <option {{ ($module['MODULERESULT'] == $opt->id ? 'Selected' : '') }} value="{{ $opt->id }}">{{ $opt->name }}</option>
                                                                                                            @endforeach
                                                                                                        @endif
                                                                                                    </select>
                                                                                                </div>
                                                                                            </div>
                                                                                        </fieldset>
                                                                                    @endforeach
                                                                                @endif
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                @endforeach
                                                            </div>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <!-- END: Module Instance -->

                                        <!-- BEGIN: Qualification Awarded -->
                                        <div id="df-accordion-ReferencePeriodStudentLoad-{{$stu->id}}" class="lcc-accordion lcc-accordion-boxed mt-5">
                                            <div class="lcc-accordion-item">
                                                <div id="df-accr-ReferencePeriodStudentLoad-content-{{$stu->id}}" class="lcc-accordion-header">
                                                    <button class="lcc-accordion-button bg_color_6" type="button">
                                                        Reference Period Student Load
                                                        <span class="accordionCollaps"></span>
                                                    </button>
                                                </div>
                                                <div id="df-accr-ReferencePeriodStudentLoad-collapse-{{$stu->id}}" class="lcc-accordion-collapse lcc-show" style="display: block;">
                                                    <div class="lcc-accordion-body text-slate-600 dark:text-slate-500 leading-relaxed">
                                                        <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-5 gap-4 gap-y-2">
                                                            <div class="grid-column">
                                                                <div class="col-span-4 text-slate-500 uppercase">REFPERIOD</div>
                                                                <div class="col-span-8 font-medium">{{ $REFPERIOD_INC }}</div>
                                                            </div>
                                                            <div class="grid-column">
                                                                <div class="col-span-4 text-slate-500 uppercase">YEAR</div>
                                                                <div class="col-span-8 font-medium">{{ (isset($stu->instance->year->from_date) && !empty($stu->instance->year->from_date) ? date('Y', strtotime($stu->instance->year->from_date)) : '---') }}</div>
                                                            </div>
                                                            <div class="grid-column">
                                                                <div class="col-span-4 text-slate-500 uppercase">RPSTULOAD</div>
                                                                <div class="col-span-8 font-medium">{{ ($stu->student_load && $stu->student_load > 0 ? ($stu->student_load == 99 ? '100' : $stu->student_load) : '---') }}</div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <!-- END: Qualification Awarded -->

                                        
                                        <!-- BEGIN: Session Status -->
                                        <div id="df-accordion-SessionStatus-{{$stu->id}}" class="lcc-accordion lcc-accordion-boxed mt-5">
                                            <div class="lcc-accordion-item">
                                                <div id="df-accr-SessionStatus-content-{{$stu->id}}" class="lcc-accordion-header">
                                                    <button class="lcc-accordion-button bg_color_6" type="button">
                                                        Session Status
                                                        <span class="accordionCollaps"></span>
                                                    </button>
                                                </div>
                                                <div id="df-accr-SessionStatus-collapse-{{$stu->id}}" class="lcc-accordion-collapse lcc-show" style="display: block;">
                                                    <div class="lcc-accordion-body text-slate-600 dark:text-slate-500 leading-relaxed">
                                                        @if(isset($sessionStatuses[$stu->id]) && !empty($sessionStatuses[$stu->id]))
                                                            @foreach($sessionStatuses[$stu->id] as $termDecId => $sts)
                                                                <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-5 gap-4 gap-y-2 {{ !$loop->first ? 'border-t pt-3 mt-4' : '' }}">
                                                                    <div class="grid-column">
                                                                        <div class="col-span-4 text-slate-500 uppercase">STATUSVALIDFROM</div>
                                                                        <div class="col-span-8 font-medium">{{ (isset($sts['STATUSVALIDFROM']) && !empty($sts['STATUSVALIDFROM']) ? date('Y-m-d', strtotime($sts['STATUSVALIDFROM'])) : '---') }}</div>
                                                                    </div>
                                                                    <div class="grid-column">
                                                                        <div class="col-span-4 text-slate-500 uppercase">STATUSCHANGEDTO</div>
                                                                        <div class="col-span-8 font-medium">
                                                                            @if($sessionStatus->count() > 0 && isset($sts['STATUSCHANGEDTO']) && !empty($sts['STATUSCHANGEDTO']))
                                                                                @foreach($sessionStatus as $opt)
                                                                                    @if(isset($sts['STATUSCHANGEDTO']) && $sts['STATUSCHANGEDTO'] == $opt->id)
                                                                                        {{ $opt->name }} 
                                                                                        {{ ($opt->is_hesa == 1 && !empty($opt->hesa_code) ? ' ['.$opt->hesa_code.']' : '') }} 
                                                                                        {{ ($opt->is_df == 1 && !empty($opt->df_code) ? ' ['.$opt->df_code.']' : '') }}
                                                                                    @endif
                                                                                @endforeach
                                                                            @else 
                                                                                {{ '---' }}
                                                                            @endif
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            @endforeach
                                                        @else
                                                            <div class="alert alert-pending-soft show flex items-center mb-2" role="alert">
                                                                <i data-lucide="alert-triangle" class="w-6 h-6 mr-2"></i> Session Status does not available.
                                                            </div>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <!-- END: Session Status -->

                                        <!-- BEGIN: Session Status -->
                                        <div id="df-accordion-StudentFinancialSupport-{{$stu->id}}" class="lcc-accordion lcc-accordion-boxed mt-5">
                                            <div class="lcc-accordion-item">
                                                <div id="df-accr-StudentFinancialSupport-content-{{$stu->id}}" class="lcc-accordion-header">
                                                    <button class="lcc-accordion-button bg_color_6" type="button">
                                                        Student Financial Support
                                                        <span class="accordionCollaps"></span>
                                                    </button>
                                                </div>
                                                <div id="df-accr-StudentFinancialSupport-collapse-{{$stu->id}}" class="lcc-accordion-collapse lcc-show" style="display: block;">
                                                    <div class="lcc-accordion-body text-slate-600 dark:text-slate-500 leading-relaxed">
                                                        <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-5 gap-4 gap-y-2">
                                                            <div class="grid-column">
                                                                <label class="form-label uppercase">FINSUPTYPE</label>
                                                                <input value="{{ (isset($stu->df->FINSUPTYPE) && !empty($stu->df->FINSUPTYPE) ? $stu->df->FINSUPTYPE : '') }}" type="text" name="SCS[{{ $stu->id }}][FINSUPTYPE]" class="w-full form-control" placeholder="FINSUPTYPE"/>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <!-- END: Session Status -->

                                        <!-- BEGIN: Study Location -->
                                        <div id="df-accordion-StudyLocation-{{$stu->id}}" class="lcc-accordion lcc-accordion-boxed mt-5">
                                            <div class="lcc-accordion-item">
                                                <div id="df-accr-StudyLocation-content-{{$stu->id}}" class="lcc-accordion-header">
                                                    <button class="lcc-accordion-button bg_color_6" type="button">
                                                        Study Location
                                                        <span class="accordionCollaps"></span>
                                                    </button>
                                                </div>
                                                <div id="df-accr-StudyLocation-collapse-{{$stu->id}}" class="lcc-accordion-collapse lcc-show" style="display: block;">
                                                    <div class="lcc-accordion-body text-slate-600 dark:text-slate-500 leading-relaxed">
                                                        <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-5 gap-4 gap-y-2">
                                                            <div class="grid-column">
                                                                <div class="col-span-4 text-slate-500 uppercase">STUDYLOCID</div>
                                                                <div class="col-span-8 font-medium">{{ (isset($stu->studentCR->propose->venue->name) && !empty($stu->studentCR->propose->venue->name) ? $stu->studentCR->propose->venue->name : '---') }}</div>
                                                            </div>
                                                            <div class="grid-column">
                                                                <div class="col-span-4 text-slate-500 uppercase">STUDYPROPORTION</div>
                                                                <div class="col-span-8 font-medium">100</div>
                                                            </div>
                                                            <div class="grid-column">
                                                                <div class="col-span-4 text-slate-500 uppercase">VENUEID</div>
                                                                <div class="col-span-8 font-medium">{{ (isset($stu->studentCR->propose->venue->idnumber) && !empty($stu->studentCR->propose->venue->idnumber) ? $stu->studentCR->propose->venue->idnumber : '---') }}</div>
                                                            </div>


                                                            <!-- <div class="grid-column">
                                                                <label class="form-label uppercase">DISTANCE</label>
                                                                <input  value="{{ (isset($stu->df->DISTANCE) && !empty($stu->df->DISTANCE) ? $stu->df->DISTANCE : '') }}" type="text" name="SCS[{{ $stu->id }}][DISTANCE]" class="w-full form-control" placeholder="DISTANCE"/>
                                                            </div>
                                                            <div class="grid-column">
                                                                <label class="form-label uppercase">STUDYPROPORTION</label>
                                                                <input  value="{{ (isset($stu->df->STUDYPROPORTION) && !empty($stu->df->STUDYPROPORTION) ? $stu->df->STUDYPROPORTION : '100') }}" type="text" name="SCS[{{ $stu->id }}][STUDYPROPORTION]" class="w-full form-control" placeholder="STUDYPROPORTION"/>
                                                            </div>
                                                            <div class="grid-column">
                                                                <label class="form-label uppercase">VENUEID</label>
                                                                <input type="text" value="{{ (isset($stu->studentCR->propose->venue->idnumber) && !empty($stu->studentCR->propose->venue->idnumber) ? $stu->studentCR->propose->venue->idnumber : '') }}" name="SCS[{{ $stu->id }}][VENUEID]" class="w-full form-control" placeholder="VENUEID"/>
                                                            </div> -->
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <!-- END: Study Location -->

                                    </div>
                                </div>
                            </div>
                            @php $i++; @endphp
                        @endforeach
                    </div>
                @endif
                <!-- END: Student Course Sessions -->

            </div>
        </div>
    </div>
</div>
<!-- END: Engagement -->