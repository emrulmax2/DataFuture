@extends('../layout/' . $layout)

@section('subhead')
    <title>{{ $title }}</title>
@endsection

@section('subcontent')
    @php
        $agent = $comission?->agent;
        $agentName = trim(($agent?->full_name ?? '').(!empty($agent?->organization) ? ' ('.$agent->organization.')' : ''));
        $agentName = $agentName !== '' ? $agentName : ($comission?->agentuser?->email ?? 'Agent');
        $agentEmail = $agent?->email ?? ($comission?->agentuser?->email ?? '');
        $addressHtml = (string) ($agent?->address?->full_address ?? '');
        $addressValue = trim(preg_replace('/\s+/', ' ', strip_tags(str_replace(['<br>', '<br/>', '<br />'], ', ', $addressHtml))));
        $studentCount = $comission?->comissions?->count() ?? 0;
        $remittanceTotal = \Illuminate\Support\Number::currency($comission?->comissions?->sum('amount') ?? 0, in: 'GBP');
        $paymentStatus = (int) ($comission?->payment?->status ?? 0);
        $statusLabel = match ($paymentStatus) {
            1 => 'Scheduled',
            2 => 'Paid',
            3 => 'Canceled',
            default => 'Pending',
        };
        $statusTone = match ($paymentStatus) {
            1 => 'is-scheduled',
            2 => 'is-paid',
            3 => 'is-canceled',
            default => 'is-pending',
        };
        $commissionDetails = [
            ['label' => 'Name', 'value' => $agentName, 'icon' => 'users', 'tone' => 'teal'],
            ['label' => 'Email', 'value' => $agentEmail, 'icon' => 'mail', 'tone' => 'blue'],
            ['label' => 'Address', 'value' => $addressValue, 'icon' => 'map-pin', 'tone' => 'red'],
            ['label' => 'Remittance Ref', 'value' => $comission?->remittance_ref ?? '', 'icon' => 'file-text', 'tone' => 'slate'],
            ['label' => 'Generate Date', 'value' => !empty($comission?->entry_date) ? date('jS F, Y', strtotime($comission->entry_date)) : '', 'icon' => 'calendar-days', 'tone' => 'purple'],
            ['label' => 'Intake Semester', 'value' => $comission?->semester?->name ?? '', 'icon' => 'graduation-cap', 'tone' => 'gold'],
            ['label' => 'Remittance Total', 'value' => $remittanceTotal, 'icon' => 'pound-sterling', 'tone' => 'green', 'emphasis' => true],
        ];
    @endphp

    <div class="agm-page agm-commission-page agm-commission-detail-page">
        <section class="agm-commission-hero agm-commission-hero--remittance">
            <div class="agm-commission-hero__copy">
                <span class="agm-commission-hero__icon">
                    <i data-lucide="file-text"></i>
                </span>
                <div>
                    <span class="agm-eyebrow">Remittance {{ $comission?->remittance_ref ?? '' }}</span>
                    <h1>Agent Commission Details</h1>
                    <p>Per-student breakdown of this remittance payout</p>
                </div>
            </div>

            <a href="{{ route('agent.management.comission', [$comission->semester_id, $comission->agent_user_id]) }}" class="agm-btn agm-btn--dark">
                <i data-lucide="arrow-left"></i>
                <span>Back to List</span>
            </a>
        </section>

        <section class="agm-commission-details agm-commission-remittance-details">
            <div class="agm-commission-card__head">
                <span></span>
                <strong>Details</strong>
                <b class="agm-commission-payment-pill {{ $statusTone }}">{{ $statusLabel }}</b>
            </div>

            <div class="agm-commission-details__grid agm-commission-remittance-details__grid">
                @foreach($commissionDetails as $detail)
                    <div class="agm-commission-detail agm-commission-remittance-detail {{ !empty($detail['emphasis']) ? 'agm-commission-remittance-detail--emphasis' : '' }}">
                        <span class="agm-commission-detail__icon is-{{ $detail['tone'] }}">
                            <i data-lucide="{{ $detail['icon'] }}"></i>
                        </span>
                        <div>
                            <small>{{ $detail['label'] }}</small>
                            <strong>{{ $detail['value'] !== '' ? $detail['value'] : 'Not set' }}</strong>
                        </div>
                    </div>
                @endforeach
            </div>
        </section>

        <section class="agm-commission-card agm-commission-remittance-students">
            <div class="agm-commission-students-head">
                <div class="agm-commission-students-head__title">
                    <span></span>
                    <strong>Students in this remittance</strong>
                    <b id="comissionStudentCount">{{ $studentCount }}</b>
                </div>

                <a href="{{ route('agent.management.remittance.export', $comission->id) }}" class="agm-btn agm-btn--export">
                    <i data-lucide="download"></i>
                    <span>Export</span>
                </a>
            </div>

            <div class="agm-commission-table-wrap agm-commission-remittance-table-wrap">
                <div id="agentComissionDetailsListTable"
                     data-comission="{{ $comission->id }}"
                     data-total="{{ $remittanceTotal }}"
                     class="agm-commission-table agm-commission-remittance-table"></div>
            </div>
        </section>
    </div>

    <div id="successModal" class="modal agm-commission-feedback-modal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-body">
                    <i data-lucide="check-circle"></i>
                    <h2 class="successModalTitle"></h2>
                    <p class="successModalDesc"></p>
                    <button type="button" data-tw-dismiss="modal" class="agm-btn agm-btn--primary">Ok</button>
                </div>
            </div>
        </div>
    </div>

    <div id="confirmModal" class="modal agm-commission-feedback-modal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-body">
                    <i data-lucide="x-circle"></i>
                    <h2 class="confModTitle">Are you sure?</h2>
                    <p class="confModDesc"></p>
                    <div class="agm-commission-feedback-modal__actions">
                        <button type="button" data-tw-dismiss="modal" class="agm-btn agm-btn--muted">No, Cancel</button>
                        <button type="button" data-id="0" data-action="none" class="agreeWith agm-btn agm-btn--danger">Yes, I agree</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('script')
    @vite('resources/js/agent-management-comission-details.js')
@endsection
