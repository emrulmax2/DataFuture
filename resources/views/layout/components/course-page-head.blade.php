{{--
    Course Management page-head band.

    Sits directly under the module header and carries the page title, the
    breadcrumb trail and the single "back" action. Driven purely by view data
    so every screen in the module gets the same band without repeating markup:

      $cmPageTitle  string  Falls back to $subtitle, then $title.
      $breadcrumbs  array   [['label' => …, 'href' => …], …] — existing app-wide
                            convention, so controllers need no new payload.
      $cmBackUrl    string  Omit to fall back to the module landing page.
      $cmBackLabel  string  Omit for "Back to dashboard".
--}}
@php
    $cmHeadTitle = $cmPageTitle ?? ($subtitle ?? ($title ?? 'Course Management'));

    $cmHeadCrumbs = [];
    if (!empty($breadcrumbs) && is_array($breadcrumbs)) {
        foreach ($breadcrumbs as $cmCrumb) {
            $cmHeadCrumbs[] = [
                'label' => $cmCrumb['label'] ?? '',
                'href' => $cmCrumb['href'] ?? 'javascript:void(0);',
            ];
        }
    }

    $cmHeadBackUrl = $cmBackUrl ?? (Route::has('dashboard') ? route('dashboard') : 'javascript:void(0);');
    $cmHeadBackLabel = $cmBackLabel ?? 'Back to dashboard';
@endphp

<div class="cm-pagehead no-print">
    <div class="cm-pagehead__inner">
        <div class="cm-pagehead__main">
            <span class="cm-pagehead__icon" aria-hidden="true">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"></path><path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"></path></svg>
            </span>

            <h1 class="cm-pagehead__title cm-serif">{{ $cmHeadTitle }}</h1>

            @if(!empty($cmHeadCrumbs))
                <span class="cm-pagehead__rule" aria-hidden="true"></span>

                <nav class="cm-crumbs" aria-label="Breadcrumb">
                    @foreach($cmHeadCrumbs as $cmCrumbIndex => $cmCrumb)
                        @if($cmCrumbIndex === count($cmHeadCrumbs) - 1)
                            <strong aria-current="page">{{ $cmCrumb['label'] }}</strong>
                        @else
                            <a href="{{ $cmCrumb['href'] }}">{{ $cmCrumb['label'] }}</a>
                            <span class="cm-crumbs__sep" aria-hidden="true">/</span>
                        @endif
                    @endforeach
                </nav>
            @endif
        </div>

        <a href="{{ $cmHeadBackUrl }}" class="cm-btn cm-btn--gold-outline">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><path d="M19 12H5M12 19l-7-7 7-7"></path></svg>
            {{ $cmHeadBackLabel }}
        </a>
    </div>
</div>
