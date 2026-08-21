@extends('../layout/' . $layout)

@section('body_class', 'accounts-shell-body')

@section('subhead')
    <title>{{ $title }}</title>
@endsection

@section('subcontent')

    @php
        /* The accounts sidebar shows a balance per storage. Resolved once here
           and handed to the partial, the same way the transaction list does it —
           AccBank::$balance costs four aggregate queries per bank. */
        $accBankBalances = [];
        if (! empty($banks)):
            foreach ($banks as $bnk):
                $accBankBalances[$bnk->id] = $bnk->balance;
            endforeach;
        endif;
    @endphp

    <div class="accounts-shell accounts-storage-redesign">
        <div class="acc-shell__layout">
            <!-- BEGIN: Accounts Menu -->
            <aside class="acc-shell__aside">
                @include('pages.accounts.sidebar', ['redesign' => true, 'bankBalances' => $accBankBalances])
            </aside>
            <!-- END: Accounts Menu -->

            <div class="acc-shell__main">

    @php
        $money = fn ($n) => ($n < 0 ? '-' : '') . '£' . number_format(abs((float) $n), 2);

        /* The four pipeline stages, and how far this requisition has come. */
        $stages = ['Submitted' => 'Requester', 'First approval' => 'First approver',
                   'Final approval' => 'Final approver', 'Payment' => 'Budget holder'];

        $reached = match ($req['status'] ?? null) {
            'submitted'        => 1,
            'first_approved'   => 2,
            'awaiting_payment' => 3,
            'paid'             => 4,
            default            => 0,
        };
    @endphp

    {{-- No heading row: the breadcrumb already names the bank and links back to
         its statement, and the document below carries its own title. --}}
    <div class="intro-y">
        @if (! $req)
            {{-- The other system is unreachable or does not know this reference.
                 Said plainly, because an accounts screen showing nothing at all
                 reads as "no requisition", which is a different fact. --}}
            <div class="bmo bmo-card bmo-empty">
                <div class="bmo-empty-title">Requisition {{ $reference }} could not be loaded</div>
                <p>
                    It is held in the Operations system, which did not answer. The transaction and its
                    link are unaffected — only this preview is unavailable. Try again shortly.
                </p>
            </div>
        @else
            <div class="bmo">

                {{-- Masthead --}}
                <div class="bmo-card">
                    <div class="bmo-masthead">
                        <div class="bmo-masthead-grid">
                            <div>
                                <div class="bmo-eyebrow">London Churchill College</div>
                                <h1 class="bmo-display">Requisition</h1>
                                <div class="bmo-ref">{{ $req['reference'] }}</div>
                            </div>
                            <div class="bmo-vendor">
                                <div class="bmo-vendor-name">{{ $req['vendor']['name'] ?? '—' }}</div>
                                @foreach (['email', 'phone', 'address'] as $field)
                                    @if (! empty($req['vendor'][$field]))
                                        <div>{{ $req['vendor'][$field] }}</div>
                                    @endif
                                @endforeach
                            </div>
                        </div>

                        <div class="bmo-status-row">
                            <span class="bmo-pill bmo-pill-{{ $req['status'] }}">{{ $req['status_label'] }}</span>
                            @if ($req['is_deleted'])
                                <span class="bmo-pill bmo-pill-rejected">Deleted</span>
                            @endif
                            @if ($req['is_force_completed'])
                                <span class="bmo-pill bmo-pill-awaiting_payment">Force completed</span>
                            @endif
                            <a href="{{ $req['url'] }}" target="_blank" rel="noopener" class="bmo-open">
                                Open in Operations <i data-lucide="external-link" class="w-3.5 h-3.5"></i>
                            </a>
                        </div>

                        <hr class="bmo-rule">

                        <div class="bmo-facts">
                            @foreach ([
                                'Requisitioner'     => $req['requisitioner'],
                                'Budget source'     => $req['budget_line'],
                                'Raised'            => $req['raised_on'] ? date('j M Y', strtotime($req['raised_on'])) : null,
                                'Required by'       => $req['required_by'] ? date('j M Y', strtotime($req['required_by'])) : null,
                                'Year'              => $req['year'],
                                'Delivery location' => $req['venue'],
                            ] as $label => $value)
                                <div>
                                    <div class="bmo-label">{{ $label }}</div>
                                    <div class="bmo-value">{{ $value ?: '—' }}</div>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    {{-- Pipeline --}}
                    <div class="bmo-stepper-wrap">
                        @if ($req['status'] === 'rejected')
                            <div class="bmo-returned">
                                <span class="bmo-returned-mark"><i data-lucide="x" class="w-4 h-4"></i></span>
                                <div>
                                    <div class="bmo-returned-title">Returned to requester</div>
                                    <div class="bmo-returned-sub">The requisitioner can amend and resubmit for approval.</div>
                                </div>
                            </div>
                        @else
                            <div class="bmo-stepper">
                                @foreach ($stages as $stage => $who)
                                    @php
                                        /* Paid ends the pipeline: every step is
                                           finished, none is still in progress. */
                                        $finished = ($req['status'] ?? null) === 'paid' ? count($stages) : $reached - 1;
                                        $i = $loop->index; $done = $i < $finished; $active = $i === $finished;
                                    @endphp
                                    <div class="bmo-step">
                                        <div class="bmo-step-node {{ $done ? 'is-done' : ($active ? 'is-active' : '') }}">
                                            @if ($done)
                                                <i data-lucide="check" class="w-4 h-4"></i>
                                            @else
                                                {{ $i + 1 }}
                                            @endif
                                        </div>
                                        <div class="bmo-step-label {{ $done || $active ? 'is-on' : '' }}">{{ $stage }}</div>
                                        <div class="bmo-step-who">{{ $who }}</div>
                                        @if (! $loop->last)
                                            <span class="bmo-step-line {{ $done ? 'is-done' : '' }}"></span>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>

                    {{-- Items --}}
                    <div class="bmo-section">
                        <table class="bmo-table">
                            <thead>
                                <tr>
                                    <th>Description</th>
                                    <th class="bmo-num" style="width:90px">Qty</th>
                                    <th class="bmo-num" style="width:130px">Unit price</th>
                                    <th class="bmo-num" style="width:130px">Line total</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($req['items'] as $item)
                                    <tr>
                                        <td>{{ $item['description'] }}</td>
                                        <td class="bmo-num">{{ rtrim(rtrim(number_format($item['quantity'], 2), '0'), '.') }}</td>
                                        <td class="bmo-num">{{ $money($item['unit_price']) }}</td>
                                        <td class="bmo-num bmo-strong">{{ $money($item['line_total']) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td colspan="2"></td>
                                    <td class="bmo-num bmo-label">Subtotal</td>
                                    <td class="bmo-num bmo-total">{{ $money($req['total']) }}</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>

                    {{-- Sign-off and decision trail --}}
                    <div class="bmo-section bmo-two">
                        <div>
                            <div class="bmo-heading">Sign-off</div>
                            @foreach ([
                                'First approver'          => $req['first_approver'],
                                'Final approver'          => $req['final_approver'],
                                'Budget holder (payment)' => $req['budget_holder'],
                            ] as $role => $person)
                                <div class="bmo-person">
                                    <div class="bmo-label">{{ $role }}</div>
                                    <div class="bmo-value bmo-strong">{{ $person ?: '—' }}</div>
                                </div>
                            @endforeach
                        </div>

                        <div>
                            <div class="bmo-heading">Approval history</div>
                            @forelse ($req['history'] as $entry)
                                <div class="bmo-event">
                                    <span class="bmo-dot bmo-dot-{{ $entry['decision'] }}"></span>
                                    <div class="bmo-event-body">
                                        <div class="bmo-event-top">
                                            <span class="bmo-strong">{{ $entry['actor'] }}</span>
                                            <span class="bmo-tag bmo-tag-{{ $entry['decision'] }}">{{ ucfirst($entry['decision']) }}</span>
                                        </div>
                                        <div class="bmo-event-meta">
                                            {{ $entry['stage'] }} · {{ $entry['created_at'] ? date('j M Y, g:i A', strtotime($entry['created_at'])) : '' }}
                                        </div>
                                        @if ($entry['note'])
                                            <div class="bmo-event-note">“{{ $entry['note'] }}”</div>
                                        @endif
                                    </div>
                                </div>
                            @empty
                                <div class="bmo-quiet">No decisions recorded yet.</div>
                            @endforelse
                        </div>
                    </div>

                    {{-- Settlement: the reason this page exists --}}
                    <div class="bmo-section bmo-settlement">
                        <div class="bmo-heading">Settlement</div>

                        @if (count($req['transactions']))
                            <table class="bmo-table">
                                <thead>
                                    <tr>
                                        <th style="width:120px">Transaction</th>
                                        <th>Detail</th>
                                        <th style="width:150px">Category</th>
                                        <th style="width:110px">Bank</th>
                                        <th class="bmo-num" style="width:120px">Amount</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($req['transactions'] as $t)
                                        <tr>
                                            <td>
                                                <a href="{{ route('reports.accounts.transaction.connection', $t['sms_id']) }}"
                                                   class="bmo-code-link">{{ $t['code'] }}</a>
                                                <div class="bmo-event-meta">{{ $t['date'] ? date('j M Y', strtotime($t['date'])) : '' }}</div>
                                            </td>
                                            <td>{{ $t['detail'] }}</td>
                                            <td>{{ $t['category'] }}</td>
                                            <td>{{ $t['bank'] }}</td>
                                            <td class="bmo-num bmo-strong">{{ $money($t['amount']) }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>

                            <div class="bmo-reconcile">
                                <span>Requisition {{ $money($req['total']) }}</span>
                                <span>Settled {{ $money($req['settled_total']) }}</span>
                                <span class="{{ abs($req['settlement_variance']) > 0.005 ? 'bmo-variance' : '' }}">
                                    Difference {{ $money($req['settlement_variance']) }}
                                </span>
                            </div>

                            @if ($req['is_force_completed'] && $req['force_reason'])
                                <div class="bmo-force">
                                    <strong>Force completed.</strong> {{ $req['force_reason'] }}
                                </div>
                            @endif
                        @else
                            <div class="bmo-quiet">No transactions linked to this requisition.</div>
                        @endif
                    </div>

                    {{-- Documents (names only; the files stay in Operations) --}}
                    @if (count($req['documents']))
                        <div class="bmo-section bmo-docs">
                            <div class="bmo-heading">Documents</div>
                            <div class="bmo-chips">
                                @foreach ($req['documents'] as $doc)
                                    <span class="bmo-chip">
                                        <i data-lucide="file-text" class="w-3.5 h-3.5"></i>
                                        {{ $doc['name'] }}
                                    </span>
                                @endforeach
                            </div>
                            <div class="bmo-event-meta" style="margin-top:8px">
                                Files are held in Operations — open the requisition there to download them.
                            </div>
                        </div>
                    @endif

                    @if ($req['note'])
                        <div class="bmo-section">
                            <div class="bmo-heading">Note</div>
                            <div class="bmo-value">{{ $req['note'] }}</div>
                        </div>
                    @endif
                </div>
            </div>
        @endif
    </div>

            </div>
        </div>
    </div>

@endsection

@section('script')
    @vite('resources/css/budget-operations.css')
@endsection
