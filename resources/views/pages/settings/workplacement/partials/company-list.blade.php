@forelse($companies as $company)
    <div class="ss-wp-node ss-wp-company accordion">
        <div id="companyAccordion-{{ $company->id }}" class="ss-wp-node__head ss-wp-company__head accordion-header">
            <button class="accordion-button collapsed ss-wp-node__toggle"
                type="button"
                data-target="#companyAccordion-collapse-{{ $company->id }}"
                aria-expanded="false"
                aria-controls="companyAccordion-collapse-{{ $company->id }}">
                <span class="ss-wp-toggle-icon">
                    <i data-lucide="plus" class="accordion-icon-plus"></i>
                    <i data-lucide="minus" class="accordion-icon-minus hidden"></i>
                </span>
                <span class="ss-wp-company__badge"><i data-lucide="building"></i></span>
                <span class="ss-wp-node__title">{{ $company->name }}</span>
            </button>
            <div class="ss-wp-node__actions">
                <button data-id="{{ $company->id }}" data-tw-toggle="modal" data-tw-target="#addCompanySupervisorModal" type="button" class="add_sup_btn ss-wp-soft-btn">
                    <i data-lucide="plus"></i>
                    Add Supervisor
                </button>
                <button data-id="{{ $company->id }}" data-tw-toggle="modal" data-tw-target="#editWPCompanyModal" type="button" class="editCompany_btn ss-wp-icon-btn ss-wp-icon-btn--edit" aria-label="Edit {{ $company->name }}">
                    <i data-lucide="pencil"></i>
                </button>
                <button data-id="{{ $company->id }}" type="button" class="deleteCompanyBtn ss-wp-icon-btn ss-wp-icon-btn--delete" aria-label="Delete {{ $company->name }}">
                    <i data-lucide="trash-2"></i>
                </button>
            </div>
        </div>

        <div id="companyAccordion-collapse-{{ $company->id }}" class="accordion-collapse collapse ss-wp-company__body"
            aria-labelledby="companyAccordion-{{ $company->id }}">
            <div id="wpSupervisorListTable_{{ $company->id }}" class="ss-tabulator ss-wp-supervisor-table table-report table-report--tabulator"></div>
        </div>
    </div>
@empty
    <div class="ss-wp-empty">
        <span><i data-lucide="building-2"></i></span>
        <strong>No workplacement companies found</strong>
        <p>Add a company to start recording placements and their supervisors.</p>
    </div>
@endforelse
