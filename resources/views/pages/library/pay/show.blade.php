@extends('../layout/noauth')

@section('head')
    <title>{{ $title }}</title>
@endsection

@section('styles')
    @vite('resources/css/library-pay.css')
@endsection

@section('content')
    {{-- Opened from a link on a phone, by a student or whoever is paying for
         them. One column, one figure, one button: there is nothing to browse
         here and nowhere else to go. --}}
    <div class="libpay">
        <div class="libpay__card">

            <div class="libpay__head">
                <div class="libpay__brand">London Churchill College</div>
                <div class="libpay__dept">Library</div>
            </div>

            @if($settled)
                {{-- Paid at the desk, or paid here and the page reopened. Said
                     plainly, because the alternative is someone paying twice. --}}
                <div class="libpay__body libpay__body--centred">
                    <div class="libpay__mark libpay__mark--ok">&check;</div>
                    <h1 class="libpay__title">This charge is settled</h1>
                    <p class="libpay__lead">
                        Nothing is outstanding on
                        {{ $issue->reference ?? 'this loan' }}, and your return is complete.
                        There is nothing more to do.
                    </p>
                </div>

            @elseif($lapsed)
                <div class="libpay__body libpay__body--centred">
                    <div class="libpay__mark libpay__mark--warn">!</div>
                    <h1 class="libpay__title">This link has expired</h1>
                    <p class="libpay__lead">
                        A payment link is only good until midnight on the day it was issued.
                        The book still counts as out on loan and the charge has continued
                        to build, so please see the library desk — they will take the
                        return again and send you a fresh link.
                    </p>
                </div>

            @else
                <div class="libpay__body">
                    <div class="libpay__eyebrow">Overdue charge</div>
                    <h1 class="libpay__amount">£{{ number_format($deposit->amount, 2) }}</h1>

                    @if($issue)
                        <div class="libpay__book">{{ $issue->title }}</div>
                        <div class="libpay__meta">
                            {{ $issue->reference }}
                            @if($issue->due_at) &middot; was due {{ $issue->due_at->format('j M Y') }} @endif
                        </div>
                    @endif

                    {{-- The deadline is the reason this page exists, so it sits
                         above the button rather than in the small print. --}}
                    <div class="libpay__deadline">
                        <strong>Pay by {{ optional($deposit->expires_at)->format('g:ia \o\n j M Y') }}.</strong>
                        The library desk is holding your return open until then. If the charge
                        is not paid today, another day is added and you will need to see the
                        desk again.
                    </div>

                    @if($error)
                        <div class="libpay__error" role="alert">{{ $error }}</div>
                    @endif

                    {{-- The signed pay URL, not the current one: this page is
                         also shown after a cancelled or failed payment, where
                         the address bar holds PayPal's return route. --}}
                    <form method="post" action="{{ $payUrl }}" class="libpay__form">
                        @csrf
                        <button type="submit" class="libpay__go">
                            Pay £{{ number_format($deposit->amount, 2) }} with PayPal
                        </button>
                    </form>

                    <p class="libpay__note">
                        You can pay by card without a PayPal account. Your return completes
                        as soon as the payment goes through.
                    </p>
                </div>
            @endif

            <div class="libpay__foot">
                Questions about this charge? Please speak to the library desk.
            </div>

        </div>
    </div>
@endsection

@section('script')
    <script>
        /* The button leaves for PayPal, which takes a moment on a phone. Left
           live it gets pressed twice, and that is two orders for one charge. */
        document.querySelector('.libpay__form')?.addEventListener('submit', function () {
            const go = this.querySelector('.libpay__go');
            go.disabled = true;
            go.textContent = 'Taking you to PayPal…';
        });
    </script>
@endsection
