{{--
    Step 3: residency status and criminal convictions.

    Laid out as the design has it - two sections, the question as pill
    radios, the declaration as a boxed checkbox - with the College's own
    declaration copy kept in full and set in the portal's prose style.

    Hooks kept for the script and the validator: `residency_status_id`
    (Tom Select, `applicationLccTom`), `have_you_been_convicted` (the change
    handler shows `.criminalConvictionDetailsWrap`), `criminal_conviction_details`,
    `criminal_declaration`, the `error-<field>` holders and `applicant_id`.
--}}
@php
    $apdResId   = isset($apply->residency->residency_status_id) ? $apply->residency->residency_status_id : null;
    $apdConv    = isset($apply->criminalConviction->have_you_been_convicted) ? (int) $apply->criminalConviction->have_you_been_convicted : null;
    $apdDecl    = isset($apply->criminalConviction->criminal_declaration) && (int) $apply->criminalConviction->criminal_declaration === 1;
    $apdDetails = isset($apply->criminalConviction->criminal_conviction_details) ? $apply->criminalConviction->criminal_conviction_details : '';
@endphp

<h3 class="apd-h3">Residency status</h3>
<div class="apd-field">
    <label for="residency_status_id" class="form-label">What is your residency / immigration status in the UK? <span class="text-danger">*</span></label>
    <select id="residency_status_id" class="applicationLccTom lcc-tom-select w-full" name="residency_status_id">
        <option value="" selected>Please Select</option>
        @if(!empty($residencyStatuses))
            @foreach($residencyStatuses as $residency_status)
                <option data-ew="{{ $residency_status->id }}" {{ $apdResId == $residency_status->id ? 'selected' : '' }} value="{{ $residency_status->id }}">{{ $residency_status->name }}</option>
            @endforeach
        @endif
    </select>
    <span class="apd-hint">You will be asked to upload proof (passport, BRP, share code) during admissions checks.</span>
    <div class="acc__input-error error-residency_status_id apd-err text-danger"></div>
</div>

<h3 class="apd-h3">Criminal convictions</h3>
<p class="apd-prose">
    As part of the admissions process, applicants are required to declare any relevant criminal convictions.
    This declaration helps the College to meet its safeguarding responsibilities while ensuring that admissions
    decisions are fair, transparent, and proportionate.
</p>
<div class="apd-field">
    <label class="form-label">Have you been convicted of any criminal offence in the UK or any other country? <span class="text-danger">*</span></label>
    <x-applicant.switch name="have_you_been_convicted" id="criminal_conviction_yes" :checked="$apdConv === 1" />
    <div class="acc__input-error error-have_you_been_convicted apd-err text-danger"></div>
</div>

{{-- The script fades this wrapper in and out; the field grid sits inside it
     so the fade's `display: block` does not flatten the label/control gap. --}}
<div class="criminalConvictionDetailsWrap" style="{{ $apdConv === 1 ? '' : 'display: none;' }}">
    <div class="apd-field">
        <label for="criminal_conviction_details" class="form-label">Please give details <span class="text-danger">*</span></label>
        <textarea id="criminal_conviction_details" name="criminal_conviction_details" class="form-control apd-ctl criminal_conviction_details" rows="4" placeholder="Provide details of the conviction(s)">{{ $apdDetails }}</textarea>
        <div class="acc__input-error error-criminal_conviction_details apd-err text-danger"></div>
    </div>
</div>

<div class="apd-sub apd-declaration">
    <h4 class="apd-sub__title">Declaration</h4>
    <p class="apd-prose">
        Please ensure that all information provided is complete and accurate. Failure to disclose relevant
        information, or the provision of false or misleading information, may result in:
    </p>
    {{-- Lucide `check-circle` (0.284.0), inlined so it does not wait on createIcons(). --}}
    <ul class="apd-list">
        <li><svg class="apd-list__icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg><span>Withdrawal of an offer</span></li>
        <li><svg class="apd-list__icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg><span>Termination of enrolment</span></li>
        <li><svg class="apd-list__icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg><span>Further action in line with College policies</span></li>
    </ul>
    <label class="apd-opt chk">
        <input class="sr" type="checkbox" id="criminal_declaration" name="criminal_declaration" value="1" {{ $apdDecl ? 'checked' : '' }}>
        <span class="box"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M20 6 9 17l-5-5"/></svg></span>
        <span>I confirm I have read and understood the above declaration, and that the information about my residency and any convictions is accurate and may be verified. <span class="text-danger">*</span></span>
    </label>
    <div class="acc__input-error error-criminal_declaration apd-err text-danger"></div>
</div>

<input type="hidden" name="applicant_id" value="{{ isset($apply->id) && $apply->id > 0 ? $apply->id : 0 }}"/>
