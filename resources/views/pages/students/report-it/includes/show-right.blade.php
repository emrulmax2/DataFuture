@php
    $ritModifiedBy = $reportItAll->employee_name ?? null;
    $ritModifiedInitials = collect(preg_split('/\s+/', trim((string) $ritModifiedBy)))
        ->filter()
        ->take(2)
        ->map(fn ($part) => mb_strtoupper(mb_substr($part, 0, 1)))
        ->implode('') ?: '?';

    $ritUploads = $reportItAll->uploads ?? collect();
@endphp

<div class="rit-panel">
    <div class="rit-panel__head">
        <h2 class="rit-panel__title rit-panel__title--sm">Report details</h2>
    </div>
    <div class="rit-detaillist">
        <div class="rit-detail">
            <div class="rit-detail__label">Issue type</div>
            <div class="rit-detail__value">{{ $reportItAll->issueType->name ?? 'N/A' }}</div>
        </div>
        <div class="rit-detail">
            <div class="rit-detail__label">Venue</div>
            <div class="rit-detail__value">{{ $reportItAll->venue->name ?? 'N/A' }}</div>
        </div>
        <div class="rit-detail">
            <div class="rit-detail__label">Location</div>
            <div class="rit-detail__value">{{ $reportItAll->location ?: 'N/A' }}</div>
        </div>
        <div class="rit-detail">
            <div class="rit-detail__label">Description</div>
            <div class="rit-detail__value">{{ $reportItAll->description }}</div>
        </div>
        <div class="rit-detail">
            <div class="rit-detail__label">Created at</div>
            <div class="rit-detail__value rit-detail__value--num">{{ $reportItAll->created_at }}</div>
        </div>
        <div class="rit-detail">
            <div class="rit-detail__label">Last modified by</div>
            @if($ritModifiedBy)
                <span class="rit-miniperson">
                    <span class="rit-miniperson__avatar">{{ $ritModifiedInitials }}</span>
                    <span class="rit-miniperson__name">{{ $ritModifiedBy }}</span>
                </span>
            @else
                <div class="rit-detail__value">N/A</div>
            @endif
        </div>
    </div>
</div>

<div class="rit-panel">
    <div class="rit-panel__head">
        <h2 class="rit-panel__title rit-panel__title--sm">Attachments</h2>
        <span class="rit-panel__count">{{ $ritUploads->count() }}</span>
    </div>
    @if($ritUploads->isNotEmpty())
        <div class="rit-files">
            @foreach($ritUploads as $upload)
                @php
                    $isImage = $upload->file_type == 'image';
                    $fileUrl = $isImage && !empty($upload->file_image_url)
                        ? $upload->file_image_url
                        : asset('storage/' . $upload->file_path);
                    $ext = strtoupper(pathinfo($upload->file_name, PATHINFO_EXTENSION)) ?: 'FILE';
                @endphp
                <a href="{{ $fileUrl }}" target="_blank" rel="noopener" class="rit-file">
                    <span class="rit-file__icon">
                        @if($isImage && !empty($upload->file_image_url))
                            <img alt="{{ $upload->file_name }}" src="{{ $upload->file_image_url }}">
                        @elseif($isImage)
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="5" width="18" height="14" rx="2"></rect><circle cx="9" cy="10" r="1.8"></circle><path d="m4 17 5-4 4 3 3-2 4 3"></path></svg>
                        @else
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><path d="M13.5 3H7a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V8.5L13.5 3z"></path><path d="M13.5 3v5.5H19"></path></svg>
                        @endif
                    </span>
                    <span class="rit-file__body">
                        <span class="rit-file__name">{{ $upload->file_name }}</span>
                        <span class="rit-file__meta">{{ $ext }}</span>
                    </span>
                    <svg class="rit-file__go" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 4v11M7.5 11l4.5 4.5 4.5-4.5M5 20h14"></path></svg>
                </a>
            @endforeach
        </div>
    @else
        <div class="rit-empty">
            <div class="rit-empty__title">No attachments</div>
            <div class="rit-empty__desc">Nothing was uploaded with this report</div>
        </div>
    @endif
</div>
