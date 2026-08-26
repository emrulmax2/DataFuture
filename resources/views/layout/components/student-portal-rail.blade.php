{{--
    Student portal right rail: news carousel, library links and the
    today / upcoming class lists. Data comes from `StudentPortalRail`, so this
    renders identically on every portal screen.
--}}
@php
    $rail = App\Support\StudentPortalRail::for($student);
    $railNews = $rail['news'];
    $railToday = $rail['today'];
    $railUpcoming = $rail['upcoming'];
@endphp

<aside class="spf-rail">
    <div class="spf-rail__grid">
        <section class="spf-railcard" id="spfNews" data-news-count="{{ $railNews->count() }}">
            <div class="spf-railcard__head">
                <div class="spf-railcard__title">News &amp; updates</div>
                <div class="spf-spacer"></div>
                @if($railNews->count() > 1)
                    <button type="button" class="spf-railnav" data-news-nav="-1" aria-label="Previous update">
                        <i data-lucide="chevron-left" class="w-3 h-3"></i>
                    </button>
                    <button type="button" class="spf-railnav" data-news-nav="1" aria-label="Next update">
                        <i data-lucide="chevron-right" class="w-3 h-3"></i>
                    </button>
                @endif
            </div>

            @foreach($railNews as $index => $item)
                <article class="spf-news__item" data-news-slide="{{ $index }}" @if($index > 0) style="display:none" @endif>
                    <div class="spf-news__age">{{ $item->age }}</div>
                    <div class="spf-news__title">{{ $item->title }}</div>
                    <div class="spf-news__body" data-news-body>{!! $item->body !!}</div>
                    <div class="spf-news__actions">
                        {{-- Revealed by the shell script only when the body actually overflows. --}}
                        <button type="button" class="spf-linkbtn" data-news-more hidden>Read more &rarr;</button>
                        @if(isset($item->documents) && count($item->documents) > 0)
                            <div class="spf-dd">
                                <button type="button" class="spf-linkbtn" data-spf-dd="newsDocs{{ $index }}">Attachments &#9662;</button>
                                <div id="newsDocs{{ $index }}" class="spf-dd__menu">
                                    @foreach($item->documents as $doc)
                                        <a href="javascript:void(0);" data-docid="{{ $doc->id }}" class="spf-dd__item downloadEventDoc">{{ $doc->display_file_name }}</a>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    </div>
                </article>
            @endforeach

            @if($railNews->count() > 1)
                <div class="spf-news__foot">
                    <div class="spf-spacer"></div>
                    <div class="spf-dots" id="spfNewsDots">
                        @foreach($railNews as $index => $item)
                            <span class="{{ $index === 0 ? 'is-active' : '' }}" data-news-dot="{{ $index }}"></span>
                        @endforeach
                    </div>
                </div>
            @endif

            @if($railNews->count() === 0)
                <div class="spf-news__none">
                    <p>No announcements at the moment.</p>
                    <p class="spf-news__none-sub">College news and updates will appear here as they are published.</p>
                </div>
            @endif
        </section>

        <section style="display:flex;flex-direction:column;gap:10px">
            <div class="spf-railcard__title" style="padding:0 2px">Library &amp; resources</div>
            <a href="https://www.jstor.org/" target="_blank" rel="noopener" class="spf-lib spf-lib--jstor">
                <span class="spf-lib__mark">JSTOR</span>
                <span class="spf-lib__text">Explore the world&rsquo;s knowledge, cultures and ideas</span>
            </a>
            <a href="https://research.ebsco.com/c/c4wm42" target="_blank" rel="noopener" class="spf-lib spf-lib--ebsco">
                <span class="spf-lib__mark">EBSCO</span>
                <span class="spf-lib__text">Research databases &amp; e-journals</span>
            </a>
            <a href="https://sites.google.com/lcc.ac.uk/training-guidance/home" target="_blank" rel="noopener" class="spf-lib spf-lib--training">
                <span class="spf-lib__mark"><i data-lucide="graduation-cap" class="w-4 h-4"></i></span>
                <span class="spf-lib__text">Training and guidance</span>
            </a>
        </section>

        <section class="spf-railcard">
            <div class="spf-railcard__head">
                <div class="spf-railcard__title">Today&rsquo;s classes</div>
            </div>
            @if($railToday->count() > 0)
                <div class="spf-classlist">
                    @foreach($railToday as $class)
                        <div class="spf-class">
                            <div class="spf-class__module">{{ $class->module }}</div>
                            <div class="spf-class__meta">{{ $class->time }}@if($class->class_type) &middot; {{ $class->class_type }} @endif</div>
                            @if($class->where)
                                <div class="spf-class__room">{{ $class->where }}</div>
                            @endif
                            @if($class->virtual_room)
                                <a href="{{ $class->virtual_room }}" target="_blank" rel="noopener" class="spf-class__join">
                                    <i data-lucide="video" class="w-3 h-3"></i> Join online
                                </a>
                            @endif
                        </div>
                    @endforeach
                </div>
            @else
                <p class="spf-railcard__empty">No classes scheduled today.</p>
            @endif

            <div class="spf-railcard__divider"></div>

            <div class="spf-railcard__head">
                <div class="spf-railcard__title">Upcoming classes</div>
            </div>
            @if($railUpcoming->count() > 0)
                <div class="spf-classlist">
                    @foreach($railUpcoming as $class)
                        <div class="spf-class">
                            <div class="spf-class__module">{{ $class->module }}</div>
                            <div class="spf-class__meta">{{ $class->day }} &middot; {{ $class->time }}</div>
                            @if($class->where)
                                <div class="spf-class__room">{{ $class->where }}</div>
                            @endif
                        </div>
                    @endforeach
                </div>
            @else
                <p class="spf-railcard__empty">Your next sessions will appear here.</p>
            @endif
        </section>
    </div>
</aside>
