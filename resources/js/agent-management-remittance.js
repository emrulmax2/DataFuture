import { createIcons, icons } from "lucide";
import Tabulator from "tabulator-tables";
import Litepicker from "litepicker";

("use strict");

const escapeHtml = (value) => {
    const div = document.createElement("div");
    div.textContent = value == null ? "" : String(value);
    return div.innerHTML;
};

const avatarPalette = ["#0d7a76", "#6d4bb0", "#2f6ea5", "#1e6b4e", "#c65a2a", "#c24f24"];

const initials = (value) => {
    const parts = String(value || "Agent")
        .replace(/^Mr\s+|^Mrs\s+|^Miss\s+|^Ms\s+/i, "")
        .trim()
        .split(/\s+/)
        .filter(Boolean);

    if (parts.length === 0) return "AG";

    return `${parts[0][0] || ""}${(parts[1] || parts[0])[0] || ""}`.toUpperCase();
};

const avatarColor = (value) => {
    const source = String(value || "agent remittance");
    let hash = 0;

    for (let index = 0; index < source.length; index += 1) {
        hash = source.charCodeAt(index) + ((hash << 5) - hash);
    }

    return avatarPalette[Math.abs(hash) % avatarPalette.length];
};

const refreshIcons = () => {
    createIcons({
        icons,
        "stroke-width": 1.8,
        nameAttr: "data-lucide",
    });
};

const getModal = (selector) => {
    const element = document.querySelector(selector);
    return element ? tailwind.Modal.getOrCreateInstance(element) : null;
};

const setButtonBusy = ($button, busy) => {
    const $loader = $button.find("svg.theLoader, svg").last();

    $button.prop("disabled", busy);
    $loader.css("display", busy ? "inline-block" : "none");
};

const statusMeta = (status) => {
    const normalizedStatus = Number(status || 0);

    if (normalizedStatus === 1) {
        return { label: "Scheduled", tone: "scheduled", icon: "clock" };
    }

    if (normalizedStatus === 2) {
        return { label: "Paid", tone: "paid", icon: "check" };
    }

    if (normalizedStatus === 3) {
        return { label: "Canceled", tone: "canceled", icon: "x" };
    }

    return { label: "Pending", tone: "pending", icon: "alert-triangle" };
};

const renderReference = (reference) => {
    return `<span class="agm-remittance-ref">${escapeHtml(reference || "—")}</span>`;
};

const renderReferral = (row) => {
    const name = row.agent_name || "Unknown Agent";
    const organization = row.organization || "Organisation not set";

    return `
        <div class="agm-remittance-referral">
            <span class="agm-remittance-avatar" style="background:${avatarColor(name)}">${escapeHtml(initials(name))}</span>
            <span class="agm-remittance-referral__copy">
                <strong>${escapeHtml(name)}</strong>
                <small>${escapeHtml(organization)}</small>
            </span>
        </div>
    `;
};

const renderStatus = (row) => {
    const meta = statusMeta(row.payment_status);

    return `
        <span class="agm-remittance-status is-${meta.tone}">
            <i data-lucide="${meta.icon}"></i>
            ${escapeHtml(meta.label)}
        </span>
    `;
};

const renderActions = (row) => {
    const id = escapeHtml(row.id);
    const detailsUrl = escapeHtml(row.url || "javascript:void(0);");
    const pdfUrl = escapeHtml(route("agent.management.remittance.print", row.id));
    const excelUrl = escapeHtml(route("agent.management.remittance.export", row.id));

    return `
        <div class="agm-remittance-actions">
            <a href="${detailsUrl}" class="agm-agent-action agm-agent-action--view" title="View remittance" aria-label="View remittance">
                <i data-lucide="eye"></i>
            </a>

            <div class="dropdown agm-remittance-download">
                <button type="button" class="dropdown-toggle agm-agent-action agm-agent-action--download" aria-expanded="false" data-tw-toggle="dropdown" title="Download" aria-label="Download remittance">
                    <i data-lucide="download"></i>
                </button>
                <div class="dropdown-menu agm-remittance-download__menu">
                    <div class="dropdown-content agm-remittance-download__content">
                        <a href="${pdfUrl}" class="agm-remittance-download__item is-pdf">
                            <span><i data-lucide="printer"></i></span>
                            Download PDF
                        </a>
                        <a href="${excelUrl}" class="agm-remittance-download__item is-excel">
                            <span><i data-lucide="file-spreadsheet"></i></span>
                            Download Excel
                        </a>
                    </div>
                </div>
            </div>

            <input type="hidden" class="agent_comission_ids" name="agent_comission_ids" value="${id}"/>
            <input type="hidden" class="agent_ids" name="agent_ids" value="${escapeHtml(row.agent_id || 0)}"/>
        </div>
    `;
};

var agentRemittanceListTable = (function () {
    let tableContent;
    let totalRows = 0;

    const $table = $("#agentRemittanceListTable");
    const listUrl = route("agent.management.remittance.list");

    const getParams = () => ({
        querystr: ($("#query").val() || "").trim(),
        status: $("#status").val() || "0",
    });

    const syncFooter = () => {
        window.requestAnimationFrame(() => {
            const tableElement = $table.get(0);
            if (!tableElement || !tableContent) return;

            const footerContents = tableElement.querySelector(".tabulator-footer .tabulator-footer-contents");
            const paginator = tableElement.querySelector(".tabulator-paginator");

            if (!footerContents || !paginator) return;

            tableElement.classList.add("agm-footer-synced");
            footerContents.classList.add("agm-remittance-footer-layout");

            const label = footerContents.querySelector(".agm-remittance-page-size-group label")
                || Array.from(paginator.children || []).find((child) => child.tagName === "LABEL")
                || footerContents.querySelector("label");
            const pageSize = footerContents.querySelector(".tabulator-page-size");

            if (!label || !pageSize) return;

            label.textContent = "Page Size";

            let footerLeft = footerContents.querySelector(".agm-remittance-footer-left");
            if (!footerLeft) {
                footerLeft = document.createElement("span");
                footerLeft.className = "agm-remittance-footer-left";
            }

            let footerRight = footerContents.querySelector(".agm-remittance-footer-right");
            if (!footerRight) {
                footerRight = document.createElement("span");
                footerRight.className = "agm-remittance-footer-right";
            }
            footerContents.append(footerLeft, footerRight);
            footerRight.append(paginator);

            let range = footerContents.querySelector(".agm-remittance-page-range");
            if (!range) {
                range = document.createElement("span");
                range.className = "agm-remittance-page-range";
            }

            let pageSizeGroup = footerContents.querySelector(".agm-remittance-page-size-group");
            if (!pageSizeGroup) {
                pageSizeGroup = document.createElement("span");
                pageSizeGroup.className = "agm-remittance-page-size-group";
            }
            pageSizeGroup.append(label, pageSize);
            footerContents.querySelectorAll(".agm-remittance-page-size-group").forEach((group) => {
                if (group !== pageSizeGroup && group.children.length === 0) group.remove();
            });
            footerLeft.append(pageSizeGroup, range);

            let pageControls = footerContents.querySelector(".agm-remittance-page-controls");
            if (!pageControls) {
                pageControls = document.createElement("span");
                pageControls.className = "agm-remittance-page-controls";
            }

            ["first", "prev"].forEach((page) => {
                const button = paginator.querySelector(`.tabulator-page[data-page="${page}"]`)
                    || pageControls.querySelector(`.tabulator-page[data-page="${page}"]`);
                if (button) pageControls.append(button);
            });

            const pages = paginator.querySelector(".tabulator-pages") || pageControls.querySelector(".tabulator-pages");
            if (pages) pageControls.append(pages);

            ["next", "last"].forEach((page) => {
                const button = paginator.querySelector(`.tabulator-page[data-page="${page}"]`)
                    || pageControls.querySelector(`.tabulator-page[data-page="${page}"]`);
                if (button) pageControls.append(button);
            });

            paginator.append(pageControls);

            const currentPage = Number(tableContent.getPage ? tableContent.getPage() : 1) || 1;
            const rawSize = tableContent.getPageSize ? tableContent.getPageSize() : 10;
            const pageSizeValue = rawSize === true ? totalRows : Number(rawSize) || totalRows || 10;
            const start = totalRows > 0 ? ((currentPage - 1) * pageSizeValue) + 1 : 0;
            const end = totalRows > 0 ? Math.min(currentPage * pageSizeValue, totalRows) : 0;

            range.innerHTML = `Showing <b>${start}-${end}</b> of ${totalRows} remittances`;
        });
    };

    const _tableGen = function () {
        tableContent = new Tabulator("#agentRemittanceListTable", {
            ajaxURL: listUrl,
            ajaxParams: getParams(),
            ajaxFiltering: true,
            ajaxSorting: true,
            printAsHtml: true,
            printStyled: true,
            pagination: "remote",
            paginationSize: 10,
            paginationSizeSelector: [10, 25, 50, 100, true],
            layout: "fitColumns",
            responsiveLayout: false,
            placeholder: "No matching remittances found",
            selectable: true,
            columns: [
                {
                    formatter: "rowSelection",
                    titleFormatter: "rowSelection",
                    hozAlign: "center",
                    headerHozAlign: "center",
                    width: 54,
                    headerSort: false,
                    download: false,
                    cellClick: function (event, cell) {
                        cell.getRow().toggleSelect();
                    },
                },
                {
                    title: "#ID",
                    field: "id",
                    width: 72,
                    headerSort: false,
                    formatter(cell) {
                        return `<span class="agm-remittance-id">${escapeHtml(cell.getValue())}</span>`;
                    },
                },
                {
                    title: "Remittance Ref.",
                    field: "remittance_ref",
                    width: 178,
                    headerHozAlign: "left",
                    headerSort: false,
                    formatter(cell) {
                        return renderReference(cell.getValue());
                    },
                },
                {
                    title: "Created Date",
                    field: "entry_date",
                    width: 178,
                    headerHozAlign: "left",
                    headerSort: false,
                    formatter(cell) {
                        return `<span class="agm-remittance-muted">${escapeHtml(cell.getValue())}</span>`;
                    },
                },
                {
                    title: "Intake",
                    field: "semester",
                    width: 150,
                    headerHozAlign: "left",
                    headerSort: false,
                    formatter(cell) {
                        return `<span class="agm-remittance-muted">${escapeHtml(cell.getValue())}</span>`;
                    },
                },
                {
                    title: "Refferal Name",
                    field: "agent_name",
                    minWidth: 280,
                    widthGrow: 1,
                    headerHozAlign: "left",
                    headerSort: false,
                    formatter(cell) {
                        return renderReferral(cell.getData());
                    },
                },
                {
                    title: "Total Amount",
                    field: "amount_html",
                    width: 160,
                    hozAlign: "right",
                    headerHozAlign: "right",
                    headerSort: false,
                    formatter(cell) {
                        return `<span class="agm-remittance-money">${escapeHtml(cell.getValue())}</span>`;
                    },
                },
                {
                    title: "Status",
                    field: "payment_status",
                    width: 130,
                    headerHozAlign: "left",
                    headerSort: false,
                    formatter(cell) {
                        return renderStatus(cell.getData());
                    },
                },
                {
                    title: "Actions",
                    field: "id",
                    headerSort: false,
                    hozAlign: "right",
                    headerHozAlign: "right",
                    width: 128,
                    minWidth: 128,
                    download: false,
                    formatter(cell) {
                        return renderActions(cell.getData());
                    },
                },
            ],
            ajaxResponse(url, params, response) {
                totalRows = response.all_rows && response.all_rows > 0 ? response.all_rows : 0;
                return response;
            },
            pageLoaded() {
                syncFooter();
            },
            renderComplete() {
                syncFooter();
                refreshIcons();
            },
            rowSelectionChanged(data, rows) {
                const $button = $("#scheduleRemitPaymentBtn");

                if (rows.length > 0) {
                    $button.css("display", "inline-flex");
                } else {
                    $button.hide();
                }
            },
            selectableCheck(row) {
                return row.getData().id > 0;
            },
        });

        window.addEventListener("resize", () => {
            tableContent.redraw();
            refreshIcons();
        });
    };

    const reload = () => {
        const $button = $("#tabulator-html-filter-go");

        setButtonBusy($button, true);

        const request = tableContent
            ? tableContent.setData(listUrl, getParams())
            : Promise.resolve(_tableGen());

        const resetButton = () => setButtonBusy($button, false);

        if (request && typeof request.finally === "function") {
            request.finally(resetButton);
        } else {
            resetButton();
        }
    };

    return {
        init() {
            if (tableContent) {
                reload();
            } else {
                _tableGen();
            }
        },
        reload,
        getSelectedData() {
            return tableContent ? tableContent.getSelectedData() : [];
        },
    };
})();

(function () {
    if (!$("#agentRemittanceListTable").length) return;

    agentRemittanceListTable.init();

    const payDateOption = {
        autoApply: true,
        singleMode: true,
        numberOfColumns: 1,
        numberOfMonths: 1,
        showWeekNumbers: false,
        inlineMode: false,
        format: "DD-MM-YYYY",
        dropdowns: {
            minYear: 1900,
            maxYear: 2050,
            months: true,
            years: true,
        },
    };

    const successModal = getModal("#successModal");
    const warningModal = getModal("#warningModal");
    const scheduleRemitPaymentModal = getModal("#scheduleRemitPaymentModal");

    const showSuccess = (title, description, autoHide = true) => {
        $("#successModal .successModalTitle").html(title);
        $("#successModal .successModalDesc").html(description);
        refreshIcons();
        successModal?.show();

        if (autoHide) {
            window.setTimeout(() => successModal?.hide(), 2000);
        }
    };

    const showWarning = (title, description, autoHide = true) => {
        $("#warningModal .warningModalTitle").html(title);
        $("#warningModal .warningModalDesc").html(description);
        refreshIcons();
        warningModal?.show();

        if (autoHide) {
            window.setTimeout(() => warningModal?.hide(), 2400);
        }
    };

    const resetScheduleModal = () => {
        const $modal = $("#scheduleRemitPaymentModal");

        $modal.find(".theScheduleContent").hide().html("");
        $modal.find(".theScheduleLoader").css("display", "flex");
        $modal.find(".acc__input-error").html("");
        setButtonBusy($("#schedulePayBtn"), false);
    };

    document.getElementById("scheduleRemitPaymentModal")?.addEventListener("hide.tw.modal", resetScheduleModal);

    $("#tabulatorFilterForm").on("submit", function (event) {
        event.preventDefault();
        agentRemittanceListTable.reload();
    });

    $("#tabulator-html-filter-reset").on("click", function (event) {
        event.preventDefault();
        $("#query").val("");
        $("#status").val("0");
        agentRemittanceListTable.reload();
    });

    $(document).on("click", "#scheduleRemitPaymentBtn", function (event) {
        event.preventDefault();

        const selectedRows = agentRemittanceListTable.getSelectedData();
        const agentComissionIds = selectedRows.map((row) => row.id).filter(Boolean);
        const agentIds = [...new Set(selectedRows.map((row) => row.agent_id).filter(Boolean))];

        if (agentComissionIds.length <= 0 || agentIds.length <= 0) {
            showWarning("Error!", "Please select some remittance first.");
            return;
        }

        if (agentIds.length > 1) {
            showWarning("Error!", "You can not select multiple agents remittance at a time.");
            return;
        }

        resetScheduleModal();
        scheduleRemitPaymentModal?.show();

        axios({
            method: "post",
            url: route("agent.management.remittances.details"),
            data: { agent_comission_ids: agentComissionIds },
            headers: { "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content") },
        })
            .then((response) => {
                if (response.status === 200) {
                    $("#scheduleRemitPaymentModal .theScheduleLoader").fadeOut("fast", function () {
                        $("#scheduleRemitPaymentModal .theScheduleContent").html(response.data.html).fadeIn("fast");
                    });

                    window.setTimeout(() => {
                        $("#scheduleRemitPaymentModal .datepickers").each(function () {
                            new Litepicker({
                                element: this,
                                ...payDateOption,
                            });
                        });
                    }, 350);
                }
            })
            .catch((error) => {
                scheduleRemitPaymentModal?.hide();
                showWarning("Error!", error.response?.data?.msg || "Something went wrong. Please try again.");
            });
    });

    $("#scheduleRemitPaymentForm").on("submit", function (event) {
        event.preventDefault();

        const form = document.getElementById("scheduleRemitPaymentForm");
        const $form = $(form);
        const $button = $("#schedulePayBtn");
        let errorCount = 0;

        $form.find(".acc__input-error").html("");
        setButtonBusy($button, true);

        $("#scheduleRemitPaymentForm .theScheduleContent .datepickers").each(function () {
            if ($(this).val() === "") {
                errorCount += 1;
                $(this).siblings(".acc__input-error").html("This field is required.");
            }
        });

        if (errorCount > 0) {
            setButtonBusy($button, false);
            return;
        }

        axios({
            method: "post",
            url: route("agent.management.remittances.store.payment"),
            data: new FormData(form),
            headers: { "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content") },
        })
            .then((response) => {
                if (response.status === 200) {
                    scheduleRemitPaymentModal?.hide();
                    showSuccess("Congratulations!", "Agent comission remittances successfully scheduled for payment.");
                    agentRemittanceListTable.reload();
                }
            })
            .catch((error) => {
                if (error.response?.status === 422 && error.response.data.errors) {
                    Object.entries(error.response.data.errors).forEach(([key, value]) => {
                        const message = Array.isArray(value) ? value[0] : value;
                        $form.find(`.${key}`).addClass("border-danger");
                        $form.find(`.error-${key}`).html(escapeHtml(message));
                    });
                } else {
                    showWarning("Error!", error.response?.data?.msg || "Something went wrong. Please try again.");
                }
            })
            .finally(() => {
                setButtonBusy($button, false);
            });
    });
})();
