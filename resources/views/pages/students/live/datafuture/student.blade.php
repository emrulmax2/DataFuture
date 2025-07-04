<div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-5 gap-4 gap-y-2">
    <div class="grid-column readonlyBlock">
        <label class="form-label uppercase">SID</label>
        <input type="text" value="{{ (isset($student->laststuload->sid_number) ? $student->laststuload->sid_number : '') }}" name="SID" class="w-full form-control" placeholder="SID"/>
    </div>
    <div class="grid-column readonlyBlock">
        <label class="form-label uppercase">BIRTHDTE</label>
        <input type="text" value="{{ (!empty($student->date_of_birth) ? date('Y-m-d', strtotime($student->date_of_birth)) : '') }}" name="BIRTHDTE" class="w-full form-control df-datepicker" placeholder="BIRTHDTE"/>
    </div>
    <div class="grid-column readonlyBlock">
        <label class="form-label uppercase">ETHNIC</label>
        <select name="ETHNIC" class="w-full tom-selects df-tom-selects">
            <option value="">Please Select</option>
            @if($ethnicity->count() > 0)
                @foreach($ethnicity as $opt)
                    @if($opt->active == 1) 
                        <option {{ (isset($student->other->ethnicity_id) && $student->other->ethnicity_id == $opt->id ? 'Selected' : '') }} value="{{ $opt->id }}">{{ $opt->name }} {{ ($opt->is_hesa == 1 && !empty($opt->hesa_code) ? ' ['.$opt->hesa_code.']' : '') }} {{ ($opt->is_df == 1 && !empty($opt->df_code) ? ' ['.$opt->df_code.']' : '') }}</option>
                    @endif;
                    
                @endforeach
            @endif
        </select>
    </div>
    <div class="grid-column readonlyBlock">
        <label class="form-label uppercase">FNAMES</label>
        <input value="{{ (!empty($student->first_name) ? $student->first_name : '') }}" type="text" name="FNAMES" class="w-full form-control" placeholder="FNAMES"/>
    </div>
    <div class="grid-column readonlyBlock">
        <label class="form-label uppercase">GENDERID</label>
        <select name="GENDERID" class="w-full tom-selects df-tom-selects">
            <option value="">Please Select</option>
            @if($gender->count() > 0)
                @foreach($gender as $opt)
                    <option {{ (isset($student->other->hesa_gender_id) && $student->other->hesa_gender_id == $opt->id ? 'Selected' : '') }} value="{{ $opt->id }}">{{ $opt->name }} {{ ($opt->is_hesa == 1 && !empty($opt->hesa_code) ? ' ['.$opt->hesa_code.']' : '') }} {{ ($opt->is_df == 1 && !empty($opt->df_code) ? ' ['.$opt->df_code.']' : '') }}</option>
                @endforeach
            @endif
        </select>
    </div>
    <div class="grid-column readonlyBlock">
        <label class="form-label uppercase">NATION</label>
        <select name="NATION" class="w-full tom-selects df-tom-selects">
            <option value="">Please Select</option>
            @if($countries->count() > 0)
                @foreach($countries as $opt) 
                    <option {{ (isset($student->nationality_id) && $student->nationality_id == $opt->id ? 'Selected' : '') }} value="{{ $opt->id }}">{{ $opt->name }} {{ ($opt->is_hesa == 1 && !empty($opt->hesa_code) ? ' ['.$opt->hesa_code.']' : '') }} {{ ($opt->is_df == 1 && !empty($opt->df_code) ? ' ['.$opt->df_code.']' : '') }}</option>
                @endforeach
            @endif
        </select>
    </div>
    <div class="grid-column readonlyBlock">
        <label class="form-label uppercase">OWNSTU</label>
        <input type="text" value="{{ (!empty($student->registration_no) ? $student->registration_no : '') }}" name="OWNSTU" class="w-full form-control" placeholder="OWNSTU"/>
    </div>
    <div class="grid-column readonlyBlock">
        <label class="form-label uppercase">RELIGION</label>
        <select name="RELIGION" class="w-full tom-selects df-tom-selects">
            <option value="">Please Select</option>
            @if($religion->count() > 0)
                @foreach($religion as $opt)
                    <option {{ (isset($student->other->religion_id) && $student->other->religion_id == $opt->id ? 'Selected' : '') }} value="{{ $opt->id }}">{{ $opt->name }} {{ ($opt->is_hesa == 1 && !empty($opt->hesa_code) ? ' ['.$opt->hesa_code.']' : '') }} {{ ($opt->is_df == 1 && !empty($opt->df_code) ? ' ['.$opt->df_code.']' : '') }}</option>
                @endforeach
            @endif
        </select>
    </div>
    <div class="grid-column readonlyBlock">
        <label class="form-label uppercase">SEXID</label>
        <select name="SEXID" class="w-full tom-selects df-tom-selects">
            <option value="">Please Select</option>
            @if($sexindtity->count() > 0)
                @foreach($sexindtity as $opt)
                    <option {{ (isset($student->sex_identifier_id) && $student->sex_identifier_id == $opt->id ? 'Selected' : '') }} value="{{ $opt->id }}">{{ $opt->name }} {{ ($opt->is_hesa == 1 && !empty($opt->hesa_code) ? ' ['.$opt->hesa_code.']' : '') }} {{ ($opt->is_df == 1 && !empty($opt->df_code) ? ' ['.$opt->df_code.']' : '') }}</option>
                @endforeach
            @endif
        </select>
    </div>
    <div class="grid-column readonlyBlock">
        <label class="form-label uppercase">SEXORT</label>
        <select name="SEXORT" class="w-full tom-selects df-tom-selects">
            <option value="">Please Select</option>
            @if($sexort->count() > 0)
                @foreach($sexort as $opt)
                    <option {{ (isset($student->other->sexual_orientation_id) && $student->other->sexual_orientation_id == $opt->id ? 'Selected' : '') }} value="{{ $opt->id }}">{{ $opt->name }} {{ ($opt->is_hesa == 1 && !empty($opt->hesa_code) ? ' ['.$opt->hesa_code.']' : '') }} {{ ($opt->is_df == 1 && !empty($opt->df_code) ? ' ['.$opt->df_code.']' : '') }}</option>
                @endforeach
            @endif
        </select>
    </div>
    <div class="grid-column readonlyBlock">
        <label class="form-label uppercase">SSN</label>
        <input value="{{ (!empty($student->ssn_no) ? $student->ssn_no : '') }}" type="text" name="SSN" class="w-full form-control" placeholder="SSN"/>
    </div>
    <div class="grid-column readonlyBlock">
        <label class="form-label uppercase">SURNAME</label>
        <input value="{{ (!empty($student->last_name) ? $student->last_name : '') }}" type="text" name="SURNAME" class="w-full form-control" placeholder="SURNAME"/>
    </div>
    <div class="grid-column readonlyBlock">
        <label class="form-label uppercase">TTACCOM</label>
        <select name="TTACCOM" class="w-full tom-selects df-tom-selects">
            <option value="">Please Select</option>
            @if($ttacom->count() > 0)
                @foreach($ttacom as $opt)
                    <option {{ (isset($student->contact->term_time_accommodation_type_id) && $student->contact->term_time_accommodation_type_id == $opt->id ? 'Selected' : '') }} value="{{ $opt->id }}">{{ $opt->name }} {{ ($opt->is_hesa == 1 && !empty($opt->hesa_code) ? ' ['.$opt->hesa_code.']' : '') }} {{ ($opt->is_df == 1 && !empty($opt->df_code) ? ' ['.$opt->df_code.']' : '') }}</option>
                @endforeach
            @endif
        </select>
    </div>
    <div class="grid-column readonlyBlock">
        <label class="form-label uppercase">TTPCODE</label>
        <input value="{{ (isset($student->contact->term_time_post_code) ? $student->contact->term_time_post_code : '') }}" type="text" name="TTPCODE" class="w-full form-control" placeholder="TTPCODE"/>
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
                <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-5 gap-4 gap-y-2">
                    <div class="grid-column readonlyBlock">
                        <label class="form-label uppercase">DISABILITY</label>
                        <select name="DISABILITY[]" multiple class="w-full tom-selects" id="DISABILITY_IDS">
                            <option value="">Please Select</option>
                            @if($disabilities->count() > 0)
                                @foreach($disabilities as $opt)
                                    <option {{ isset($student->other->disability_status) && $student->other->disability_status == 1 && !empty($disability_ids) && in_array($opt->id, $disability_ids) ? 'Selected' : '' }} value="{{ $opt->id }}">{{ $opt->name }} {{ ($opt->is_hesa == 1 && !empty($opt->hesa_code) ? ' ['.$opt->hesa_code.']' : '') }} {{ ($opt->is_df == 1 && !empty($opt->df_code) ? ' ['.$opt->df_code.']' : '') }}</option>
                                @endforeach
                            @endif
                        </select>
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
                        <label class="form-label uppercase">NUMHUS</label>
                        <input type="text" value="{{ (isset($student->df->NUMHUS) && !empty($student->df->NUMHUS) ? $student->df->NUMHUS : '1') }}" name="NUMHUS" class="w-full form-control" placeholder="NUMHUS"/>
                    </div>
                    <div class="grid-column">
                        <label class="form-label uppercase">ENGEXPECTEDENDDATE</label>
                        <input type="text" value="{{ (isset($student->crel->course_end_date) && !empty($student->crel->course_end_date) ? date('Y-m-d', strtotime($student->crel->course_end_date)) : (isset($student->crel->creation->available->course_end_date) && !empty($student->crel->creation->available->course_end_date) ? date('Y-m-d', strtotime($student->crel->creation->available->course_end_date)) : '')) }}" name="ENGEXPECTEDENDDATE" class="w-full form-control df-datepicker" placeholder="ENGEXPECTEDENDDATE"/>
                    </div>
                    <div class="grid-column">
                        <label class="form-label uppercase">ENGSTARTDATE</label>
                        <input type="text" value="{{(isset($student->crel->course_start_date) && !empty($student->crel->course_start_date) ? date('Y-m-d', strtotime($student->crel->course_start_date)) : (isset($student->crel->creation->available->course_start_date) && !empty($student->crel->creation->available->course_start_date) ? date('Y-m-d', strtotime($student->crel->creation->available->course_start_date)) : '')) }}" name="ENGSTARTDATE" class="w-full form-control df-datepicker" placeholder="ENGSTARTDATE"/>
                    </div>
                    <div class="grid-column">
                        <label class="form-label uppercase">OWNENGID</label>
                        <select name="OWNENGID" class="w-full tom-selects df-tom-selects">
                            <option value="">Please Select</option>
                            @if($semesters->count() > 0)
                                @foreach($semesters as $opt)
                                    <option {{ (isset($student->crel->creation->semester_id) && $student->crel->creation->semester_id == $opt->id ? 'Selected' : '') }} value="{{ $opt->id }}">{{ $opt->name }}</option>
                                @endforeach
                            @endif
                        </select>
                    </div>
                    <div class="grid-column">
                        <label class="form-label uppercase">FEEELIG</label>
                        <select name="FEEELIG" class="w-full tom-selects df-tom-selects">
                            <option value="">Please Select</option>
                            @if($feeelig->count() > 0)
                                @foreach($feeelig as $opt)
                                    <option {{ (isset($student->crel->feeeligibility->fee_eligibility_id) && $student->crel->feeeligibility->fee_eligibility_id == $opt->id ? 'Selected' : '') }} value="{{ $opt->id }}">{{ $opt->name }} {{ ($opt->is_hesa == 1 && !empty($opt->hesa_code) ? ' ['.$opt->hesa_code.']' : '') }} {{ ($opt->is_df == 1 && !empty($opt->df_code) ? ' ['.$opt->df_code.']' : '') }}</option>
                                @endforeach
                            @endif
                        </select>
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
                                        <label class="form-label uppercase">CARELEAVER</label>
                                        <input value="{{ (isset($student->df->CARELEAVER) && !empty($student->df->CARELEAVER) ? $student->df->CARELEAVER : '') }}" type="text" name="CARELEAVER" class="w-full form-control" placeholder="CARELEAVER"/>
                                    </div>
                                    <div class="grid-column">
                                        <label class="form-label uppercase">PERMADDCOUNTRY</label>
                                        <select name="PERMADDCOUNTRY" class="w-full tom-selects df-tom-selects">
                                            <option value="">Please Select</option>
                                            @if($countries->count() > 0)
                                                @foreach($countries as $opt)
                                                    <option {{ (isset($student->contact->permanent_country_id) && $student->contact->permanent_country_id == $opt->id ? 'Selected' : '') }} value="{{ $opt->id }}">{{ $opt->name }} {{ ($opt->is_hesa == 1 && !empty($opt->hesa_code) ? ' ['.$opt->hesa_code.']' : '') }} {{ ($opt->is_df == 1 && !empty($opt->df_code) ? ' ['.$opt->df_code.']' : '') }}</option>
                                                @endforeach
                                            @endif
                                        </select>
                                    </div>
                                    <div class="grid-column">
                                        <label class="form-label uppercase">PERMADDPOSTCODE</label>
                                        <input type="text" value="{{ (isset($student->contact->permanent_post_code) && !empty($student->contact->permanent_post_code) ? $student->contact->permanent_post_code : '') }}" name="PERMADDPOSTCODE" class="w-full form-control" placeholder="PERMADDPOSTCODE"/>
                                    </div>
                                    <div class="grid-column">
                                        <label class="form-label uppercase">PREVIOUSPROVIDER</label>
                                        <select name="PREVIOUSPROVIDER" class="w-full tom-selects df-tom-selects">
                                            <option value="">Please Select</option>
                                            @if($prefprovider->count() > 0)
                                                @foreach($prefprovider as $opt)
                                                    <option {{ (isset($student->other->is_education_qualification) && $student->other->is_education_qualification == 1 && isset($student->qualHigest->previous_provider_id) && $student->qualHigest->previous_provider_id == $opt->id ? 'Selected' : '') }} value="{{ $opt->id }}">{{ $opt->name }} {{ ($opt->is_hesa == 1 && !empty($opt->hesa_code) ? ' ['.$opt->hesa_code.']' : '') }} {{ ($opt->is_df == 1 && !empty($opt->df_code) ? ' ['.$opt->df_code.']' : '') }}</option>
                                                @endforeach
                                            @endif
                                        </select>
                                    </div>
                                    <div class="grid-column">
                                        <label class="form-label uppercase">RELIGIOUSBGROUND</label>
                                        <select name="RELIGIOUSBGROUND" class="w-full tom-selects df-tom-selects">
                                            <option value="">Please Select</option>
                                            @if($religion->count() > 0)
                                                @foreach($religion as $opt)
                                                    <option {{ (isset($student->other->religion_id) && $student->other->religion_id == $opt->id ? 'Selected' : '') }} value="{{ $opt->id }}">{{ $opt->name }} {{ ($opt->is_hesa == 1 && !empty($opt->hesa_code) ? ' ['.$opt->hesa_code.']' : '') }} {{ ($opt->is_df == 1 && !empty($opt->df_code) ? ' ['.$opt->df_code.']' : '') }}</option>
                                                @endforeach
                                            @endif
                                        </select>
                                    </div>
                                    <div class="grid-column">
                                        <label class="form-label uppercase">HIGHESTQOE</label>
                                        <select name="HIGHESTQOE" class="w-full tom-selects df-tom-selects">
                                            <option value="">Please Select</option>
                                            @if($highestqoe->count() > 0)
                                                @foreach($highestqoe as $opt)
                                                    <option {{ (isset($student->other->is_education_qualification) && $student->other->is_education_qualification == 1 && isset($student->qualHigest->highest_qualification_on_entry_id) && $student->qualHigest->highest_qualification_on_entry_id == $opt->id ? 'Selected' : '') }} value="{{ $opt->id }}">{{ $opt->name }} {{ ($opt->is_hesa == 1 && !empty($opt->hesa_code) ? ' ['.$opt->hesa_code.']' : '') }} {{ ($opt->is_df == 1 && !empty($opt->df_code) ? ' ['.$opt->df_code.']' : '') }}</option>
                                                @endforeach
                                            @endif
                                        </select>
                                    </div>
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
                                                        <label class="form-label uppercase">ENTRYQUALAWARDID</label>
                                                        <!-- <input value="{{ (isset($student->other->is_education_qualification) && $student->other->is_education_qualification == 1 && isset($student->qualHigest->highest_academic) && !empty($student->qualHigest->highest_academic) ? $student->qualHigest->highest_academic : '') }}" type="text" name="ENTRYQUALAWARDID" class="w-full form-control" placeholder="ENTRYQUALAWARDID"/> -->
                                                        <select name="ENTRYQUALAWARDID" class="w-full tom-selects df-tom-selects">
                                                            <option value="">Please Select</option>
                                                            @if($otherAcademicQualifications->count() > 0)
                                                                @foreach($otherAcademicQualifications as $opt)
                                                                    <option {{ (isset($student->other->is_education_qualification) && $student->other->is_education_qualification == 1 && isset($student->qualHigest->other_academic_qualification_id) && $student->qualHigest->other_academic_qualification_id == $opt->id ? 'Selected' : '') }} value="{{ $opt->id }}">{{ $opt->name }}</option>
                                                                @endforeach
                                                            @endif
                                                        </select>
                                                    </div>
                                                    <div class="grid-column">
                                                        <label class="form-label uppercase">ENTRYQUALAWARDRESULT</label>
                                                        <!-- <input value="{{ (isset($student->other->is_education_qualification) && $student->other->is_education_qualification == 1 && isset($student->qualHigest->grade->name) && !empty($student->qualHigest->grade->name) ? $student->qualHigest->grade->name : '') }}" type="text" name="ENTRYQUALAWARDRESULT" class="w-full form-control" placeholder="ENTRYQUALAWARDRESULT"/> -->
                                                        <select name="ENTRYQUALAWARDRESULT" class="w-full tom-selects df-tom-selects">
                                                            <option value="">Please Select</option>
                                                            @if($qualGrades->count() > 0)
                                                                @foreach($qualGrades as $opt)
                                                                    <option {{ (isset($student->other->is_education_qualification) && $student->other->is_education_qualification == 1 && isset($student->qualHigest->qualification_grade_id) && $student->qualHigest->qualification_grade_id == $opt->id ? 'Selected' : '') }} value="{{ $opt->id }}">{{ $opt->name }} {{ ($opt->is_hesa == 1 && !empty($opt->hesa_code) ? ' ['.$opt->hesa_code.']' : '') }} {{ ($opt->is_df == 1 && !empty($opt->df_code) ? ' ['.$opt->df_code.']' : '') }}</option>
                                                                @endforeach
                                                            @endif
                                                        </select>
                                                    </div>
                                                    <div class="grid-column">
                                                        <label class="form-label uppercase">QUALTYPEID</label>
                                                        <select name="QUALTYPEID" class="w-full tom-selects df-tom-selects">
                                                            <option value="">Please Select</option>
                                                            @if($qualtypeids->count() > 0)
                                                                @foreach($qualtypeids as $opt)
                                                                    <option {{ (isset($student->other->is_education_qualification) && $student->other->is_education_qualification == 1 && isset($student->qualHigest->qualification_type_identifier_id) && $student->qualHigest->qualification_type_identifier_id == $opt->id ? 'Selected' : '') }} value="{{ $opt->id }}">{{ $opt->name }} {{ ($opt->is_hesa == 1 && !empty($opt->hesa_code) ? ' ['.$opt->hesa_code.']' : '') }} {{ ($opt->is_df == 1 && !empty($opt->df_code) ? ' ['.$opt->df_code.']' : '') }}</option>
                                                                @endforeach
                                                            @endif
                                                        </select>
                                                    </div>
                                                    <div class="grid-column">
                                                        <label class="form-label uppercase">QUALYEAR</label>
                                                        <input value="{{ (isset($student->other->is_education_qualification) && $student->other->is_education_qualification == 1 && isset($student->qualHigest->degree_award_date) && !empty($student->qualHigest->degree_award_date) ? date('Y', strtotime($student->qualHigest->degree_award_date)) : '') }}" type="text" name="QUALYEAR" class="w-full form-control" placeholder="QUALYEAR"/>
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
                                                                        <label class="form-label uppercase">SUBJECTID</label>
                                                                        <select name="SUBJECTID" class="w-full tom-selects df-tom-selects">
                                                                            <option value="">Please Select</option>
                                                                            @if($qualtypesubs->count() > 0)
                                                                                @foreach($qualtypesubs as $opt)
                                                                                    <option {{ (isset($student->other->is_education_qualification) && $student->other->is_education_qualification == 1 && isset($student->qualHigest->hesa_qualification_subject_id) && $student->qualHigest->hesa_qualification_subject_id == $opt->id ? 'Selected' : '') }} value="{{ $opt->id }}">{{ $opt->name }} {{ ($opt->is_hesa == 1 && !empty($opt->hesa_code) ? ' ['.$opt->hesa_code.']' : '') }} {{ ($opt->is_df == 1 && !empty($opt->df_code) ? ' ['.$opt->df_code.']' : '') }}</option>
                                                                                @endforeach
                                                                            @endif
                                                                        </select>
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
                                        <label class="form-label uppercase">ENGENDDATE</label>
                                        <!-- <input value="{{ (isset($student->df->ENGENDDATE) && !empty($student->df->ENGENDDATE) ? date('Y-m-d', strtotime($student->df->ENGENDDATE)) : '') }}" type="text" name="ENGENDDATE" class="w-full form-control df-datepicker" placeholder="ENGENDDATE"/> -->
                                        <input value="{{ $ENGENDDATE }}" type="text" name="ENGENDDATE" class="w-full form-control df-datepicker" placeholder="ENGENDDATE"/>
                                    </div>
                                    <div class="grid-column">
                                        <label class="form-label uppercase">RSNENGEND</label>
                                        <select name="RSNENGEND" class="w-full tom-selects df-tom-selects">
                                            <option value="">Please Select</option>
                                            @if($endreasons->count() > 0)
                                                @foreach($endreasons as $opt)
                                                    <option {{ ($RSNENGEND == $opt->id ? 'Selected' : '') }} value="{{ $opt->id }}">{{ $opt->name }} {{ ($opt->is_hesa == 1 && !empty($opt->hesa_code) ? ' ['.$opt->hesa_code.']' : '') }} {{ ($opt->is_df == 1 && !empty($opt->df_code) ? ' ['.$opt->df_code.']' : '') }}</option>
                                                @endforeach
                                            @endif
                                        </select>
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
                                        <label class="form-label uppercase">QUALAWARDID</label>
                                        <!-- <input type="text" value="{{ $QUALAWARDID }}" name="QUALAWARDID" class="w-full form-control" placeholder="QUALAWARDID"/> -->
                                        <select id="QUALAWARDID" name="QUALAWARDID" class="form-control w-full">
                                            <option value="">Please Select</option>
                                            <option {{ isset($student->awarded->qual_award_type) && $student->awarded->qual_award_type == 'HND' ? 'Selected' : '' }} value="HND">HND</option>
                                            <option {{ isset($student->awarded->qual_award_type) && $student->awarded->qual_award_type == 'HNC' ? 'Selected' : '' }} value="HNC">HNC</option>
                                        </select>
                                    </div>
                                    <div class="grid-column">
                                        <label class="form-label uppercase">QUALID</label>
                                        <input type="text" value="{{ $QUALID }}" name="QUALID" class="w-full form-control" placeholder="QUALID"/>
                                    </div>
                                    <div class="grid-column">
                                        <label class="form-label uppercase">QUALRESULT</label>
                                        <!-- <input type="text" value="{{ (isset($student->awarded->qual->name) && !empty($student->awarded->qual->name) ? $student->awarded->qual->name : '') }}" name="QUALRESULT" class="w-full form-control" placeholder="QUALRESULT"/> -->
                                        <select id="QUALRESULT" name="QUALRESULT" class="form-control w-full">
                                            <option value="">Please Select</option>
                                            @if($qualAwards->count() > 0)
                                                @foreach($qualAwards as $oaq)
                                                    <option {{ isset($student->awarded->qual_award_result_id) && $student->awarded->qual_award_result_id == $oaq->id ? 'Selected' : '' }} value="{{ $oaq->id }}">{{ $oaq->name }}</option>
                                                @endforeach
                                            @endif
                                        </select>
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

                                $SCSMODE = (isset($stu->mode_id) && $stu->mode_id > 0 ? $stu->mode_id : '');
                                $SCSEXPECTEDENDDATE = $instanceEnd;
                                $SCSENDDATE = $hesaEndDate;
                                if(!empty($ENGENDDATE) && ($ENGENDDATE > $periodStartDate &&  $ENGENDDATE < $periodEndDate) && $ENGENDDATE < $instanceEnd):
                                    $SCSENDDATE = $ENGENDDATE;
                                    $SCSMODE = (!empty($SCSMODE) ? 2 : $SCSMODE);
                                elseif(empty($hesaEndDate) && (!empty($SCSEXPECTEDENDDATE) && $SCSEXPECTEDENDDATE < date('Y-m-d'))):
                                    $SCSENDDATE = $SCSEXPECTEDENDDATE;
                                    $SCSMODE = (!empty($SCSMODE) ? 4 : $SCSMODE);
                                endif;

                                $RSNSCSEND = '';
                                if(($hesaEndDate == '' && $instanceEnd <= date('Y-m-d')) || ($hesaEndDate != '' && $hesaEndDate == $instanceEnd) || ($hesaEndDate != '' && $hesaEndDate > $instanceEnd && $instanceEnd <= date('Y-m-d'))):
                                    $RSNSCSEND = 4;
                                elseif($hesaEndDate != '' && $hesaEndDate > $instanceStart && $hesaEndDate < $instanceEnd):
                                    $RSNSCSEND = 2;
                                else:
                                    $RSNSCSEND = '';
                                endif;
                                $FUNDCOMP = (!empty($periodEndDate) && $periodEndDate < date('Y-m-d') ? 1 : (!empty($periodStartDate) && $periodStartDate <= date('Y-m-d') && !empty($periodEndDate) && $periodEndDate > date('Y-m-d') ? 2 : 3));
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
                                </div>
                                <div id="df-accr-Student-Course-Session-collapse-{{ $i }}" class="lcc-accordion-collapse lcc-show" style="display: block;">
                                    <div class="lcc-accordion-body text-slate-600 dark:text-slate-500 leading-relaxed">
                                        <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-5 gap-4 gap-y-2">
                                            <div class="grid-column">
                                                <label class="form-label uppercase">SCSESSIONID</label>
                                                <input type="text" value="{{ $stu->course_creation_instance_id }}" name="SCS[{{ $stu->id }}][SCSESSIONID]" class="w-full form-control" placeholder="SCSESSIONID"/>
                                            </div>
                                            <div class="grid-column">
                                                <label class="form-label uppercase">COURSEID</label>
                                                <input type="text" value="{{ $stu->courseaim_id }}" name="SCS[{{ $stu->id }}][COURSEID]" class="w-full form-control" placeholder="COURSEID"/>
                                            </div>
                                            <div class="grid-column">
                                                <label class="form-label uppercase">INVOICEFEEAMOUNT</label>
                                                <input type="text" value="{{ $stu->gross_fee }}" name="SCS[{{$stu->id }}][INVOICEFEEAMOUNT]" class="w-full form-control" placeholder="INVOICEFEEAMOUNT"/>
                                            </div>
                                            <div class="grid-column">
                                                <label class="form-label uppercase">INVOICEHESAID</label>
                                                <input type="text" value="5026" name="SCS[{{ $stu->id }}][INVOICEHESAID]" class="w-full form-control" placeholder="INVOICEHESAID"/>
                                            </div>
                                            <div class="grid-column">
                                                <label class="form-label uppercase">SCSEXPECTEDENDDATE</label>
                                                <input type="text" value="{{ (isset($stu->instance->end_date) && !empty($stu->instance->end_date) ? date('Y-m-d', strtotime($stu->instance->end_date)) : '') }}" name="SCS[{{ $stu->id }}][SCSEXPECTEDENDDATE]" class="w-full form-control df-datepicker" placeholder="SCSEXPECTEDENDDATE"/>
                                            </div>
                                            <div class="grid-column">
                                                <label class="form-label uppercase">SCSENDDATE</label>
                                                <!-- <input type="text" value="{{ (isset($stu->enddate) && !empty($stu->enddate) ? date('Y-m-d', strtotime($stu->enddate)) : '') }}" name="SCS[{{ $stu->id }}][SCSENDDATE]" class="w-full form-control df-datepicker" placeholder="SCSENDDATE"/> -->
                                                <input type="text" value="{{ (!empty($SCSENDDATE) ? date('Y-m-d', strtotime($SCSENDDATE)) : '') }}" name="SCS[{{ $stu->id }}][SCSENDDATE]" class="w-full form-control df-datepicker" placeholder="SCSENDDATE"/>
                                            </div>
                                            <div class="grid-column">
                                                <label class="form-label uppercase">SCSFEEAMOUNT</label>
                                                <input type="text" value="{{ (isset($stu->netfee) && $stu->netfee > 0 ? $stu->netfee : '') }}" name="SCS[{{ $stu->id }}][SCSFEEAMOUNT]" class="w-full form-control" placeholder="SCSFEEAMOUNT"/>
                                            </div>
                                            <div class="grid-column">
                                                <label class="form-label uppercase">SCSMODE</label>
                                                <select name="SCS[{{ $stu->id }}][SCSMODE]" class="w-full tom-selects df-tom-selects">
                                                    <option value="">Please Select</option>
                                                    @if($modes->count() > 0)
                                                        @foreach($modes as $opt)
                                                            <option {{ ($SCSMODE == $opt->id ? 'Selected' : '') }} value="{{ $opt->id }}">{{ $opt->name }} {{ ($opt->is_hesa == 1 && !empty($opt->hesa_code) ? ' ['.$opt->hesa_code.']' : '') }} {{ ($opt->is_df == 1 && !empty($opt->df_code) ? ' ['.$opt->df_code.']' : '') }}</option>
                                                        @endforeach
                                                    @endif
                                                </select>
                                            </div>
                                            <div class="grid-column">
                                                <label class="form-label uppercase">SCSSTARTDATE</label>
                                                <input type="text" value="{{ (isset($stu->periodstart) && !empty($stu->periodstart) ? date('Y-m-d', strtotime($stu->periodstart)) : '') }}" name="SCS[{{ $stu->id }}][SCSSTARTDATE]" class="w-full form-control df-datepicker" placeholder="SCSSTARTDATE"/>
                                            </div>
                                            <div class="grid-column">
                                                <label class="form-label uppercase">SESSIONYEARID</label>
                                                <input type="text" value="{{ $stu->course_creation_instance_id }}" name="SCS[{{ $stu->id }}][SESSIONYEARID]" class="w-full form-control" placeholder="SESSIONYEARID"/>
                                            </div>
                                            <div class="grid-column">
                                                <label class="form-label uppercase">YEARPRG</label>
                                                <input type="text" value="{{ ($stu->yearprg > 0 ? $stu->yearprg : '') }}" name="SCS[{{ $stu->id }}][YEARPRG]" class="w-full form-control" placeholder="YEARPRG"/>
                                            </div>
                                            <div class="grid-column">
                                                <label class="form-label uppercase">RSNSCSEND</label>
                                                <select name="SCS[{{ $stu->id }}][RSNSCSEND]" class="w-full tom-selects df-tom-selects">
                                                    <option value="">Please Select</option>
                                                    @if($rsnscsends->count() > 0)
                                                        @foreach($rsnscsends as $opt)
                                                            <option {{ ($RSNSCSEND == $opt->id ? 'Selected' : '') }} value="{{ $opt->id }}">{{ $opt->name }} {{ ($opt->is_hesa == 1 && !empty($opt->hesa_code) ? ' ['.$opt->hesa_code.']' : '') }} {{ ($opt->is_df == 1 && !empty($opt->df_code) ? ' ['.$opt->df_code.']' : '') }}</option>
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
                                                                <label class="form-label uppercase">REFPERIOD</label>
                                                                <select name="SCS[{{ $stu->id }}][REFPERIOD]" class="w-full tom-selects df-tom-selects">
                                                                    <option value="">Please Select</option>
                                                                    <option {{ ($REFPERIOD_INC == '01' ? 'Selected' : '') }} value="01">Reference period 1</option>
                                                                    <option {{ ($REFPERIOD_INC == '02' ? 'Selected' : '') }} value="02">Reference period 2</option>
                                                                    <option {{ ($REFPERIOD_INC == '03' ? 'Selected' : '') }} value="03">Reference period 3</option>
                                                                </select>
                                                            </div>
                                                            <div class="grid-column">
                                                                <label class="form-label uppercase">YEAR</label>
                                                                <input type="text" value="{{ (isset($stu->instance->year->from_date) && !empty($stu->instance->year->from_date) ? date('Y', strtotime($stu->instance->year->from_date)) : '') }}" name="SCS[{{ $stu->id }}][YEAR]" class="w-full form-control" placeholder="YEAR"/>
                                                            </div>
                                                            <div class="grid-column">
                                                                <label class="form-label uppercase">RPSTULOAD</label>
                                                                <input type="text" name="SCS[{{ $stu->id }}][RPSTULOAD]" value="{{ ($stu->student_load && $stu->student_load > 0 ? ($stu->student_load == 99 ? '100' : $stu->student_load) : '') }}" class="w-full form-control" placeholder="RPSTULOAD"/>
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
                                                                        <label class="form-label uppercase">STATUSVALIDFROM</label>
                                                                        <input type="text" value="{{ (isset($sts['STATUSVALIDFROM']) && !empty($sts['STATUSVALIDFROM']) ? date('Y-m-d', strtotime($sts['STATUSVALIDFROM'])) : '') }}" name="SCS[{{ $stu->id }}][STATUSVALIDFROM]" class="w-full form-control df-datepicker" placeholder="STATUSVALIDFROM"/>
                                                                    </div>
                                                                    <div class="grid-column">
                                                                        <label class="form-label uppercase">STATUSCHANGEDTO</label>
                                                                        <!-- <input type="text" name="SCS[{{ $stu->id }}][STATUSCHANGEDTO]" class="w-full form-control df-datepicker" placeholder="STATUSCHANGEDTO"/> -->
                                                                        <select name="SCS[{{ $stu->id }}][STATUSCHANGEDTO]" class="w-full tom-selects df-tom-selects">
                                                                            <option value="">Please Select</option>
                                                                            @if($sessionStatus->count() > 0)
                                                                                @foreach($sessionStatus as $opt)
                                                                                    <option {{ (isset($sts['STATUSCHANGEDTO']) && $sts['STATUSCHANGEDTO'] == $opt->id ? 'Selected' : '') }} value="{{ $opt->id }}">{{ $opt->name }} {{ ($opt->is_hesa == 1 && !empty($opt->hesa_code) ? ' ['.$opt->hesa_code.']' : '') }} {{ ($opt->is_df == 1 && !empty($opt->df_code) ? ' ['.$opt->df_code.']' : '') }}</option>
                                                                                @endforeach
                                                                            @endif
                                                                        </select>
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
                                                                <label class="form-label uppercase">STUDYLOCID</label>
                                                                <input type="text" value="{{ (isset($stu->studentCR->propose->venue->name) && !empty($stu->studentCR->propose->venue->name) ? $stu->studentCR->propose->venue->name : '') }}" name="SCS[{{ $stu->id }}][STUDYLOCID]" class="w-full form-control" placeholder="STUDYLOCID"/>
                                                            </div>
                                                            <div class="grid-column">
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
                                                            </div>
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