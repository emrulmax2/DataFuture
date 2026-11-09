{{--
    The college logo for the verification emails, as a picture attached to the
    message (resources/images/email-logo.png, drawn from the old artwork).

    It used to be the logo itself as inline SVG: 180 KB of paths. Gmail does not
    draw inline SVG, and those 180 KB pushed every email that included it past
    the 102 KB Gmail shows before "[Message clipped]" - so the message opened
    blank, with the verify button behind the clip. An attached image is not
    counted towards that limit.

    $message is the mail being built, which Laravel hands to every mail view.
    Outside a mail (a preview in the browser) the hosted copy is used instead.
--}}
@php($emailLogoFile = resource_path('images/email-logo.png'))
<img src="{{ isset($message) && is_file($emailLogoFile) ? $message->embed($emailLogoFile) : asset('build/assets/images/email-logo.png') }}"
     alt="London Churchill College" width="190" height="70"
     style="display: block; width: 190px; max-width: 190px; height: auto; border: 0; outline: none; text-decoration: none;">
