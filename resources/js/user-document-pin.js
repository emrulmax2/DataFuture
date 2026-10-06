import { createIcons, icons } from "lucide";

("use strict");

/*
 * Account menu -> PIN.
 *
 * Three panels, one on screen at a time: "new" (no PIN yet — choose one),
 * "set" (a PIN exists — change it, with the current one) and "reset"
 * (forgotten — a code has been emailed; the code and a new PIN replace it).
 *
 * The server decides whether a PIN is acceptable; the checks here only save a
 * round trip for the obvious slips. A PIN is never shown back: once saved
 * only its hash exists, so the boxes are emptied after every attempt.
 */
(function () {
    const page = document.getElementById("documentPinPage");

    if (!page || page.dataset.state === "blocked") {
        return;
    }

    const FALLBACK_ERROR = "Something went wrong. Please try again or contact the administrator.";

    const panels = Array.from(page.querySelectorAll("[data-panel]"));
    const setupForm = document.getElementById("documentPinSetupForm");
    const changeForm = document.getElementById("documentPinChangeForm");
    const successEl = document.getElementById("documentPinSuccess");
    const setAtEl = document.getElementById("documentPinSetAt");

    const headers = () => ({
        "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').getAttribute("content"),
        "X-Requested-With": "XMLHttpRequest",
        Accept: "application/json",
    });

    const drawIcons = () => {
        createIcons({ icons, "stroke-width": 1.5, nameAttr: "data-lucide" });
    };

    const setError = (panel, message) => {
        const el = page.querySelector('[data-error="' + panel + '"]');
        if (el) {
            el.textContent = message || "";
        }
    };

    const showPanel = (name) => {
        panels.forEach((panel) => {
            panel.hidden = panel.dataset.panel !== name;
        });
    };

    /* `continueUrl` is where sign-in was taking them before it sent them here. */
    const showSuccess = (message, continueUrl) => {
        document.getElementById("documentPinSuccessText").textContent = message || "";
        successEl.hidden = !message;

        const link = document.getElementById("documentPinContinue");
        link.hidden = !continueUrl;
        if (continueUrl) {
            link.setAttribute("href", continueUrl);
        }
    };

    const showSetAt = (setAt) => {
        setAtEl.textContent = setAt ? setAtEl.dataset.prefix + setAt + "." : "";
    };

    const post = (routeName, data) => {
        return axios({ method: "post", url: route(routeName), data: data, headers: headers() });
    };

    const messageFrom = (error) => {
        return (error.response && error.response.data && error.response.data.message) || FALLBACK_ERROR;
    };

    const field = (form, name) => form.querySelector('[name="' + name + '"]');

    const clear = (form) => {
        Array.from(form.querySelectorAll("input")).forEach((input) => {
            input.value = "";
        });
    };

    /* What is wrong with a chosen PIN before it is even sent, or "". */
    const slip = (pin, confirmation) => {
        const min = Number(field(setupForm, "pin").getAttribute("minlength"));
        const max = Number(field(setupForm, "pin").getAttribute("maxlength"));

        if (pin.length < min || pin.length > max) {
            return "Your PIN must be " + min + " to " + max + " digits, numbers only.";
        }
        if (pin !== confirmation) {
            return "The two PINs do not match. Type the same PIN in both boxes.";
        }

        return "";
    };

    // Digits only, in every PIN box on the page.
    Array.from(page.querySelectorAll('input[type="password"]')).forEach((input) => {
        input.addEventListener("input", () => {
            input.value = input.value.replace(/\D/g, "");
        });
    });

    const setupBtn = document.getElementById("documentPinSetup");
    setupForm.addEventListener("submit", (event) => {
        event.preventDefault();
        setError("new", "");

        const pin = field(setupForm, "pin").value;
        const confirmation = field(setupForm, "pin_confirmation").value;
        const problem = slip(pin, confirmation);
        if (problem) {
            setError("new", problem);
            return;
        }

        setupBtn.disabled = true;

        post("user.account.document.pin.setup", { pin: pin, pin_confirmation: confirmation }).then((response) => {
            setupBtn.disabled = false;
            clear(setupForm);
            showSetAt(response.data.set_at);
            showPanel("set");

            // Held here on the way somewhere else: say so, then take them on.
            // The link stays in case the browser does not follow.
            const continueUrl = response.data.continue_url;
            if (continueUrl) {
                showSuccess("Your PIN is saved. Taking you on to where you were going.", continueUrl);
                window.setTimeout(() => window.location.assign(continueUrl), 1500);
            } else {
                showSuccess("Your PIN is saved. Use it whenever you open an encrypted document.");
            }
        }).catch((error) => {
            setupBtn.disabled = false;
            setError("new", messageFrom(error));
            clear(setupForm);
            field(setupForm, "pin").focus();
        });
    });

    const changeBtn = document.getElementById("documentPinChange");
    changeForm.addEventListener("submit", (event) => {
        event.preventDefault();
        setError("set", "");
        showSuccess("");

        const current = field(changeForm, "current_pin").value;
        const pin = field(changeForm, "pin").value;
        const confirmation = field(changeForm, "pin_confirmation").value;

        if (current === "") {
            setError("set", "Enter your current PIN.");
            field(changeForm, "current_pin").focus();
            return;
        }

        const problem = slip(pin, confirmation);
        if (problem) {
            setError("set", problem);
            return;
        }

        changeBtn.disabled = true;

        post("user.account.document.pin.change", { current_pin: current, pin: pin, pin_confirmation: confirmation }).then((response) => {
            changeBtn.disabled = false;
            clear(changeForm);
            showSetAt(response.data.set_at);
            showSuccess("Your PIN has been changed. The old one no longer works.");
        }).catch((error) => {
            changeBtn.disabled = false;
            setError("set", messageFrom(error));

            // Wrong current PIN: start over. A new PIN that was refused: keep
            // the current one so only the new one has to be retyped.
            const wrongCurrent = !error.response || !error.response.data || error.response.data.field !== "pin";
            if (wrongCurrent) {
                clear(changeForm);
                field(changeForm, "current_pin").focus();
            } else {
                field(changeForm, "pin").value = "";
                field(changeForm, "pin_confirmation").value = "";
                field(changeForm, "pin").focus();
            }
        });
    });

    /*
     * Forgotten PIN. The owner resets it themselves: a code is emailed to the
     * address they sign in with, and the code plus a new PIN replaces the old
     * one. Asking for a code changes nothing by itself.
     */
    const resetForm = document.getElementById("documentPinResetForm");
    const forgotBtn = document.getElementById("documentPinForgot");
    const resendBtn = document.getElementById("documentPinResend");
    const resetBtn = document.getElementById("documentPinReset");

    /* `from` is the panel the request was made on, which is where a refusal is shown. */
    const sendCode = (button, from) => {
        setError(from, "");
        showSuccess("");
        button.disabled = true;

        post("user.account.document.pin.reset.code", {}).then(() => {
            button.disabled = false;
            clear(resetForm);
            setError("reset", "");
            showPanel("reset");
            field(resetForm, "code").focus();
        }).catch((error) => {
            button.disabled = false;
            setError(from, messageFrom(error));
        });
    };

    forgotBtn.addEventListener("click", () => sendCode(forgotBtn, "set"));
    resendBtn.addEventListener("click", () => sendCode(resendBtn, "reset"));

    document.getElementById("documentPinResetCancel").addEventListener("click", () => {
        clear(resetForm);
        setError("reset", "");
        setError("set", "");
        showPanel("set");
    });

    // The code is digits too, but it is not a password box.
    field(resetForm, "code").addEventListener("input", (event) => {
        event.target.value = event.target.value.replace(/\D/g, "");
    });

    resetForm.addEventListener("submit", (event) => {
        event.preventDefault();
        setError("reset", "");

        const code = field(resetForm, "code").value;
        const pin = field(resetForm, "pin").value;
        const confirmation = field(resetForm, "pin_confirmation").value;

        if (code === "") {
            setError("reset", "Enter the code from the email.");
            field(resetForm, "code").focus();
            return;
        }

        const problem = slip(pin, confirmation);
        if (problem) {
            setError("reset", problem);
            return;
        }

        resetBtn.disabled = true;

        post("user.account.document.pin.reset", { code: code, pin: pin, pin_confirmation: confirmation }).then((response) => {
            resetBtn.disabled = false;
            clear(resetForm);
            clear(changeForm);
            showSetAt(response.data.set_at);
            showPanel("set");
            showSuccess("Your PIN has been reset. The old one no longer works.");
        }).catch((error) => {
            resetBtn.disabled = false;
            setError("reset", messageFrom(error));

            // A wrong code: retype the code. A refused PIN: the code is still
            // good, so only the PIN has to be chosen again.
            const wrongCode = !error.response || !error.response.data || error.response.data.field !== "pin";
            if (wrongCode) {
                field(resetForm, "code").value = "";
                field(resetForm, "code").focus();
            } else {
                field(resetForm, "pin").value = "";
                field(resetForm, "pin_confirmation").value = "";
                field(resetForm, "pin").focus();
            }
        });
    });

    drawIcons();
})();
