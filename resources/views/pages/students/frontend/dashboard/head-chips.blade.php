{{--
    Page-header chips: enrolment status and the switcher for the student's
    other enrolments (an earlier or later intake of the same person).

    These belong to the page header rather than the profile card, so every
    screen that shows the profile card includes this in its `.spf-page-head`.
--}}
@php
    $relatedEnrolments = collect();

    if (isset($student->children) && count($student->children) > 0) {
        $relatedEnrolments = isset($student->descendants) ? collect($student->descendants) : collect($student->children);
    } elseif (isset($student->parent) && is_object($student->parent) && $student->ancestors->count()) {
        $relatedEnrolments = collect($student->ancestors);
    }
@endphp

@if(isset($student->status->name))
    <span class="spf-chip spf-chip--cream">{{ strtoupper($student->status->name) }}</span>
@endif

{{-- Nothing to switch to means nothing to show: the chip only appears for a
     student who actually has another enrolment. --}}
@if($relatedEnrolments->count() > 0)
    <div class="spf-dd">
        <button type="button" class="spf-pillbtn" data-spf-dd="spfCoursesMenu">PREVIOUS COURSES &#9662;</button>
        <div id="spfCoursesMenu" class="spf-dd__menu spf-dd__menu--wide">
            @foreach($relatedEnrolments as $enrolment)
                <a href="{{ route('students.dashboard.student.select', $enrolment->id) }}" class="spf-dd__item">
                    <i data-lucide="user" class="w-4 h-4"></i>
                    View {{ isset($enrolment->course->semester->name) ? $enrolment->course->semester->name : 'enrolment' }}
                </a>
            @endforeach
        </div>
    </div>
@endif
