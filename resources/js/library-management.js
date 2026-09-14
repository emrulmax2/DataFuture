import Tabulator from "tabulator-tables";
import { createIcons, icons } from "lucide";

/**
 * Library Management — the issue desk.
 *
 * Two parts: the day-reading panel (two lookups that must both resolve before
 * a book can be handed over) and the Tabulator grid of issues.
 *
 * The grid renders what the server computed. Fines, overdue state and status
 * wording all follow the Library Management settings, and re-deriving them here
 * is how the desk and the student portal end up disagreeing about whether a
 * book is late.
 */
(function () {
    const page = document.getElementById("libraryDesk");
    if (!page) return;

    const esc = (v) => $("<div>").text(v == null ? "" : v).html();
    const csrf = () => $('meta[name="csrf-token"]').attr("content");
    const refreshIcons = () => createIcons({ icons, "stroke-width": 1.5, nameAttr: "data-lucide" });


    /**
     * A cover or avatar, with initials behind it.
     *
     * The API already drops covers a browser cannot draw (137 of them are PDFs),
     * but a file can still be missing or moved — so a URL that fails to load is
     * swapped for the placeholder rather than left as a broken icon.
     */
    /**
     * A cover or avatar with its placeholder behind it.
     *
     * The placeholder is always in the DOM and the image is layered over it, so
     * a broken image only has to remove itself. Building the replacement markup
     * inside an `onerror` attribute does not work: that markup contains its own
     * double quotes, which close the attribute early and leak the rest onto the
     * page as text.
     *
     * A cover falls back to a book icon rather than letters — two characters off
     * a title reads as noise ("15" for "150 Leading Cases"). People still get
     * initials, which is what an avatar is for.
     */
    const thumb = (url, fallback, cls) => {
        const isCover = cls.indexOf("lib-media__cover") === 0;
        const inner = isCover
            ? '<i data-lucide="book"></i>'
            : esc(String(fallback || "?").slice(0, 2).toUpperCase());

        const img = url ? `<img src="${esc(url)}" alt="" loading="lazy" onerror="this.remove()">` : "";

        return `<span class="${cls} ${cls}--none">${inner}${img}</span>`;
    };

    /* ------------------------------------------------------------------ */
    /* Grid                                                                */
    /* ------------------------------------------------------------------ */

    const gridEl = document.getElementById("libraryIssuesTable");

    if (gridEl) {
        const bookCell = (cell) => {
            const r = cell.getRow().getData();
            return `
                <div class="lib-media">
                    ${thumb(r.cover_url, r.title, "lib-media__cover")}
                    <div class="lib-media__copy">
                        <div class="lib-media__title">${esc(r.title)}${
                            r.is_day_reading ? '<span class="lib-badge">Day reading</span>' : ""
                        }</div>
                        <div class="lib-media__sub">${esc(r.author || "")}</div>
                        ${r.barcode ? `<div class="lib-media__sub">Barcode ${esc(r.barcode)}</div>` : ""}
                    </div>
                </div>`;
        };

        const studentCell = (cell) => {
            const r = cell.getRow().getData();
            return `
                <div class="lib-media">
                    ${thumb(r.student_photo, r.student_initials, "lib-media__avatar")}
                    <div class="lib-media__copy">
                        <div class="lib-media__title">${esc(r.student_name || "—")}</div>
                        <div class="lib-media__sub">${esc(r.registration_no || "")}</div>
                    </div>
                </div>`;
        };

        const whereCell = (cell) => {
            const r = cell.getRow().getData();
            return `<div class="lib-media__sub">${esc(r.campus || "—")}${
                r.location ? `<div>${esc(r.location)}</div>` : ""
            }</div>`;
        };

        const datesCell = (cell) => {
            const r = cell.getRow().getData();

            if (r.status === "requested") {
                return `<div class="lib-dates">Booked ${esc(r.booked_at)}
                        <div class="lib-dates__sub">Hold ends ${esc(r.expires_at)}</div></div>`;
            }

            return `<div class="lib-dates">
                ${r.issued_at ? "Issued " + esc(r.issued_at) : ""}
                ${
                    r.due_at
                        ? `<div class="${r.overdue ? "lib-dates__late" : "lib-dates__sub"}">Due ${esc(r.due_at)}</div>`
                        : ""
                }
                ${r.returned_at ? `<div class="lib-dates__sub">Back ${esc(r.returned_at)}</div>` : ""}
            </div>`;
        };

        /* Colour carries the meaning, so each state gets its own tone rather
           than sharing one grey: amber while the desk is waiting on someone,
           indigo while a book is out, green once it is back, red when it is
           late, and grey for the ones that ended without a loan. */
        const STATUS = {
            requested: ["Awaiting collection", "lib-status--wait"],
            issued: ["On loan", "lib-status--out"],
            returned: ["Returned", "lib-status--done"],
            cancelled: ["Cancelled", "lib-status--void"],
            not_collected: ["Not collected", "lib-status--void"],
        };

        const statusCell = (cell) => {
            const r = cell.getRow().getData();
            let [label, cls] = STATUS[r.status] || [r.status, "lib-status--void"];
            if (r.status === "issued" && r.overdue) [label, cls] = ["Overdue", "lib-status--late"];

            /* The charge belongs inside the pill: as a separate figure alongside
               it, it read as its own column and left "Overdue" looking like the
               whole story. */
            const fine = r.fine > 0 ? ` - <b class="lib-fine">£${r.fine.toFixed(2)}</b>` : "";

            return `<span class="lib-status ${cls}"><i></i>${esc(label)}${fine}</span>`;
        };

        /* All three rest in the same quiet grey and take their colour from the
           hover: green for handing a book over or taking it back, red for
           cancelling, blue for reading the history. An overdue return is the
           one exception — it is tinted at rest, because there is a charge to
           raise before the book goes back on the shelf. */
        const actionCell = (cell) => {
            const r = cell.getRow().getData();
            const buttons = [];

            if (r.status === "requested") {
                buttons.push(
                    `<button class="lib-act-btn lib-act-btn--ok lib-act" data-act="issue" data-id="${r.id}">
                        <i data-lucide="check"></i>Issue</button>`,
                    `<button class="lib-act-btn lib-act-btn--danger lib-act" data-act="cancel" data-id="${
                        r.id
                    }" data-ref="${esc(r.reference)}" data-student="${esc(r.student_name || "")}">
                        <i data-lucide="x-circle"></i>Cancel</button>`
                );
            } else if (r.status === "issued") {
                buttons.push(
                    `<button class="lib-act-btn lib-act ${
                        r.overdue ? "lib-act-btn--alert" : "lib-act-btn--ok"
                    }" data-act="return" data-id="${r.id}" data-ref="${esc(r.reference)}" data-fine="${r.fine}"
                        data-title="${esc(r.title)}" data-student="${esc(r.student_name || "")}">
                        <i data-lucide="corner-down-left"></i>Return</button>`
                );
            }

            if (r.logs && r.logs.length) {
                buttons.push(
                    `<button class="lib-act-btn lib-act-btn--info lib-act-btn--tight lib-history" data-id="${r.id}"
                             title="${r.logs.length} event${r.logs.length === 1 ? "" : "s"}">
                        <i data-lucide="history"></i>${r.logs.length}</button>`
                );
            }

            /* Wrapped rather than joined on a space: the buttons are inline-flex,
               so collapsed whitespace was all the gap they got. */
            return buttons.length
                ? '<div class="lib-acts">' + buttons.join("") + "</div>"
                : '<span class="lib-media__sub">—</span>';
        };

        let grid = null;

        const build = () => {
            if (grid) grid.destroy();

            grid = new Tabulator(gridEl, {
                ajaxURL: gridEl.dataset.url,
                ajaxParams: {
                    status: page.dataset.status,
                    type: page.dataset.type,
                    overdue: page.dataset.overdue,
                    q: page.dataset.q,
                },
                ajaxFiltering: true,
                ajaxSorting: true,
                pagination: "remote",
                paginationSize: 25,
                paginationSizeSelector: [10, 25, 50, 100],
                layout: "fitColumns",
                responsiveLayout: false,
                placeholder: "Nothing to show for these filters.",
                columns: [
                    { title: "Reference", field: "reference", width: 150, headerSort: false, cssClass: "lib-col-ref" },
                    { title: "Book", field: "title", minWidth: 260, widthGrow: 2, headerSort: false, formatter: bookCell },
                    { title: "Student", field: "student_name", minWidth: 200, widthGrow: 1.4, headerSort: false, formatter: studentCell },
                    { title: "Where", field: "campus", minWidth: 150, headerSort: false, formatter: whereCell },
                    { title: "Dates", field: "due_at", minWidth: 150, headerSort: false, formatter: datesCell },
                    { title: "Status", field: "status", minWidth: 150, headerSort: false, formatter: statusCell },
                    {
                        title: "Action",
                        field: "id",
                        minWidth: 250,
                        widthGrow: 1.2,
                        headerSort: false,
                        hozAlign: "right",
                        headerHozAlign: "right",
                        formatter: actionCell,
                    },
                ],
                renderComplete: refreshIcons,
                /* The head carries the record count, so it has to be told
                   whenever the grid reloads with a different filter. */
                dataLoaded(data) {
                    const total = this.getDataCount ? this.getDataCount() : (data || []).length;
                    $("#libRecordCount").text(total === 1 ? "1 record" : total + " records");
                },
            });
        };

        build();

        /* ---- row actions ---- */

        const ACTION_URL = {
            issue: gridEl.dataset.issueUrl,
            return: gridEl.dataset.returnUrl,
            cancel: gridEl.dataset.cancelUrl,
        };

        const submit = (action, id) => {
            const form = document.createElement("form");
            form.method = "POST";
            form.action = ACTION_URL[action].replace("__ID__", id);
            form.innerHTML = `<input type="hidden" name="_token" value="${csrf()}">`;
            document.body.appendChild(form);
            form.submit();
        };

        /* ---- return dialog ---- */

        const returnModal = document.getElementById("libReturnModal");
        const $returnForm = $("#libReturnForm");

        const closeReturn = () => {
            if (returnModal) returnModal.hidden = true;
        };

        function openReturn($btn) {
            const fine = Number($btn.data("fine")) || 0;

            $returnForm.attr("action", ACTION_URL.return.replace("__ID__", $btn.data("id")));
            $("#libReturnRef").text($btn.data("ref") || "");
            $("#libReturnBook").text($btn.data("title") || "");
            $("#libReturnStudent").text($btn.data("student") || "");
            $("#libReturnNote").val("");

            $("#libReturnCharge").prop("hidden", fine <= 0);
            $("#libReturnFine").text("£" + fine.toFixed(2));

            $("#libReturnGo").prop("disabled", false);

            returnModal.hidden = false;
            refreshIcons();
        }

        $returnForm.on("submit", () => $("#libReturnGo").prop("disabled", true));
        $(returnModal).on("click", "[data-lib-close]", closeReturn);
        $(returnModal).on("click", function (e) {
            if (e.target === returnModal) closeReturn();
        });

        /* ---- cancel dialog ---- */

        const cancelModal = document.getElementById("libCancelModal");
        const $cancelForm = $("#libCancelForm");
        const $cancelReason = $("#libCancelReason");
        const $cancelGo = $("#libCancelGo");

        const closeCancel = () => {
            if (cancelModal) cancelModal.hidden = true;
        };

        // Nothing is cancelled without a reason — the student is shown it.
        const syncCancelGo = () =>
            $cancelGo.prop("disabled", $.trim($cancelReason.val()).length === 0);

        function openCancel($btn) {
            $cancelForm.attr("action", ACTION_URL.cancel.replace("__ID__", $btn.data("id")));
            $("#libCancelRef").text($btn.data("ref") || "");
            $("#libCancelStudent").text($btn.data("student") || "the student");
            $("#libCancelError").prop("hidden", true).text("");
            $cancelReason.val("");
            $("#libCancelChips .lib-chip").removeClass("is-on");
            syncCancelGo();

            cancelModal.hidden = false;
            refreshIcons();
            $cancelReason.trigger("focus");
        }

        $("#libCancelChips").on("click", ".lib-chip", function () {
            const $chip = $(this);
            $("#libCancelChips .lib-chip").removeClass("is-on");
            $chip.addClass("is-on");
            $cancelReason.val($chip.data("reason"));
            syncCancelGo();
        });

        // Typing over a chip's text means the chip no longer describes it.
        $cancelReason.on("input", function () {
            const typed = $.trim($cancelReason.val());
            $("#libCancelChips .lib-chip").each(function () {
                $(this).toggleClass("is-on", $(this).data("reason") === typed);
            });
            syncCancelGo();
        });

        $cancelForm.on("submit", function (e) {
            if ($.trim($cancelReason.val()).length === 0) {
                e.preventDefault();
                $("#libCancelError").prop("hidden", false).text("Please give a reason before cancelling.");
                return;
            }
            $cancelGo.prop("disabled", true);
        });

        $(cancelModal).on("click", "[data-lib-close]", closeCancel);
        $(cancelModal).on("click", function (e) {
            if (e.target === cancelModal) closeCancel();
        });

        $(gridEl).on("click", ".lib-act", function () {
            const $b = $(this);
            const act = $b.data("act");

            if (act === "cancel") {
                if (cancelModal) {
                    openCancel($b);
                    return;
                }

                // No dialog markup: still refuse to cancel without a reason.
                const typed = window.prompt(`Why is ${$b.data("ref")} being cancelled?`);
                if (!typed || !typed.trim()) return;

                const form = document.createElement("form");
                form.method = "POST";
                form.action = ACTION_URL.cancel.replace("__ID__", $b.data("id"));
                form.innerHTML =
                    `<input type="hidden" name="_token" value="${csrf()}">` +
                    `<input type="hidden" name="cancel_reason">`;
                form.querySelector('[name="cancel_reason"]').value = typed.trim();
                document.body.appendChild(form);
                form.submit();
                return;
            }

            if (act === "return") {
                if (returnModal) {
                    openReturn($b);
                    return;
                }

                const fine = Number($b.data("fine")) || 0;
                const owed = fine > 0 ? ` £${fine.toFixed(2)} is owed.` : "";
                if (!confirm(`Mark ${$b.data("ref")} as returned?${owed}`)) return;
            }

            $b.attr("disabled", "disabled");
            submit(act, $b.data("id"));
        });

        /* ---- history dialog ---- */

        const modal = document.getElementById("libHistoryModal");

        const facts = (pairs) =>
            pairs
                .filter(([, v]) => v !== null && v !== undefined && v !== "")
                .map(([k, v]) => `<dt>${esc(k)}</dt><dd>${esc(v)}</dd>`)
                .join("");

        const openHistory = (r) => {
            $("#libHistoryTitle").text(r.title || "Library issue");
            $("#libHistoryRef").text(
                [r.reference, r.is_day_reading ? "Day reading" : "Take home"].filter(Boolean).join(" · ")
            );

            $("#libHistoryBook").html(`
                <div class="lib-party__label">Book</div>
                <div class="lib-media">
                    ${thumb(r.cover_url, r.title, "lib-media__cover lib-media__cover--lg")}
                    <div class="lib-media__copy">
                        <div class="lib-media__title">${esc(r.title)}</div>
                        <div class="lib-media__sub">${esc(r.author || "Unknown author")}</div>
                    </div>
                </div>
                <dl class="lib-party__facts">${facts([
                    ["Barcode", r.barcode],
                    ["Venue", r.campus],
                    ["Shelf", r.location],
                ])}</dl>`);

            $("#libHistoryStudent").html(`
                <div class="lib-party__label">Student</div>
                <div class="lib-media">
                    ${thumb(r.student_photo, r.student_initials, "lib-media__avatar lib-media__avatar--lg")}
                    <div class="lib-media__copy">
                        <div class="lib-media__title">${esc(r.student_name || "—")}</div>
                        <div class="lib-media__sub">${esc(r.registration_no || "")}</div>
                    </div>
                </div>
                <dl class="lib-party__facts">${facts([
                    ["Booked", r.booked_at],
                    ["Hold ends", r.status === "requested" ? r.expires_at : null],
                    ["Issued", r.issued_at],
                    ["Due", r.due_at],
                    ["Returned", r.returned_at],
                    ["Charge", r.fine > 0 ? "£" + r.fine.toFixed(2) : null],
                ])}</dl>`);

            $("#libHistoryTrail").html(
                (r.logs || [])
                    .map(
                        (l) => `
                        <li data-action="${esc(l.action)}">
                            <div class="lib-timeline__what">${esc(l.action)}</div>
                            <div class="lib-timeline__meta">
                                <span class="lib-timeline__when">${esc(l.when)}</span> &middot; ${esc(l.who)}
                            </div>
                            ${l.note ? `<div class="lib-timeline__note">${esc(l.note)}</div>` : ""}
                        </li>`
                    )
                    .join("") || '<li class="lib-timeline__meta">No history recorded.</li>'
            );

            modal.hidden = false;
            refreshIcons();
        };

        const closeHistory = () => {
            if (modal) modal.hidden = true;
        };

        /* History travels with the row data, so the dialog opens without a round
           trip — a desk that waits to answer "who cancelled this" stops asking. */
        $(gridEl).on("click", ".lib-history", function () {
            const row = grid.getRow($(this).data("id"));
            if (row && modal) openHistory(row.getData());
        });

        $(modal).on("click", "[data-lib-close]", closeHistory);
        $(modal).on("click", function (e) {
            // Click the backdrop, not the card.
            if (e.target === modal) closeHistory();
        });
        $(document).on("keydown", (e) => {
            if (e.key === "Escape") closeHistory();
        });
    }

    /* ------------------------------------------------------------------ */
    /* Day reading                                                         */
    /* ------------------------------------------------------------------ */

    const dayPanel = document.getElementById("dayReadingPanel");

    if (dayPanel) {
        /**
         * A combobox: closed it shows the current choice, open it is a search
         * box over a list of results.
         *
         * Replaces the field + results + chosen-card stack, which pushed the
         * next step further down the page with every interaction. Book and
         * student behave identically, so they share this.
         */
        function combo(opts) {
            const root = document.querySelector(opts.root);
            if (!root) return;

            const $root = $(root);
            const $trigger = $root.find(".lib-combo__trigger");
            const $panel = $root.find(".lib-combo__panel");
            const $input = $root.find(".lib-combo__input");
            const $list = $root.find(".lib-combo__list");
            const $id = $(opts.id);

            let found = {};
            let timer = null;
            let request = null;

            const open = () => {
                $panel.prop("hidden", false);
                $trigger.attr("aria-expanded", "true");
                $input.val("").trigger("focus");
                $list.html(`<div class="lib-combo__hint">${esc(opts.hint)}</div>`);
            };

            const close = () => {
                $panel.prop("hidden", true);
                $trigger.attr("aria-expanded", "false");
                if (request) request.abort();
            };

            function paint(item) {
                if (!item) {
                    $trigger
                        .find(".lib-combo__value")
                        .addClass("lib-combo__value--empty")
                        .html(`<i data-lucide="${opts.icon}"></i>${esc(opts.placeholder)}`);
                    $root.find(".lib-combo__clear").remove();
                } else {
                    $trigger.find(".lib-combo__value").removeClass("lib-combo__value--empty").html(opts.valueHtml(item));

                    if (!$root.find(".lib-combo__clear").length) {
                        $trigger.before(
                            '<button type="button" class="lib-combo__clear" aria-label="Change">&times;</button>'
                        );
                    }
                }
                refreshIcons();
            }

            function choose(id) {
                const item = found[id];
                if (!item) return;

                $id.val(id);
                paint(item);
                close();
                opts.onChange();
                $id.trigger("lib:chosen");
            }

            function clear() {
                $id.val("");
                paint(null);
                opts.onChange();
            }

            $trigger.on("click", () => ($panel.prop("hidden") ? open() : close()));
            $root.on("click", ".lib-combo__clear", (e) => {
                e.stopPropagation();
                clear();
            });

            $input.on("input", function () {
                const term = $.trim($input.val());
                clearTimeout(timer);

                if (term.length < 2) {
                    $list.html(`<div class="lib-combo__hint">${esc(opts.hint)}</div>`);
                    return;
                }

                $list.html('<div class="lib-combo__hint">Searching…</div>');

                timer = setTimeout(function () {
                    // One lookup in flight, so a slow earlier response cannot
                    // overwrite the list for what was typed most recently.
                    if (request) request.abort();

                    request = $.ajax({
                        url: opts.url,
                        data: { q: term },
                        success: (res) => {
                            const rows = opts.map(res);
                            found = {};
                            rows.forEach((r) => (found[r.id] = r));

                            $list.html(
                                rows.length
                                    ? rows.map(opts.rowHtml).join("")
                                    : '<div class="lib-combo__hint">Nothing found.</div>'
                            );
                            refreshIcons();
                        },
                        error: (xhr) => {
                            if (xhr.statusText === "abort") return;
                            $list.html(
                                `<div class="lib-combo__hint lib-combo__hint--bad">${esc(
                                    (xhr.responseJSON && xhr.responseJSON.message) || "Lookup failed."
                                )}</div>`
                            );
                        },
                    });
                }, 300);
            });

            $list.on("click", ".lib-combo__row", function () {
                choose($(this).data("id"));
            });

            // Clicking away or pressing Escape closes without choosing.
            $(document).on("mousedown", (e) => {
                if (!$panel.prop("hidden") && !root.contains(e.target)) close();
            });
            $root.on("keydown", (e) => {
                if (e.key === "Escape") {
                    close();
                    $trigger.trigger("focus");
                }
            });

            if (opts.nextInput) {
                $id.on("lib:chosen", () => $(opts.nextInput).trigger("focus"));
            }

            /* Cleared by an earlier step rather than by its own × — the trigger
               has to fall back to its placeholder or it keeps showing a choice
               that is no longer submitted. */
            $id.on("lib:reset", () => {
                close();
                paint(null);
            });

            paint(null);
        }

        const $submit = $("#drSubmit");

        /* Each step appears only once the one before it is answered. Undoing a
           step also clears everything after it — a book chosen for the previous
           student must not survive into the next issue. */
        function refresh() {
            const hasStudent = !!$("#drStudentId").val();
            const hasBook = !!$("#drTitleId").val();

            if (!hasStudent && hasBook) {
                $("#drTitleId").val("").trigger("lib:reset");
            }

            $("#drStepBook").prop("hidden", !hasStudent);
            $("#drStepIssue").prop("hidden", !(hasStudent && $("#drTitleId").val()));

            if (!$("#drTitleId").val()) $("#drNote").val("");

            $submit.prop("disabled", !($("#drTitleId").val() && hasStudent));
        }

        combo({
            root: "#drStudentCombo",
            id: "#drStudentId",
            url: dayPanel.dataset.studentsUrl,
            icon: "user",
            placeholder: "Name or registration number...",
            hint: "Type at least two characters.",
            onChange: refresh,
            nextInput: "#drBookCombo .lib-combo__trigger",
            map: (res) => res.data || [],
            rowHtml: (s) => `
                <button type="button" class="lib-combo__row" data-id="${esc(s.id)}" role="option">
                    ${thumb(s.photo_url, s.label, "lib-media__avatar")}
                    <span class="lib-media__copy">
                        <strong>${esc(s.label)}</strong>
                        <small>${esc(s.registration_no || "")}</small>
                    </span>
                </button>`,
            valueHtml: (s) => `
                ${thumb(s.photo_url, s.label, "lib-media__avatar")}
                <span class="lib-media__copy">
                    <strong>${esc(s.label)}</strong>
                    <small>${esc(s.registration_no || "")}</small>
                </span>`,
        });

        combo({
            root: "#drBookCombo",
            id: "#drTitleId",
            url: dayPanel.dataset.catalogueUrl,
            icon: "book",
            placeholder: "Title, author, ISBN or barcode...",
            hint: "Type at least two characters.",
            onChange: refresh,
            nextInput: "#drNote",
            // Only titles with a free copy can be handed over at the desk.
            map: (res) => (res.data || []).filter((b) => Number(b.available_copies) > 0),
            rowHtml: (b) => `
                <button type="button" class="lib-combo__row" data-id="${esc(b.id)}" role="option">
                    ${thumb(b.image_url, b.title, "lib-media__cover")}
                    <span class="lib-media__copy">
                        <strong>${esc(b.title)}</strong>
                        <small>${esc([b.author, b.publisher].filter(Boolean).join(" · ") || "Unknown author")}</small>
                        <small>${esc(
                            b.available_copies + " of " + b.total_copies + " free · " + (b.campuses || []).join(", ")
                        )}</small>
                    </span>
                </button>`,
            valueHtml: (b) => `
                ${thumb(b.image_url, b.title, "lib-media__cover")}
                <span class="lib-media__copy">
                    <strong>${esc(b.title)}</strong>
                    <small>${esc(
                        [b.author, b.isbn13, b.available_copies + " free"].filter(Boolean).join(" · ")
                    )}</small>
                </span>`,
        });

        // Nothing chosen yet, so only step 1 shows.
        refresh();

        // Guards a double submit: the second would hold a second copy.
        $("#dayReadingBody").on("submit", () => $submit.prop("disabled", true).text("Issuing…"));

        const $panelBody = $("#dayReadingBody");
        $("#dayReadingToggle").on("click", function () {
            const open = $panelBody.prop("hidden");
            $panelBody.prop("hidden", !open);
            $(this).attr("aria-expanded", open ? "true" : "false").toggleClass("is-open", open);
        });
    }

    refreshIcons();
})();
