@extends('../layout/' . $layout)

@section('subhead')
    <title>{{ $title }}</title>
@endsection

@section('styles')
    @vite('resources/css/library-management.css')
@endsection

@section('subcontent')
    @php
        $tiles = [
            ['label' => 'Deposits held', 'value' => '£'.number_format($totals['held'], 2),
             'icon' => 'wallet', 'tone' => 'green'],
            ['label' => 'Fines outstanding', 'value' => '£'.number_format($totals['outstanding'], 2),
             'icon' => 'alert-triangle', 'tone' => 'red'],
            ['label' => 'Fines collected', 'value' => '£'.number_format($totals['collected'], 2),
             'icon' => 'receipt', 'tone' => 'indigo'],
            ['label' => 'Deposits refunded', 'value' => '£'.number_format($totals['refunded'], 2),
             'icon' => 'undo-2', 'tone' => 'teal'],
        ];

        $filters = [
            'all' => ['Everything', 'layers'],
            'deposits' => ['Deposits', 'wallet'],
            'fines' => ['Fines', 'receipt'],
        ];

        /* Blank leads and means "everything that is money" — a pending row is
           a checkout the student walked away from, so it is reachable by name
           rather than mixed into the default view. */
        $statuses = [
            '' => 'All',
            'paid' => 'Paid',
            'outstanding' => 'Outstanding',
            'refunded' => 'Refunded',
            'failed' => 'Failed',
            'pending' => 'Abandoned',
        ];

        /* One pill per state. `outstanding` is the only one that is not a
           stored status — it is a charge with no payment row yet. */
        $statusPill = [
            'paid' => ['Paid', 'lib-status--done'],
            'pending' => ['Pending', 'lib-status--wait'],
            'refunded' => ['Refunded', 'lib-status--void'],
            'failed' => ['Failed', 'lib-status--void'],
            'outstanding' => ['Outstanding', 'lib-status--late'],
        ];
    @endphp

    <div class="lib-desk">
        <div class="lib-stats">
            @foreach($tiles as $tile)
                <span class="lib-stat lib-stat--{{ $tile['tone'] }}">
                    <span class="lib-stat__top">
                        <span class="lib-stat__label">{{ $tile['label'] }}</span>
                        <span class="lib-stat__icon" aria-hidden="true"><i data-lucide="{{ $tile['icon'] }}"></i></span>
                    </span>
                    <span class="lib-stat__value">{{ $tile['value'] }}</span>
                </span>
            @endforeach

            {{-- The grid is five columns wide, so four figures left a dead
                 cell. Filling it with the way back keeps the row complete and
                 puts the only navigation this page needs where the eye already
                 is. --}}
            <a href="{{ route('library.management') }}" class="lib-stat lib-stat--back">
                <span class="lib-stat__top">
                    <span class="lib-stat__label">Library management</span>
                    <span class="lib-stat__icon" aria-hidden="true"><i data-lucide="arrow-left"></i></span>
                </span>
                <span class="lib-stat__value lib-stat__value--back">Back to the desk</span>
                <span class="lib-stat__foot">
                    Issue &amp; return
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M7 17L17 7M7 7h10v10"></path></svg>
                </span>
            </a>
        </div>

        <div class="lib-card">
            <div class="lib-cardhead lib-cardhead--divided">
                <span class="lib-cardhead__titles">
                    <span class="lib-cardhead__title">Deposits &amp; fines</span>
                    <span class="lib-cardhead__sub">
                        {{ $rows->count() }} {{ $rows->count() == 1 ? 'record' : 'records' }}
                        @if($status === '' && $abandoned > 0)
                            &middot; {{ $abandoned }} abandoned {{ $abandoned == 1 ? 'checkout' : 'checkouts' }} hidden
                        @endif
                    </span>
                </span>

                <span class="lib-cardhead__spacer"></span>

                <form method="get" action="{{ route('library.management.money') }}" class="lib-headsearch">
                    <input type="hidden" name="show" value="{{ $filter }}">

                    <label class="lib-money__statuslabel" for="moneyStatus">Status</label>
                    <select id="moneyStatus" name="status" class="lib-input lib-money__status"
                            onchange="this.form.submit()">
                        @foreach($statuses as $key => $label)
                            <option value="{{ $key }}" @selected($status === $key)>{{ $label }}</option>
                        @endforeach
                    </select>
                    <span class="lib-headsearch__box">
                        <i data-lucide="search"></i>
                        <input type="text" name="q" class="lib-input" value="{{ $search }}"
                               placeholder="Student, reference or reason..." autocomplete="off">
                    </span>
                    @if($search !== '')
                        <a href="{{ route('library.management.money', ['show' => $filter, 'status' => $status]) }}" class="lib-headsearch__clear">Reset</a>
                    @endif
                </form>
            </div>

            <div class="lib-segs">
                @foreach($filters as $key => [$label, $icon])
                    <a href="{{ route('library.management.money', ['show' => $key, 'q' => $search, 'status' => $status]) }}"
                       class="lib-seg__item {{ $filter === $key ? 'lib-seg__item--on' : '' }}">
                        <i data-lucide="{{ $icon }}"></i>{{ $label }}
                    </a>
                @endforeach
            </div>

            <div class="lib-tabulator-wrap">
                <table class="lib-money">
                    <thead>
                        <tr>
                            <th>Student</th>
                            <th>Type</th>
                            <th>Detail</th>
                            <th>Reference</th>
                            <th>Date</th>
                            <th>Status</th>
                            <th class="lib-money__right">Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($rows as $row)
                            @php
                                $student = $row['student'];
                                $name = trim(($student->first_name ?? '').' '.($student->last_name ?? '')) ?: 'Unknown student';
                                [$pillText, $pillClass] = $statusPill[$row['status']] ?? [ucfirst($row['status']), 'lib-status--void'];
                            @endphp
                            <tr>
                                <td>
                                    <div class="lib-media__title">{{ $name }}</div>
                                    <div class="lib-media__sub">{{ $student->registration_no ?? '—' }}</div>
                                </td>
                                <td>
                                    <span class="lib-badge {{ $row['kind'] === 'fine' ? 'lib-b--red' : 'lib-b--gold' }}">
                                        {{ $row['kind'] === 'fine' ? 'Fine' : 'Deposit' }}
                                    </span>
                                </td>
                                <td class="lib-money__detail">{{ $row['detail'] }}</td>
                                <td class="lib-media__sub">{{ $row['reference'] ?: '—' }}</td>
                                <td class="lib-media__sub">
                                    {{ $row['date'] ? \Carbon\Carbon::parse($row['date'])->format('j M Y') : '—' }}
                                </td>
                                <td><span class="lib-status {{ $pillClass }}">{{ $pillText }}</span></td>
                                {{-- Outstanding money is the only figure the desk
                                     has to act on, so it is the only one coloured. --}}
                                <td class="lib-money__right {{ $row['outstanding'] ? 'lib-fine' : '' }}">
                                    £{{ number_format($row['amount'], 2) }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="lib-results__empty">
                                    <i data-lucide="wallet"></i>
                                    No deposits or fines to show.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
