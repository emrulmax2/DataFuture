@extends('../layout/my-account')

@section('subhead')
    <title>{{ $title }}- </title>
@endsection

@section('body_class', 'my-account-signature-body')

@section('subcontent')
    @include('pages.users.my-account.show-info')

    @php
        /*
         * Each row on the form: the input name, its label, and the value the
         * HR record would supply. Showing the HR value under the box is what
         * makes "clear it to go back to HR" discoverable.
         */
        $hint = function ($field) use ($defaults) {
            return (isset($defaults[$field]) && filled($defaults[$field])) ? $defaults[$field] : null;
        };
    @endphp

    <section class="myhr-sign" data-screen-label="Email Signature">
        <header class="myhr-sign__header">
            <span class="myhr-sign__header-icon">
                <i data-lucide="pen-line"></i>
            </span>
            <div class="myhr-sign__header-text">
                <h2>Email Signature</h2>
                <p>Built from your HR record. Edit anything below, then copy it into Gmail or Outlook / Hotmail.</p>
            </div>
            <div class="myhr-sign__header-actions">
                <button type="button" id="signatureReset" class="myhr-sign-btn myhr-sign-btn--muted">
                    <i data-lucide="rotate-ccw"></i>
                    Reset to HR record
                </button>
                <button type="submit" form="signatureForm" id="signatureSave" class="myhr-sign-btn myhr-sign-btn--primary">
                    <i data-lucide="save"></i>
                    Save changes
                </button>
            </div>
        </header>

        @unless($fields['images_public'])
            {{-- A signature built against localhost pastes in looking correct
                 and then shows broken icons to every recipient, because mail
                 clients fetch images from their own servers. Say so up front. --}}
            <div class="myhr-sign-alert">
                <i data-lucide="image-off"></i>
                <div>
                    <strong>Images will not appear when you paste this yet.</strong>
                    The crest, icons and your photo are linked from <code>{{ $fields['image_host'] }}</code>, which Gmail and Outlook cannot reach &mdash; they fetch images from their own servers rather than carrying them with the message. Everything else pastes correctly, and the images appear on their own once this site is running on its public address.
                </div>
            </div>
        @endunless

        <div class="myhr-sign__layout">
            <form id="signatureForm" class="myhr-sign__form" autocomplete="off">
                @csrf

                <fieldset class="myhr-sign-group">
                    <legend>Who you are</legend>

                    <label class="myhr-sign-field">
                        <span>Name</span>
                        <input type="text" name="display_name" value="{{ $fields['display_name'] }}" placeholder="{{ $hint('display_name') ?? 'Your name' }}">
                        @if($hint('display_name'))<small>HR record: {{ $hint('display_name') }}</small>@endif
                    </label>

                    <label class="myhr-sign-field">
                        <span>Degree / qualifications</span>
                        <input type="text" name="qualifications" value="{{ $fields['qualifications'] }}" placeholder="e.g. MCSA, MCSE, MCDBA, MSc in Networking">
                        <small>Optional. Shown on its own line under your name.</small>
                    </label>

                    <label class="myhr-sign-field">
                        <span>Designation</span>
                        <input type="text" name="job_title" value="{{ $fields['job_title'] }}" placeholder="{{ $hint('job_title') ?? 'Job title' }}">
                        @if($hint('job_title'))<small>HR record: {{ $hint('job_title') }}</small>@endif
                    </label>

                </fieldset>

                <fieldset class="myhr-sign-group">
                    <legend>How to reach you</legend>

                    <label class="myhr-sign-field">
                        <span>Email address</span>
                        <input type="email" name="email" value="{{ $fields['email'] }}" placeholder="name@lcc.ac.uk">
                        @if($hint('email'))<small>HR record: {{ $hint('email') }}</small>@endif
                    </label>

                    <label class="myhr-sign-field">
                        <span>Extension</span>
                        <input type="text" name="extension" value="{{ $fields['extension'] }}" placeholder="230">
                        <small>Shown after the switchboard number. Leave blank to hide it.</small>
                    </label>

                    <label class="myhr-sign-field">
                        <span>Mobile</span>
                        <input type="text" name="mobile" value="{{ $fields['mobile'] }}" placeholder="{{ $hint('mobile') ?? '07000 000000' }}">
                        <small>Leave blank to keep your mobile out of the signature.@if($hint('mobile')) HR record: {{ $hint('mobile') }}.@endif</small>
                    </label>
                </fieldset>

                <p class="myhr-sign-note">
                    <i data-lucide="info"></i>
                    Clearing your name or designation lets it follow your HR record again. Clearing your extension or mobile leaves it out of the signature.
                </p>
            </form>

            <div class="myhr-sign__preview">
                <div class="myhr-sign-clients" role="tablist">
                    <button type="button" class="myhr-sign-clients__item active" data-client="gmail" role="tab" aria-selected="true">
                        <i data-lucide="mail"></i>
                        Gmail
                    </button>
                    <button type="button" class="myhr-sign-clients__item" data-client="outlook" role="tab" aria-selected="false">
                        <i data-lucide="mail-check"></i>
                        Outlook &amp; Hotmail
                    </button>
                    <span class="myhr-sign-saved" id="signatureSavedAt">
                        @if($fields['has_saved_row'] && $fields['updated_at'])
                            Last saved {{ $fields['updated_at'] }}
                        @endif
                    </span>
                </div>

                <div class="myhr-sign-toolbar">
                    <button type="button" id="signatureCopy" class="myhr-sign-btn myhr-sign-btn--primary">
                        <i data-lucide="clipboard-copy"></i>
                        <span>Copy signature</span>
                    </button>
                    <span class="myhr-sign-toolbar__status" id="signatureStatus" role="status" aria-live="polite"></span>
                </div>

                <div class="myhr-sign-stage">
                    <iframe id="signatureFrame" title="Email signature preview" sandbox="allow-same-origin"></iframe>
                </div>

                <div class="myhr-sign-help" data-help="gmail">
                    <h3><i data-lucide="list-ordered"></i> Add this to Gmail</h3>
                    <ol>
                        <li>Press <strong>Copy signature</strong> above.</li>
                        <li>In Gmail, open <strong>Settings</strong> (gear icon) &rarr; <strong>See all settings</strong>.</li>
                        <li>On the <strong>General</strong> tab scroll to <strong>Signature</strong> and choose <strong>Create new</strong>.</li>
                        <li>Click inside the signature box and paste with <strong>Ctrl&nbsp;+&nbsp;V</strong> (<strong>Cmd&nbsp;+&nbsp;V</strong> on Mac).</li>
                        <li>Set it as the default for new emails and replies, then <strong>Save Changes</strong> at the bottom.</li>
                    </ol>
                </div>

                <div class="myhr-sign-help" data-help="outlook" hidden>
                    <h3><i data-lucide="list-ordered"></i> Add this to Outlook or Hotmail</h3>
                    <ol>
                        <li>Press <strong>Copy signature</strong> above.</li>
                        <li><strong>Outlook on the web / Hotmail:</strong> <strong>Settings</strong> &rarr; <strong>Mail</strong> &rarr; <strong>Compose and reply</strong>, then paste into the signature box and <strong>Save</strong>.</li>
                        <li><strong>Outlook for Windows:</strong> <strong>File</strong> &rarr; <strong>Options</strong> &rarr; <strong>Mail</strong> &rarr; <strong>Signatures</strong>, create a signature, paste into the edit box and click <strong>OK</strong>.</li>
                        <li>Choose the signature under <strong>New messages</strong> and <strong>Replies/forwards</strong>.</li>
                    </ol>
                    <p class="myhr-sign-help__aside">
                        <i data-lucide="triangle-alert"></i>
                        Outlook for Windows draws email with Word, so it ignores rounded corners. This version is laid out to survive that &mdash; use it rather than the Gmail one in any Outlook or Hotmail account.
                    </p>
                </div>
            </div>
        </div>
    </section>

    {{--
        Off-screen copies of the real markup. The clipboard needs live DOM to
        produce rich HTML (not escaped text), and the iframe preview is built
        from the same nodes so what is copied is exactly what is shown.
    --}}
    <div id="signatureSource" aria-hidden="true" class="myhr-sign-source">
        <div data-variant="gmail">{!! $markup['gmail'] !!}</div>
        <div data-variant="outlook">{!! $markup['outlook'] !!}</div>
    </div>

    <!-- BEGIN: Success Modal Content -->
    <div id="successModal" class="modal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-body p-0">
                    <div class="p-5 text-center">
                        <i data-lucide="check-circle" class="w-16 h-16 text-success mx-auto mt-3"></i>
                        <div class="text-3xl mt-5 successModalTitle"></div>
                        <div class="text-slate-500 mt-2 successModalDesc"></div>
                    </div>
                    <div class="px-5 pb-8 text-center">
                        <button type="button" data-action="DISMISS" class="successCloser btn btn-primary w-24">Ok</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- END: Success Modal Content -->
@endsection

@section('script')
    @vite('resources/js/user-email-signature.js')
@endsection
