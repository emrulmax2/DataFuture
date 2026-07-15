/**
 * HR daily attendance (hr/attendance/show/{timestamp}).
 *
 * Rostered shift vs what was actually recorded, one row per person, with the
 * editable fields in a drawer.
 *
 * IMPORTANT: hr.attendance.update stores whatever total_work_hour the browser
 * posts - the server does not recompute it. The arithmetic in recalcWork() IS the
 * payroll figure, and it is carried over unchanged from the old screen:
 *
 *     (clock out - clock in) - unpaid break - (break taken over its allowance)
 *                                           +/- the manual adjustment
 */
import { createIcons, icons } from "lucide";
import IMask from "imask";

(function () {
    const $page = $(".att-page");
    if (!$page.length) {
        return;
    }

    const successModal   = tailwind.Modal.getOrCreateInstance(document.querySelector("#successModal"));
    const warningModal   = tailwind.Modal.getOrCreateInstance(document.querySelector("#warningModal"));
    const viewBreakModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#viewBreakModal"));
    const confirmModal   = tailwind.Modal.getOrCreateInstance(document.querySelector("#confirmModal"));

    const csrf = () => $('meta[name="csrf-token"]').attr("content");

    /* The timeline track runs 07:00 -> 21:00, matching the header scale. */
    const TL_START = 420;
    const TL_SPAN  = 840;

    /* An adjustment is "+/-HH:MM". Anything else is a half-typed value: leave the
       last good total alone rather than flickering the hours while HR types. */
    const ADJUSTMENT = /^[+-]?\d{1,2}:\d{2}$/;

    /* ------------------------------------------------------------------ helpers */

    function stringToMinute(value) {
        const parts = String(value || "").split(":");
        const hours = parseInt(parts[0], 10);
        const mins  = parseInt(parts[1], 10);
        return (isNaN(hours) ? 0 : hours * 60) + (isNaN(mins) ? 0 : mins);
    }

    function minuteToHourMinute(minutes) {
        const sign = minutes < 0 ? "-" : "";
        minutes = Math.abs(minutes);
        let hours = Math.floor(minutes / 60);
        let mins  = minutes % 60;
        if (hours < 10) hours = "0" + hours;
        if (mins < 10)  mins  = "0" + mins;
        return sign + hours + ":" + mins;
    }

    /** "-01:30" -> -90, "+00:30" -> 30, "" -> 0, half-typed -> null. */
    function adjustmentMinutes(value) {
        const raw = String(value || "").trim();
        if (raw === "") return 0;
        if (!ADJUSTMENT.test(raw)) return null;
        const sign = raw.charAt(0) === "-" ? -1 : 1;
        return sign * stringToMinute(raw.replace(/^[+-]/, ""));
    }

    function humanDelta(minutes) {
        minutes = Math.abs(minutes);
        const hours = Math.floor(minutes / 60);
        const mins  = minutes % 60;
        if (hours && mins) return hours + "h " + mins + "m";
        return hours ? hours + "h" : mins + "m";
    }

    const trackPct = (time) =>
        Math.max(0, Math.min(100, ((stringToMinute(time) - TL_START) / TL_SPAN) * 100));

    const allRows  = () => $("#attRows .att-row");
    const rowOf    = (id) => $("#attRow_" + id);
    const editorOf = (id) => $('.att-editor[data-editor="' + id + '"]');

    const hasFlag   = ($row, flag)   => (" " + ($row.attr("data-flags")   || "") + " ").indexOf(" " + flag   + " ") > -1;
    const hasBucket = ($row, bucket) => (" " + ($row.attr("data-buckets") || "") + " ").indexOf(" " + bucket + " ") > -1;

    function notify(title, message, reload) {
        successModal.show();
        $("#successModal .successModalTitle").html(title);
        $("#successModal .successModalDesc").html(message);
        $("#successModal .successCloser").attr("data-action", reload ? "RELOAD" : "NONE");
    }

    function warn(title, message) {
        warningModal.show();
        $("#warningModal .warningModalTitle").html(title);
        $("#warningModal .warningModalDesc").html(message);
    }

    function busy($button, on) {
        $button.prop("disabled", on);
        $button.find("svg.att-spin").css("display", on ? "inline-block" : "none");
    }

    /* ------------------------------------------------------------- calculations */

    /**
     * Recomputes one row's payable minutes into the hidden field the endpoints read,
     * then repaints the hours, the delta and the timeline bar.
     */
    function recalcWork(id) {
        const $editor = editorOf(id);
        const $total  = $editor.find(".total_work_hour");
        if (!$total.length) return;

        const clockIn  = String($editor.find(".clockin_system").val()  || "");
        const clockOut = String($editor.find(".clockout_system").val() || "");
        if (clockIn.length !== 5 || clockOut.length !== 5) return;

        const adjustment = adjustmentMinutes($editor.find(".adjustment").val());
        if (adjustment === null) return;

        const unpaidBreak  = stringToMinute($editor.find(".unpadi_break").val());
        const takenBreak   = parseInt($editor.find(".total_break").val(), 10) || 0;
        const allowedBreak = parseInt($editor.find(".allowed_br").val(), 10) || 0;

        let minutes = stringToMinute(clockOut) - stringToMinute(clockIn) - unpaidBreak;
        if (takenBreak > allowedBreak) {
            minutes -= takenBreak - allowedBreak;
        }
        minutes += adjustment;

        $total.val(minutes);

        const $row = rowOf(id);
        const text = minuteToHourMinute(minutes);

        $editor.find(".js-editor-hours").text(text);
        $row.find(".js-row-hours").html(text + "<small>hrs</small>");

        // Clocking out before clocking in is never a real shift; block the save
        // rather than write a negative figure into payroll.
        flagNegative(id, minutes < 0);

        const rostered = $row.attr("data-rostered");
        const $delta = $row.find(".js-row-delta");
        if ($delta.length) {
            if (rostered === "" || rostered === undefined) {
                $delta.text("No rostered shift");
            } else {
                const diff = minutes - parseInt(rostered, 10);
                $delta.text(diff === 0 ? "Same as rostered" : humanDelta(diff) + (diff > 0 ? " more" : " less"));
            }
        }

        const $bar = $row.find(".js-clock-bar");
        if ($bar.length) {
            const left  = trackPct(clockIn);
            const width = Math.max(0.6, trackPct(clockOut) - left);
            $bar.css({ left: left + "%", width: width + "%" });
            $row.find(".js-stamp-in").css("left", left + "%").text(clockIn);
            $row.find(".js-stamp-out").css("left", Math.min(100, left + width) + "%").text(clockOut);
        }
    }

    /**
     * Leave hours = the unadjusted base +/- the adjustment.
     *
     * The old screen added the adjustment to the RUNNING total and then wrote that
     * back as the new base, so every time HR retyped "-01:00" another hour came off.
     * data-base is the figure before any adjustment, so this is idempotent.
     */
    function recalcLeave(id) {
        const $editor = editorOf(id);
        const $hidden = $editor.find(".leave_hour");
        if (!$hidden.length) return;

        const adjustment = adjustmentMinutes($editor.find(".leave_adjustment").val());
        if (adjustment === null) return;

        const minutes = (parseInt($hidden.attr("data-base"), 10) || 0) + adjustment;
        $hidden.val(minutes);

        const text = minuteToHourMinute(minutes);
        $editor.find(".js-editor-leave-hours").text(text);
        rowOf(id).find(".js-row-leave-hours").html(text + "<small>hrs</small>");
    }

    // "input" as well as "keyup": a pasted time fires no keystroke, and the old
    // screen missed it. Both recalcs are idempotent, so double-firing is harmless.
    $(document).on("keyup input change", ".att-editor .clockin_system, .att-editor .clockout_system, .att-editor .adjustment", function () {
        recalcWork($(this).closest(".att-editor").attr("data-editor"));
    });

    $(document).on("keyup input change", ".att-editor .leave_adjustment", function () {
        recalcLeave($(this).closest(".att-editor").attr("data-editor"));
    });

    /**
     * A self-classified absence (no approved leave) that HR marks Authorised Paid or
     * Holiday credits paid hours. Those are paid out of leave_hour - the same column
     * the reports read - so the typed figure is written straight into it, with no
     * base+adjustment to fold the way a linked leave row has.
     */
    function setLeaveHours(id, minutes) {
        const $editor = editorOf(id);
        $editor.find(".leave_hour").val(minutes);
        const text = minuteToHourMinute(minutes);
        $editor.find(".js-editor-leave-hours").text(text);
        rowOf(id).find(".js-row-leave-hours").html(text + "<small>hrs</small>");
    }

    // Only Authorised Paid (5) and Holiday (1) carry paid hours; the rest credit none.
    const LEAVE_PAID = ["1", "5"];

    $(document).on("change", ".att-editor .js-leave-radios input[type=radio]", function () {
        const $editor = $(this).closest(".att-editor");
        const id = $editor.attr("data-editor");
        const paid = LEAVE_PAID.indexOf(String($(this).val())) > -1;

        $editor.find(".js-paid-hours").toggle(paid);
        setLeaveHours(id, paid ? stringToMinute($editor.find(".js-leave-hours-input").val()) : 0);
    });

    $(document).on("keyup input change", ".att-editor .js-leave-hours-input", function () {
        setLeaveHours($(this).closest(".att-editor").attr("data-editor"), stringToMinute($(this).val()));
    });

    /* -------------------------------------------------------------------- drawer */

    const $drawer   = $("#attDrawer");
    const $backdrop = $("#attBackdrop");
    let openId = null;

    function openDrawer(id) {
        if (openId !== null && openId !== id) {
            closeDrawer(true);
        }
        const $editor = editorOf(id);
        if (!$editor.length) return;

        // Moved, not cloned. The inputs (and their IMasks) are the same nodes, so
        // what HR edits here is exactly what gets serialised and posted.
        $editor.appendTo($drawer).removeAttr("hidden");
        openId = id;

        rowOf(id).addClass("is-open");
        $("body").addClass("att-drawer-open");

        window.requestAnimationFrame(function () {
            $drawer.addClass("is-open").attr("aria-hidden", "false");
            $backdrop.addClass("is-open");
        });
    }

    function closeDrawer(immediate) {
        if (openId === null) return;

        const id = openId;
        openId = null;

        $drawer.removeClass("is-open").attr("aria-hidden", "true");
        $backdrop.removeClass("is-open");
        $("body").removeClass("att-drawer-open");
        rowOf(id).removeClass("is-open");

        const park = function () {
            if (openId === id) return; // reopened while the panel was sliding out
            editorOf(id).attr("hidden", "hidden").appendTo(rowOf(id));
        };

        immediate ? park() : window.setTimeout(park, 260);
    }

    $(document).on("click", ".js-edit", function () {
        openDrawer($(this).attr("data-id"));
    });
    $(document).on("click", ".js-drawer-close", function () {
        closeDrawer();
    });
    $backdrop.on("click", function () {
        closeDrawer();
    });
    $(document).on("keydown", function (e) {
        if (e.key === "Escape" && openId !== null && !document.querySelector(".modal.show")) {
            closeDrawer();
        }
    });

    /* --------------------------------------------------------------------- state */

    /**
     * Mirrors what the server just did, so the list agrees with the database without
     * a reload: update() sets updated_by and zeroes user_issues, so the row is now
     * reviewed and can no longer sit in Issues. Only leave_status can move it between
     * the other buckets - overtime_status is not editable here.
     */
    function markReviewed(id) {
        const $row    = rowOf(id);
        const $editor = editorOf(id);

        const field   = 'attendance[' + id + '][leave_status]';
        const $radio  = $editor.find('input[type=radio][name="' + field + '"]:checked');
        const $hidden = $editor.find('input[type=hidden][name="' + field + '"]');
        const leaveStatus = parseInt(($radio.length ? $radio.val() : $hidden.val()) || 0, 10);

        const overtime = $row.attr("data-overtime") === "1";
        const buckets = [];
        if (leaveStatus > 1) buckets.push("absents");
        if (overtime) buckets.push("overtime");
        if (!overtime && leaveStatus < 2) buckets.push("noissues");

        const flags = ($row.attr("data-flags") || "")
            .split(/\s+/)
            .filter((f) => f && f !== "pending" && f !== "adjusted");
        flags.push("reviewed");

        // A leave adjustment only counts where there is leave; the field can hold a
        // stale value on a row whose leave was later cleared.
        const workAdj  = adjustmentMinutes($editor.find(".adjustment").val()) || 0;
        const leaveAdj = adjustmentMinutes($editor.find(".leave_adjustment").val()) || 0;
        const adjusted = workAdj !== 0 || (leaveStatus > 0 && leaveAdj !== 0);
        if (adjusted) {
            flags.push("adjusted");
        }

        $row.attr("data-buckets", buckets.join(" "))
            .attr("data-flags", flags.join(" "))
            .attr("data-leave-status", leaveStatus)
            .addClass("is-reviewed")
            .toggleClass("is-adjusted", adjusted);

        // Same accent the server would render now: approved as clocked, or approved
        // after a manual edit. See edge_tone in EmployeeAttendanceController.
        const edge = adjusted ? "accent" : "done";
        $row.find(".att-row__edge").attr("class", "att-row__edge att-edge--" + edge);
        $row.find(".att-avatar").attr("class", "att-avatar att-avatar--" + edge);

        $editor.find(".att-card, .att-breaks").removeClass("is-flagged");
        $editor.find('input[name="attendance[' + id + '][user_issues]"]').val(0);

        // Reflect a saved note on the leave/absence line without a reload. data-hint-base
        // is the server-known part (holiday hours, leave comment); the typed note goes
        // after it, mirroring the Blade. Only leave rows carry that block.
        const $noclock = $row.find(".att-noclock--leave");
        if ($noclock.length) {
            const base = ($noclock.attr("data-hint-base") || "").trim();
            const note = ($editor.find(".rowNote").val() || "").trim();
            const parts = [base, note].filter(Boolean);
            $row.find(".att-noclock__hint").text(parts.length ? parts.join(" · ") : "Recorded absence");
        }
    }

    function refreshCounts() {
        const $rows    = allRows();
        const total    = $rows.length;

        // The summary strip (headline, bar, label) describes the SELECTED tab, not the
        // whole day - so "12 of 16 reviewed" means 16 rows are in this tab. matchesFilter
        // is the same test the list uses, so the numbers always agree with what is shown.
        const $scope = $rows.filter(function () { return matchesFilter($(this)); });
        const scopeTotal    = $scope.length;
        const scopeReviewed = $scope.filter(".is-reviewed").length;
        const scopePending  = scopeTotal - scopeReviewed;

        $("#attPendingHeadline").text(scopePending);
        $("#attSummaryTotal").text(scopeTotal);
        $("#attProgressLabel").text(scopeReviewed + " of " + scopeTotal + " reviewed");
        $("#attProgressFill").css("width", (scopeTotal ? Math.round((scopeReviewed / scopeTotal) * 100) : 0) + "%");

        const tabCounts = {
            all: total,
            absents: $rows.filter(function () { return hasBucket($(this), "absents"); }).length,
            noissues: $rows.filter(function () { return hasBucket($(this), "noissues"); }).length,
            issues: $rows.filter(function () { return hasBucket($(this), "issues"); }).length,
            overtime: $rows.filter(function () { return hasBucket($(this), "overtime"); }).length,
        };

        Object.keys(tabCounts).forEach(function (filter) {
            $('[data-count-for="' + filter + '"]').text(tabCounts[filter]);
        });

        refreshBulkAction();
    }

    /**
     * Accept-all is a No-issues-tab action: it only shows on that tab, its label counts
     * the no-issue rows, and it is disabled once none of them are still pending. Called
     * from refreshCounts (row state changes) and applyFilter (tab changes).
     */
    function refreshBulkAction() {
        const noissues = allRows().filter(function () { return hasBucket($(this), "noissues"); });
        const pendingNoIssues = noissues.filter(function () { return !$(this).hasClass("is-reviewed"); }).length;

        $("#attNoIssuesCount").text(noissues.length);
        $("#attAcceptAll").toggle(activeFilter === "noissues").prop("disabled", pendingNoIssues === 0);
    }

    /* ------------------------------------------------------------------ filtering */

    let activeFilter = "all";
    const activeFlags = new Set();

    function matchesFilter($row) {
        if (activeFilter === "late") return hasFlag($row, "late-in");
        if (activeFilter === "early") return hasFlag($row, "early-out");
        if (activeFilter === "adjusted") return hasFlag($row, "adjusted");
        if (activeFilter !== "all" && hasBucket($row, activeFilter)) return true;
        return activeFilter === "all";
    }

    function applyFilter() {
        let visible = 0;

        allRows().each(function () {
            const $row = $(this);
            let show = matchesFilter($row);

            if (show) {
                activeFlags.forEach(function (flag) {
                    if (!hasFlag($row, flag)) show = false;
                });
            }

            $row.toggle(show);
            if (show) visible++;
        });

        $("#attNoMatch").toggle(visible === 0 && allRows().length > 0);
        refreshBulkAction();
    }

    $(document).on("click", ".att-tab", function () {
        activeFilter = $(this).attr("data-filter") || $(this).attr("data-bucket") || "all";
        $(".att-tab").removeClass("is-active");
        $(this).addClass("is-active");
        window.location.hash = activeFilter;
        applyFilter();
        refreshCounts(); // summary follows the newly-selected tab
    });

    $(document).on("click", ".att-chip", function () {
        const flag = $(this).attr("data-flag");
        if (activeFlags.has(flag)) {
            activeFlags.delete(flag);
            $(this).removeClass("is-active");
        } else {
            activeFlags.add(flag);
            $(this).addClass("is-active");
        }
        applyFilter();
    });

    /* --------------------------------------------------------------------- saving */

    /** Every field the endpoints read lives in the editor, so this is the whole row. */
    const payloadFor = (id) => editorOf(id).find("input, textarea, select").serialize();

    /** Marks a row unsaveable rather than letting a negative figure reach payroll. */
    function flagNegative(id, negative) {
        editorOf(id).find(".js-negative-warning").toggle(negative);
        editorOf(id).find(".js-save").prop("disabled", negative);
        rowOf(id).toggleClass("is-invalid", negative).find(".js-approve").prop("disabled", negative);
    }

    function saveRow(id, $button) {
        const $row    = rowOf(id);
        const $editor = editorOf(id);

        if ((parseInt($editor.find(".total_work_hour").val(), 10) || 0) < 0) {
            warn("Cannot save", "The recorded hours for <strong>" + $row.attr("data-name") +
                "</strong> are negative. Fix the clock times first.");
            return;
        }

        // An absence with no reason posts no leave_status at all, and update() reads
        // that as 0 - which would quietly turn the absence into a normal working day.
        const $reasons = $editor.find('input[type=radio][name="attendance[' + id + '][leave_status]"]');
        if ($reasons.length && !$reasons.filter(":checked").length) {
            openDrawer(id);
            warn("Pick a reason", "Classify <strong>" + $row.attr("data-name") +
                "</strong>'s absence before saving, otherwise it is recorded as a normal working day.");
            return;
        }

        busy($button, true);

        axios({
            method: "post",
            url: route("hr.attendance.update"),
            data: {
                rowData: payloadFor(id),
                rowNote: $editor.find(".rowNote").val() || "",
                // Leave fields now sit in the same payload as everything else, so the
                // separate leave serialisation the old two-row markup needed is gone.
                leaveData: "",
                isLeaveRow: parseInt($row.attr("data-leave-status"), 10) > 0 ? 1 : 0,
            },
            headers: { "X-CSRF-TOKEN": csrf() },
        }).then(function () {
            busy($button, false);
            markReviewed(id);
            refreshCounts();
            applyFilter();
            closeDrawer();
            notify("Saved", $row.attr("data-name") + "'s attendance has been updated and approved.");
        }).catch(function () {
            busy($button, false);
            warn("Could not save", "Something went wrong and nothing was changed. Please try again or contact the administrator.");
        });
    }

    $(document).on("click", ".js-approve", function () {
        saveRow($(this).attr("data-id"), $(this));
    });

    $(document).on("click", ".js-save", function () {
        saveRow($(this).attr("data-id"), $(this));
    });

    /* ----------------------------------------------------------------------- bulk */

    /**
     * updateAll() only touches rows that carry an id - the old screen supplied that
     * from a per-row checkbox. Selection is gone, so the id is appended here.
     */
    function bulkSave($targets, prepare, done) {
        const chunks  = [];
        const saved   = [];
        const skipped = [];

        $targets.each(function () {
            const $row = $(this);
            const id = $row.attr("data-id");

            if (prepare) prepare(id);

            if ((parseInt(editorOf(id).find(".total_work_hour").val(), 10) || 0) < 0) {
                skipped.push($row.attr("data-name"));
                return;
            }

            saved.push(id);
            chunks.push(payloadFor(id) + "&attendance[" + id + "][id]=" + id);
        });

        if (!saved.length) {
            refreshCounts();
            warn("Nothing saved", "Every row would have ended up with negative hours. Fix the clock times first.");
            return;
        }

        $("#attAcceptAll").prop("disabled", true);

        axios({
            method: "post",
            url: route("hr.attendance.update.all"),
            data: { allData: chunks.join("&") },
            headers: { "X-CSRF-TOKEN": csrf() },
        }).then(function () {
            saved.forEach(markReviewed);
            refreshCounts();
            applyFilter();
            done(saved.length, skipped);
        }).catch(function () {
            refreshCounts();
            warn("Could not save", "Something went wrong and nothing was changed. Please try again or contact the administrator.");
        });
    }

    const pendingRows = () => allRows().filter(function () { return !$(this).hasClass("is-reviewed"); });

    // Accept-all only ever touches the still-pending no-issue rows.
    const pendingNoIssueRows = () =>
        pendingRows().filter(function () { return hasBucket($(this), "noissues"); });

    /**
     * Copies each pending no-issue row's clocked punch into its recorded time, then
     * approves it. That overwrites what is there, so it asks first.
     */
    $("#attAcceptAll").on("click", function () {
        const $targets = pendingNoIssueRows();
        if (!$targets.length) return;

        confirmModal.show();
        $("#confirmModal .confModTitle").html("Accept all clocked times?");
        $("#confirmModal .confModDesc").html(
            "This copies the clocked punch into the recorded time on <strong>" + $targets.length +
            "</strong> no-issue row(s) and approves them. Rows with no punch keep the recorded time they have."
        );
        $("#confirmModal .agreeWith").attr("data-action", "ACCEPT_ALL").attr("data-id", "0").attr("data-date", "");
    });

    function acceptClocked(id) {
        const $row    = rowOf(id);
        const $editor = editorOf(id);

        const punchIn  = String($row.attr("data-punch-in")  || "");
        const punchOut = String($row.attr("data-punch-out") || "");

        // A missing punch would blank the recorded time and pay them nothing, so only
        // a real HH:MM is copied across.
        if (punchIn.length === 5)  $editor.find(".clockin_system").val(punchIn);
        if (punchOut.length === 5) $editor.find(".clockout_system").val(punchOut);

        recalcWork(id);
    }

    /* ---------------------------------------------------------------- break editor */

    $(document).on("click", ".view_break", function (e) {
        e.preventDefault();
        const rowID = $(this).attr("data-id");

        axios({
            method: "post",
            url: route("hr.attendance.edit"),
            data: { rowID: rowID },
            headers: { "X-CSRF-TOKEN": csrf() },
        }).then(function (response) {
            $("#viewBreakModal .modal-body").html(response.data.res);
            $('#viewBreakModal input[name="id"]').val(rowID);

            createIcons({ icons, "stroke-width": 1.5, nameAttr: "data-lucide" });

            $("#viewBreakModal input.timepicker").each(function () {
                IMask(this, {
                    overwrite: true,
                    autofix: true,
                    mask: "HH:MM",
                    blocks: {
                        HH: { mask: IMask.MaskedRange, placeholderChar: "HH", from: 0, to: 23, maxLength: 2 },
                        MM: { mask: IMask.MaskedRange, placeholderChar: "MM", from: 0, to: 59, maxLength: 2 },
                    },
                });
            });

            viewBreakModal.show();
        }).catch(function () {
            warn("Could not load breaks", "The break times for this row could not be loaded. Please try again.");
        });
    });

    const breakMinutes = (start, end) => stringToMinute(end) - stringToMinute(start);

    $("#viewBreakModal").on("keyup change paste", ".breakStart, .breakEnd", function () {
        const $break = $(this).closest("tr.breakRow");
        const start = String($break.find(".breakStart").val() || "");
        const end   = String($break.find(".breakEnd").val() || "");

        // A break that ends before it starts is not a break. Zero it so the form's
        // own validation rejects it, rather than posting a negative total.
        const minutes = start.length === 5 && end.length === 5 ? breakMinutes(start, end) : 0;
        $break.find(".breakRowTotal").val(minutes > 0 ? minuteToHourMinute(minutes) : "00:00");

        let dayTotal = 0;
        $("#viewBreakModal .breakRowTotal").each(function () {
            dayTotal += stringToMinute($(this).val());
        });
        $("#viewBreakModal .breakGrandTotal").val(minuteToHourMinute(dayTotal));
    });

    $("#viewBreakForm").on("submit", function (e) {
        e.preventDefault();
        const $form = $(this);

        let errors = 0;
        $("#viewBreakModal .breakRow").each(function () {
            const start = $(this).find(".breakStart").val();
            const end   = $(this).find(".breakEnd").val();
            const total = $(this).find(".breakRowTotal").val();

            if (!start || start === "00:00" || !end || end === "00:00" || !total) {
                errors++;
            }
        });
        if (!$("#viewBreakModal .breakGrandTotal").val()) {
            errors++;
        }

        if (errors > 0) {
            $form.find(".modError").remove();
            $(".modal-content", $form).prepend(
                '<div class="modError alert alert-danger-soft show flex items-center mb-2" role="alert">' +
                '<i data-lucide="alert-octagon" class="w-6 h-6 mr-2"></i> Invalid time found. Please enter a valid, formatted time.</div>'
            );
            createIcons({ icons, "stroke-width": 1.5, nameAttr: "data-lucide" });
            window.setTimeout(function () { $form.find(".modError").remove(); }, 3000);
            return;
        }

        $("#updateBreak").prop("disabled", true).find("svg").css("display", "inline-block");

        axios({
            method: "post",
            url: route("hr.attendance.update.break"),
            data: new FormData(document.getElementById("viewBreakForm")),
            headers: { "X-CSRF-TOKEN": csrf() },
        }).then(function () {
            $("#updateBreak").prop("disabled", false).find("svg").css("display", "none");
            viewBreakModal.hide();
            // updateBreak() recomputes total_work_hour server-side, so the page has to
            // come back from the database rather than guess.
            notify("Breaks updated", "The break times have been saved and the hours recalculated.", true);
        }).catch(function () {
            $("#updateBreak").prop("disabled", false).find("svg").css("display", "none");
            warn("Could not save breaks", "Something went wrong. Please try again or contact the administrator.");
        });
    });

    /* -------------------------------------------------------------------- re-sync */

    $(document).on("click", ".reSyncRow", function (e) {
        e.preventDefault();
        confirmModal.show();
        $("#confirmModal .confModTitle").html("Re-sync this person?");
        $("#confirmModal .confModDesc").html(
            "This throws away the current row and rebuilds it from the raw punches. Any edit made here is lost."
        );
        $("#confirmModal .agreeWith")
            .attr("data-action", "RESYNC")
            .attr("data-id", $(this).attr("data-id"))
            .attr("data-date", $(this).attr("data-date"));
    });

    $("#confirmModal .agreeWith").on("click", function (e) {
        e.preventDefault();
        const $agree = $(this);
        const action = $agree.attr("data-action");

        if (action === "ACCEPT_ALL") {
            confirmModal.hide();
            const $targets = pendingNoIssueRows();
            bulkSave($targets, acceptClocked, function (count, skipped) {
                if (skipped.length) {
                    warn("Approved with exceptions",
                        count + " row(s) approved. Skipped (negative hours): <strong>" + skipped.join(", ") + "</strong>.");
                } else {
                    notify("Approved", count + " row(s) approved with their clocked times.");
                }
            });
            return;
        }

        if (action !== "RESYNC") return;

        $("#confirmModal button").prop("disabled", true);

        axios({
            method: "post",
            url: route("hr.attendance.re.sync"),
            data: { employee_id: $agree.attr("data-id"), the_date: $agree.attr("data-date") },
            headers: { "X-CSRF-TOKEN": csrf() },
        }).then(function () {
            $("#confirmModal button").prop("disabled", false);
            confirmModal.hide();
            notify("Re-synced", "The attendance has been rebuilt from the raw punches.", true);
        }).catch(function () {
            $("#confirmModal button").prop("disabled", false);
            confirmModal.hide();
            warn("Could not re-sync", "Something went wrong. Please try again or contact the administrator.");
        });
    });

    $(document).on("click", ".successCloser", function (e) {
        e.preventDefault();
        if ($(this).attr("data-action") === "RELOAD") {
            window.location.reload();
        } else {
            successModal.hide();
        }
    });

    document.getElementById("viewBreakModal").addEventListener("hide.tw.modal", function () {
        $("#viewBreakModal .modal-body").html("");
        $('#viewBreakModal input[name="id"]').val("0");
    });

    document.getElementById("confirmModal").addEventListener("hide.tw.modal", function () {
        $("#confirmModal button").prop("disabled", false);
        $("#confirmModal .agreeWith").attr("data-id", "0").attr("data-date", "").attr("data-action", "");
    });

    /* --------------------------------------------------------------------- boot up */

    $(".att-editor input.att-time").each(function () {
        IMask(this, { mask: "00:00" });
    });

    // A row can arrive from the sync already holding negative hours. Catch that on
    // load rather than only once HR happens to touch a field.
    allRows().each(function () {
        const id = $(this).attr("data-id");
        flagNegative(id, (parseInt(editorOf(id).find(".total_work_hour").val(), 10) || 0) < 0);
    });

    const filter = (window.location.hash || "").replace("#", "");
    if (filter && $('.att-tab[data-filter="' + filter + '"]').length) {
        activeFilter = filter;
        $(".att-tab").removeClass("is-active");
        $('.att-tab[data-filter="' + filter + '"]').addClass("is-active");
    }

    applyFilter();
    refreshCounts();
    createIcons({ icons, "stroke-width": 1.5, nameAttr: "data-lucide" });
})();
