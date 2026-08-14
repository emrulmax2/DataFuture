{{--
    Rows inside the "Tutors" / "Personal Tutors" cards. Rendered on first paint
    and again by the class-info XHR, so both paths share this markup.
--}}
<div class="pgd-staffbox__list">
@forelse($people as $person)
    <a href="{{ $person['url'] }}" class="pgd-person" data-pgd-person data-terms="{{ implode(',', $person['term_ids']) }}">
        <span class="pgd-avatar pgd-avatar--lg" style="background: {{ $person['color'] }};">
            @if(!empty($person['photo']))
                <img src="{{ $person['photo'] }}" alt="{{ $person['name'] }}">
            @else
                {{ $person['initials'] }}
            @endif
        </span>
        <span class="pgd-person__copy">
            <span class="pgd-person__name">{{ $person['name'] }}</span>
            <span class="pgd-person__terms">
                @foreach($person['terms'] as $term)
                    <span class="pgd-person__term" style="background: {{ $term['tint'] }}; color: {{ $term['dot'] }};" data-term="{{ $term['id'] }}">
                        <span style="background: {{ $term['dot'] }};"></span>{{ $term['name'] }}
                    </span>
                @endforeach
            </span>
        </span>
        <span class="pgd-person__count">
            <strong>{{ $person['count'] }}</strong>
            <span>{{ $person['count'] == 1 ? 'module' : 'modules' }}</span>
        </span>
        <span class="pgd-person__go">
            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="m9 6 6 6-6 6"></path></svg>
        </span>
    </a>
@empty
    <div class="pgd-note pgd-note--warn">
        <span class="pgd-note__icon">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><path d="M10.3 3.9 1.9 18a2 2 0 0 0 1.7 3h16.8a2 2 0 0 0 1.7-3L13.7 3.9a2 2 0 0 0-3.4 0Z"></path><path d="M12 9v4"></path><path d="M12 17h.01"></path></svg>
        </span>
        <span>
            <strong>No {{ $kind == 'personal' ? 'personal tutors' : 'tutors' }} found</strong>
            Nothing is scheduled against the selected terms and filters.
        </span>
    </div>
@endforelse
</div>

{{-- One link per open term: the card aggregates across them, so a single link
     would silently send the reader to just one. --}}
@if(!empty($perTerm))
    <div class="pgd-staffbox__more">
        @foreach($perTerm as $term)
            <a href="{{ $term['url'] }}">
                <span>View all {{ $term['count'] }} {{ $kind == 'personal' ? ($term['count'] == 1 ? 'personal tutor' : 'personal tutors') : ($term['count'] == 1 ? 'tutor' : 'tutors') }}</span>
                <small><span class="pgd-staffbox__dot" style="background: {{ $term['dot'] }};"></span>{{ $term['name'] }}</small>
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h13"></path><path d="m12 6 6 6-6 6"></path></svg>
            </a>
        @endforeach
    </div>
@endif
