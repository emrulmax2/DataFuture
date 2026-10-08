/*
 * Service Desk tickets on the student notes page.
 *
 * The list is rendered by the server; this only opens one. A ticket carries its
 * whole conversation, so it is fetched when a row is clicked rather than with
 * the page — most people open this page to read notes, and loading twenty
 * conversations to show none of them is a lot of traffic for nothing.
 *
 * A ticket the holding department marked confidential is listed but not served:
 * clicking it asks for the reader's own document PIN first, and Operations
 * releases the conversation only once this application says that happened.
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

    const pinEl = document.getElementById('sdConfidentialModal');
    const pinModal = pinEl ? tailwind.Modal.getOrCreateInstance(pinEl) : null;
    const pinForm = document.getElementById('sdConfidentialForm');
    const pinInput = document.getElementById('sdConfidentialPin');
    const pinError = document.getElementById('sdConfidentialError');
    const pinRef = document.getElementById('sdConfidentialRef');
    const pinSubmit = document.getElementById('sdConfidentialSubmit');

    let pendingTicket = null;

    const token = document.querySelector('meta[name="csrf-token"]')?.content || '';

    /* The panel's markup arrives after the page's icons were drawn. */
    const drawIcons = () => {
        createIcons({ icons, attrs: { 'stroke-width': 1.5 }, nameAttr: 'data-lucide' });
    };

    const showTicket = (html) => {
        bodyEl.innerHTML = html;
        drawIcons();
    };

    const fail = (message) => {
        bodyEl.innerHTML = '<div class="p-5 text-center text-danger">' + message + '</div>';
    };

    const askForPin = (ticketId, reference) => {
        if (!pinModal) {
            fail('This ticket is confidential and cannot be opened here.');

            return;
        }

        modal.hide();

        pendingTicket = ticketId;
        pinError.textContent = '';
        pinInput.value = '';
        pinRef.textContent = reference || 'Confidential ticket';

        pinModal.show();
        /* The dialog animates in; focusing before it has finished puts the
           caret somewhere the eye is not. */
        window.setTimeout(() => pinInput.focus(), 220);
    };

    document.addEventListener('click', function (event) {
        const row = event.target.closest('.sd-ticket-row');

        if (!row) {
            return;
        }

        const ticketId = row.dataset.ticket;
        const reference = row.querySelector('.is-ref')?.textContent.trim();

        /* Asked for straight away rather than after a refusal: the row already
           says it is confidential, so a round trip to be told so is a wait for
           nothing. */
        if (row.dataset.confidential) {
            askForPin(ticketId, reference);

            return;
        }

        /* Shown before the request goes out: on a slow link the panel opening
           empty reads as a ticket with nothing in it. */
        bodyEl.innerHTML = '<div class="p-5 text-center text-slate-500">Loading…</div>';
        modal.show();

        fetch('/student/service-desk/ticket/' + encodeURIComponent(ticketId), {
            headers: { Accept: 'application/json' },
            credentials: 'same-origin',
        })
            .then((response) => response.json().catch(() => ({})))
            .then((payload) => {
                if (payload.status) {
                    showTicket(payload.html);

                    return;
                }

                /* Marked confidential since the page was drawn. */
                if (payload.confidential) {
                    askForPin(ticketId, reference);

                    return;
                }

                fail(payload.message || 'That ticket could not be loaded.');
            })
            .catch(() => fail('Could not reach the Service Desk. Try again in a moment.'));
    });

    if (pinForm) {
        pinForm.addEventListener('submit', function (event) {
            event.preventDefault();

            const pin = pinInput.value.trim();

            if (pin === '') {
                pinError.textContent = 'Enter your document PIN.';
                pinInput.focus();

                return;
            }

            pinSubmit.disabled = true;
            pinError.textContent = '';

            fetch('/student/service-desk/ticket/' + encodeURIComponent(pendingTicket) + '/unlock', {
                method: 'POST',
                headers: {
                    Accept: 'application/json',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': token,
                },
                credentials: 'same-origin',
                body: JSON.stringify({ pin: pin }),
            })
                .then((response) => response.json().catch(() => ({})))
                .then((payload) => {
                    pinSubmit.disabled = false;

                    if (!payload.status) {
                        /* Wrong PIN, locked out, no PIN set up: the service
                           says which, and it is the only thing worth showing. */
                        pinError.textContent = payload.message || 'That PIN was not accepted.';
                        pinInput.select();

                        return;
                    }

                    pinInput.value = '';
                    pinModal.hide();
                    modal.show();
                    showTicket(payload.html);
                })
                .catch(() => {
                    pinSubmit.disabled = false;
                    pinError.textContent = 'Could not reach the Service Desk. Try again in a moment.';
                });
        });
    }
})();
