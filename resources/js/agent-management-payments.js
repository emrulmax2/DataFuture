import { createIcons, icons } from "lucide";
import Tabulator from "tabulator-tables";

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
    const source = String(value || "agent payment");
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
    const $loader = $button.find("svg.theLoader").last();
    const $icon = $button.find("svg.theIcon").first();

    $button.prop("disabled", busy);
    $loader.css("display", busy ? "inline-block" : "none");
    $icon.css("opacity", busy ? "0" : "1");
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
    return `<span class="agm-remittance-ref">${escapeHtml(reference || "-")}</span>`;
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
    const meta = statusMeta(row.status);

    return `
        <span class="agm-remittance-status is-${meta.tone}">
            <i data-lucide="${meta.icon}"></i>
            ${escapeHtml(meta.label)}
        </span>
    `;
};

const renderTransaction = (row) => {
    if (Number(row.acc_transaction_id || 0) <= 0 || !row.transaction_code) {
        return `<span class="agm-remittance-muted">-</span>`;
    }

    return `
        <div class="agm-payment-transaction">
            <strong>${escapeHtml(row.transaction_code)}</strong>
            <small>${escapeHtml(row.transaction_date || "")}</small>
        </div>
    `;
};

const renderActions = (row) => {
    const id = escapeHtml(row.id);
    const amount = escapeHtml(row.amount || 0);
    const transactionId = Number(row.acc_transaction_id || 0);

    let linkAction = "";
    if (transactionId > 0) {
        const detailsUrl = escapeHtml(route("agent.management.remittances.payment.details", transactionId));
        linkAction = `
            <a href="${detailsUrl}" class="agm-agent-action agm-agent-action--link" title="View linked transaction" aria-label="View linked transaction">
                <i data-lucide="link"></i>
            </a>
        `;
    } else {
        linkAction = `
            <button data-id="${id}" data-amount="${amount}" type="button" data-tw-toggle="modal" data-tw-target="#linkTransactionModal" class="linked_trans_btn agm-agent-action agm-agent-action--link" title="Link transaction" aria-label="Link transaction">
                <i data-lucide="link"></i>
            </button>
        `;
    }

    return `
        <div class="agm-remittance-actions agm-payment-actions">
            ${linkAction}
            <button data-id="${id}" type="button" class="send_email agm-agent-action agm-agent-action--mail" title="Send email" aria-label="Send payment email">
                <i data-lucide="mail" class="theIcon"></i>
                <svg style="display: none;" width="25" viewBox="-2 -2 42 42" xmlns="http://www.w3.org/2000/svg" stroke="currentColor" class="theLoader">
                    <g fill="none" fill-rule="evenodd">
                        <g transform="translate(1 1)" stroke-width="4">
                            <circle stroke-opacity=".5" cx="18" cy="18" r="18"></circle>
                            <path d="M36 18c0-9.94-8.06-18-18-18">
                                <animateTransform attributeName="transform" type="rotate" from="0 18 18" to="360 18 18" dur="1s" repeatCount="indefinite"></animateTransform>
                            </path>
                        </g>
                    </g>
                </svg>
            </button>
        </div>
    `;
};

var agentRemittPaymentsListTable = (function () {
    let tableContent;
    let totalRows = 0;

    const $table = $("#agentRemittPaymentsListTable");
    const listUrl = route("agent.management.remittances.payment.list");

    const getParams = () => ({
        querystr: ($("#query").val() || "").trim(),
        status: $("#status").val() || "1",
    });

    const syncFooter = () => {
        window.requestAnimationFrame(() => {
            const tableElement = $table.get(0);
            if (!tableElement || !tableContent) return;

            const footerContents = tableElement.querySelector(".tabulator-footer .tabulator-footer-contents");
            const paginator = tableElement.querySelector(".tabulator-paginator");

            if (!footerContents || !paginator) return;

            tableElement.classList.add("agm-footer-synced");
            footerContents.classList.add("agm-remittance-footer-layout", "agm-payments-footer-layout");

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

            let pageSizeGroup = footerContents.querySelector(".agm-remittance-page-size-group");
            if (!pageSizeGroup) {
                pageSizeGroup = document.createElement("span");
                pageSizeGroup.className = "agm-remittance-page-size-group";
            }
            pageSizeGroup.append(label, pageSize);
            footerLeft.append(pageSizeGroup);

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
        });
    };

    const _tableGen = function () {
        tableContent = new Tabulator("#agentRemittPaymentsListTable", {
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
            placeholder: "No matching payment records found",
            columns: [
                {
                    title: "#ID",
                    field: "id",
                    width: 82,
                    headerSort: false,
                    formatter(cell) {
                        return `<span class="agm-remittance-id">${escapeHtml(cell.getValue())}</span>`;
                    },
                },
                {
                    title: "Reference",
                    field: "reference",
                    width: 140,
                    headerHozAlign: "left",
                    headerSort: false,
                    formatter(cell) {
                        return renderReference(cell.getValue());
                    },
                },
                {
                    title: "Date",
                    field: "date",
                    width: 165,
                    headerHozAlign: "left",
                    headerSort: false,
                    formatter(cell) {
                        return `<span class="agm-remittance-muted">${escapeHtml(cell.getValue())}</span>`;
                    },
                },
                {
                    title: "Referral Name",
                    field: "agent_name",
                    minWidth: 260,
                    widthGrow: 1,
                    headerHozAlign: "left",
                    headerSort: false,
                    formatter(cell) {
                        return renderReferral(cell.getData());
                    },
                },
                {
                    title: "Terms",
                    field: "semsters",
                    width: 190,
                    headerHozAlign: "left",
                    headerSort: false,
                    formatter(cell) {
                        return `<span class="agm-remittance-muted">${escapeHtml(cell.getValue())}</span>`;
                    },
                },
                {
                    title: "Remit Ref.",
                    field: "remittance_refs",
                    width: 170,
                    headerHozAlign: "left",
                    headerSort: false,
                    formatter(cell) {
                        return `<div class="agm-payment-remit-links">${cell.getData().remittance_refs || "-"}</div>`;
                    },
                },
                {
                    title: "Transaction",
                    field: "acc_transaction_id",
                    width: 170,
                    headerHozAlign: "left",
                    headerSort: false,
                    visible: Number($("#status").val() || 1) === 2,
                    formatter(cell) {
                        return renderTransaction(cell.getData());
                    },
                },
                {
                    title: "Status",
                    field: "status",
                    width: 150,
                    headerHozAlign: "left",
                    headerSort: false,
                    formatter(cell) {
                        return renderStatus(cell.getData());
                    },
                },
                {
                    title: "Amount",
                    field: "amount_html",
                    width: 150,
                    hozAlign: "right",
                    headerHozAlign: "right",
                    headerSort: false,
                    formatter(cell) {
                        return `<span class="agm-remittance-money">${escapeHtml(cell.getValue())}</span>`;
                    },
                },
                {
                    title: "Actions",
                    field: "id",
                    headerSort: false,
                    hozAlign: "right",
                    headerHozAlign: "right",
                    width: 126,
                    minWidth: 126,
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
        });

        window.addEventListener("resize", () => {
            tableContent.redraw();
            refreshIcons();
        });
    };

    const reload = () => {
        const $button = $("#tabulator-html-filter-go");

        setButtonBusy($button, true);

        if (tableContent) {
            const transactionColumn = tableContent.getColumn("acc_transaction_id");

            if (transactionColumn) {
                if (Number($("#status").val() || 1) === 2) {
                    transactionColumn.show();
                } else {
                    transactionColumn.hide();
                }
            }
        }

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
    };
})();

(function () {
    if (!$("#agentRemittPaymentsListTable").length) return;

    agentRemittPaymentsListTable.init();
    refreshIcons();

    const successModal = getModal("#successModal");
    const warningModal = getModal("#warningModal");
    const linkTransactionModal = getModal("#linkTransactionModal");

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

    const resetLinkModal = () => {
        const $modal = $("#linkTransactionModal");

        $modal.find(".acc__input-error").html("");
        $modal.find("#transaction_code").val("");
        $modal.find("#transaction_id").val("");
        $modal.find(".autoFillDropdown").html("").fadeOut();
        $modal.find('[name="agent_comission_payment_id"]').val("0");
        $modal.find('[name="agent_comission_total"]').val("0");
        $modal.find(".modal-body .agm-payment-amount-message").remove();
        setButtonBusy($("#linkTransBtn"), false);
    };

    document.getElementById("linkTransactionModal")?.addEventListener("hide.tw.modal", resetLinkModal);

    $("#tabulatorFilterForm").on("submit", function (event) {
        event.preventDefault();
        agentRemittPaymentsListTable.reload();
    });

    $("#tabulator-html-filter-reset").on("click", function (event) {
        event.preventDefault();
        $("#query").val("");
        $("#status").val("1");
        agentRemittPaymentsListTable.reload();
    });

    $("#agentRemittPaymentsListTable").on("click", ".linked_trans_btn", function (event) {
        event.preventDefault();

        const $button = $(this);

        $("#linkTransactionModal [name=\"agent_comission_payment_id\"]").val($button.attr("data-id"));
        $("#linkTransactionModal [name=\"agent_comission_total\"]").val($button.attr("data-amount"));
        linkTransactionModal?.show();
    });

    /* ------------------------------------------------------------------ *
     * Transaction picker
     *
     * The endpoint returns rows; the markup is built here because whether a
     * transaction matches the remittance total is only known on this page,
     * and that is the one thing worth showing before the click.
     * ------------------------------------------------------------------ */

    const escapeHtml = (value) =>
        String(value ?? "").replace(/[&<>"']/g, (char) => {
            return { "&": "&amp;", "<": "&lt;", ">": "&gt;", '"': "&quot;", "'": "&#39;" }[char];
        });

    /** Highlights the typed part of a reference so the match is visible. */
    const markQuery = (code, query) => {
        const safe = escapeHtml(code);
        const needle = String(query || "").trim();
        if (!needle) return safe;

        const at = safe.toLowerCase().indexOf(escapeHtml(needle).toLowerCase());
        if (at < 0) return safe;

        const end = at + needle.length;

        return `${safe.slice(0, at)}<mark>${safe.slice(at, end)}</mark>${safe.slice(end)}`;
    };

    const emptyResult = (message) =>
        `<li class="agm-trans-empty">
            <a href="javascript:void(0);" class="disable">
                <i data-lucide="search-x"></i>
                <span>
                    <strong>${escapeHtml(message)}</strong>
                    Transactions already linked to a remittance are not listed.
                </span>
            </a>
        </li>`;

    /** One result row. `href` / `data-id` / `data-amount` drive the click handler. */
    const resultRow = (row, query, commissionTotal) => {
        const matches = commissionTotal > 0 && commissionTotal.toFixed(2) === Number(row.amount || 0).toFixed(2);
        const meta = [row.date, row.bank, row.detail].filter(Boolean).map(escapeHtml);

        return `<li>
            <a href="${escapeHtml(row.code)}" data-id="${escapeHtml(row.id)}" data-amount="${escapeHtml(row.amount)}" class="agm-trans-option${matches ? " is-match" : ""}">
                <span class="agm-trans-option__icon"><i data-lucide="${matches ? "check-circle" : "credit-card"}"></i></span>
                <span class="agm-trans-option__body">
                    <span class="agm-trans-option__top">
                        <span class="agm-trans-option__code">${markQuery(row.code, query)}</span>
                        <span class="agm-trans-option__amount">${escapeHtml(row.amount_label)}</span>
                    </span>
                    ${meta.length ? `<span class="agm-trans-option__meta">${meta.map((part) => `<span>${part}</span>`).join("")}</span>` : ""}
                </span>
                ${matches ? '<span class="agm-trans-option__flag">Matches total</span>' : ""}
            </a>
        </li>`;
    };

    const renderResults = (data, query) => {
        const rows = Array.isArray(data.rows) ? data.rows : [];
        if (rows.length === 0) return emptyResult("No transaction found");

        const commissionTotal = Number($('#linkTransactionModal [name="agent_comission_total"]').val() || 0);
        let html = rows.map((row) => resultRow(row, query, commissionTotal)).join("");

        // The cap is the endpoint's, so say so rather than let the list look complete.
        if (data.total > data.shown) {
            html += `<li class="agm-trans-more">Showing the ${data.shown} most recent of ${data.total} matches — keep typing to narrow it down.</li>`;
        }

        return html;
    };

    let transactionSearchTimer = null;

    $("#linkTransactionModal #transaction_code").on("keyup", function () {
        const $input = $(this);
        const searchValue = $input.val();
        const $dropdown = $input.siblings(".autoFillDropdown");

        window.clearTimeout(transactionSearchTimer);

        if (searchValue.length < 3) {
            $dropdown.html("").fadeOut();
            return;
        }

        // Debounced: the old handler fired a request on every keystroke.
        transactionSearchTimer = window.setTimeout(() => {
            axios({
                method: "post",
                url: route("agent.management.remittance.search.transaction"),
                data: { SearchVal: searchValue },
                headers: { "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content") },
            })
                .then((response) => {
                    if (response.status === 200) {
                        $dropdown.html(renderResults(response.data, searchValue)).fadeIn();
                        refreshIcons();
                    }
                })
                .catch(() => {
                    $dropdown.html("").fadeOut();
                });
        }, 250);
    });

    $("#linkTransactionModal .autoFillDropdown").on("click", "li a:not(.disable)", function (event) {
        event.preventDefault();

        const $item = $(this);
        const transactionCode = $item.attr("href");

        if (!transactionCode || transactionCode === "javascript:void(0);") {
            return;
        }

        const $dropdown = $item.closest(".autoFillDropdown");
        const commissionTotal = Number($("#linkTransactionModal [name=\"agent_comission_total\"]").val() || 0);
        const transactionId = $item.attr("data-id");
        const transactionAmount = Number($item.attr("data-amount") || 0);
        const matches = commissionTotal.toFixed(2) === transactionAmount.toFixed(2);

        $dropdown.siblings(".transaction_code").val(transactionCode);
        $dropdown.siblings(".transaction_id").val(transactionId);
        $dropdown.html("").fadeOut();

        $("#linkTransactionModal .modal-body .agm-payment-amount-message").remove();
        $("#linkTransactionModal .modal-body").append(`
            <div class="agm-payment-amount-message is-${matches ? "success" : "danger"}">
                <i data-lucide="${matches ? "check-circle" : "alert-triangle"}"></i>
                <span>
                    <strong>${matches ? "Matched" : "Amount mismatch"}</strong>
                    ${matches ? "The transaction amount matches the remittance total." : "The transaction amount does not match the remittance total."}
                </span>
            </div>
        `);

        refreshIcons();
    });

    $("#linkTransactionForm").on("submit", function (event) {
        event.preventDefault();

        const form = document.getElementById("linkTransactionForm");
        const $button = $("#linkTransBtn");

        setButtonBusy($button, true);

        axios({
            method: "post",
            url: route("agent.management.remittance.linked.transaction"),
            data: new FormData(form),
            headers: { "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content") },
        })
            .then((response) => {
                setButtonBusy($button, false);

                if (response.status === 200) {
                    linkTransactionModal?.hide();
                    showSuccess("Congratulations!", "Transaction successfully linked with the payment.");
                    agentRemittPaymentsListTable.reload();
                }
            })
            .catch((error) => {
                setButtonBusy($button, false);

                if (error.response && error.response.status === 422) {
                    showWarning("Error!", error.response.data.msg || "Please check the transaction and try again.");
                }
            });
    });

    $("#agentRemittPaymentsListTable").on("click", ".send_email", function (event) {
        event.preventDefault();

        const $button = $(this);
        const id = $button.attr("data-id");

        setButtonBusy($button, true);

        axios({
            method: "post",
            url: route("agent.management.remittance.payment.send.mail"),
            data: { payment_id: id },
            headers: { "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content") },
        })
            .then((response) => {
                setButtonBusy($button, false);

                if (response.status === 200) {
                    showSuccess("Congratulations!", "Mail successfully sent to the agent.");
                }
            })
            .catch((error) => {
                setButtonBusy($button, false);

                if (error.response) {
                    showWarning("Error!", "Unable to send this payment email right now.");
                }
            });
    });
})();
