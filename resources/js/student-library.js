import { createIcons, icons } from "lucide";

/**
 * Student library: catalogue search against Operations, and the borrow /
 * renew / cancel actions on the student's own loans.
 */
(function () {
    const page = document.getElementById("studentLibrary");
    if (!page) return;

    const searchUrl = page.dataset.searchUrl;
    const borrowUrl = page.dataset.borrowUrl;
    const csrf = () => $('meta[name="csrf-token"]').attr("content");

    const $input = $("#libSearch");
    const $availability = $("#libAvailability");
    const $venue = $("#libVenue");
    const $course = $("#libCourse");
    const $module = $("#libModule");
    const $results = $("#libResults");
    const $resultsWrap = $("#libResultsWrap");
    const $count = $("#libCount");

    // The panel only exists once there is something to show.
    const showResults = (on) => $resultsWrap.prop("hidden", !on);

    /* Titles this student is already holding, as { titleId: status }. The server
       rejects a duplicate borrow regardless; this stops the card inviting one. */
    let held = {};
    try {
        held = JSON.parse(page.dataset.heldTitles || "{}") || {};
    } catch (e) {
        held = {};
    }

    const heldLabel = (status) =>
        status === "issued" ? "You have this" : "Already reserved";

    const esc = (v) => $("<div>").text(v == null ? "" : v).html();
    const money = (v) => "£" + Number(v || 0).toFixed(2);

    const notice = (text, tone, icon) =>
        `<div class="slib-note ${tone === "bad" ? "slib-note--bad" : ""}">` +
        (icon ? `<i data-lucide="${icon}"></i>` : "") +
        `${esc(text)}</div>`;

    /* Cover with its placeholder behind it: a missing or unreadable file just
       reveals what is underneath, and every card keeps the same footprint. A
       book icon rather than letters — two characters off a title reads as
       noise. */
    const cover = (book) => `
        <span class="slib-cover">
            <i data-lucide="book"></i>
            ${book.image_url ? `<img src="${esc(book.image_url)}" alt="" loading="lazy" onerror="this.remove()">` : ""}
        </span>`;

    function card(book) {
        const available = Number(book.available_copies || 0);
        const total = Number(book.total_copies || 0);
        const blocked = !!page.dataset.blockedReason;
        const mine = held[book.id];
        const canBorrow = available > 0 && !blocked && !mine;

        const meta = [
            book.edition,
            book.isbn13 ? "ISBN " + book.isbn13 : null,
            book.publication_date ? String(book.publication_date).slice(0, 4) : null,
        ].filter(Boolean);

        return `
        <div class="slib-card">
            <div class="slib-card__top">
                ${cover(book)}
                <div class="slib-card__copy">
                    <div class="slib-card__title" title="${esc(book.title)}">${esc(book.title)}</div>
                    <div class="slib-card__author">${esc(book.author || "Unknown author")}</div>
                    <div class="slib-card__meta">${esc(meta.join(" · "))}</div>
                    <span class="slib-pill ${available > 0 ? "slib-pill--in" : "slib-pill--out"}">
                        ${available > 0 ? available + " of " + total + " available" : "All " + total + " out"}
                    </span>
                </div>
            </div>
            <div class="slib-card__foot">
                <span class="slib-card__where" title="${esc((book.campuses || []).join(" · "))}">${
                    book.campuses && book.campuses.length ? esc(book.campuses.join(" · ")) : "&nbsp;"
                }</span>
                <button class="slib-borrow libBorrow"
                        data-id="${esc(book.id)}"
                        data-title="${esc(book.title)}"
                        data-author="${esc(book.author || "")}"
                        data-where="${esc((book.campuses || []).join(" · "))}"
                        ${canBorrow ? "" : "disabled"}
                        ${
                            mine
                                ? `title="Return it before borrowing this title again."`
                                : blocked
                                ? `title="${esc(page.dataset.blockedReason)}"`
                                : ""
                        }>
                    <i data-lucide="${mine ? "check" : available > 0 ? "book-plus" : "circle-slash"}"></i>
                    ${mine ? heldLabel(mine) : available > 0 ? "Reserve" : "Unavailable"}
                </button>
            </div>
        </div>`;
    }

    // The last set of results, keyed by id: a button can only carry an id, but
    // the dialog wants the whole record.
    let found = {};

    function render(payload) {
        $count.text(payload.total ? payload.total + " title" + (payload.total === 1 ? "" : "s") : "");

        if (!payload.data.length) {
            $results.html(notice("No books matched that search.", "", "search-x"));
            return;
        }
        found = {};
        payload.data.forEach((b) => (found[b.id] = b));

        $results.html('<div class="slib-grid">' + payload.data.map(card).join("") + "</div>");
        createIcons({ icons, "stroke-width": 1.5, nameAttr: "data-lucide" });
    }

    let pending = null;
    function search() {
        const criteria = {
            q: $.trim($input.val()),
            availability: $availability.val(),
            venue: $venue.val(),
            course: $course.val(),
            module: $module.val(),
        };

        const hasAny = Object.keys(criteria).some((k) => criteria[k]);

        if (!hasAny) {
            $count.text("");
            $results.empty();
            showResults(false);
            return;
        }

        showResults(true);
        $results.html(notice("Searching…", "", "loader"));

        // One search in flight at a time: results arriving out of order would
        // leave the list showing an older query than the form.
        if (pending) pending.abort();
        pending = $.ajax({
            url: searchUrl,
            data: criteria,
            success: (res) => render(res),
            error: (xhr) =>
                $results.html(
                    notice(
                        xhr.responseJSON && xhr.responseJSON.message
                            ? xhr.responseJSON.message
                            : "The catalogue could not be reached.",
                        "bad",
                        "wifi-off"
                    )
                ),
        });
    }

    let typing = null;

    /* `input` rather than `keyup`: a paste from the mouse or context menu, an
       autofill, a cut, or the field being cleared all change the value without
       any key being released, and none of them fired a keyup. */
    $input.on("input", function () {
        clearTimeout(typing);
        typing = setTimeout(search, 300);
    });

    // Enter should not wait out the debounce.
    $input.on("keydown", function (e) {
        if (e.key === "Enter") {
            e.preventDefault();
            clearTimeout(typing);
            search();
        }
    });

    $availability.add($venue).add($course).add($module).on("change", search);

    /* A value can already be there on load — restored by the browser on a back
       navigation, or autofilled — and would otherwise sit in an empty page
       until the field was touched. */
    if ($.trim($input.val()) || $availability.val() || $venue.val() || $course.val() || $module.val()) {
        search();
    }

    $("#libReset").on("click", function () {
        clearTimeout(typing);
        $input.val("");
        $availability.add($venue).add($course).add($module).val("");
        search(); // nothing set, so this collapses the panel
    });

    /* ---- actions ---- */

    function post(url, data, $btn, done) {
        const label = $btn.html();
        $btn.attr("disabled", "disabled").text("Please wait…");

        $.ajax({
            method: "POST",
            url: url,
            data: data,
            headers: { "X-CSRF-TOKEN": csrf() },
            success: (res) => done(res),
            error: (xhr) => {
                $btn.removeAttr("disabled").html(label);
                alert(
                    xhr.responseJSON && xhr.responseJSON.message
                        ? xhr.responseJSON.message
                        : "That could not be completed. Please try again."
                );
            },
        });
    }

    /* Confirmation before anything is reserved: booking takes a copy off the
       shelf for other students, so it should never happen on a stray click. */
    const confirmModal = document.getElementById("libConfirmModal");
    let pendingBorrow = null;

    const closeConfirm = () => {
        if (confirmModal) confirmModal.hidden = true;
    };

    const facts = (pairs) =>
        pairs
            .filter(([, v]) => v !== null && v !== undefined && v !== "")
            .map(([k, v]) => `<dt>${esc(k)}</dt><dd>${esc(v)}</dd>`)
            .join("");

    /* Pickup points for one title.

       The search payload carries campus names only, so the copies come from
       the title endpoint — each one reports its own campus, shelf and status.
       Only `available` copies are offered: a campus holding nothing but books
       already on loan is not somewhere the student can collect from.

       Falls back to the campus names already on the card if that call fails,
       because a dialog that cannot be completed is worse than one offering a
       coarser choice. */
    function fillLocations(book) {
        const $pick = $("#libConfirmLocation");

        if (!$pick.length) return;

        const fallback = (book.campuses || []).map((c) => ({ campus: c, location: "", n: 0 }));

        const render = (points) => {
            if (!points.length) {
                $pick.html(`<option value="">The library desk</option>`);
                return;
            }

            $pick.html(
                points
                    .map((p) => {
                        const label =
                            esc(p.campus) +
                            (p.location ? " — " + esc(p.location) : "") +
                            (p.n ? " (" + p.n + " on the shelf)" : "");

                        return `<option value="${esc(p.campus)}" data-location="${esc(p.location)}">${label}</option>`;
                    })
                    .join("")
            );
        };

        render(fallback);

        $.getJSON(route("students.library.title", book.id))
            .done((res) => {
                const copies = (res && res.data && res.data.copies) || [];

                /* One option per shelf, not per copy: nine copies on one shelf
                   is one place to walk to, and listing each barcode would make
                   the student choose between things that are the same choice. */
                const byShelf = {};

                copies
                    .filter((c) => String(c.status).toLowerCase() === "available")
                    .forEach((c) => {
                        const key = (c.campus || "") + "|" + (c.location || "");
                        byShelf[key] = byShelf[key] || { campus: c.campus || "", location: c.location || "", n: 0 };
                        byShelf[key].n += 1;
                    });

                const points = Object.values(byShelf).sort((a, b) => b.n - a.n);

                if (points.length) render(points);
            })
            .fail(() => {
                /* Leave the fallback showing. */
            });
    }

    function openConfirm(book) {
        const available = Number(book.available_copies || 0);
        const total = Number(book.total_copies || 0);

        $("#libConfirmCover").html(
            `<i data-lucide="book"></i>` +
                (book.image_url
                    ? `<img src="${esc(book.image_url)}" alt="" loading="lazy" onerror="this.remove()">`
                    : "")
        );

        $("#libConfirmTitle").text(book.title || "");
        $("#libConfirmAuthor").text(book.author || "Unknown author");
        fillLocations(book);

        $("#libConfirmPill").html(
            `<span class="slib-pill ${available > 0 ? "slib-pill--in" : "slib-pill--out"}">
                ${available > 0 ? available + " of " + total + " available" : "All " + total + " out"}
             </span>`
        );

        $("#libConfirmFacts").html(
            facts([
                ["Publisher", book.publisher],
                ["ISBN", book.isbn13 || book.isbn10],
                ["Edition", book.edition],
                ["Published", book.publication_date],
                ["Language", book.language],
                ["Pages", book.pages],
                ["Price", book.price ? money(book.price) : null],
            ])
        );

        $("#libConfirmError").prop("hidden", true).text("");
        $("#libConfirmGo").prop("disabled", false).find("span").remove();

        confirmModal.hidden = false;
        createIcons({ icons, "stroke-width": 1.5, nameAttr: "data-lucide" });
    }

    $results.on("click", ".libBorrow", function () {
        const $btn = $(this);
        const book = found[$btn.data("id")];

        if (held[$btn.data("id")]) return;

        pendingBorrow = { id: $btn.data("id"), $btn: $btn };

        // No dialog markup on the page: confirm in the browser rather than
        // reserving a copy on an unconfirmed click.
        if (!confirmModal || !book) {
            if (window.confirm("Reserve \"" + ($btn.data("title") || "this book") + "\"?")) doBorrow();
            return;
        }

        openConfirm(book);
    });

    function doBorrow() {
        if (!pendingBorrow) return;
        const { id, $btn } = pendingBorrow;
        const $go = $("#libConfirmGo");

        $go.prop("disabled", true);

        $.ajax({
            method: "POST",
            url: borrowUrl,
            data: {
                title_id: id,
                campus: $("#libConfirmLocation").val() || "",
                /* The shelf the student picked. Operations still chooses the
                   actual copy, so the loan records where that copy really came
                   from — this only says which shelf to prefer. */
                location: $("#libConfirmLocation").find(":selected").data("location") || "",
            },
            headers: { "X-CSRF-TOKEN": csrf() },
            success: () => window.location.reload(),
            error: (xhr) => {
                $go.prop("disabled", false);
                $("#libConfirmError")
                    .prop("hidden", false)
                    .text(
                        xhr.responseJSON && xhr.responseJSON.message
                            ? xhr.responseJSON.message
                            : "That could not be reserved. Please try again."
                    );
                if ($btn) $btn.prop("disabled", false);
            },
        });
    }

    $(document).on("click", "#libConfirmGo", doBorrow);
    $(confirmModal).on("click", "[data-slib-close]", closeConfirm);
    $(confirmModal).on("click", function (e) {
        // Backdrop, not the card.
        if (e.target === confirmModal) closeConfirm();
    });
    $(document).on("keydown", (e) => {
        if (e.key !== "Escape") return;
        closeConfirm();
        closeCancel();
    });

    $(document).on("click", ".libRenew", function () {
        const $btn = $(this);
        post(route("students.library.renew", $btn.data("id")), {}, $btn, (res) => {
            alert(res.message);
            window.location.reload();
        });
    });

    /* Cancelling is confirmed in the page's own dialog. A browser confirm()
       is unstyled, unbrandable and — on the reserve side — already replaced,
       so the two destructive-ish actions on this page behaved differently. */
    const cancelModal = document.getElementById("libCancelModal");
    let pendingCancel = null;

    const closeCancel = () => {
        if (cancelModal) cancelModal.hidden = true;
        pendingCancel = null;
    };

    $(document).on("click", ".libCancel", function () {
        const $btn = $(this);

        pendingCancel = $btn;

        // No dialog markup on the page: fall back rather than silently doing
        // nothing when the button is pressed.
        if (!cancelModal) {
            if (window.confirm("Cancel this reservation?")) runCancel();
            return;
        }

        $("#libCancelTitle").text($btn.data("title") || "this book");
        $("#libCancelWhere").text($btn.data("where") || "");
        $("#libCancelError").prop("hidden", true).text("");
        $("#libCancelGo").prop("disabled", false);

        cancelModal.hidden = false;
        createIcons({ icons, "stroke-width": 1.5, nameAttr: "data-lucide" });
    });

    function runCancel() {
        if (!pendingCancel) return;

        const $btn = pendingCancel;
        const $go = $("#libCancelGo");

        $go.prop("disabled", true);

        $.ajax({
            method: "POST",
            url: route("students.library.cancel", $btn.data("id")),
            headers: { "X-CSRF-TOKEN": csrf() },
            success: () => window.location.reload(),
            error: (xhr) => {
                $go.prop("disabled", false);
                $("#libCancelError")
                    .prop("hidden", false)
                    .text(
                        xhr.responseJSON && xhr.responseJSON.message
                            ? xhr.responseJSON.message
                            : "That could not be cancelled. Please try again."
                    );
            },
        });
    }

    $(document).on("click", "#libCancelGo", runCancel);
    $(document).on("click", "[data-slib-cancel-close]", closeCancel);
    $(cancelModal).on("click", function (e) {
        // Backdrop, not the card.
        if (e.target === cancelModal) closeCancel();
    });

    /* One click, one checkout. Paying leaves the page for PayPal, so the tab
       sits on a form that still looks submittable for as long as the redirect
       takes — and a second press starts a second order. The button is locked
       on the way out rather than relying on the student not pressing twice. */
    $(document).on("submit", "#studentLibrary form", function () {
        const btn = this.querySelector('button[type="submit"]');

        if (!btn) return;

        btn.disabled = true;
        btn.style.opacity = "0.6";
        btn.style.cursor = "wait";
    });
})();
