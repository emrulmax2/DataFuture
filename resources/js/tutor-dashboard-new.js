import { createIcons, icons } from "lucide";
import moment from "moment";

("use strict");

const refreshIcons = () => {
    setTimeout(() => {
        createIcons({
            icons,
            "stroke-width": 1.5,
            nameAttr: "data-lucide",
        });
    }, 50);
};

const escapeHtml = (value) => String(value ?? "").replace(/[&<>"'`=\/]/g, (char) => ({
    "&": "&amp;",
    "<": "&lt;",
    ">": "&gt;",
    '"': "&quot;",
    "'": "&#039;",
    "`": "&#096;",
    "=": "&#061;",
    "/": "&#047;",
}[char]));

const moduleBadge = (group) => {
    const parts = String(group || "MOD").split(/[-\s]+/).filter(Boolean);
    const candidate = parts[1] || parts[0] || "MOD";

    return candidate.substring(0, 3).toUpperCase();
};

const moduleToneStyle = (seed) => {
    const palettes = [
        ["#e4f1ee", "#0d7c73", "#c4e2da"],
        ["#f3ecd8", "#a1802f", "#e9dcbc"],
        ["#e6ecf5", "#2f5fa1", "#cdd9ee"],
        ["#f4e6ec", "#a13f6b", "#eccdda"],
        ["#e9f0e4", "#4a7a2f", "#d3e4c7"],
        ["#ece4f5", "#7a4fa3", "#ddd0ec"],
    ];
    const chars = String(seed || "module");
    let hash = 0;

    for (let i = 0; i < chars.length; i += 1) {
        hash = ((hash << 5) - hash) + chars.charCodeAt(i);
        hash |= 0;
    }

    const tone = palettes[Math.abs(hash) % palettes.length];

    return `background: ${tone[0]}; color: ${tone[1]}; border: 1px solid ${tone[2]};`;
};

const splitClassTime = (time) => {
    const clean = String(time || "").trim();
    const parsed = moment(clean, ["hh:mm A", "h:mm A", "HH:mm:ss", "HH:mm"], true);

    if (parsed.isValid()) {
        return {
            main: parsed.format("hh:mm"),
            ampm: parsed.format("A"),
        };
    }

    const match = clean.match(/^(\d{1,2}:\d{2})\s*([AP]M)$/i);

    if (match) {
        return {
            main: match[1],
            ampm: match[2].toUpperCase(),
        };
    }

    return {
        main: clean || "--:--",
        ampm: "",
    };
};

const isFeedGiven = (data) => Number(data.feed_given) === 1;

const isShowClassReady = (data) => data.showClass === true || Number(data.showClass) === 1;

const classStatus = (data) => {
    const attendanceInfo = data.attendance_information;
    const completed = attendanceInfo && isFeedGiven(data) && attendanceInfo.end_time;
    const started = attendanceInfo && isFeedGiven(data) && !attendanceInfo.end_time;
    const ready = (attendanceInfo && !isFeedGiven(data)) || (!attendanceInfo && isShowClassReady(data));

    if (completed) {
        return {
            label: "Completed",
            color: "#2f5fa1",
            background: "#e6ecf5",
            border: "#cdd9ee",
            cardClass: "",
        };
    }

    if (started) {
        return {
            label: "In Progress",
            color: "#0d7c73",
            background: "#e4f1ee",
            border: "#c4e2da",
            cardClass: "",
        };
    }

    if (ready) {
        return {
            label: "Ready",
            color: "#0d7c73",
            background: "#e4f1ee",
            border: "#c4e2da",
            cardClass: "",
        };
    }

    return {
        label: "Upcoming",
        color: "#a1802f",
        background: "#f6efdc",
        border: "#e9dcbc",
        cardClass: "is-gold",
    };
};

const classLocation = (data) => [data.venue, data.room].filter(Boolean).join(" - ");

const selectedDateIsToday = () => {
    const selectedDate = $("#tutor-calendar-date").val();
    const parsed = moment(selectedDate, ["DD/MM/YYYY", "DD-MM-YYYY"], true);

    return parsed.isValid() && parsed.isSame(moment(), "day");
};

const shouldShowLockedAlert = (data) => {
    if (data.attendance_information || isShowClassReady(data)) {
        return false;
    }

    if (Number(data.showClass) === 2) {
        return true;
    }

    if (data.is_today === 0 || data.is_today === "0") {
        return false;
    }

    const start = moment(data.start_time, ["hh:mm A", "h:mm A"], true);

    return selectedDateIsToday() && start.isValid() && moment().isBefore(start.clone().subtract(15, "minutes"));
};

const renderClassActions = (data) => {
    const attendanceInfo = data.attendance_information;
    const tutorId = typeof data.tutor_id === "object" ? data.tutor_id.id : data.tutor_id;
    const attendanceUrl = route("tutor-dashboard.attendance", [tutorId, data.id]);

    if (attendanceInfo) {
        if (!isFeedGiven(data)) {
            return `<div class="td-action-row">
                <a data-attendanceinfo="${escapeHtml(attendanceInfo.id)}" data-id="${escapeHtml(data.id)}" href="${attendanceUrl}" class="start-punch td-action">
                    <i data-lucide="check-square" class="w-4 h-4"></i>Feed Attendance
                </a>
            </div>`;
        }

        let html = `<div class="td-action-row">
            <a href="${attendanceUrl}" data-attendanceinfo="${escapeHtml(attendanceInfo.id)}" data-id="${escapeHtml(data.id)}" class="start-punch td-action">
                <i data-lucide="eye" class="w-4 h-4"></i>View Feed
            </a>`;

        if (!attendanceInfo.end_time) {
            html += `<a href="javascript:;" data-attendanceinfo="${escapeHtml(attendanceInfo.id)}" data-id="${escapeHtml(data.id)}" data-tw-toggle="modal" data-tw-target="#endClassModal" class="start-punch td-action is-danger">
                <i data-lucide="x-circle" class="w-4 h-4"></i>End Class
            </a>`;
        }

        return `${html}</div>`;
    }

    if (isShowClassReady(data)) {
        return `<div class="td-action-row">
            <a href="javascript:;" data-tw-toggle="modal" data-id="${escapeHtml(data.id)}" data-tw-target="#editPunchNumberDeteilsModal" class="start-punch td-action">
                <i data-lucide="check-square" class="w-4 h-4"></i>Start Class
            </a>
        </div>`;
    }

    if (shouldShowLockedAlert(data)) {
        return `<div class="td-alert" role="alert">
            <i data-lucide="clock" class="w-4 h-4"></i>
            <span>Class Start button appears <strong>15 minutes</strong> before the scheduled time.</span>
        </div>`;
    }

    return "";
};

const renderClassCard = (data) => {
    const status = classStatus(data);
    const time = splitClassTime(data.start_time);
    const location = classLocation(data);
    const subMeta = [
        `<span><i data-lucide="graduation-cap" class="w-4 h-4"></i>${escapeHtml(data.course)}</span>`,
        location ? `<span><i data-lucide="map-pin" class="w-4 h-4"></i>${escapeHtml(location)}</span>` : "",
    ].join("");

    return `<div class="td-card td-class-card ${status.cardClass}">
        <div class="td-class-time">
            <div class="td-time-main">${escapeHtml(time.main)}</div>
            <div class="td-time-ampm">${escapeHtml(time.ampm)}</div>
            <div class="td-time-rule"></div>
            <div class="td-time-status" style="color: ${status.color}">${escapeHtml(status.label)}</div>
        </div>
        <div class="td-class-divider"></div>
        <div class="td-class-body">
            <div class="td-class-meta">
                <span class="td-code">${escapeHtml(data.group)}</span>
                <span class="td-status-pill" style="color: ${status.color}; background: ${status.background}; border: 1px solid ${status.border};">
                    <span class="td-status-dot" style="background: ${status.color}"></span>${escapeHtml(status.label)}
                </span>
            </div>
            <div class="td-class-title">${escapeHtml(data.module)}</div>
            <div class="td-class-sub">${subMeta}</div>
            ${renderClassActions(data)}
        </div>
    </div>`;
};

const renderModuleCard = (data) => `<a href="${route("tutor-dashboard.plan.module.show", data.id)}" target="_blank" class="td-module-card">
    <span class="td-module-badge" style="${moduleToneStyle(`${data.group || ""}${data.module || ""}`)}">${escapeHtml(moduleBadge(data.group))}</span>
    <span class="min-w-0 flex-1">
        <span class="td-module-code">${escapeHtml(data.group)}</span>
        <span class="td-module-title">${escapeHtml(data.module)}</span>
        <span class="td-module-course">${escapeHtml(data.course)}</span>
    </span>
    <i data-lucide="chevron-right" class="w-4 h-4 text-[#c9bd9a] flex-none"></i>
</a>`;

const renderEmptyClasses = () => '<div class="td-card td-empty">No class found for the selected day.</div>';

const renderEmptyModules = () => '<div class="td-empty">Modules not found.</div>';

var attendanceListTable = (function () {
    var _tableGen = function (form) {
        $.ajax({
            method: "GET",
            url: route("tutor-dashboard.list"),
            data: form,
            dataType: "json",
            async: false,
            contentType: false,
            cache: false,
            headers: { "X-CSRF-TOKEN": $("meta[name=\"csrf-token\"]").attr("content") },
            success: function (res, textStatus, xhr) {
                if (xhr.status !== 200) {
                    return;
                }

                const dataSet = Array.isArray(res.data) ? res.data : [];
                const classLabel = dataSet.length === 1 ? "session" : "sessions";

                $("#tdClassCount").text(`${dataSet.length} ${classLabel}`);
                $("#tdHeroClassCount").text(dataSet.length);
                $("#todays-classlist").html(dataSet.length ? dataSet.map(renderClassCard).join("") : renderEmptyClasses());
                refreshIcons();
            },
            error: function (jqXHR, textStatus, errorThrown) {
                console.log(`${textStatus} => ${errorThrown}`);
            },
        });
    };

    return {
        init: function (form = []) {
            _tableGen(form);
        },
    };
})();

(function () {
    if ($("#tutorDashboard").length > 0) {
        const dateOption = {
            autoApply: true,
            singleMode: true,
            numberOfColumns: 1,
            numberOfMonths: 1,
            showWeekNumbers: true,
            format: "DD/MM/YYYY",
            dropdowns: {
                minYear: 1900,
                maxYear: 2050,
                months: true,
                years: true,
            },
        };

        new Litepicker({
            element: document.getElementById("tutor-calendar-date"),
            ...dateOption,
            setup: (picker) => {
                picker.on("selected", (date) => {
                    const tutorData = $("input[name='tutor_id']").val();
                    const customDate = moment(date.dateInstance).format("DD-MM-YYYY");

                    attendanceListTable.init({
                        id: tutorData,
                        plan_date: customDate,
                    });
                });
            },
        });

        $(document).on("click", "#tutorDashboard .start-punch", function () {
            const data = $(this).data("id");
            const punchInput = document.getElementById("employee_punch_number");

            if (punchInput) {
                punchInput.focus();
            }

            $(".plan-datelist").val(data);
        });

        const successModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#successModal"));
        const editPunchNumberDeteilsModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#editPunchNumberDeteilsModal"));
        const endClassModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#endClassModal"));
        const confirmModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#confirmModal"));
        const startClassConfirmModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#startClassConfirmModal"));
        const errorModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#errorModal"));
        const termDropdownEl = document.querySelector("#term-dropdown");
        const termDropdown = termDropdownEl && window.tailwind?.Dropdown ? tailwind.Dropdown.getOrCreateInstance(termDropdownEl) : null;

        $(".save").on("click", function (e) {
            e.preventDefault();
            let $theBtn = $(this);

            $theBtn.attr("disabled", "disabled");
            $theBtn.find("svg").fadeIn();

            var parentForm = $(this).parents("form");
            var formID = parentForm.attr("id");
            const form = document.getElementById(formID);
            let url = $(`#${formID} input[name=url]`).val();
            let form_data = new FormData(form);

            $.ajax({
                method: "POST",
                url: url,
                data: form_data,
                dataType: "json",
                async: false,
                enctype: "multipart/form-data",
                processData: false,
                contentType: false,
                cache: false,
                headers: { "X-CSRF-TOKEN": $("meta[name=\"csrf-token\"]").attr("content") },
                success: function (res, textStatus, xhr) {
                    $theBtn.removeAttr("disabled");
                    $theBtn.find("svg").fadeOut();

                    $(".acc__input-error", parentForm).html("");
                    if (xhr.status == 206) {
                        editPunchNumberDeteilsModal.hide();
                        startClassConfirmModal.hide();
                        successModal.show();
                        confirmModal.hide();
                        errorModal.hide();
                        document.getElementById("successModal").addEventListener("shown.tw.modal", function () {
                            $("#successModal .successModalTitle").html("Congratulations!");
                            $("#successModal .successModalDesc").html("Data updated.");
                        });

                        setTimeout(function () {
                            successModal.hide();
                            location.href = route("tutor-dashboard.attendance", [res.data.tutor, res.data.plandate]);
                        }, 1000);
                    }
                    if (xhr.status == 207) {
                        editPunchNumberDeteilsModal.hide();
                        successModal.hide();
                        startClassConfirmModal.show();
                        errorModal.hide();
                    } else if (xhr.status == 200) {
                        editPunchNumberDeteilsModal.hide();
                        startClassConfirmModal.hide();
                        successModal.show();
                        confirmModal.hide();
                        errorModal.hide();
                        endClassModal.hide();
                        document.getElementById("successModal").addEventListener("shown.tw.modal", function () {
                            $("#successModal .successModalTitle").html("Congratulations!");
                            $("#successModal .successModalDesc").html("Data updated.");
                        });

                        setTimeout(function () {
                            successModal.hide();
                            location.reload();
                        }, 1000);
                    }
                },
                error: function (jqXHR, textStatus, errorThrown) {
                    $(".acc__input-error").html("");
                    $theBtn.removeAttr("disabled");
                    $theBtn.find("svg").fadeOut();

                    if (jqXHR.status == 422) {
                        for (const [key, val] of Object.entries(jqXHR.responseJSON.errors)) {
                            $(`#${formID} .${key}`).addClass("border-danger");
                            $(`#${formID}  .error-${key}`).html(val);
                        }
                    } else if (jqXHR.status == 443) {
                        document.getElementById("confirmModal").addEventListener("shown.tw.modal", function () {
                            $("#confirmModal .confModTitle").html("End Class!");
                            $("#confirmModal .confModDesc").html("Do you want to End Class.");
                        });
                        confirmModal.show();
                        editPunchNumberDeteilsModal.hide();
                    } else if (jqXHR.status == 442) {
                        document.getElementById("confirmModal").addEventListener("shown.tw.modal", function () {
                            $("#confirmModal .confModTitle").html("Different Tutor ?");
                            $("#confirmModal .confModDesc").html("Please Put a note Below, why are you taking this class?");
                        });
                        editPunchNumberDeteilsModal.hide();
                        startClassConfirmModal.hide();
                        confirmModal.show();
                    } else if (jqXHR.status == 444) {
                        document.getElementById("errorModal").addEventListener("shown.tw.modal", function () {
                            $("#errorModal .errorModalTitle").html("Wrong Punch Number");
                            $("#errorModal .errorModalDesc").html("It is not your punch number");
                        });
                        editPunchNumberDeteilsModal.hide();
                        startClassConfirmModal.hide();
                        errorModal.show();
                        setTimeout(function () {
                            errorModal.hide();
                            editPunchNumberDeteilsModal.show();
                        }, 1000);
                    } else if (jqXHR.status == 402) {
                        document.getElementById("errorModal").addEventListener("shown.tw.modal", function () {
                            $("#errorModal .errorModalTitle").html("Invalid Punch");
                            $("#errorModal .errorModalDesc").html("Invalid Punch Number");
                        });
                        editPunchNumberDeteilsModal.hide();
                        startClassConfirmModal.hide();
                        errorModal.show();
                        setTimeout(function () {
                            errorModal.hide();
                            editPunchNumberDeteilsModal.show();
                        }, 1000);
                    } else if (jqXHR.status == 322) {
                        endClassModal.hide();
                        startClassConfirmModal.hide();
                        errorModal.show();
                        document.getElementById("errorModal").addEventListener("shown.tw.modal", function () {
                            $("#errorModal .errorModalTitle").html("Oops!");
                            $("#errorModal .errorModalDesc").html("You are out of College. Please return to college to end your class");
                        });

                        setTimeout(function () {
                            errorModal.hide();
                        }, 2000);
                    } else {
                        console.log(`${textStatus} => ${errorThrown}`);
                    }
                },
            });
        });

        $(".term-select").on("click", function (e) {
            e.preventDefault();

            const $selectedTerm = $(this);
            const $button = $("#selected-term");
            const $calendarIcon = $button.find('[data-lucide="calendar-days"]').first();
            const $spinner = $button.find(".td-term-spinner");
            const $selectedText = $button.find(".td-term-label");
            const termName = $selectedTerm.data("instance_term_label") || $selectedTerm.attr("data-instance_term_label") || $.trim($selectedTerm.text());
            const instanceTermId = $selectedTerm.data("instance_term_id");
            const tutorId = $selectedTerm.data("tutor_id");

            $calendarIcon.hide();
            $spinner.css("display", "block");

            axios({
                method: "get",
                url: route("tutor-dashboard.tutor.modulelist", [instanceTermId, tutorId]),
                headers: {
                    "X-CSRF-TOKEN": $("meta[name=\"csrf-token\"]").attr("content"),
                },
            }).then((response) => {
                if (response.status == 200) {
                    const dataset = response.data || {};
                    const modules = dataset.module_data && dataset.module_data[instanceTermId] ? dataset.module_data[instanceTermId] : [];

                    $selectedText.html(escapeHtml(termName));
                    $(".term-select").removeClass("dropdown-active").find('svg[data-lucide="check"], [data-lucide="check"]').remove();
                    $(`#term-${instanceTermId}`).addClass("dropdown-active").append('<i data-lucide="check" class="w-4 h-4"></i>');

                    $("#tdModuleCount").text(modules.length);
                    $("#tdHeroModuleCount").text(modules.length);
                    $("#TermBox").html(modules.length ? modules.map(renderModuleCard).join("") : renderEmptyModules());
                    termDropdown?.hide();
                    refreshIcons();
                }
            }).catch((error) => {
                $("#TermBox").html(renderEmptyModules());
                $("#tdModuleCount").text("0");
                $("#tdHeroModuleCount").text("0");

                if (error.response) {
                    if (error.response.status == 422 && error.response.data?.errors) {
                        for (const [key, val] of Object.entries(error.response.data.errors)) {
                            $(`#addSmtpForm .${key}`).addClass("border-danger");
                            $(`#addSmtpForm  .error-${key}`).html(val);
                        }
                    } else if (error.response.status == 303) {
                        document.getElementById("confirmModal").addEventListener("shown.tw.modal", function () {
                            $("#confirmModal .confModTitle").html("End Class!");
                            $("#confirmModal .confModDesc").html("Do you want to End Class.");
                        });
                        confirmModal.show();
                        editPunchNumberDeteilsModal.hide();
                    } else {
                        console.log("error");
                    }
                }
            }).finally(() => {
                $spinner.hide();
                $calendarIcon.show();
            });
        });

        refreshIcons();
    }
})();
