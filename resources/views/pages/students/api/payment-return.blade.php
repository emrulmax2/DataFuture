{{-- Shown in the student app's in-app browser after the card payment page,
     when the app did not say where to send the student back to. --}}
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex">
    <title>Document request payment - London Churchill College</title>
    <style>
        body { margin: 0; font-family: Arial, Helvetica, sans-serif; background: #f4f5f7; color: #1c2a4a; }
        .box { max-width: 420px; margin: 18vh auto 0; padding: 28px 24px; background: #fff; border-radius: 12px; text-align: center; }
        h1 { margin: 0 0 10px; font-size: 20px; }
        p { margin: 0; font-size: 15px; line-height: 1.5; color: #55607a; }
    </style>
</head>
<body>
    <div class="box">
        @if($status == 'succeeded')
            <h1>Payment received</h1>
            <p>Thank you. You can close this page and return to the app.</p>
        @elseif($status == 'pending')
            <h1>Payment is being confirmed</h1>
            <p>You can close this page and return to the app. Your order will update shortly.</p>
        @elseif($status == 'unknown')
            <h1>Payment not found</h1>
            <p>We could not match this page to a payment. Return to the app and check My orders.</p>
        @else
            <h1>Payment not completed</h1>
            <p>Nothing has been charged. You can close this page and try again from the app.</p>
        @endif
    </div>
</body>
</html>
