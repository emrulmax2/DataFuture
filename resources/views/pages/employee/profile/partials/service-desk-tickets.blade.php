{{--
    Service Desk tickets this employee has been tagged on.

    The employee-profile twin of the list on a student's notes page. The
    tickets live in Operations and are worked there — this is a read-only
    window on them, so there is nothing to add, edit or reply to here.

    Drawn as a second card under Notes with the same Tabulator table, so the
    two lists on this page are one design: see resources/js/employee-service-desk.js.
    The rows arrive with the page (a ticket's summary is small); a conversation
    is fetched only when its row is opened.

    $serviceDeskTickets is null when Operations could not be reached, which is a
    different thing from "no tickets" and is said differently.

    A ticket marked confidential in Operations is listed, but opens only after
    the reader has entered their own document PIN. Until then Operations
    refuses its contents to this application at all.
--}}
<section class="ep-doc-card">
    <div class="ep-doc-card__head">
        <div class="ep-doc-card__head-main">
            <span class="ep-doc-card__icon ep-doc-card__icon--teal">
                <i data-lucide="ticket" class="w-4 h-4"></i>
            </span>
            <div>
                <h2 class="ep-doc-card__title">Service Desk Tickets</h2>
                <p id="employeeServiceDeskSummary" class="ep-doc-card__meta">Raised in Operations by staff, tagged to this employee.</p>
            </div>
        </div>
    </div>

    <div class="ep-doc-card__body">
        @if ($serviceDeskTickets === null)
            <p class="ep-sd-unreachable">
                <i data-lucide="alert-triangle" class="w-4 h-4"></i>
                <span>The Service Desk could not be reached, so tickets are not shown. This does not mean there are none.</span>
            </p>
        @else
            <div class="ep-doc-table-wrap">
                <div id="employeeServiceDeskTable"
                     data-tickets="{{ json_encode(array_values($serviceDeskTickets)) }}"
                     class="table-report table-report--tabulator ep-doc-table ep-sd-table"></div>
            </div>
        @endif
    </div>
</section>
