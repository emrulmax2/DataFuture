{{-- LCC Preloader — matches the LCC Preloader prototype. Keeps the .sitePreLoader
     class so the existing fadeOut-on-load handler still dismisses it. --}}
<div class="sitePreLoader lcc-preloader" role="status" aria-label="Loading">
    <div class="lcc-preloader__mark">
        <svg class="lcc-preloader__ring" width="104" height="104" viewBox="0 0 104 104">
            <circle cx="52" cy="52" r="46" fill="none" stroke="rgba(255,255,255,0.1)" stroke-width="3"></circle>
            <circle cx="52" cy="52" r="46" fill="none" stroke="var(--lcc-gold)" stroke-width="3" stroke-linecap="round" stroke-dasharray="82 207"></circle>
        </svg>
        <div class="lcc-preloader__tile">LCC</div>
    </div>
    <div class="lcc-preloader__wordmark">
        <div class="lcc-preloader__wordmark-top">LONDON CHURCHILL</div>
        <div class="lcc-preloader__wordmark-bottom">COLLEGE</div>
    </div>
    <div class="lcc-preloader__track"><div class="lcc-preloader__bar"></div></div>
    <div class="lcc-preloader__status">Loading&hellip;</div>
</div>
