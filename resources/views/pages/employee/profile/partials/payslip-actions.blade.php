@if(isset($canManagePayslips) && $canManagePayslips)
    @php $payslipLabel = \App\Services\PayslipManagementService::label($record->type, $record->month_year, $record->holidayYear); @endphp
    <button type="button" class="payslipReplaceBtn btn btn-outline-secondary btn-sm ml-1" data-id="{{ $record->id }}" data-label="{{ $payslipLabel }}"><i data-lucide="upload" class="w-4 h-4 mr-2"></i>Replace</button>
    <button type="button" class="payslipDeleteBtn btn btn-outline-danger btn-sm ml-1" data-id="{{ $record->id }}" data-label="{{ $payslipLabel }}"><i data-lucide="trash-2" class="w-4 h-4 mr-2"></i>Delete</button>
@endif
