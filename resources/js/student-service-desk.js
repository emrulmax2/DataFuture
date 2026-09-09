/*
 * Service Desk tickets on the student notes page.
 *
 * The list is rendered by the server; this only opens one. A ticket carries its
 * whole conversation, so it is fetched when a row is clicked rather than with
 * the page — most people open this page to read notes, and loading twenty
 * conversations to show none of them is a lot of traffic for nothing.
 */
import { createIcons, icons } from 'lucide';

(function () {
    'use strict';

    const modalEl = document.getElementById('serviceDeskTicketModal');
    const bodyEl = document.getElementById('serviceDeskTicketBody');

    if (!modalEl || !bodyEl) {
        return;
    }

    const modal = tailwind.Modal.getOrCreateInstance(modalEl);

    document.addEventListener('click', function (event) {
        const row = event.target.closest('.sd-ticket-row');

        if (!row) {
            return;
        }

        /* Shown before the request goes out: on a slow link the panel opening
           empty reads as a ticket with nothing in it. */
        bodyEl.innerHTML = '<div class="p-5 text-center text-slate-500">Loading…</div>';
        modal.show();

        fetch('/student/service-desk/ticket/' + encodeURIComponent(row.dataset.ticket), {
            headers: { Accept: 'application/json' },
            credentials: 'same-origin',
        })
            .then(function (response) {
                return response.json().catch(function () { return {}; });
            })
            .then(function (payload) {
                if (!payload.status) {
                    bodyEl.innerHTML = '<div class="p-5 text-center text-danger">'
                        + (payload.message || 'That ticket could not be loaded.')
                        + '</div>';
                    return;
                }

                bodyEl.innerHTML = payload.html;

                /* The panel's markup arrived after the page's icons were drawn. */
                createIcons({ icons, attrs: { 'stroke-width': 1.5 }, nameAttr: 'data-lucide' });
            })
            .catch(function () {
                bodyEl.innerHTML = '<div class="p-5 text-center text-danger">'
                    + 'Could not reach the Service Desk. Try again in a moment.</div>';
            });
    });
})();
