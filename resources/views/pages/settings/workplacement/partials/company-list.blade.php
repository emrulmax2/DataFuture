@if($companies->count() > 0)
@foreach($companies as $company)
<div id="companyAccordion-{{ $company->id }}" class="accordion">
    <div class="accordion-item {{ $loop->last ? '' : 'border-b' }}">
        <div id="companyAccordion-{{ $company->id }}" class="accordion-header flex justify-between {{ $loop->first ? '' : 'pt-4' }}">
            <button class="accordion-button collapsed relative w-full text-lg font-semibold"
                type="button"
                data-target="#companyAccordion-collapse-{{ $company->id }}"
                aria-expanded="false"
                aria-controls="companyAccordion-collapse-{{ $company->id }}">
                <div class="flex items-center font-medium text-base">
                    <i data-lucide="plus" class="w-4 h-4 mr-2 accordion-icon-plus"></i>
                    <i data-lucide="minus" class="w-4 h-4 mr-2 accordion-icon-minus hidden"></i>
                    {{ $company->name }}
                </div>
            </button>
            <div class="flex">
                <button data-id="{{ $company->id }}" data-tw-toggle="modal" data-tw-target="#addCompanySupervisorModal" type="button" class="add_sup_btn btn-rounded btn btn-primary text-white px-2 whitespace-nowrap ml-1 text-xs font-light h-[30px]"><i data-lucide="plus" class="w-3 h-3"></i>Add Supervisor</button>
                <button data-id="{{ $company->id }}" data-tw-toggle="modal" data-tw-target="#editWPCompanyModal" type="button" class="editCompany_btn btn-rounded btn btn-success text-white p-0 w-[30px] h-[30px] ml-1"><i data-lucide="Pencil" class="w-3 h-3"></i></button>
                <button data-id="{{ $company->id }}" class="deleteCompanyBtn btn btn-danger text-white btn-rounded ml-1 p-0 w-[30px] h-[30px]"><i data-lucide="Trash2" class="w-3 h-3"></i></button>
            </div>
        </div>
        <div id="companyAccordion-collapse-{{ $company->id }}" class="accordion-collapse collapse"
            aria-labelledby="companyAccordion-{{ $company->id }}">
            <div class="accordion-body text-slate-600 dark:text-slate-500 leading-relaxed">
                <div class="overflow-x-auto scrollbar-hidden">
                    <div id="wpSupervisorListTable_{{ $company->id }}" class="mt-5 table-report table-report--tabulator"></div>
                </div>
            </div>
        </div>
    </div>
</div>
@endforeach
@else
<div class="col-span-12">
    <div class="intro-y box p-5">
        <div class="text-center">No Workplacement Companies Found</div>
    </div>
</div>
@endif