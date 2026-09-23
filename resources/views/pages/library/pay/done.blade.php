@extends('../layout/noauth')

@section('head')
    <title>{{ $title }}</title>
@endsection

@section('styles')
    @vite('resources/css/library-pay.css')
@endsection

@section('content')
    <div class="libpay">
        <div class="libpay__card">

            <div class="libpay__head">
                <div class="libpay__brand">London Churchill College</div>
                <div class="libpay__dept">Library</div>
            </div>

            @if($error)
                <div class="libpay__body libpay__body--centred">
                    <div class="libpay__mark libpay__mark--warn">!</div>
                    <h1 class="libpay__title">We could not match that payment</h1>
                    <p class="libpay__lead">{{ $error }}</p>
                </div>
            @else
                <div class="libpay__body libpay__body--centred">
                    <div class="libpay__mark libpay__mark--ok">&check;</div>
                    <h1 class="libpay__title">Payment received</h1>
                    <p class="libpay__lead">
                        £{{ number_format($deposit->amount, 2) }} paid. Your return is now complete
                        @if($issue)
                            — {{ $issue->title }} is off your record —
                        @endif
                        and you can borrow again straight away.
                    </p>

                    {{-- A receipt, because this is the only record the payer
                         gets: the confirmation email goes to the PayPal
                         account, which is often not the student's. --}}
                    <dl class="libpay__receipt">
                        @if($issue)
                            <div><dt>Loan</dt><dd>{{ $issue->reference }}</dd></div>
                        @endif
                        <div><dt>Paid</dt><dd>{{ optional($deposit->paid_at)->format('j M Y, g:ia') }}</dd></div>
                        @if($deposit->provider_capture_id)
                            <div><dt>PayPal reference</dt><dd>{{ $deposit->provider_capture_id }}</dd></div>
                        @endif
                    </dl>
                </div>
            @endif

            <div class="libpay__foot">
                Keep this page for your records, or take a screenshot. Any questions,
                please speak to the library desk.
            </div>

        </div>
    </div>
@endsection
