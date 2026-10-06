{{--
    Policy Assessments: a level badge — a rosette medallion in the level's colour
    (Beginner bronze, Intermediate silver, Expert gold) carrying 1 / 2 / 3 stars, so the
    level never rests on colour alone — with an optional caption underneath.
    Same markup as badgeSvg() in resources/js/policy-assessment/levels.js; styles in
    resources/css/policy-assessment/levels.css.

    @include('pages.hr.policy-assessment.partials.level-badge', [
        'level' => $badge->level,         // beginner | intermediate | expert (anything else reads as beginner)
        'size' => 'md',                   // sm (28px) | md (48px) | lg (88px)
        'title' => $badge->policy->title, // policy title: caption + aria-label. null = the medal alone
        'date' => $badge->awarded_at,     // optional caption line: a string, or a date shown as d M Y
        'revoked' => false,               // optional: drawn greyed out
    ])

    Accessible name: "Intermediate badge — <policy title>" (role="img").

    @include passes the parent view's variables through, and most pages set $title
    (the page title) — so always pass 'title' and 'date', null when not wanted.
--}}
@php
    $paBadgeLevel = (isset($level) && is_string($level) && \App\Support\PolicyLevel::isValid($level) ? $level : \App\Support\PolicyLevel::BEGINNER);
    $paBadgeSize = (isset($size) && in_array($size, ['sm', 'md', 'lg'], true) ? $size : 'md');
    $paBadgeTitle = (isset($title) && is_scalar($title) && trim((string) $title) !== '' ? trim((string) $title) : null);
    $paBadgeDate = null;
    if(isset($date) && $date instanceof \DateTimeInterface):
        $paBadgeDate = $date->format('d M Y');
    elseif(isset($date) && is_scalar($date) && trim((string) $date) !== ''):
        $paBadgeDate = trim((string) $date);
    endif;
    $paBadgeRevoked = (isset($revoked) && $revoked);
    $paBadgeLabel = \App\Support\PolicyLevel::label($paBadgeLevel);
    $paBadgeAria = $paBadgeLabel.' badge'.($paBadgeTitle !== null ? ' — '.$paBadgeTitle : '');
    $paBadgeColours = \App\Support\PolicyLevel::colours($paBadgeLevel);
    $paBadgeDims = ['sm' => [28, 33], 'md' => [48, 56], 'lg' => [88, 103]][$paBadgeSize];
    /* Star polygons in the 48 x 56 viewBox — keep in step with STARS in levels.js. */
    $paBadgeStars = [
        'beginner' => [
            '24,15 25.69,19.07 30.09,19.42 26.74,22.29 27.76,26.58 24,24.28 20.24,26.58 21.26,22.29 17.91,19.42 22.31,19.07',
        ],
        'intermediate' => [
            '19.1,16.9 20.34,19.89 23.57,20.15 21.11,22.25 21.86,25.4 19.1,23.72 16.34,25.4 17.09,22.25 14.63,20.15 17.86,19.89',
            '28.9,16.9 30.14,19.89 33.37,20.15 30.91,22.25 31.66,25.4 28.9,23.72 26.14,25.4 26.89,22.25 24.43,20.15 27.66,19.89',
        ],
        'expert' => [
            '16.5,18.7 17.58,21.31 20.4,21.53 18.25,23.37 18.91,26.12 16.5,24.65 14.09,26.12 14.75,23.37 12.6,21.53 15.42,21.31',
            '24,15.3 25.08,17.91 27.9,18.13 25.75,19.97 26.41,22.72 24,21.25 21.59,22.72 22.25,19.97 20.1,18.13 22.92,17.91',
            '31.5,18.7 32.58,21.31 35.4,21.53 33.25,23.37 33.91,26.12 31.5,24.65 29.09,26.12 29.75,23.37 27.6,21.53 30.42,21.31',
        ],
    ][$paBadgeLevel];
    $paBadgeCaption = ($paBadgeTitle !== null || $paBadgeDate !== null);
@endphp
@if($paBadgeCaption)
<span class="pa-badge-card pa-badge-card--{{ $paBadgeSize }} pa-badge-card--{{ $paBadgeLevel }}{{ $paBadgeRevoked ? ' is-revoked' : '' }}">
@endif
<span class="pa-badge-medal pa-badge-medal--{{ $paBadgeSize }} pa-badge-medal--{{ $paBadgeLevel }}{{ $paBadgeRevoked ? ' is-revoked' : '' }}" role="img" aria-label="{{ $paBadgeAria }}" title="{{ $paBadgeAria }}">
<svg class="pa-badge-medal__svg" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 48 56" width="{{ $paBadgeDims[0] }}" height="{{ $paBadgeDims[1] }}" aria-hidden="true" focusable="false">
<polygon points="15,33 10,53 14.2,50.2 18.5,55.5 23,36" fill="{{ $paBadgeColours['ink'] }}"/>
<polygon points="33,33 38,53 33.8,50.2 29.5,55.5 25,36" fill="{{ $paBadgeColours['ink'] }}"/>
<polygon points="24,0.5 27.16,3.08 31.01,1.74 33.1,5.24 37.18,5.3 37.94,9.3 41.75,10.75 41.1,14.78 44.19,17.44 42.2,21 44.19,24.56 41.1,27.22 41.75,31.25 37.94,32.7 37.18,36.7 33.1,36.76 31.01,40.26 27.16,38.92 24,41.5 20.84,38.92 16.99,40.26 14.9,36.76 10.82,36.7 10.06,32.7 6.25,31.25 6.9,27.22 3.81,24.56 5.8,21 3.81,17.44 6.9,14.78 6.25,10.75 10.06,9.3 10.82,5.3 14.9,5.24 16.99,1.74 20.84,3.08" fill="{{ $paBadgeColours['main'] }}"/>
<path d="M8.12 15.22 A16.9 16.9 0 0 1 28.37 4.68" fill="none" stroke="#ffffff" stroke-opacity="0.5" stroke-width="1.6" stroke-linecap="round"/>
<circle cx="24" cy="21" r="15" fill="{{ $paBadgeColours['soft'] }}" stroke="{{ $paBadgeColours['ink'] }}" stroke-opacity="0.3" stroke-width="1"/>
<circle cx="24" cy="21" r="12.6" fill="none" stroke="{{ $paBadgeColours['main'] }}" stroke-opacity="0.6" stroke-width="0.8"/>
@foreach($paBadgeStars as $paBadgeStar)
<polygon points="{{ $paBadgeStar }}" fill="{{ $paBadgeColours['ink'] }}"/>
@endforeach
</svg>
</span>
@if($paBadgeCaption)
<span class="pa-badge-card__caption">
<span class="pa-badge-card__level">{{ $paBadgeLabel }} badge</span>
@if($paBadgeTitle !== null)
<span class="pa-badge-card__title">{{ $paBadgeTitle }}</span>
@endif
@if($paBadgeDate !== null)
<span class="pa-badge-card__date">{{ $paBadgeDate }}</span>
@endif
</span>
</span>
@endif
