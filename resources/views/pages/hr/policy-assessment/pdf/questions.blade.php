{{--
    Policy Assessments: one policy's question bank as a PDF handout (HR only — it is the answer key).
    Rendered by PolicyQuestionController@pdf with dompdf, so the layout is tables, blocks and
    inline-blocks only (no flexbox / grid). A card is the on-screen review card
    (cardFormatter() in resources/js/policy-assessment-questions.js, styles in
    resources/css/policy-assessment/admin-bank.css) without its controls; keep the colours in step.

    $policy       PolicyDocument (category loaded)
    $footerTitle  the policy title, already cut to fit the footer on one line (pdfFooterTitle())
    $sections    [[level, label, cards, count, active, first, last], ...] easiest first; a card is row()'s array
    $total / $active / $drafts   questions in this export
    $filterLabel  "All questions", "Expert only", "Expert · Active only", ...
    $generatedAt / $generatedBy

    "Page X of Y" is written onto the finished pages by the controller (pdfPageNumbers()).
--}}
@php
    $paFont = function ($file) {
        return str_replace('\\', '/', resource_path('fonts/plus-jakarta-sans/'.$file));
    };

    /* Lucide icons (the ones the screen uses) as SVG data URIs: nothing is fetched. */
    $paSvg = function ($inner, $colour, $stroke = 2.2) {
        return 'data:image/svg+xml;base64,'.base64_encode('<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"><g fill="none" stroke="'.$colour.'" stroke-width="'.$stroke.'" stroke-linecap="round" stroke-linejoin="round">'.$inner.'</g></svg>');
    };
    $paIconActive = $paSvg('<path d="M12 22c5.523 0 10-4.477 10-10S17.523 2 12 2 2 6.477 2 12s4.477 10 10 10z"/><path d="m9 12 2 2 4-4"/>', '#0f8278');
    $paIconIssue = $paSvg('<path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3Z"/><path d="M12 9v4"/><path d="M12 17h.01"/>', '#c23b3b');
    $paIconTick = $paSvg('<polyline points="20 6 9 17 4 12"/>', '#0f8278', 2.9);
    $paIconWhy = $paSvg('<path d="M15 14c.2-1 .7-1.7 1.5-2.5 1-.9 1.5-2.2 1.5-3.5A6 6 0 0 0 6 8c0 1 .2 2.2 1.5 3.5.7.7 1.3 1.5 1.5 2.5"/><path d="M9 18h6"/><path d="M10 22h4"/>', '#8f5f00', 2);
    $paIconLock = $paSvg('<rect width="18" height="11" x="3" y="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/>', '#b3261e', 2.3);

    /* A colour laid over another at the given strength — the pills' rgba() borders, made solid. */
    $paBlend = function ($hex, $over, $alpha) {
        $out = '#';
        foreach([1, 3, 5] as $at):
            $out .= str_pad(dechex((int) round(hexdec(substr($hex, $at, 2)) * $alpha + hexdec(substr($over, $at, 2)) * (1 - $alpha))), 2, '0', STR_PAD_LEFT);
        endforeach;

        return $out;
    };
    $paLevels = [];
    foreach(\App\Support\PolicyLevel::all() as $paKey):
        $paColours = \App\Support\PolicyLevel::colours($paKey);
        $paLevels[$paKey] = $paColours + ['edge' => $paBlend($paColours['main'], $paColours['soft'], 0.4)];
    endforeach;

    /*
     * Characters each face is trusted to draw, as the inside of a regex class. Every
     * list is a part of what the font really has, never more (the kit's pdf_test.php
     * checks them against the font metrics): 'sans' is Plus Jakarta Sans in all four
     * weights, 'serif' DejaVu Serif Italic (the excerpt), 'fallback' DejaVu Sans.
     */
    $paGlyphs = [
        'sans' => '\x{0020}-\x{007E}\x{00A0}-\x{00AC}\x{00AE}-\x{0148}\x{014A}-\x{017E}\x{2013}\x{2014}\x{2018}-\x{201A}\x{201C}-\x{201E}\x{2020}-\x{2022}\x{2026}\x{2030}\x{2032}\x{2033}\x{2039}\x{203A}\x{20AC}\x{2122}\x{2190}-\x{2199}\x{2212}\x{2248}\x{2260}\x{2264}\x{2265}',
        'serif' => '\x{0020}-\x{007E}\x{00A0}-\x{02CD}\x{0300}-\x{033F}\x{0384}-\x{038A}\x{038C}\x{038E}-\x{03A1}\x{03A3}-\x{03E1}\x{03F0}-\x{045F}\x{1E00}-\x{1EFB}\x{2000}-\x{2026}\x{202F}-\x{203A}\x{2070}\x{2071}\x{2074}-\x{208E}\x{20AC}\x{2116}\x{2122}\x{2150}-\x{2185}\x{2190}-\x{21FF}\x{2212}\x{2248}\x{2260}\x{2264}\x{2265}\x{2500}-\x{2600}\x{FB00}-\x{FB06}\x{FFFD}',
        'fallback' => '\x{0020}-\x{007E}\x{00A0}-\x{02E9}\x{0300}-\x{034F}\x{0384}-\x{038A}\x{038C}\x{038E}-\x{03A1}\x{03A3}-\x{0525}\x{1E00}-\x{1EFB}\x{2000}-\x{2064}\x{2070}\x{2071}\x{2074}-\x{208E}\x{2090}-\x{209C}\x{20A0}-\x{20B5}\x{20B8}-\x{20BA}\x{20BD}\x{2100}-\x{2109}\x{210B}-\x{2149}\x{2150}-\x{2185}\x{2190}-\x{2311}\x{2460}-\x{2469}\x{2500}-\x{269C}\x{269E}-\x{26B8}\x{2701}-\x{2704}\x{2706}-\x{2709}\x{270C}-\x{2727}\x{2729}-\x{274B}\x{274D}\x{274F}-\x{2752}\x{2756}\x{2758}-\x{275E}\x{2761}-\x{2794}\x{2798}-\x{27AF}\x{27B1}-\x{27BE}\x{2B00}-\x{2B1A}\x{FB00}-\x{FB06}\x{FFFD}',
    ];

    /*
     * HR-entered text, escaped, with every character given to a font that can draw it.
     * dompdf gives a character its font lacks no width: it prints as a box on top of
     * the next letter and the line is measured too short, so it runs off the page.
     *   - line and paragraph separators become line breaks; invisible characters
     *     (soft hyphens, zero-width marks, control codes) are dropped;
     *   - Word's private-use bullets (Symbol / Wingdings) become a bullet;
     *   - a run no bundled font can draw (emoji, other scripts) becomes one U+FFFD;
     *   - what the face lacks and DejaVu Sans has (ticks, shapes) goes in a .fb span.
     * Fallback runs that only white space separates share one span: the controller
     * drops the white space between tags, so a line break has to sit inside the span.
     * $face is 'sans', or 'serif' for the policy excerpt.
     */
    $paText = function ($text, $face = 'sans') use ($paGlyphs) {
        $text = (string) $text;
        $clean = preg_replace(
            ['/[\x{2028}\x{2029}]/u', '/(?![\t\n\r])[\p{Cc}\p{Cf}]/u', '/[\x{E000}-\x{F8FF}]/u', '/[^\t\n\r'.$paGlyphs[$face].$paGlyphs['fallback'].']+/u'],
            ["\n", '', "\u{2022}", "\u{FFFD}"],
            $text
        );
        $html = e($clean === null ? $text : $clean);
        $other = '[^\t\n\r'.$paGlyphs[$face].']+';
        $withFallback = preg_replace('/'.$other.'(?:[ \t\r\n]+'.$other.')*/u', '<span class="fb">$0</span>', $html);

        return new \Illuminate\Support\HtmlString($withFallback === null ? $html : $withFallback);
    };

    $paCategory = (isset($policy->category->name) ? $policy->category->name : 'No category');
    if(isset($policy->category) && $policy->category->trashed()):
        $paCategory .= ' (archived)';
    endif;
    $paVersion = trim((string) $policy->version);
    if($paVersion !== '' && preg_match('/^\d/', $paVersion)):
        $paVersion = 'Version '.$paVersion;
    endif;
@endphp
<!doctype html>
<html lang="en-GB">
<head>
    <meta charset="utf-8">
    <title>{{ $policy->title }} — Question bank</title>
    <style>
        @font-face {
            font-family: 'Plus Jakarta Sans';
            font-style: normal;
            font-weight: 400;
            src: url("{{ $paFont('PlusJakartaSans-Regular.ttf') }}") format('truetype');
        }

        @font-face {
            font-family: 'Plus Jakarta Sans';
            font-style: normal;
            font-weight: 600;
            src: url("{{ $paFont('PlusJakartaSans-SemiBold.ttf') }}") format('truetype');
        }

        @font-face {
            font-family: 'Plus Jakarta Sans';
            font-style: normal;
            font-weight: 700;
            src: url("{{ $paFont('PlusJakartaSans-Bold.ttf') }}") format('truetype');
        }

        @font-face {
            font-family: 'Plus Jakarta Sans';
            font-style: normal;
            font-weight: 800;
            src: url("{{ $paFont('PlusJakartaSans-ExtraBold.ttf') }}") format('truetype');
        }

        /* Keep the bottom margin and .foot in step with pdfPageNumbers() in the controller. */
        @page {
            margin: 11mm 11mm 17mm;
        }

        /*
         * Line heights: dompdf makes a line  line-height x the font's own height
         * (ascender to descender) x 1.1 — 1.39 x the font size for Plus Jakarta
         * Sans, 1.28 x for DejaVu. So "1" is already a comfortable line, and every
         * value below is that much smaller than the on-screen one it stands for.
         */
        body {
            margin: 0;
            background: #ffffff;
            color: #16264f;
            font-family: "Plus Jakarta Sans", "DejaVu Sans", sans-serif;
            font-size: 9pt;
            font-weight: 400;
            line-height: 1;
            /* Plus Jakarta's own space is 0.17em: right on screen, cramped at 8pt on paper. */
            word-spacing: 0.55pt;
        }

        table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
        }

        td {
            padding: 0;
            vertical-align: top;
        }

        img {
            border: 0;
        }

        /* Characters Plus Jakarta Sans cannot draw. Always the regular weight: DejaVu Sans has no 600 or 800. */
        .fb {
            font-family: "DejaVu Sans", sans-serif;
            font-style: normal;
            font-weight: normal;
            word-spacing: 0;
        }

        /* ------------------------------------------------ footer (every page) */
        .foot {
            position: fixed;
            right: 0;
            bottom: -11.5mm;
            left: 0;
            height: 8mm;
            border-top: 1px solid #e6e2d6;
        }

        .foot td {
            padding-top: 2.2mm;
            color: #6a7586;
            font-size: 7.5pt;
            font-weight: 600;
            line-height: 1;
        }

        /* One line, always: the controller cuts a title that is wider than this cell (pdfFooterTitle()). */
        .foot .foot-title {
            width: 46%;
            color: #16264f;
            font-weight: 800;
            white-space: nowrap;
        }

        .foot .foot-mark {
            width: 32%;
            color: #b3261e;
            font-weight: 800;
            letter-spacing: 0.04em;
            text-align: center;
            text-transform: uppercase;
        }

        /* ------------------------------------------------ first page header */
        .dh {
            padding: 15px 20px 15px;
            border-radius: 12px;
            background: #16264f;
            color: #ffffff;
        }

        .dh td {
            vertical-align: middle;
        }

        .dh-eyebrow {
            color: #e0b04a;
            font-size: 7.6pt;
            font-weight: 800;
            letter-spacing: 0.12em;
            text-transform: uppercase;
        }

        .dh-kind {
            width: 38%;
            text-align: right;
        }

        .dh-kind span {
            display: inline-block;
            padding: 2.5px 11px 3.5px;
            border-radius: 999px;
            background: #0f8278;
            color: #ffffff;
            font-size: 7.6pt;
            font-weight: 800;
            letter-spacing: 0.12em;
            text-transform: uppercase;
        }

        .dh-title {
            margin-top: 8px;
            color: #ffffff;
            font-size: 19pt;
            font-weight: 800;
            line-height: 0.88;
            word-wrap: break-word;
        }

        .dh-foot {
            margin-top: 7px;
        }

        .dh-foot td {
            vertical-align: bottom;
            overflow-wrap: anywhere;
        }

        .dh-meta {
            color: #d3d9e8;
            font-size: 9.4pt;
            font-weight: 600;
        }

        .dh-meta span {
            color: #8793b3;
        }

        .dh-by {
            width: 46%;
            color: #aab4cf;
            font-size: 7.8pt;
            font-weight: 400;
            text-align: right;
        }

        .dh-by b {
            color: #ffffff;
            font-weight: 600;
        }

        .sum {
            margin-top: 10px;
        }

        .sum td {
            vertical-align: middle;
        }

        .sum-total {
            width: 22%;
            color: #16264f;
            font-size: 10pt;
            font-weight: 800;
            text-align: right;
        }

        .note {
            margin-top: 9px;
            padding: 7px 12px 8px;
            border-left: 3px solid #c23b3b;
            border-radius: 0 8px 8px 0;
            background: #fdeeee;
            color: #8c2420;
            font-size: 8.6pt;
            font-weight: 600;
        }

        .note b {
            color: #b3261e;
            font-weight: 800;
        }

        .note img {
            position: relative;
            top: 1px;
            width: 10.5px;
            height: 10.5px;
            margin-right: 5px;
        }

        .empty {
            margin-top: 16px;
            padding: 22px 18px;
            border: 1px dashed #d9d4c5;
            border-radius: 12px;
            color: #6a7586;
            font-size: 10pt;
            font-weight: 600;
            text-align: center;
        }

        /* ------------------------------------------------ pills */
        .pill {
            display: inline-block;
            margin-right: 5px;
            padding: 2px 8px 3px;
            border: 1px solid #dde2e9;
            border-radius: 999px;
            background: #f1f3f6;
            color: #4a5563;
            font-size: 7.6pt;
            font-weight: 800;
            line-height: 0.95;
            vertical-align: middle;
            white-space: nowrap;
        }

        .pill img {
            position: relative;
            top: 1px;
            width: 9px;
            height: 9px;
            margin-right: 3px;
        }

        .pill .dot {
            display: inline-block;
            width: 5.5px;
            height: 5.5px;
            margin-right: 4px;
            border-radius: 3px;
        }

        .pill b {
            margin-left: 4px;
            font-weight: 800;
        }

        .pill-level {
            font-weight: 700;
        }

        .pill-filter {
            border-color: #16264f;
            background: #16264f;
            color: #ffffff;
        }

        .pill-active {
            border-color: #b9e4db;
            background: #e5f7f3;
            color: #0f8278;
        }

        .pill-draft {
            border-color: #f0d9a4;
            background: #fdf3dc;
            color: #8f5f00;
        }

        .pill-issue {
            border-color: #f4cbcb;
            background: #fde7e7;
            color: #c23b3b;
        }

        /* ------------------------------------------------ level heading */
        .sec {
            margin: 11px 0 7px;
            padding: 6px 14px 7px;
            border-radius: 10px;
            page-break-after: avoid;
            page-break-inside: avoid;
        }

        .sec td {
            vertical-align: middle;
        }

        .sec-name {
            font-size: 12.5pt;
            font-weight: 800;
        }

        .sec-name .dot {
            display: inline-block;
            width: 9px;
            height: 9px;
            margin-right: 7px;
            border-radius: 5px;
        }

        .sec-count {
            font-size: 8.4pt;
            font-weight: 700;
            text-align: right;
        }

        /* ------------------------------------------------ question card */
        .card {
            margin: 0 0 6px;
            padding: 5px 0;
            border: 1px solid #e6e2d6;
            border-radius: 11px;
            background: #ffffff;
            page-break-inside: avoid;
        }

        /* The coloured left edge: teal when active, amber for a draft. */
        .card-in {
            padding: 1px 15px 1px 14px;
            border-left: 3px solid #d9dee6;
        }

        .card.is-active .card-in {
            border-left-color: #0f8278;
        }

        .card.is-draft .card-in {
            border-left-color: #e0b04a;
        }

        .num {
            display: inline-block;
            margin-right: 7px;
            padding: 2.5px 8px 3.5px;
            border-radius: 6px;
            background: #16264f;
            color: #ffffff;
            font-size: 8.4pt;
            font-weight: 800;
            line-height: 0.95;
            vertical-align: middle;
        }

        .question {
            margin: 5px 0 5px;
            color: #16264f;
            font-size: 9.8pt;
            font-weight: 700;
            line-height: 1.02;
            white-space: pre-line;
            word-wrap: break-word;
        }

        .opts {
            table-layout: fixed;
            page-break-inside: avoid;
        }

        .opts .gap {
            width: 1.6%;
        }

        .opts .rowgap td {
            height: 4px;
            font-size: 1px;
            line-height: 1px;
        }

        .opt {
            width: 49.2%;
            padding: 4px 8px 4px;
            border: 1px solid #ebe8df;
            border-radius: 8px;
            background: #fbfaf7;
            color: #3f4a5a;
            font-size: 8.5pt;
            font-weight: 600;
            line-height: 0.98;
        }

        .opt.is-correct {
            border-color: #8fd3c5;
            background: #eefaf7;
            color: #0c5f57;
            font-weight: 700;
        }

        .opt-blank {
            width: 49.2%;
        }

        .opt-letter {
            width: 23px;
        }

        .opt-letter div {
            width: 16px;
            height: 16px;
            border-radius: 5px;
            background: #eceae3;
            color: #5a6675;
            font-size: 7.6pt;
            font-weight: 800;
            line-height: 1.07;
            text-align: center;
        }

        .opt.is-correct .opt-letter div {
            background: #0f8278;
            color: #ffffff;
        }

        /* "anywhere", not "break-word": inside a table cell an unbroken run would otherwise widen the column. */
        .opt-text {
            padding-top: 0.5px;
            overflow-wrap: anywhere;
        }

        .opt-mark {
            width: 55px;
            padding-top: 1px;
            color: #0f8278;
            font-size: 7.6pt;
            font-weight: 800;
            line-height: 1;
            text-align: right;
            white-space: nowrap;
        }

        .opt-mark img {
            position: relative;
            top: 0.5px;
            width: 9.5px;
            height: 9.5px;
            margin-right: 2px;
        }

        .none {
            margin: 0 0 2px;
            color: #9aa3b0;
            font-size: 8.6pt;
            font-weight: 600;
        }

        .why {
            margin-top: 5px;
            padding-top: 4px;
            border-top: 1px dashed #e0dccf;
        }

        .why-title {
            margin-bottom: 3px;
            color: #8f5f00;
            font-size: 8.4pt;
            font-weight: 800;
        }

        .why-title img {
            position: relative;
            top: 1.5px;
            width: 11px;
            height: 11px;
            margin-right: 4px;
        }

        .why-text {
            color: #3f4a5a;
            font-size: 8.5pt;
            font-weight: 600;
            line-height: 1.04;
            white-space: pre-line;
            word-wrap: break-word;
        }

        .quote {
            margin-top: 4px;
            padding: 4px 11px 5px;
            border-left: 3px solid #c9a24a;
            border-radius: 0 7px 7px 0;
            background: #faf6ec;
        }

        .why .quote:first-child {
            margin-top: 1px;
        }

        .quote-label {
            color: #b48729;
            font-size: 7pt;
            font-weight: 800;
            letter-spacing: 0.05em;
            text-transform: uppercase;
        }

        .quote-text {
            margin-top: 2px;
            color: #4a5666;
            font-family: "DejaVu Serif", serif;
            font-size: 8pt;
            font-style: italic;
            font-weight: normal;
            line-height: 1.1;
            white-space: pre-line;
            word-wrap: break-word;
            /* DejaVu's space is 0.32em, nearly twice the sans text's: drawn in to match the card. */
            word-spacing: -0.7pt;
        }
    </style>
</head>
<body>
    <div class="foot">
        <table>
            <tr>
                <td class="foot-title">{{ $paText($footerTitle) }}</td>
                <td class="foot-mark">Confidential &mdash; answer key</td>
                <td>&nbsp;</td>
            </tr>
        </table>
    </div>

    <div class="dh">
        <table>
            <tr>
                <td class="dh-eyebrow">London Churchill College</td>
                <td class="dh-kind"><span>Question bank</span></td>
            </tr>
        </table>
        <div class="dh-title">{{ $paText($policy->title) }}</div>
        <table class="dh-foot">
            <tr>
                <td class="dh-meta">{{ $paText($paCategory) }}@if($paVersion !== '') <span>&nbsp;&middot;&nbsp;</span> {{ $paText($paVersion) }}@endif</td>
                <td class="dh-by">Generated <b>{{ $generatedAt }}</b>@if($generatedBy !== '') by <b>{{ $paText($generatedBy) }}</b>@endif</td>
            </tr>
        </table>
    </div>

    <table class="sum">
        <tr>
            <td>
                <span class="pill pill-filter">{{ $filterLabel }}</span>
                @foreach($sections as $paSection)
                    <span class="pill pill-level" style="background: {{ $paLevels[$paSection['level']]['soft'] }}; border-color: {{ $paLevels[$paSection['level']]['edge'] }}; color: {{ $paLevels[$paSection['level']]['ink'] }};"><span class="dot" style="background: {{ $paLevels[$paSection['level']]['main'] }};"></span>{{ $paSection['label'] }}<b>{{ $paSection['count'] }}</b></span>
                @endforeach
                @if($active > 0)
                    <span class="pill pill-active"><img src="{{ $paIconActive }}" alt="">Active<b>{{ $active }}</b></span>
                @endif
                @if($drafts > 0)
                    <span class="pill pill-draft">Draft<b>{{ $drafts }}</b></span>
                @endif
            </td>
            <td class="sum-total">{{ $total }} {{ $total == 1 ? 'question' : 'questions' }}</td>
        </tr>
    </table>

    <div class="note"><img src="{{ $paIconLock }}" alt=""><b>Contains the answer key &mdash; for HR use only.</b> Do not share with staff who will sit this test.</div>

    @if($total == 0)
        <div class="empty">No questions match this filter, so there is nothing to print.</div>
    @endif

    @foreach($sections as $paSection)
        @php $paLevel = $paLevels[$paSection['level']]; @endphp
        <div class="sec" style="background: {{ $paLevel['soft'] }}; border: 1px solid {{ $paLevel['edge'] }};">
            <table>
                <tr>
                    <td class="sec-name" style="color: {{ $paLevel['ink'] }};"><span class="dot" style="background: {{ $paLevel['main'] }};"></span>{{ $paSection['label'] }}</td>
                    <td class="sec-count" style="color: {{ $paLevel['ink'] }};">{{ $paSection['count'] }} {{ $paSection['count'] == 1 ? 'question' : 'questions' }} &nbsp;&middot;&nbsp; Q{{ $paSection['first'] }}@if($paSection['last'] != $paSection['first'])&ndash;Q{{ $paSection['last'] }}@endif</td>
                </tr>
            </table>
        </div>

        @foreach($paSection['cards'] as $paCard)
            @php $paCardLevel = $paLevels[$paCard['level']]; @endphp
            <div class="card {{ $paCard['is_active'] == 1 ? 'is-active' : 'is-draft' }}">
                <div class="card-in">
                    <div class="head">
                        <span class="num">Q{{ $paCard['sl'] }}</span><span class="pill pill-level" style="background: {{ $paCardLevel['soft'] }}; border-color: {{ $paCardLevel['edge'] }}; color: {{ $paCardLevel['ink'] }};"><span class="dot" style="background: {{ $paCardLevel['main'] }};"></span>{{ $paCard['level_label'] }}</span>
                        @if($paCard['is_active'] == 1)
                            <span class="pill pill-active"><img src="{{ $paIconActive }}" alt="">Active</span>
                        @else
                            <span class="pill pill-draft">Draft</span>
                        @endif
                        @if($paCard['well_formed'] != 1 && $paCard['issue'] !== '')
                            <span class="pill pill-issue"><img src="{{ $paIconIssue }}" alt="">{{ $paCard['issue'] }}</span>
                        @endif
                    </div>

                    <div class="question">{{ $paText($paCard['question']) }}</div>

                    @if(!empty($paCard['options']))
                        <table class="opts">
                            @foreach(array_chunk($paCard['options'], 2) as $paPair)
                                @if(!$loop->first)
                                    <tr class="rowgap"><td colspan="3">&nbsp;</td></tr>
                                @endif
                                <tr>
                                    @foreach([0, 1] as $paSide)
                                        @if($paSide == 1)
                                            <td class="gap"></td>
                                        @endif
                                        @if(isset($paPair[$paSide]))
                                            <td class="opt{{ $paPair[$paSide]['is_correct'] == 1 ? ' is-correct' : '' }}">
                                                <table>
                                                    <tr>
                                                        <td class="opt-letter"><div>{{ $paPair[$paSide]['letter'] }}</div></td>
                                                        <td class="opt-text">{{ $paText($paPair[$paSide]['text']) }}</td>
                                                        @if($paPair[$paSide]['is_correct'] == 1)
                                                            <td class="opt-mark"><img src="{{ $paIconTick }}" alt="">Correct</td>
                                                        @endif
                                                    </tr>
                                                </table>
                                            </td>
                                        @else
                                            <td class="opt-blank"></td>
                                        @endif
                                    @endforeach
                                </tr>
                            @endforeach
                        </table>
                    @else
                        <div class="none">No options yet.</div>
                    @endif

                    @if(!empty($paCard['explanation']) || !empty($paCard['source_excerpt']))
                        <div class="why">
                            @if(!empty($paCard['explanation']))
                                <div class="why-title"><img src="{{ $paIconWhy }}" alt="">Why this answer</div>
                                <div class="why-text">{{ $paText($paCard['explanation']) }}</div>
                            @endif
                            @if(!empty($paCard['source_excerpt']))
                                <div class="quote">
                                    <div class="quote-label">From the policy</div>
                                    <div class="quote-text">{{ $paText($paCard['source_excerpt'], 'serif') }}</div>
                                </div>
                            @endif
                        </div>
                    @endif
                </div>
            </div>
        @endforeach
    @endforeach
</body>
</html>
