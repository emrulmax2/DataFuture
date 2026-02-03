<div class="grid grid-cols-12 gap-4 gap-y-5 mt-5">
    <div class="col-span-12 sm:col-span-8">
        <div class="grid grid-cols-12 gap-x-4">
            <label for="residency_status_id" class="form-label sm:pt-2 col-span-12 sm:col-span-6 inline-flex">Residency Status <span class="text-danger">*</span> <i data-loading-icon="oval" data-color="black"  class="courseLoading w-4 h-4 ml-2 hidden"></i></label>
            <div class="col-span-12 sm:col-span-6">
                <select id="residency_status_id" class="applicationLccTom lcc-tom-select w-full" name="residency_status_id">
                    <option value="" selected>Please Select</option>
                    @if(!empty($residencyStatuses))
                        @foreach($residencyStatuses as $residency_status)
                            <option data-ew="{{ $residency_status->id }}" {{ isset($apply->residency->residency_status_id) && $apply->residency->residency_status_id == $residency_status->id ? 'selected' : ''}} value="{{ $residency_status->id }}">{{ $residency_status->name }}</option>
                        @endforeach 
                    @endif 
                </select>
                <div class="acc__input-error error-residency_status_id text-danger mt-2"></div>
            </div>
        </div>

        <div class="grid grid-cols-12 gap-x-4 mt-5">
            <div class="col-span-12">
                <div class="font-medium text-base">Declaration of Criminal Convictions</div>
                <p class="mt-2 text-slate-600">
                    As part of the admissions process, applicants are required to declare any relevant criminal convictions.
                    This declaration helps the College to meet its safeguarding responsibilities while ensuring that admissions decisions are fair,
                    transparent, and proportionate.
                </p>

                <div class="mt-4">
                    <div class="font-medium">Have you been convicted of any criminal offence in the UK or any other Country?</div>
                    <div class="mt-2 flex flex-wrap gap-6">
                        <div class="form-check">
                            <input id="criminal_conviction_yes" class="form-check-input" type="radio" name="have_you_been_convicted" value="1" {{ isset($apply->criminalConviction->have_you_been_convicted) && (int) $apply->criminalConviction->have_you_been_convicted === 1 ? 'checked' : '' }}>
                            <label class="form-check-label" for="criminal_conviction_yes">Yes</label>
                        </div>
                        <div class="form-check">
                            <input id="criminal_conviction_no" class="form-check-input" type="radio" name="have_you_been_convicted" value="0" {{ isset($apply->criminalConviction->have_you_been_convicted) && (int) $apply->criminalConviction->have_you_been_convicted === 0 ? 'checked' : '' }}>
                            <label class="form-check-label" for="criminal_conviction_no">No</label>
                        </div>
                    </div>
                    <div class="acc__input-error error-have_you_been_convicted text-danger mt-2"></div>
                    <div class="criminalConvictionDetailsWrap mt-4" style="{{ isset($apply->criminalConviction->have_you_been_convicted) && (int) $apply->criminalConviction->have_you_been_convicted === 1 ? '' : 'display: none;' }}">
                        <label for="criminal_conviction_details" class="form-label">If yes, please provide details <span class="text-danger">*</span></label>
                        <textarea id="criminal_conviction_details" name="criminal_conviction_details" class="form-control w-full" rows="4" placeholder="Provide details of the conviction(s)">{{ isset($apply->criminalConviction->criminal_conviction_details) ? $apply->criminalConviction->criminal_conviction_details : '' }}</textarea>
                        <div class="acc__input-error error-criminal_conviction_details text-danger mt-2"></div>
                    </div>
                </div>
            </div>
        </div>


        <div class="grid grid-cols-12 gap-x-4 mt-6">
            <div class="col-span-12">
                <div class="font-medium text-base">Declaration</div>
                <p class="mt-2 text-slate-600">
                    Please ensure that all information provided is complete and accurate. Failure to disclose relevant information,
                    or the provision of false or misleading information, may result in:
                </p>
                <ul class="mt-2 list-disc pl-6 text-slate-600">
                    <li>Withdrawal of an offer</li>
                    <li>Termination of enrolment</li>
                    <li>Further action in line with College policies</li>
                </ul>
                <div class="form-check mt-4">
                    <input id="criminal_declaration" class="form-check-input" type="checkbox" name="criminal_declaration" value="1" {{ isset($apply->criminalConviction->criminal_declaration) && (int) $apply->criminalConviction->criminal_declaration === 1 ? 'checked' : '' }}>
                    <label class="form-check-label" for="criminal_declaration">I confirm I have read and understood the above declaration.</label>
                </div>
                <div class="acc__input-error error-criminal_declaration text-danger mt-2"></div>
            </div>
        </div>
        <input type="hidden" name="applicant_id" value="{{ isset($apply->id) && $apply->id > 0 ? $apply->id : 0 }}"/>
    </div>
</div>

