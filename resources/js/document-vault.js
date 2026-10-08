/*
 * Encrypted documents: the PIN prompt and the open that follows it.
 *
 * Shared by the two Documents tabs that can hold an encrypted file — staff
 * (employee-upload.js) and student (student-upload.js). Each page carries the
 * same prompt (#documentPinModal) and marks its table with data-vault-* flags.
 *
 * The file never has a link. It is asked for with the reader's document PIN
 * and comes back in the response itself, so there is nothing to copy, bookmark
 * or pass on. The checks below only choose what to say first — the server
 * makes every decision again, and records the attempt.
 */
("use strict");

export const escapeHtml = (value) => {
    return String(value == null ? "" : value)
        .replace(/&/g, "&amp;")
        .replace(/</g, "&lt;")
        .replace(/>/g, "&gt;")
        .replace(/"/g, "&quot;")
        .replace(/'/g, "&#039;");
};

const vaultFallbackError = "This document could not be opened. Please try again or contact the administrator.";

/*
 * table        the documents table: holds the data-vault-* flags and the
 *              .openEncryptedDoc buttons
 * openRoute    name of the route that checks the PIN and returns the file
 * showWarning  (title, html) => void, the page's own warning dialog
 * onActivity   called after every attempt, so the page can refresh its log
 */
export const initDocumentVault = ({ table, openRoute, showWarning, onActivity = () => {} }) => {
    const documentPinModalEl = document.getElementById("documentPinModal");
    const documentPinModal = documentPinModalEl ? tailwind.Modal.getOrCreateInstance(documentPinModalEl) : null;
    const $documentTable = $(table);
    const vaultState = {
        canUse: $documentTable.attr("data-vault-can-use") == 1,
        hasPin: $documentTable.attr("data-vault-has-pin") == 1,
        impersonating: $documentTable.attr("data-vault-impersonating") == 1,
        pinUrl: $documentTable.attr("data-vault-pin-url"),
    };

    const setDocumentPinBusy = (busy) => {
        $("#documentPinModal .documentPinSubmit").prop("disabled", busy);
    };

    // An error arrives as a Blob too, because the request asked for one.
    const readVaultError = async (error) => {
        try {
            const body = JSON.parse(await error.response.data.text());
            return body.message || vaultFallbackError;
        } catch (e) {
            return vaultFallbackError;
        }
    };

    const requestEncryptedDocument = (rowId, pin, mode) => {
        // Opened here, while this is still the user's own click: a tab opened
        // after the response would be blocked as a pop-up.
        const viewer = mode === "view" ? window.open("", "_blank") : null;

        setDocumentPinBusy(true);

        axios({
            method: "post",
            url: route(openRoute),
            data: { row_id: rowId, pin: pin, mode: mode },
            responseType: "blob",
            headers: { "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"), Accept: "application/json" },
        }).then(response => {
            setDocumentPinBusy(false);

            const blobUrl = URL.createObjectURL(response.data);
            if (viewer) {
                viewer.location.href = blobUrl;
            } else {
                let fileName = "document";
                try {
                    fileName = decodeURIComponent(response.headers["x-document-name"] || fileName);
                } catch (e) {}

                const link = document.createElement("a");
                link.href = blobUrl;
                link.download = fileName;
                document.body.appendChild(link);
                link.click();
                link.remove();
                setTimeout(() => URL.revokeObjectURL(blobUrl), 60000);
            }

            if (documentPinModal) {
                documentPinModal.hide();
            }
            onActivity();
        }).catch(async error => {
            setDocumentPinBusy(false);
            if (viewer) {
                viewer.close();
            }

            const message = error.response ? await readVaultError(error) : vaultFallbackError;
            onActivity();

            if (documentPinModalEl && documentPinModalEl.classList.contains("show")) {
                $("#documentPinError").text(message);
                $("#documentPinInput").val("").trigger("focus");
            } else {
                showWarning("Document Locked", escapeHtml(message));
            }
        });
    };

    $documentTable.on("click", ".openEncryptedDoc", function (e) {
        e.preventDefault();
        const $theLink = $(this);
        const rowId = $theLink.attr("data-id");

        // canUse and hasPin describe whoever is really at the keyboard: when
        // signed in as somebody else, that is the impersonator's own account.

        // Nothing a PIN could change, so do not ask for one. The request is
        // still made: the refusal belongs in the access log.
        if (!vaultState.canUse) {
            requestEncryptedDocument(rowId, "", "download");
            return;
        }

        if (!vaultState.hasPin) {
            // The PIN page is closed to an impersonated session, so there is
            // nothing useful to link to from one.
            showWarning("No Document PIN Yet", vaultState.impersonating
                ? "Your own account has no document PIN yet. Leave impersonation, set one up from <strong>PIN</strong> in your account menu, then try again."
                : 'You need a document PIN to open encrypted documents. <a href="' + escapeHtml(vaultState.pinUrl) + '" class="font-medium underline">Set up your PIN</a>, then come back to this page.');
            return;
        }

        $("#documentPinDocName").text($theLink.attr("data-name"));
        $('#documentPinForm input[name="row_id"]').val(rowId);
        // There is no download button: the file opens in the browser and is
        // saved from there. What a browser cannot show (Word, Excel...) can
        // only be saved, so the button and the note say that instead.
        const viewable = $theLink.attr("data-viewable") == 1;
        $("#documentPinViewBtn").attr("data-mode", viewable ? "view" : "download").text(viewable ? "View" : "Open");
        $("#documentPinSaveNote").prop("hidden", viewable);
        documentPinModal.show();
    });

    const submitDocumentPin = (mode) => {
        const pin = $("#documentPinInput").val().trim();
        $("#documentPinError").text("");

        if (pin === "") {
            $("#documentPinError").text("Enter your document PIN.");
            $("#documentPinInput").trigger("focus");
            return;
        }

        requestEncryptedDocument($('#documentPinForm input[name="row_id"]').val(), pin, mode);
    };

    $("#documentPinModal .documentPinSubmit").on("click", function (e) {
        e.preventDefault();
        submitDocumentPin($(this).attr("data-mode"));
    });

    // Enter in the PIN box does what the button does.
    $("#documentPinForm").on("submit", function (e) {
        e.preventDefault();
        submitDocumentPin($("#documentPinViewBtn").attr("data-mode"));
    });

    $("#documentPinInput").on("input", function () {
        $(this).val($(this).val().replace(/\D/g, ""));
    });

    if (documentPinModalEl) {
        documentPinModalEl.addEventListener("shown.tw.modal", function () {
            $("#documentPinInput").trigger("focus");
        });

        // The PIN does not outlive the dialog it was typed into.
        documentPinModalEl.addEventListener("hide.tw.modal", function () {
            $("#documentPinInput").val("");
            $("#documentPinError").text("");
            $('#documentPinForm input[name="row_id"]').val("0");
            setDocumentPinBusy(false);
        });
    }

    return {
        // A document is only handed over once the open has been recorded, so a
        // refusal has to be said out loud rather than leaving a dead button.
        showRefused(error) {
            const message = error.response && error.response.data && error.response.data.message
                ? error.response.data.message
                : vaultFallbackError;

            showWarning("Document Not Opened", escapeHtml(message));
        },
    };
};
