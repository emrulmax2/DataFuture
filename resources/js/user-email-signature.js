import { createIcons, icons } from "lucide";

("use strict");

/*
 * My HR -> Email Signature.
 *
 * The server owns the signature markup: every edit is posted to the preview
 * endpoint and the rendered HTML comes back, so what the staff member sees in
 * the preview is byte-for-byte what lands on the clipboard and in the .htm
 * download. Nothing about the signature is assembled in the browser.
 */
(function () {
    const form = document.getElementById("signatureForm");
    const source = document.getElementById("signatureSource");
    const frame = document.getElementById("signatureFrame");

    if (!form || !source || !frame) {
        return;
    }

    const statusEl = document.getElementById("signatureStatus");
    const savedEl = document.getElementById("signatureSavedAt");
    const copyBtn = document.getElementById("signatureCopy");
    const saveBtn = document.getElementById("signatureSave");
    const resetBtn = document.getElementById("signatureReset");
    const clientBtns = Array.from(document.querySelectorAll(".myhr-sign-clients__item"));
    const helpBlocks = Array.from(document.querySelectorAll(".myhr-sign-help"));

    /* Named for the status line after a copy, and the only place the
     * variant keys are spelled out in the browser. */
    const CLIENT_NAMES = {
        gmail: "Gmail",
        outlook: "Outlook / Hotmail",
        apple: "Apple Mail",
        mobile: "your phone",
    };

    let client = "gmail";
    let statusTimer = null;
    let previewTimer = null;

    /*
     * Accept: application/json makes Laravel answer a failed FormRequest with
     * a 422 and the messages, instead of a 302 back to the page — otherwise a
     * validation error would reach the browser as a redirect full of HTML.
     */
    const headers = () => ({
        "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').getAttribute("content"),
        "X-Requested-With": "XMLHttpRequest",
        Accept: "application/json",
    });
    const variantNode = (name) => source.querySelector('[data-variant="' + name + '"]');

    const drawIcons = () => {
        createIcons({ icons, "stroke-width": 1.5, nameAttr: "data-lucide" });
    };

    const setStatus = (message, tone) => {
        if (!statusEl) {
            return;
        }
        statusEl.textContent = message || "";
        statusEl.className = "myhr-sign-toolbar__status" + (tone ? " is-" + tone : "");
        window.clearTimeout(statusTimer);
        if (message) {
            statusTimer = window.setTimeout(() => {
                statusEl.textContent = "";
                statusEl.className = "myhr-sign-toolbar__status";
            }, 4000);
        }
    };

    /*
     * The preview runs in an iframe so the app's stylesheet cannot leak into
     * the signature and flatter it — an email client offers no such styling.
     */
    const paintFrame = () => {
        const node = variantNode(client);
        const html = node ? node.innerHTML : "";
        /*
         * The phone card is 340px in a stage several times that wide, and left
         * against the edge it reads as a broken full-width layout rather than a
         * deliberate one. It is a single root table, so centring the preview
         * body is safe — and it never touches what gets copied, which comes
         * from the source node, not from here.
         */
        const centred = client === "mobile" ? "body{display:flex;justify-content:center;}" : "";
        frame.srcdoc =
            '<!DOCTYPE html><html><head><meta charset="utf-8">' +
            "<style>html,body{margin:0;padding:34px;background:#ffffff;}" + centred + "</style>" +
            "</head><body>" +
            html +
            "</body></html>";
    };

    const resizeFrame = () => {
        try {
            const doc = frame.contentDocument;
            if (doc && doc.body) {
                frame.style.height = Math.max(doc.body.scrollHeight + 8, 320) + "px";
            }
        } catch (error) {
            frame.style.height = "560px";
        }
    };

    frame.addEventListener("load", resizeFrame);

    const applyMarkup = (markup) => {
        if (!markup) {
            return;
        }
        Object.keys(markup).forEach((name) => {
            const node = variantNode(name);
            if (node) {
                node.innerHTML = markup[name];
            }
        });
        paintFrame();
    };

    const selectClient = (name) => {
        client = name;
        clientBtns.forEach((btn) => {
            const active = btn.dataset.client === name;
            btn.classList.toggle("active", active);
            btn.setAttribute("aria-selected", active ? "true" : "false");
        });
        helpBlocks.forEach((block) => {
            block.hidden = block.dataset.help !== name;
        });
        paintFrame();
    };

    clientBtns.forEach((btn) => {
        btn.addEventListener("click", () => selectClient(btn.dataset.client));
    });

    /* ---------------------------------------------------------------- preview */

    const requestPreview = () => {
        axios({
            method: "post",
            url: route("user.account.signature.preview"),
            data: new FormData(form),
            headers: headers(),
        })
            .then((response) => {
                if (response.status === 200 && response.data.suc === 1) {
                    applyMarkup(response.data.markup);
                }
            })
            .catch(() => {
                setStatus("Preview could not be refreshed.", "error");
            });
    };

    const schedulePreview = () => {
        window.clearTimeout(previewTimer);
        previewTimer = window.setTimeout(requestPreview, 350);
    };

    form.addEventListener("input", schedulePreview);
    form.addEventListener("change", schedulePreview);

    /* ------------------------------------------------------------------- copy */

    /*
     * Two ways to reach the clipboard as rich HTML. The async Clipboard API is
     * the clean one; the range + execCommand fallback covers browsers that
     * refuse text/html writes or run without a secure context.
     */
    const copyByRange = (node) => {
        const selection = window.getSelection();
        const range = document.createRange();
        range.selectNodeContents(node);
        selection.removeAllRanges();
        selection.addRange(range);

        let copied = false;
        try {
            copied = document.execCommand("copy");
        } catch (error) {
            copied = false;
        }
        selection.removeAllRanges();

        return copied;
    };

    const copySignature = () => {
        const node = variantNode(client);
        if (!node) {
            return;
        }
        const html = node.innerHTML;
        const label = CLIENT_NAMES[client] || "your email client";

        const done = () => setStatus("Copied. Now paste it into " + label + ".", "ok");

        if (navigator.clipboard && window.ClipboardItem) {
            const item = new window.ClipboardItem({
                "text/html": new Blob([html], { type: "text/html" }),
                "text/plain": new Blob([node.innerText], { type: "text/plain" }),
            });
            navigator.clipboard
                .write([item])
                .then(done)
                .catch(() => {
                    if (copyByRange(node)) {
                        done();
                    } else {
                        setStatus("Your browser blocked the copy — select the preview and copy it by hand.", "error");
                    }
                });

            return;
        }

        if (copyByRange(node)) {
            done();
        } else {
            setStatus("Your browser blocked the copy — select the preview and copy it by hand.", "error");
        }
    };

    if (copyBtn) {
        copyBtn.addEventListener("click", copySignature);
    }

    /* ------------------------------------------------------------------- save */

    form.addEventListener("submit", function (event) {
        event.preventDefault();

        saveBtn.setAttribute("disabled", "disabled");
        setStatus("Saving…", "busy");

        axios({
            method: "post",
            url: route("user.account.signature.update"),
            data: new FormData(form),
            headers: headers(),
        })
            .then((response) => {
                saveBtn.removeAttribute("disabled");
                if (response.status === 200 && response.data.suc === 1) {
                    applyMarkup(response.data.markup);
                    if (savedEl && response.data.updated_at) {
                        savedEl.textContent = "Last saved " + response.data.updated_at;
                    }
                    setStatus("Signature saved.", "ok");
                } else {
                    setStatus("Your signature could not be saved.", "error");
                }
            })
            .catch((error) => {
                saveBtn.removeAttribute("disabled");
                const errors =
                    error.response && error.response.data && error.response.data.errors
                        ? Object.values(error.response.data.errors).flat()
                        : [];
                setStatus(errors.length ? errors[0] : "Your signature could not be saved.", "error");
            });
    });

    /* ------------------------------------------------------------------ reset */

    if (resetBtn) {
        resetBtn.addEventListener("click", function () {
            if (!window.confirm("Reset your signature back to the details held in your HR record?")) {
                return;
            }

            resetBtn.setAttribute("disabled", "disabled");
            axios({
                method: "post",
                url: route("user.account.signature.reset"),
                headers: headers(),
            })
                .then((response) => {
                    resetBtn.removeAttribute("disabled");
                    if (response.status !== 200 || response.data.suc !== 1) {
                        setStatus("Your signature could not be reset.", "error");
                        return;
                    }

                    const fields = response.data.fields || {};
                    Object.keys(fields).forEach((name) => {
                        const input = form.querySelector('[name="' + name + '"]');
                        if (!input) {
                            return;
                        }
                        if (input.type === "checkbox") {
                            input.checked = Boolean(fields[name]);
                        } else {
                            input.value = fields[name] === null ? "" : fields[name];
                        }
                    });

                    applyMarkup(response.data.markup);
                    if (savedEl) {
                        savedEl.textContent = "";
                    }
                    setStatus("Reset to your HR record.", "ok");
                })
                .catch(() => {
                    resetBtn.removeAttribute("disabled");
                    setStatus("Your signature could not be reset.", "error");
                });
        });
    }

    selectClient("gmail");
    drawIcons();
})();
