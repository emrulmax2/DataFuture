/*
 | Applicant dashboard.
 |
 | The applications are server-rendered as cards by
 | `pages/applicant/index.blade.php`, so this file is only the delete flow for a
 | draft: open the confirm modal, call the destroy endpoint, report the outcome
 | in the success modal and refresh the list.
 |
 | It used to drive a Tabulator table here, which is why the wiring was gated on
 | that table existing; it now hangs off the confirm modal instead.
 */
(function () {
    const confirmModalEl = document.getElementById('confirmModal');
    const successModalEl = document.getElementById('successModal');

    if (!confirmModalEl || !successModalEl) {
        return;
    }

    const confModal = tailwind.Modal.getOrCreateInstance(confirmModalEl);
    const succModal = tailwind.Modal.getOrCreateInstance(successModalEl);

    // Whatever the outcome, the next open must not inherit the last row's id.
    confirmModalEl.addEventListener('hidden.tw.modal', function () {
        $('#confirmModal .agreeWith').attr('data-id', '0');
    });

    /* Say it once: re-registering this listener on every delete would stack
       handlers and overwrite the message several times. */
    let successTitle = '';
    let successDesc = '';
    successModalEl.addEventListener('shown.tw.modal', function () {
        $('#successModal .successModalTitle').html(successTitle);
        $('#successModal .successModalDesc').html(successDesc);
    });

    // A card reloads the page after a delete, so the handler is delegated.
    $(document).on('click', '.delete_btn', function () {
        const rowID = $(this).attr('data-id');
        if (!rowID || rowID === '0') {
            return;
        }
        $('#confirmModal .agreeWith').attr('data-id', rowID);
        confModal.show();
    });

    $('#confirmModal .agreeWith').on('click', function () {
        const recordID = $(this).attr('data-id');
        if (!recordID || recordID === '0') {
            return;
        }

        $('#confirmModal button').attr('disabled', 'disabled');
        $('#confirmModal .agreeWith svg').css('display', 'inline-block');

        axios({
            method: 'delete',
            url: route('applicant.dashboard.applications.destroy', recordID),
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
        }).then(response => {
            $('#confirmModal button').removeAttr('disabled');
            $('#confirmModal .agreeWith svg').css('display', 'none');
            confModal.hide();

            successTitle = 'Done!';
            successDesc = response.data.message ? response.data.message : 'Incomplete application successfully deleted.';
            succModal.show();

            // The cards are rendered server-side; reload once the message is read.
            successModalEl.addEventListener('hidden.tw.modal', function () {
                window.location.reload();
            }, { once: true });
        }).catch(error => {
            $('#confirmModal button').removeAttr('disabled');
            $('#confirmModal .agreeWith svg').css('display', 'none');
            confModal.hide();

            successTitle = 'Oops!';
            successDesc = (error.response && error.response.data && error.response.data.message)
                ? error.response.data.message
                : 'Unable to delete this application.';
            succModal.show();
        });
    });
})();
