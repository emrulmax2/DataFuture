/*
 * Presence on an applicant's record (layouts admission-portal / admission-top-menu).
 * Opens a view session when the page loads, heartbeats every 15 s while the tab is
 * visible, pauses on hide, resumes on show, and closes the session when the page is
 * left. Rows land in applicant_view_sessions (AdmissionPresenceController).
 */
(function () {
    "use strict";
    const root = document.querySelector("[data-adp-presence]");
    if (!root) return;
    const csrf = (document.querySelector('meta[name="csrf-token"]') || {}).content || "";
    const urls = { start: root.getAttribute("data-start-url"), beat: root.getAttribute("data-beat-url"), end: root.getAttribute("data-end-url") };
    const BEAT_MS = 15000;
    let id = null, timer = null, ended = false;

    const post = (url, data, viaBeacon) => {
        const fd = new FormData();
        fd.append("_token", csrf);
        Object.keys(data).forEach((k) => fd.append(k, data[k]));
        if (viaBeacon && navigator.sendBeacon) {
            navigator.sendBeacon(url, fd);
            return Promise.resolve(null);
        }
        return fetch(url, { method: "POST", body: fd, credentials: "same-origin", keepalive: true, headers: { "X-Requested-With": "XMLHttpRequest", Accept: "application/json" } })
            .then((r) => (r.ok ? r.json() : null))
            .catch(() => null);
    };
    const beat = (extra) => { if (!id || ended) return; post(urls.beat, Object.assign({ id }, extra || {})); };
    const end = () => {
        if (!id || ended) return;
        ended = true;
        clearInterval(timer);
        post(urls.end, { id }, true);
    };

    post(urls.start, {
        applicant_id: root.getAttribute("data-applicant"),
        route: root.getAttribute("data-route") || "",
        section: root.getAttribute("data-section") || "",
        url: window.location.href.slice(0, 500),
    }).then((res) => {
        if (!res || !res.id) return;
        id = res.id;
        timer = setInterval(() => { if (document.visibilityState === "visible") beat(); }, BEAT_MS);
    });

    document.addEventListener("visibilitychange", () => {
        if (!id || ended) return;
        if (document.visibilityState === "visible") beat({ resume: 1 }); /* restart the clock, credit nothing */
        else post(urls.beat, { id }, true);                              /* credit the time up to the hide */
    });
    window.addEventListener("pagehide", end);
    window.addEventListener("beforeunload", end);
})();
