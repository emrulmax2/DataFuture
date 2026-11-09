import axios from "axios";
import SignaturePad from "signature_pad";

/*
 * The applicant e-signature page (pages/students/admission/e-signature).
 *
 * Location is captured when the page opens and again when the form is sent;
 * the signature is drawn on a canvas or picked as an image and posted as a
 * PNG data URL; the server writes the audit trail. Once recorded, the form
 * gives way to the "thank you" state.
 */
(function () {
    "use strict";

    const csrf = document.querySelector('meta[name="csrf-token"]');
    const headers = { "X-CSRF-TOKEN": csrf ? csrf.getAttribute("content") : "" };
    const form = document.getElementById("esignForm");
    const applicantId = form ? form.querySelector('input[name="applicant_id"]').value : null;

    /* ---------- location ---------------------------------------------------- */
    const locModal = document.getElementById("locModal");
    const locTitle = document.getElementById("locTitle");
    const locText = document.getElementById("locText");
    const showLoc = (title, text, buttonLabel, detail) => {
        locTitle.textContent = title;
        locText.textContent = text;
        let small = document.getElementById("locDetail");
        if (!small) { small = document.createElement("small"); small.id = "locDetail"; small.className = "esign-loc-detail"; locText.insertAdjacentElement("afterend", small); }
        small.textContent = detail || "";
        small.hidden = !detail;
        const b = document.getElementById("locAllow");
        if (b) { b.hidden = buttonLabel === null; if (buttonLabel) b.textContent = buttonLabel; }
        locModal.hidden = false;
    };
    const hideLoc = () => { locModal.hidden = true; };

    /* One position fix for the page. The first attempt is quick and may use a
       cached fix; if the device cannot work out where it is, one more attempt
       asks for high accuracy. A fix, once obtained, is reused by Submit so the
       applicant is not asked twice. */
    let lastPos = null;
    const locBtn = document.getElementById("locAllow");
    const GEO_FAST = { enableHighAccuracy: false, timeout: 10000, maximumAge: 10 * 60 * 1000 };
    const GEO_PRECISE = { enableHighAccuracy: true, timeout: 20000, maximumAge: 0 };
    const GEO_PATIENT_MS = 45000;
    const locate = (onOk, onErr, onWait) => {
        if (!navigator.geolocation) { onErr({ code: 0, message: "navigator.geolocation is not available" }); return; }
        const ok = (pos) => { lastPos = pos; onOk(pos); };
        navigator.geolocation.getCurrentPosition(ok, (err) => {
            if (err && err.code === 1) { onErr(err); return; } /* denied: asking again will not help */
            if (onWait) onWait();
            navigator.geolocation.getCurrentPosition(ok, (err2) => {
                if (err2 && err2.code === 1) { onErr(err2); return; }
                /* Last, patient attempt: desktops without GPS can need most of a
                   minute for a Wi-Fi fix. Watch and take the first position. */
                let settled = false;
                const stop = (id) => { settled = true; try { navigator.geolocation.clearWatch(id); } catch (e) { /* no watch */ } };
                const watchId = navigator.geolocation.watchPosition(
                    (pos) => { if (settled) return; stop(watchId); ok(pos); },
                    (err3) => { if (!settled && err3 && err3.code === 1) { stop(watchId); onErr(err3); } },
                    { enableHighAccuracy: true, timeout: GEO_PATIENT_MS, maximumAge: 0 }
                );
                setTimeout(() => { if (settled) return; stop(watchId); onErr(err2 || { code: 3, message: "timeout" }); }, GEO_PATIENT_MS);
            }, GEO_PRECISE);
        }, GEO_FAST);
    };
    const geoDetail = (err) => {
        const code = err && err.code;
        const name = code === 1 ? "PERMISSION_DENIED" : code === 2 ? "POSITION_UNAVAILABLE" : code === 3 ? "TIMEOUT" : "UNSUPPORTED";
        return "Error " + (code === undefined ? "?" : code) + " · " + name + (err && err.message ? " · " + err.message : "");
    };
    /* What went wrong, in words the applicant can act on. Allowed-but-unavailable
       (code 2) is the common desktop case: the browser has permission, but the
       operating system's location service is off or has no Wi-Fi/GPS fix. */
    const explainLoc = (err, forSubmit) => {
        const code = err && err.code;
        if (code === 1) {
            showLoc(forSubmit ? "Location required" : "Location permission required",
                "We record where the document is signed as part of the audit trail. Please allow location access for this site in your browser and try again.", "Allow location", geoDetail(err));
        } else if (code === 0) {
            showLoc("Location not available", "Your browser cannot share your location. Please open this link in Chrome or Safari.", "Try again", geoDetail(err));
        } else {
            showLoc("We could not determine your location",
                "Your browser is allowed to share it, but your device did not return a position. Make sure Wi-Fi is switched on (a position comes from nearby Wi-Fi networks, even on a wired connection), check Location Services for your browser (on a Mac: System Settings › Privacy & Security › Location Services) and restart the browser if you just turned it on, then try again. You can also open this link on your phone.", "Try again", geoDetail(err));
        }
    };
    const waitingLoc = () => showLoc("Finding your location…", "Your device is working out where you are. On a desktop this can take up to a minute; please keep this page open.", null, "");

    const requestLocation = () => {
        if (!form) return;
        locate((pos) => {
            hideLoc();
            axios.post(route("applicant.e.signature.location", applicantId), { latitude: pos.coords.latitude, longitude: pos.coords.longitude }, { headers })
                .catch(() => {});
        }, (err) => explainLoc(err, false), waitingLoc);
    };
    if (locBtn) locBtn.addEventListener("click", requestLocation);
    requestLocation();

    if (!form) return; /* already signed: nothing else on the page */

    /* ---------- signature modal --------------------------------------------- */
    const modal = document.getElementById("sigModal");
    const canvas = document.getElementById("sigCanvas");
    const hint = document.getElementById("sigHint");
    const done = document.getElementById("sigDone");
    const tabs = document.getElementById("sigTabs");
    const panes = { draw: document.getElementById("sigDraw"), image: document.getElementById("sigImage") };
    const file = document.getElementById("sigFile");
    const drop = document.getElementById("sigDrop");
    const imgPreview = document.getElementById("sigImgPreview");
    const pick = document.getElementById("sigPick");
    const imgNote = document.getElementById("sigImgNote");
    let pad = null, tab = "draw", image = null, hasStroke = false;

    const sizeCanvas = () => {
        const ratio = Math.max(window.devicePixelRatio || 1, 1);
        const w = canvas.offsetWidth || 760, h = 240;
        canvas.width = w * ratio; canvas.height = h * ratio;
        canvas.getContext("2d").scale(ratio, ratio);
        if (pad) { pad.clear(); hasStroke = false; }
    };
    const refresh = () => {
        const ok = tab === "draw" ? hasStroke : !!image;
        done.disabled = !ok;
        hint.hidden = hasStroke;
        imgPreview.hidden = !image; pick.hidden = !!image;
        imgNote.innerHTML = image ? '<button type="button" class="btn-link" id="sigImgReset">Choose a different image</button>' : "Select or drop an image (PNG or JPG)";
    };
    const openModal = () => {
        modal.hidden = false;
        if (!pad) {
            pad = new SignaturePad(canvas, { backgroundColor: "rgba(255,255,255,0)", penColor: "#15201e", minWidth: 1.2, maxWidth: 2.6 });
            pad.addEventListener("beginStroke", () => { hasStroke = true; refresh(); });
        }
        sizeCanvas();
        refresh();
    };
    const closeModal = () => { modal.hidden = true; };
    const setTab = (name) => {
        tab = name;
        tabs.querySelectorAll("button").forEach((b) => b.classList.toggle("on", b.getAttribute("data-tab") === name));
        panes.draw.hidden = name !== "draw"; panes.image.hidden = name !== "image";
        if (name === "draw") sizeCanvas();
        refresh();
    };

    document.getElementById("signArea").addEventListener("click", openModal);
    document.getElementById("sigClose").addEventListener("click", closeModal);
    document.getElementById("sigCancel").addEventListener("click", closeModal);
    modal.addEventListener("mousedown", (e) => { if (e.target === modal) closeModal(); });
    document.addEventListener("keydown", (e) => { if (e.key === "Escape" && !modal.hidden) closeModal(); });
    tabs.addEventListener("click", (e) => { const b = e.target.closest("button[data-tab]"); if (b) setTab(b.getAttribute("data-tab")); });
    document.getElementById("sigClear").addEventListener("click", () => { if (pad) pad.clear(); hasStroke = false; refresh(); });
    window.addEventListener("resize", () => { if (!modal.hidden && tab === "draw") sizeCanvas(); });

    const pickFile = (f) => {
        if (!f || !/^image\//.test(f.type)) return;
        const r = new FileReader();
        r.onload = () => { image = r.result; imgPreview.src = image; refresh(); };
        r.readAsDataURL(f);
    };
    file.addEventListener("change", () => { pickFile(file.files[0]); file.value = ""; });
    drop.addEventListener("dragover", (e) => { e.preventDefault(); drop.classList.add("over"); });
    drop.addEventListener("dragleave", () => drop.classList.remove("over"));
    drop.addEventListener("drop", (e) => { e.preventDefault(); drop.classList.remove("over"); pickFile(e.dataTransfer.files[0]); });
    imgNote.addEventListener("click", (e) => { if (e.target.id === "sigImgReset") { image = null; imgPreview.src = ""; refresh(); } });

    /* the chosen signature goes into the hidden field and the sign area shows it */
    const signatureInput = document.getElementById("signatureInput");
    const signArea = document.getElementById("signArea");
    const signCta = document.getElementById("signCta");
    const signPreview = document.getElementById("signPreview");
    const signChange = document.getElementById("signChange");
    done.addEventListener("click", () => {
        let data = null;
        if (tab === "draw") { if (pad && !pad.isEmpty()) data = pad.toDataURL("image/png"); }
        else data = image;
        if (!data) return;
        signatureInput.value = data;
        signPreview.src = data; signPreview.hidden = false; signCta.hidden = true; signChange.hidden = false;
        signArea.classList.add("has"); signArea.setAttribute("aria-label", "Change signature");
        closeModal();
    });

    /* ---------- submit ------------------------------------------------------ */
    const errorBox = document.getElementById("esignError");
    const submitBtn = document.getElementById("esignSubmit");
    const fail = (text) => { errorBox.textContent = text; errorBox.hidden = false; submitBtn.disabled = false; };
    const showDone = () => {
        const tpl = document.getElementById("esignDoneTpl");
        document.getElementById("esignBody").innerHTML = tpl.innerHTML;
        window.scrollTo({ top: 0 });
    };

    form.addEventListener("submit", (e) => {
        e.preventDefault();
        errorBox.hidden = true;
        const video = form.querySelector('input[name="video_consent"]').checked;
        const decl = form.querySelector('input[name="declaration"]').checked;
        if (!video || !decl) return fail("Please tick both consents.");
        if (!signatureInput.value) return fail("Please add your signature.");
        if (!navigator.geolocation) return fail("Your browser cannot share your location, which the signature needs. Please open this link in Chrome or Safari.");
        submitBtn.disabled = true;

        const send = (pos) => {
            const data = new FormData(form);
            data.set("video_consent", "1"); data.set("declaration", "1");
            data.append("latitude", pos.coords.latitude); data.append("longitude", pos.coords.longitude);
            axios.post(route("applicant.e.signature.store", applicantId), data, { headers })
                .then(showDone)
                .catch((err) => {
                    if (err.response && err.response.status === 422 && err.response.data && err.response.data.errors) {
                        fail(Object.values(err.response.data.errors).flat().join(" "));
                    } else {
                        fail("Something went wrong and the signature was not saved. Please try again.");
                    }
                });
        };
        /* the fix taken when the page opened is good enough; otherwise ask now */
        if (lastPos) { send(lastPos); return; }
        locate(send, (err) => {
            submitBtn.disabled = false;
            explainLoc(err, true);
        }, waitingLoc);
    });
})();
