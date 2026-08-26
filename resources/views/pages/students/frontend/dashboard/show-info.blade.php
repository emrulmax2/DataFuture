{{--
    Student identity card — photo, registration number, course, contact
    details and correspondence address, with the "update details" and
    "previous courses" menus.

    Shared by every portal screen that wants the student in context. The
    contact-update modals it points at live in `frontend/modals/index`.
--}}
@include('pages.students.frontend.modals.index')

@php
    $termAddress = (isset($student->contact->term_time_address_id) && $student->contact->term_time_address_id > 0)
        ? $student->contact->termaddress
        : null;

    $addressLine = '';

    if ($termAddress) {
        $addressLine = implode(', ', array_filter([
            $termAddress->address_line_1 ?? null,
            $termAddress->address_line_2 ?? null,
            $termAddress->city ?? null,
            $termAddress->state ?? null,
            $termAddress->post_code ?? null,
            $termAddress->country ?? null,
        ]));
    }
@endphp

<div class="spf-hero">
    <div class="spf-hero__identity">
        <img src="{{ $student->photo_url }}" alt="{{ $student->first_name }} {{ $student->last_name }}" class="spf-hero__photo">
        <div class="spf-hero__text">
            <div class="spf-hero__reg">{{ $student->registration_no }}</div>
            <div class="spf-hero__name">
                <strong>{{ (isset($student->title->name) ? $student->title->name.' ' : '') }}{{ $student->first_name }} {{ $student->last_name }}</strong>
            </div>
            <div class="spf-hero__course">
                {{ isset($student->crel->creation->course->name) ? $student->crel->creation->course->name : 'Course not set' }}
                @if(isset($student->crel->propose->semester->name))
                    <span>&middot; Intake {{ $student->crel->propose->semester->name }}</span>
                @endif
            </div>
        </div>
    </div>

    <div class="spf-hero__aside">
        <div>
            <div class="spf-hero__blockhead">
                <div class="spf-label">Contact details</div>
                <div class="spf-spacer"></div>
                <div class="spf-dd">
                    <button type="button" class="spf-pillbtn" data-spf-dd="spfContactMenu">UPDATE DETAILS &#9662;</button>
                    <div id="spfContactMenu" class="spf-dd__menu" style="width:212px">
                        <a href="javascript:;" data-tw-toggle="modal" data-tw-target="#confirmPersonalEmailUpdateModal" class="spf-dd__item">
                            <i data-lucide="mail" class="w-4 h-4"></i> Change Email
                        </a>
                        <a href="javascript:;" data-tw-toggle="modal" data-tw-target="#confirmPersonalMobileUpdateModal" class="spf-dd__item">
                            <i data-lucide="smartphone" class="w-4 h-4"></i> Change Mobile
                        </a>
                        <a href="javascript:;" data-tw-toggle="modal" data-tw-target="#addressUpdateModal" class="spf-dd__item">
                            <i data-lucide="map-pin" class="w-4 h-4"></i> Change Address
                        </a>
                    </div>
                </div>
            </div>
            <div class="spf-hero__list">
                @if(isset($student->users->email) && $student->users->email)
                    {{ $student->users->email }}<br>
                @endif
                @if(isset($student->contact->personal_email) && $student->contact->personal_email)
                    {{ $student->contact->personal_email }}<br>
                @endif
                @if(isset($student->contact->mobile) && $student->contact->mobile)
                    {{ $student->contact->mobile }}
                @elseif(isset($student->contact->home) && $student->contact->home)
                    {{ $student->contact->home }}
                @endif
            </div>
        </div>

        <div class="spf-hero__block--divided">
            <div class="spf-hero__blockhead">
                <div class="spf-label">Correspondence address</div>
            </div>
            <div class="spf-hero__list">
                @if($addressLine !== '')
                    {{ $addressLine }}
                @else
                    <span class="spf-warn">Not set yet</span>
                @endif
            </div>
        </div>
    </div>
</div>
