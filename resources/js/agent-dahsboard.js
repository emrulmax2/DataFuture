import Tabulator from "tabulator-tables";
import TomSelect from "tom-select";

/*
 * Agent dashboard.
 *
 * The applications list is a Tabulator grid on the same endpoint and
 * parameters as before (agent.dashboard.applications.list). The "new
 * applicants" column is drawn by one renderer from the list every check
 * endpoint returns (store / verify / resend / destroy all answer with the
 * agent's pending checks), so the page never reloads to catch up.
 */
(function () {
    "use strict";

    const csrf = { "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content") };
    const esc = (v) => $("<i></i>").text(v === null || v === undefined ? "" : String(v)).html();
    const TONE = { "In Progress": "amber", "Awating Decision": "blue", "Awaiting Decision": "blue", "Offer Placed": "blue", "Accepted": "green", "Offer Accepted": "green", "Rejected": "red", "Offer Rejected": "red", "Canceled": "red" };
    const pill = (label) => `<span class="apd-pill apd-pill--${TONE[label] || "muted"}"><i></i>${esc(label === "Awating Decision" ? "Awaiting Decision" : label)}</span>`;
    const fmt = (d) => {
        if (!d) return "—";
        const m = String(d).match(/^(\d{4})-(\d{2})-(\d{2})/) || String(d).match(/^(\d{2})-(\d{2})-(\d{4})/);
        if (!m) return esc(d);
        const dt = m[1].length === 4 ? new Date(+m[1], +m[2] - 1, +m[3]) : new Date(+m[3], +m[2] - 1, +m[1]);
        return dt.toLocaleDateString("en-GB", { day: "2-digit", month: "short", year: "numeric" });
    };
    const ICON = {
        pencil: '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 20h9M16.5 3.5a2.1 2.1 0 013 3L7 19l-4 1 1-4z"/></svg>',
        plus: '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 5v14M5 12h14"/></svg>',
        eye: '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M2 12s3.6-7 10-7 10 7 10 7-3.6 7-10 7S2 12 2 12z"/><circle cx="12" cy="12" r="3"/></svg>',
        ok: '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="m8.5 12.5 2.5 2.5 4.5-5"/></svg>',
        no: '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="m9 9 6 6M15 9l-6 6"/></svg>',
        x: '<svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M18 6 6 18M6 6l12 12"/></svg>',
        left: '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m15 18-6-6 6-6"/></svg>',
        right: '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m9 18 6-6-6-6"/></svg>',
    };

    /* ---------- dialogs ---------------------------------------------------- */
    const modal = (sel) => { const el = document.querySelector(sel); return el ? tailwind.Modal.getOrCreateInstance(el) : null; };
    const addModal = modal("#addDeteilsModal"), confirmModal = modal("#confirmModal"), deleteModal = modal("#confirmDeleteModal"), succModal = modal("#successModal"), errModal = modal("#errorModal");
    const showSuccess = (title, text) => { $("#successModal .successModalTitle").html(title); $("#successModal .successModalDesc").html(text); succModal && succModal.show(); };
    const showError = (title, text) => { $("#errorModal .errorModalTitle").html(title); $("#errorModal .errorModalDesc").html(text); errModal && errModal.show(); };

    /* ---------- the applications list -------------------------------------- */
    const chips = (sel) => $(sel + " .agp-chip.on").map(function () { return $(this).attr("data-value"); }).get();
    const tsVal = (sel) => { const el = document.querySelector(sel); const v = el && el.tomselect ? el.tomselect.getValue() : $(sel).val(); return Array.isArray(v) ? v.filter(Boolean) : (v ? [v] : []); };
    const applicantApplicantionList = (function () {
        let table = null, total = 0;
        /* "x–y of N" at the left of the footer, as the design has it. Tabulator
           4.9 has no page counter of its own, so the footer gets one here. */
        const counter = () => {
            const foot = table && table.element.querySelector(".tabulator-footer");
            if (!foot) return;
            let c = foot.querySelector(".tabulator-page-counter");
            if (!c) { c = document.createElement("span"); c.className = "tabulator-page-counter"; foot.insertBefore(c, foot.firstChild); }
            const size = table.getPageSize(), page = table.getPage() || 1;
            if (!total) { c.textContent = ""; return; }
            const from = size === true ? 1 : (page - 1) * size + 1, to = size === true ? total : Math.min(page * size, total);
            c.textContent = `${from}–${to} of ${total}`;
        };
        const build = () => {
            table = new Tabulator("#applicantApplicantionList", {
                ajaxURL: route("agent.dashboard.applications.list"),
                ajaxParams: {
                    refno: $("#application_no").val() || "",
                    querystr: $("#query-CNTR").val() || "",
                    email: $("#applicantEmail").val() || "",
                    phone: $("#applicantPhone").val() || "",
                    semesters: $("#semesters").val() ? [$("#semesters").val()] : [],
                    courses: $("#courses").val() ? [$("#courses").val()] : [],
                    statuses: chips("#statusChips"),
                    agents: tsVal("#agents"),
                },
                ajaxFiltering: true,
                ajaxSorting: true,
                pagination: "remote",
                paginationSize: 10,
                paginationSizeSelector: [10, 20, true],
                /* Tabulator writes these with innerHTML, so previous / next carry chevrons. */
                langs: { default: { pagination: { page_size: "Rows", all: "All", prev: ICON.left, next: ICON.right, prev_title: "Previous page", next_title: "Next page" } } },
                ajaxResponse: (url, params, res) => { total = Number(res && res.total) || 0; return res; },
                dataLoaded: () => counter(),
                /* As the design's `.dtable`: the grid scrolls sideways when the
                   panel is narrower than its columns, never hides them. */
                layout: "fitColumns",
                placeholder: "No applications match. Change the filters or reset the search.",
                columns: [
                    { title: "Ref", field: "application_no", width: 92, formatter: (c) => `<span class="apd-mono">${esc(c.getValue() || "—")}</span>` },
                    /* Name and Course wrap onto as many lines as they need, as the design's `td.wrap`. */
                    { title: "Name", field: "name", minWidth: 180, variableHeight: true, formatter: (c) => {
                        const d = c.getData();
                        return `<div class="agp-cell"><b>${esc(d.name)}</b><span class="agp-sub">${fmt(d.dob)}${d.gender ? " · " + esc(d.gender) : ""} · <span class="apd-mono">${esc(d.referral_code || "—")}</span></span></div>`;
                    } },
                    { title: "Course", field: "course", minWidth: 150, headerSort: false, variableHeight: true, formatter: (c) => {
                        const v = String(c.getValue() || ""); const i = v.lastIndexOf(" - ");
                        if (!v) return "—";
                        return i > 0 ? `<div class="agp-cell">${esc(v.slice(0, i))}<span class="agp-sub">${esc(v.slice(i + 3))}</span></div>` : esc(v);
                    } },
                    { title: "Submitted", field: "submission_date", width: 104, formatter: (c) => fmt(c.getValue()) },
                    { title: "Status", field: "status", width: 128, headerSort: false, formatter: (c) => pill(c.getValue() || "—") },
                    { title: "", field: "id", width: 96, headerSort: false, hozAlign: "right", formatter: (c) => {
                        const d = c.getData(); let b = '<div class="agp-acts">';
                        if (!d.submission_date) {
                            b += `<a href="${route("agent.application", d.applicationCheck)}" class="agp-iconbtn" title="Continue application" aria-label="Continue application ${esc(d.name)}">${ICON.pencil}</a>`;
                        } else if (d.new_apply == true) {
                            b += `<a href="${route("agent.application.create", d.applicant_user_id)}" class="agp-iconbtn" title="Start a new application" aria-label="Start a new application for ${esc(d.name)}">${ICON.plus}</a>`;
                            b += `<a href="${route("agent.application.show", d.id)}" class="agp-iconbtn" title="View application" aria-label="View application ${esc(d.application_no)}">${ICON.eye}</a>`;
                        } else {
                            b += `<a href="${route("agent.application.show", d.id)}" class="agp-iconbtn" title="View application" aria-label="View application ${esc(d.application_no)}">${ICON.eye}</a>`;
                        }
                        return b + "</div>";
                    } },
                ],
            });
        };
        const destroy = () => { if (table) { table.destroy(); table = null; } };
        return { init: () => { destroy(); build(); }, destroy };
    })();

    /* The list is not drawn on load: it appears on Search, and goes again -
       grid destroyed, intro back - on Reset or when the search box is hidden. */
    const showList = () => { $("#agpListIntro").hide(); $(".agp-dtable").show(); applicantApplicantionList.init(); };
    const hideList = () => { applicantApplicantionList.destroy(); $(".agp-dtable").hide(); $("#agpListIntro").show(); };

    $("#studentGroupSearchSubmitBtn").on("click", showList);
    $("#agpResetBtn").on("click", () => {
        $("#application_no, #query-CNTR, #applicantEmail, #applicantPhone").val("");
        ["#semesters", "#courses", "#agents"].forEach((s) => { const el = document.querySelector(s); if (el && el.tomselect) el.tomselect.clear(); else $(s).val(""); });
        $(".agp-chip").removeClass("on");
        hideList();
    });
    $("#agpSearchbox").on("keydown", "input", (e) => { if (e.key === "Enter") { e.preventDefault(); showList(); } });
    $(document).on("click", ".agp-chip", function () { $(this).toggleClass("on"); });
    const toggleSearch = (open) => {
        $("#agpSearchbox").toggle(open);
        $("#agpFilterToggle").attr("aria-expanded", String(open)).find("span").text(open ? "Hide search" : "Search & filter");
        if (!open) hideList();
    };
    $("#agpFilterToggle").on("click", () => toggleSearch(!$("#agpSearchbox").is(":visible")));
    $("#agpIntroSearch").on("click", () => { toggleSearch(true); $("#application_no").trigger("focus"); });

    const tomOptions = { plugins: { dropdown_input: {} }, create: false, allowEmptyOption: true, dropdownParent: "body", dropdownClass: "ts-dropdown lcc-tom-float" };
    ["#semesters", "#courses"].forEach((s) => { const el = document.querySelector(s); if (el) new TomSelect(el, { ...tomOptions, placeholder: el.getAttribute("data-placeholder") || "Please select" }); });
    /* Agent / sub-agents: a tag selector - each pick becomes a removable tag in the control. */
    const agentsEl = document.querySelector("#agents");
    if (agentsEl) new TomSelect(agentsEl, { plugins: { remove_button: { title: "Remove" } }, create: false, hideSelected: true, closeAfterSelect: false, dropdownParent: "body", dropdownClass: "ts-dropdown lcc-tom-float", placeholder: agentsEl.getAttribute("data-placeholder") || "All agents" });

    /* ---------- the new-applicants column ---------------------------------- */
    let CHECKS = [];
    const $list = $("#applicant-list");
    const applyUrl = (id) => ($list.attr("data-apply-url") || "").replace("__ID__", id);
    const flag = (ok, yes, no) => `<span class="agp-flag ${ok ? "ok" : "no"}">${ok ? ICON.ok : ICON.no}${ok ? yes : no}</span>`;

    const card = (c) => {
        const ready = !!(c.email_verified_at && c.mobile_verified_at);
        return `<div class="agp-chk-card" data-id="${c.id}">
            <button type="button" class="agp-chk-card__rm delete_btn" data-id="${c.id}" title="Remove this applicant" aria-label="Remove ${esc(c.first_name)}">${ICON.x}</button>
            <div style="display:grid;gap:6px;min-width:0">
                <b>${esc(c.first_name)} ${esc(c.last_name)}</b>
                ${flag(!!c.email_verified_at, "Email verified", "Email not verified")}
                ${flag(!!c.mobile_verified_at, "Mobile verified", "Mobile not verified")}
            </div>
            ${ready
                ? `<a href="${applyUrl(c.id)}" class="apd-btn apd-btn--primary apd-btn--sm">Apply now</a>`
                : `<button type="button" class="apd-btn apd-btn--ghost apd-btn--sm newapplicant-modal" data-id="${c.id}">Verify</button>`}
        </div>`;
    };

    /* A five-card feed: the column is exactly as tall as its first five cards
       and the rest scroll in (the sheet hides the scrollbar). */
    const PAGE = 5;
    const fitFeed = (el) => {
        if (!el) return;
        const cards = el.querySelectorAll(".agp-chk-card");
        if (cards.length <= PAGE || !el.offsetParent) { el.style.maxHeight = ""; return; }
        el.style.maxHeight = Math.ceil(cards[PAGE - 1].getBoundingClientRect().bottom - el.getBoundingClientRect().top + el.scrollTop) + "px";
    };
    const nearEnd = (el) => el.scrollTop + el.clientHeight >= el.scrollHeight - 80;
    const debounce = (fn, ms) => { let t; return function (...a) { clearTimeout(t); t = setTimeout(() => fn.apply(this, a), ms); }; };

    /* The checks column: every check is already in CHECKS, so "loading" the
       next five is just drawing them, and the search box filters in place -
       by name, phone or email. */
    let shown = 0, checkQuery = "";
    const matches = (c) => !checkQuery || [`${c.first_name || ""} ${c.last_name || ""}`, c.email, c.mobile].some((v) => String(v || "").toLowerCase().includes(checkQuery));
    const visibleChecks = () => CHECKS.filter(matches);
    const applicantionCustonList = {
        init(data) {
            CHECKS = Array.isArray(data) ? data : [];
            const waiting = CHECKS.filter((c) => !(c.email_verified_at && c.mobile_verified_at)).length;
            $("#awaiting-count").text(waiting);
            this.draw();
        },
        /* (re)draws the feed for the current search */
        draw() {
            shown = 0;
            $list.scrollTop(0).css("max-height", "");
            if (!CHECKS.length) {
                $list.html('<div class="agp-empty agp-empty--small"><span>Nobody waiting. Use <b>Add new applicant</b> to start.</span></div>');
                return;
            }
            const rows = visibleChecks();
            if (!rows.length) {
                $list.html('<div class="agp-empty agp-empty--small"><span>No one matches that search.</span></div>');
                return;
            }
            $list.html("");
            /* five in view plus one under the fold, so there is something to scroll into */
            this.more(rows.length > PAGE ? PAGE + 1 : PAGE);
            this.fit();
        },
        more(count = PAGE) {
            const next = visibleChecks().slice(shown, shown + count);
            if (!next.length) return;
            $list.append(next.map(card).join(""));
            shown += next.length;
        },
        fit() { fitFeed($list[0]); },
    };
    $list.on("scroll", function () { if (nearEnd(this)) applicantionCustonList.more(); });
    $("#agpChecksSearch").on("input", debounce(function () { checkQuery = String($(this).val() || "").trim().toLowerCase(); applicantionCustonList.draw(); }, 150));

    /* ---------- drafts in progress: paged in from the server --------------- */
    const drafts = (function () {
        const el = document.getElementById("agpDrafts");
        if (!el) return null;
        let offset = +el.dataset.offset || 0, more = el.dataset.more === "1", busy = false, query = "", seq = 0;
        const url = (id) => (el.dataset.continueUrl || "").replace("__ID__", id);
        const draw = (d) => `<div class="agp-chk-card"><div style="display:grid;gap:4px"><b>${esc(d.name)}</b><span style="font-size:12.5px;color:var(--muted)">Not yet submitted</span></div><a href="${url(d.check_id)}" class="apd-btn apd-btn--primary apd-btn--sm">Continue</a></div>`;
        /* `reset` starts over from the top - a new search - and replaces the
           list; otherwise the next page is appended. A reset that lands
           after a later one is dropped (seq). */
        const load = (reset = false) => {
            if (!reset && (busy || !more)) return;
            busy = true;
            const mine = ++seq;
            const params = { offset: reset ? 0 : offset, limit: reset ? PAGE + 1 : PAGE, q: query };
            $.getJSON(el.dataset.url, params)
                .done((r) => {
                    if (mine !== seq) return;
                    offset = +r.next_offset || params.offset + params.limit; more = !!r.has_more; busy = false;
                    const html = (r.data || []).map(draw).join("");
                    if (reset) {
                        el.scrollTop = 0; el.style.maxHeight = "";
                        $(el).html(html || `<div class="agp-empty agp-empty--small"><span>${query ? "No drafts match that search." : "No drafts in progress."}</span></div>`);
                    } else {
                        $(el).append(html);
                    }
                    fitFeed(el);
                    /* a page can come back short (drafts the wizard cannot open are
                       skipped): keep going until there is something to scroll into */
                    if (more && el.scrollHeight <= el.clientHeight + 8) load();
                })
                .fail(() => { if (mine === seq) busy = false; });
        };
        el.addEventListener("scroll", () => { if (nearEnd(el)) load(); });
        $("#agpDraftsSearch").on("input", debounce(function () { query = String($(this).val() || "").trim(); load(true); }, 200));
        fitFeed(el);
        if (more && el.querySelectorAll(".agp-chk-card").length <= PAGE) load();
        return { fit: () => fitFeed(el) };
    })();
    $(window).on("resize", () => { applicantionCustonList.fit(); if (drafts) drafts.fit(); });
    try { applicantionCustonList.init(JSON.parse(document.getElementById("agpChecks").textContent || "[]")); } catch (e) { applicantionCustonList.init([]); }

    /* ---------- verify dialog ----------------------------------------------- */
    const openVerify = (id) => {
        const c = CHECKS.find((x) => String(x.id) === String(id));
        if (!c) return;
        $("#verifyTitle").text(`Verify ${c.first_name} ${c.last_name}`);
        $("#confirmModal #horizontal-email").text(c.email);
        $("#confirmModal #horizontal-mobile").text(c.mobile);
        $("#confirmModal input.id").val(c.id);
        $("#confirmModal .resend-email, #confirmModal .resend-mobile, #confirmModal .save").attr("data-id", c.id);
        $("#confirmModal .acc__input-error").html("");
        $("#confirmModal input.agp-code").val("").removeClass("border-danger");
        const row = (form, done) => { $(form + " .agp-vrow__done").prop("hidden", !done); $(form + " .agp-vrow__entry").toggle(!done); };
        row("#verifyEmailForm", !!c.email_verified_at);
        row("#verifyMobileForm", !!c.mobile_verified_at);
        const ready = !!(c.email_verified_at && c.mobile_verified_at);
        $("#verifyApplyNow").prop("hidden", !ready).attr("href", applyUrl(c.id));
        confirmModal && confirmModal.show();
    };
    $(document).on("click", ".newapplicant-modal", function () { openVerify($(this).attr("data-id")); });

    const paintErrors = ($form, error) => {
        if (!error.response) { showError("Something went wrong", "Please try again."); return; }
        if (error.response.status === 422 && error.response.data.errors) {
            Object.entries(error.response.data.errors).forEach(([key, val]) => {
                $form.find(`.${key}`).addClass("border-danger");
                $form.find(`.error-${key}`).html(Array.isArray(val) ? val[0] : val);
            });
            return;
        }
        showError("Something went wrong", (error.response.data && error.response.data.message) || "Please try again.");
    };
    const busy = ($btn, on) => { $btn.prop("disabled", on); $btn.find("svg").css("display", on ? "inline-block" : ""); };

    /* add applicant: the codes go out on save, then the verify dialog opens */
    $("#addStudentDetailsForm").on("submit", function (e) {
        e.preventDefault();
        const $form = $(this), $btn = $("#savePD");
        $form.find(".acc__input-error").html(""); $form.find(".border-danger").removeClass("border-danger");
        busy($btn, true);
        const email = $form.find('input[name="email"]').val();
        axios.post($form.find('input[name="url"]').val(), new FormData(this), { headers: csrf }).then((r) => {
            busy($btn, false);
            addModal && addModal.hide();
            $form[0].reset();
            applicantionCustonList.init(r.data);
            applicantApplicantionList.init();
            const created = CHECKS.find((c) => c.email === email) || CHECKS[CHECKS.length - 1];
            showSuccess("Codes sent", "An SMS code went to the applicant's mobile and an email code to their inbox. Enter both to verify.");
            if (created) { setTimeout(() => { succModal && succModal.hide(); openVerify(created.id); }, 1400); }
        }).catch((err) => { busy($btn, false); paintErrors($form, err); });
    });

    /* verify a code: each row is its own form posting to its own endpoint */
    $("#verifyEmailForm, #verifyMobileForm").on("submit", function (e) {
        e.preventDefault();
        const $form = $(this), $btn = $form.find(".save"), id = $form.find("input.id").val();
        $form.find(".acc__input-error").html(""); $form.find(".border-danger").removeClass("border-danger");
        if (!$form.find("input.agp-code").val()) { $form.find(".acc__input-error").html("Enter the code."); return; }
        busy($btn, true);
        axios.post($form.find('input[name="url"]').val(), new FormData(this), { headers: csrf }).then((r) => {
            busy($btn, false);
            applicantionCustonList.init(r.data);
            const c = CHECKS.find((x) => String(x.id) === String(id));
            if (c && c.email_verified_at && c.mobile_verified_at) {
                confirmModal && confirmModal.hide();
                showSuccess("Both verified", "You can start the application for this applicant now.");
            } else {
                openVerify(id);
            }
        }).catch((err) => { busy($btn, false); paintErrors($form, err); });
    });

    /* resend a code */
    $(document).on("click", ".resend-email, .resend-mobile", function () {
        const $b = $(this), type = $b.hasClass("resend-email") ? "email" : "mobile", id = $b.attr("data-id");
        $b.prop("disabled", true);
        const fd = new FormData(); fd.append("type", type); fd.append("id", id);
        axios.post(route("agent.apply.update", id), fd, { headers: csrf }).then((r) => {
            $b.prop("disabled", false);
            applicantionCustonList.init(r.data);
            showSuccess(type === "email" ? "Email sent" : "SMS sent", type === "email" ? "A new email code is on its way." : "A new SMS code is on its way.");
        }).catch((err) => { $b.prop("disabled", false); paintErrors($b.closest("form"), err); });
    });

    /* remove a pending applicant */
    $(document).on("click", ".delete_btn", function () {
        $("#confirmDeleteModal .agreeWith").attr("data-id", $(this).attr("data-id"));
        deleteModal && deleteModal.show();
    });
    $("#confirmDeleteModal .agreeWith").on("click", function () {
        const id = $(this).attr("data-id");
        $("#confirmDeleteModal button").prop("disabled", true);
        axios.delete(route("agent.apply.destory", id), { headers: csrf }).then((r) => {
            $("#confirmDeleteModal button").prop("disabled", false);
            deleteModal && deleteModal.hide();
            applicantionCustonList.init(r.data);
            showSuccess("Applicant removed", "Their pending record and any unsubmitted draft have been deleted.");
        }).catch(() => { $("#confirmDeleteModal button").prop("disabled", false); showError("Something went wrong", "The applicant could not be removed."); });
    });
})();
