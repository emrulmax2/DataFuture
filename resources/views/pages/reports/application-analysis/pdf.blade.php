@php
    $fontDir = resource_path('fonts/audit-certificate');
@endphp
<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
<title>Admission Report · {{ $report['intake'] }}</title>
<style>
    /*
     * Admission Report PDF, landscape A4 (1123 × 794 px at DomPDF's 96 dpi,
     * the size of the design artboard, so its px values carry over).
     * Inter is not shipped with the app; Hanken Grotesk is the nearest of the
     * faces the other PDFs already use, Source Serif 4 stands in for Georgia.
     */
    @font-face { font-family: 'Hanken Grotesk'; font-weight: 400; font-style: normal; src: url("{{ $fontDir }}/HankenGrotesk-400.ttf") format('truetype'); }
    @font-face { font-family: 'Hanken Grotesk'; font-weight: 500; font-style: normal; src: url("{{ $fontDir }}/HankenGrotesk-500.ttf") format('truetype'); }
    @font-face { font-family: 'Hanken Grotesk'; font-weight: 600; font-style: normal; src: url("{{ $fontDir }}/HankenGrotesk-600.ttf") format('truetype'); }
    @font-face { font-family: 'Hanken Grotesk'; font-weight: 700; font-style: normal; src: url("{{ $fontDir }}/HankenGrotesk-700.ttf") format('truetype'); }
    @font-face { font-family: 'Source Serif 4'; font-weight: 400; font-style: normal; src: url("{{ $fontDir }}/SourceSerif4-500.ttf") format('truetype'); }
    @font-face { font-family: 'Source Serif 4'; font-weight: 700; font-style: normal; src: url("{{ $fontDir }}/SourceSerif4-600.ttf") format('truetype'); }

    /* Top margin holds the running header (34px paper edge + header + 20px gap),
       the bottom one the footer (26px paper edge + footer + gap). */
    @page { size: A4 landscape; margin: 110px 44px 62px 44px; }

    body { margin: 0; color: #16242b; font-family: 'Hanken Grotesk', Arial, sans-serif; font-size: 13px; line-height: 1.02; }
    table { border-collapse: collapse; width: 100%; }
    td, th { padding: 0; text-align: left; vertical-align: top; }

    /* --- running header / footer, repeated on every page --- */
    /* The rule sits on the header block, not on its two cells, so it has no seam. */
    .aar-runhead { border-bottom: 2px solid #16242b; left: 0; position: fixed; right: 0; top: -76px; }
    .aar-runfoot { bottom: -36px; left: 0; position: fixed; right: 0; }
    .aar-head td { padding-bottom: 10px; vertical-align: bottom; }
    .aar-head__title { font-size: 17px; font-weight: 700; margin-top: 3px; }
    /* Same height as the two text lines it replaces, so the header band is unchanged. */
    .aar-head__logo { display: block; height: 44px; width: auto; }
    .aar-head__meta { text-align: right; }
    .aar-head__intake { color: #0e5a54; font-size: 13px; font-weight: 700; }
    .aar-head__by { color: #55616b; font-size: 12px; margin-top: 3px; }
    .aar-foot td { border-top: 1px solid #c3c9d1; color: #55616b; font-size: 12px; padding-top: 8px; }
    .aar-foot__right { text-align: right; }
    .aar-pagenum:after { content: "Page " counter(page){!! !empty($pages) ? ' " of '.(int) $pages.'"' : '' !!}; }

    .aar-eyebrow { color: #55616b; font-size: 12px; font-weight: 600; letter-spacing: 0.08em; text-transform: uppercase; }
    .aar-h2 { font-size: 15px; font-weight: 700; }
    .aar-h2--spaced { margin: 24px 0 10px; }
    .aar-h2--table { margin: 22px 0 6px; }
    .aar-page2 { page-break-before: always; }

    /* --- page 1: left column --- */
    /* Column widths are px on auto-layout tables: DomPDF 2.0.8 splits a
       `table-layout: fixed` table into equal columns whatever widths it is
       given. Content box = 1123 - 2 × 44 = 1035px. */
    .aar-split { width: 1035px; }
    .aar-split__left { width: 400px; }
    .aar-split__gap { width: 44px; }
    .aar-split__right { width: 591px; }
    .aar-hero { margin-top: 6px; }
    .aar-hero td { vertical-align: middle; }
    .aar-hero__big { font-family: 'Source Serif 4', Georgia, serif; font-size: 44px; font-weight: 700; }
    .aar-hero__of { color: #55616b; font-family: 'Source Serif 4', Georgia, serif; font-size: 24px; font-weight: 400; }
    .aar-hero__chipcell { text-align: right; width: 80px; }
    .aar-hero__chip { border-radius: 8px; padding: 7px 14px 8px; text-align: right; }
    .aar-hero__chip.is-up { background: #e2f1ee; color: #0e5a54; }
    .aar-hero__chip.is-down { background: #fbeadb; color: #8a3d0c; }
    .aar-hero__gap { font-size: 22px; font-weight: 700; }
    .aar-hero__pct { font-size: 12px; margin-top: 1px; }
    .aar-summary { font-size: 14px; line-height: 1.12; margin-top: 8px; }

    .aar-stack { height: 34px; }
    .aar-stack__seg { border-left: 2px solid #ffffff; color: #ffffff; font-size: 13px; font-weight: 600; height: 34px; vertical-align: middle; white-space: nowrap; }
    .aar-stack__seg.is-first { border-left: 0; padding-left: 12px; }
    .aar-stack__seg.is-first, .aar-gender__seg.is-first { border-radius: 4px 0 0 4px; }
    .aar-stack__seg.is-last, .aar-gender__seg.is-last { border-radius: 0 4px 4px 0; }
    .aar-legend { margin-top: 10px; }
    .aar-legend > tr > td, .aar-legend > tbody > tr > td { padding: 0 16px 10px 0; width: 50%; }
    .aar-legend__swatchcell { padding-top: 3px; width: 18px; }
    .aar-legend__swatch { border-radius: 2px; display: block; height: 10px; width: 10px; }
    .aar-legend__title { font-weight: 700; }
    .aar-legend__sub { color: #55616b; font-size: 12px; margin-top: 3px; }

    /* --- page 1: course bars --- */
    .aar-sechead td { border-bottom: 1px solid #c3c9d1; padding-bottom: 8px; vertical-align: bottom; }
    .aar-sechead__meta { color: #55616b; font-size: 12px; text-align: right; }
    .aar-course { border-bottom: 1px solid #e3e7ea; padding: 13px 0; }
    .aar-course.is-last { border-bottom: 0; }
    .aar-course__row { width: 591px; }
    .aar-course__row td { vertical-align: middle; }
    .aar-course__id { width: 200px; }
    .aar-course__name { font-size: 14px; font-weight: 700; }
    .aar-course__venue { color: #55616b; font-size: 12px; margin-top: 3px; }
    .aar-course__barcell { padding-left: 18px; width: 187px; } /* widths are content boxes: + 18px padding */
    .aar-track { background: #e9edf0; border-radius: 2px; height: 14px; position: relative; }
    .aar-track__fill { background: #0e5a54; border-radius: 2px; height: 14px; }
    .aar-track__tick { background: #16242b; height: 26px; margin-left: -1px; position: absolute; top: -6px; width: 2px; }
    .aar-course__fig { padding-left: 18px; text-align: right; white-space: nowrap; width: 90px; }
    .aar-course__accepted { font-size: 18px; font-weight: 700; }
    .aar-course__target { color: #55616b; font-size: 13px; }
    .aar-course__gapcell { padding-left: 18px; width: 60px; }
    .aar-gap { border-radius: 6px; font-weight: 700; padding: 5px 0; text-align: center; }
    .aar-gap.is-up { background: #e2f1ee; color: #0e5a54; }
    .aar-gap.is-down { background: #fbeadb; color: #8a3d0c; }
    .aar-gap.is-none { background: #eef1f3; color: #55616b; }
    .aar-course__note { color: #55616b; font-size: 12px; padding-top: 7px; }
    .aar-course__note.is-warn { color: #8a3d0c; font-weight: 600; }

    /* --- page 1: delivery table --- */
    .aar-table th { border-bottom: 1px solid #16242b; color: #55616b; font-size: 12px; font-weight: 600; padding: 6px 0; text-align: right; }
    .aar-table td { border-bottom: 1px solid #e3e7ea; padding: 7px 0; text-align: right; }
    .aar-table tr.is-last td { border-bottom-color: #16242b; }
    .aar-table .aar-table__label { text-align: left; }
    .aar-table td.is-strong { font-weight: 700; }
    .aar-table .is-up { color: #0e5a54; }
    .aar-table .is-down { color: #8a3d0c; }
    .aar-table .is-none { color: #55616b; }
    .aar-table__total td { border-bottom: 0; font-weight: 700; }

    /* --- page 2 --- */
    .aar-titlerow { margin-bottom: 24px; }
    .aar-titlerow td { vertical-align: bottom; }
    .aar-title { font-family: 'Source Serif 4', Georgia, serif; font-size: 36px; font-weight: 700; margin-top: 4px; }
    .aar-titlerow__note { font-size: 14px; padding-bottom: 6px; text-align: right; }
    .aar-cols { width: 1035px; }
    .aar-cols__first { width: 250px; }
    .aar-cols__gap { width: 44px; }
    .aar-cols__mid { width: 348px; }
    .aar-cols__last { width: 349px; }
    .aar-sechead--spaced { margin-top: 28px; }

    .aar-gender { height: 34px; margin-top: 12px; }
    .aar-gender__seg { border-left: 2px solid #ffffff; font-size: 13px; font-weight: 700; height: 34px; padding-left: 10px; vertical-align: middle; white-space: nowrap; }
    .aar-gender__seg.is-first { border-left: 0; }
    .aar-gender__seg--male { background: #0e5a54; color: #ffffff; }
    .aar-gender__seg--female { background: #bfdfda; color: #16242b; }
    .aar-gender__seg--other { background: #7b8490; color: #ffffff; }
    .aar-gender__seg--unknown { background: #e3e7ea; color: #16242b; }
    .aar-caption { color: #55616b; font-size: 12px; margin-top: 9px; }
    .aar-caption.is-warn { color: #8a3d0c; line-height: 1.12; }

    .aar-entry { margin-top: 8px; }
    .aar-entry { width: 250px; }
    .aar-entry th { border-bottom: 1px solid #16242b; color: #55616b; font-size: 12px; font-weight: 600; padding: 6px 0 6px 10px; text-align: right; width: 52px; }
    .aar-entry td { border-bottom: 1px solid #e3e7ea; padding: 9px 0 9px 10px; text-align: right; }
    .aar-entry .aar-entry__course { padding-left: 0; text-align: left; width: auto; }

    .aar-share { margin-top: 6px; width: 348px; }
    .aar-share td { padding: 14px 0; vertical-align: middle; }
    .aar-share__label { font-weight: 700; width: 140px; }
    .aar-share__barcell { padding: 0 12px 0 4px !important; width: 112px; }
    .aar-share__track { background: #e9edf0; border-radius: 2px; height: 10px; }
    .aar-share__fill { background: #0e5a54; border-radius: 2px; height: 10px; }
    .aar-share__count { font-weight: 700; text-align: right; width: 34px; }
    .aar-share__pct { color: #55616b; text-align: right; width: 46px; }
    .aar-share tr.is-muted .aar-share__label { color: #55616b; }
    .aar-share tr.is-muted .aar-share__fill { background: #7b8490; }

    .aar-empty { border: 1px solid #e3e7ea; border-radius: 8px; padding: 40px; text-align: center; }
    .aar-empty__title { font-size: 15px; font-weight: 700; }
    .aar-empty__text { color: #55616b; margin-top: 6px; }
</style>
</head>
<body>
    @include('pages.reports.application-analysis.report', ['medium' => 'pdf'])
</body>
</html>
