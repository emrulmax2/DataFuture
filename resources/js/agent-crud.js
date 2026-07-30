import xlsx from "xlsx";
import { createIcons, icons } from "lucide";
import Tabulator from "tabulator-tables";

("use strict");

const escapeHtml = (value) => {
    const div = document.createElement("div");
    div.textContent = value == null ? "" : String(value);
    return div.innerHTML;
};

const palette = ["#0b6b66", "#6d4bb0", "#2f6ea5", "#2b8754", "#c65a2a", "#9f2945"];

const avatarColor = (value) => {
    const source = String(value || "agent");
    let hash = 0;

    for (let index = 0; index < source.length; index += 1) {
        hash = source.charCodeAt(index) + ((hash << 5) - hash);
    }

    return palette[Math.abs(hash) % palette.length];
};

const isRealPhoto = (value) => {
    const photo = String(value || "");

    return photo !== "" && photo.slice(0, 5) !== "data:";
};

const renderAvatar = (row) => {
    const name = escapeHtml(row.name || "Agent");
    const photo = String(row.photo_url || "");

    if (isRealPhoto(photo)) {
        return `
            <span class="agm-agent-avatar agm-agent-avatar--photo" style="background:${avatarColor(row.name || row.id)}">
                <img src="${escapeHtml(photo)}" alt="${name}" data-fallback-text="${escapeHtml(row.initials || "AG")}">
            </span>
        `;
    }

    return `<span class="agm-agent-avatar" style="background:${avatarColor(row.name || row.id)}">${escapeHtml(row.initials || "AG")}</span>`;
};

const setButtonBusy = ($button, busy) => {
    const $loader = $button.find("svg").not("[data-lucide]").last();

    $button.prop("disabled", busy);
    $loader.css("display", busy ? "inline-block" : "none");
};

const clearFormErrors = ($modal) => {
    $modal.find(".acc__input-error").html("");
    $modal.find("input, select, textarea").removeClass("is-invalid border-danger");
};

const resetStrength = ($form) => {
    $form.find(".agm-agent-strength span").removeClass("is-danger is-warning is-success");
};

const passwordStrength = (password) => {
    let strength = 0;

    if (password.match(/([a-z].*[A-Z])|([A-Z].*[a-z])/)) strength += 1;
    if (password.match(/([0-9])/)) strength += 1;
    if (password.match(/([!,%,&,@,#,$,^,*,?,_,~])/)) strength += 1;
    if (password.length > 7) strength += 1;

    return strength;
};

const paintPasswordStrength = (input) => {
    const strength = passwordStrength(input.value || "");
    const $bars = $(input).closest(".agm-agent-form-field").find(".agm-agent-strength span");

    $bars.removeClass("is-danger is-warning is-success");

    if (strength >= 1) $bars.eq(0).addClass("is-danger");
    if (strength >= 2) $bars.eq(1).addClass("is-warning");
    if (strength >= 3) $bars.eq(2).addClass("is-success");
    if (strength >= 4) $bars.eq(3).addClass("is-success");
};

var agentTableId = (function () {
    let tableContent;
    let totalRows = 0;

    const $table = $("#agentTableId");
    const listUrl = route("agent-user.list");

    const getParams = () => ({
        querystr: ($("#query-Agent").val() || "").trim(),
        status: $("#status-Agent").val() || "1",
    });

    const syncFooter = () => {
        window.requestAnimationFrame(() => {
            const tableElement = $table.get(0);
            if (!tableElement || !tableContent) return;

            const paginator = tableElement.querySelector(".tabulator-paginator");
            const label = paginator?.querySelector("label");
            const pageSize = paginator?.querySelector(".tabulator-page-size");

            if (!paginator || !label || !pageSize) return;

            label.textContent = "Rows";

            let meta = paginator.querySelector(".agm-agent-page-meta");
            if (!meta) {
                meta = document.createElement("span");
                meta.className = "agm-agent-page-meta";
                paginator.insertAdjacentElement("afterbegin", meta);
            }

            let range = paginator.querySelector(".agm-agent-page-range");
            if (!range) {
                range = document.createElement("span");
                range.className = "agm-agent-page-range";
            }

            meta.appendChild(label);
            meta.appendChild(pageSize);
            meta.appendChild(range);

            let pagination = paginator.querySelector(".agm-agent-pagination-control");
            if (!pagination) {
                pagination = document.createElement("span");
                pagination.className = "agm-agent-pagination-control";
                paginator.appendChild(pagination);
            }

            Array.from(paginator.children).forEach((child) => {
                if (child === meta || child === pagination) return;
                if (child.matches(".tabulator-page, .tabulator-pages")) {
                    pagination.appendChild(child);
                }
            });

            const currentPage = Number(tableContent.getPage ? tableContent.getPage() : 1) || 1;
            const rawSize = tableContent.getPageSize ? tableContent.getPageSize() : 10;
            const pageSizeValue = rawSize === true ? totalRows : Number(rawSize) || totalRows || 10;
            const start = totalRows > 0 ? (currentPage - 1) * pageSizeValue + 1 : 0;
            const end = totalRows > 0 ? Math.min(currentPage * pageSizeValue, totalRows) : 0;

            range.textContent = `${start}-${end} of ${totalRows}`;
        });
    };

    const _tableGen = function () {
        tableContent = new Tabulator("#agentTableId", {
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
            placeholder: "No matching records found",
            columns: [
                {
                    title: "#ID",
                    field: "id",
                    width: 112,
                    minWidth: 112,
                    formatter(cell) {
                        return `<span class="agm-agent-id">${escapeHtml(cell.getValue())}</span>`;
                    },
                },
                {
                    title: "Name",
                    field: "name",
                    minWidth: 360,
                    widthGrow: 3,
                    headerHozAlign: "left",
                    formatter(cell) {
                        const row = cell.getData();
                        const email = row.email ? `<small>${escapeHtml(row.email)}</small>` : "";

                        return `
                            <div class="agm-agent-person">
                                ${renderAvatar(row)}
                                <div class="agm-agent-person__copy">
                                    <a href="${route("agent-user.show", row.id)}">${escapeHtml(row.name)}</a>
                                    ${email}
                                </div>
                            </div>
                        `;
                    },
                },
                {
                    title: "Organization",
                    field: "organization",
                    minWidth: 290,
                    widthGrow: 2,
                    headerHozAlign: "left",
                    formatter(cell) {
                        return `<span class="agm-agent-muted">${escapeHtml(cell.getValue())}</span>`;
                    },
                },
                {
                    title: "Code",
                    field: "code",
                    minWidth: 165,
                    widthGrow: 1,
                    headerHozAlign: "left",
                    formatter(cell) {
                        return `<span class="agm-agent-code">${escapeHtml(cell.getValue())}</span>`;
                    },
                },
                {
                    title: "Default",
                    field: "is_default",
                    minWidth: 210,
                    widthGrow: 1,
                    headerHozAlign: "left",
                    formatter(cell) {
                        if (Number(cell.getValue()) === 1) {
                            return `<span class="agm-agent-default"><i data-lucide="check"></i> Default</span>`;
                        }

                        return `<span class="agm-agent-empty-mark">&mdash;</span>`;
                    },
                },
                {
                    title: "Actions",
                    field: "id",
                    headerSort: false,
                    hozAlign: "right",
                    headerHozAlign: "right",
                    width: 150,
                    minWidth: 150,
                    download: false,
                    formatter(cell) {
                        const row = cell.getData();

                        if (row.deleted_at == null) {
                            return `
                                <span class="agm-agent-actions">
                                    <a href="${route("agent-user.show", row.id)}" class="agm-agent-action agm-agent-action--view" title="View Profile">
                                        <i data-lucide="eye-off"></i>
                                    </a>
                                    <button data-id="${escapeHtml(row.id)}" data-tw-toggle="modal" data-tw-target="#editAgentModal" type="button" class="edit_btn agm-agent-action agm-agent-action--edit" title="Edit">
                                        <i data-lucide="pencil"></i>
                                    </button>
                                    <button data-id="${escapeHtml(row.id)}" type="button" class="delete_btn agm-agent-action agm-agent-action--delete" title="Delete">
                                        <i data-lucide="trash-2"></i>
                                    </button>
                                </span>
                            `;
                        }

                        return `
                            <span class="agm-agent-actions">
                                <button data-id="${escapeHtml(row.id)}" type="button" class="restore_btn agm-agent-action agm-agent-action--view" title="Restore">
                                    <i data-lucide="rotate-cw"></i>
                                </button>
                            </span>
                        `;
                    },
                },
            ],
            ajaxResponse: function (url, params, response) {
                totalRows = response.all_rows && response.all_rows > 0 ? response.all_rows : 0;

                return response;
            },
            pageLoaded() {
                syncFooter();
            },
            renderComplete() {
                syncFooter();
                $table.find(".agm-agent-avatar--photo img").off("error").on("error", function () {
                    const $img = $(this);
                    const fallback = $img.attr("data-fallback-text") || "AG";
                    $img.closest(".agm-agent-avatar").removeClass("agm-agent-avatar--photo").text(fallback);
                    $img.remove();
                });
                createIcons({
                    icons,
                    "stroke-width": 1.8,
                    nameAttr: "data-lucide",
                });
            },
        });
    };

    const reload = () => {
        if (!tableContent) {
            _tableGen();
            return;
        }

        tableContent.setData(listUrl, getParams());
    };

    return {
        init: function () {
            if (tableContent) {
                reload();
            } else {
                _tableGen();
            }
        },
        reload,
        download(format, filename, options = {}) {
            if (tableContent) {
                tableContent.download(format, filename, options);
            }
        },
        print() {
            if (tableContent) {
                tableContent.print();
            }
        },
    };
})();

(function () {
    if ($("#agentTableId").length <= 0) return;

    agentTableId.init();

    const addAgentModalEl = document.querySelector("#addAgentModal");
    const editAgentModalEl = document.querySelector("#editAgentModal");
    const addAgentModal = tailwind.Modal.getOrCreateInstance(addAgentModalEl);
    const editAgentModal = tailwind.Modal.getOrCreateInstance(editAgentModalEl);
    const succModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#successModal"));
    const confirmModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#confirmModal"));

    const showSuccess = (title, description) => {
        $("#successModal .successModalTitle").html(title);
        $("#successModal .successModalDesc").html(description);
        succModal.show();
    };

    const showConfirm = (rowID, action, description) => {
        $("#confirmModal .confModTitle").html("Are you sure?");
        $("#confirmModal .confModDesc").html(description);
        $("#confirmModal .agreeWith").attr("data-id", rowID).attr("data-action", action);
        confirmModal.show();
    };

    const filterHTMLForm = () => {
        agentTableId.reload();
    };

    $("#tabulatorFilterForm-Agent")[0].addEventListener("keypress", function (event) {
        let keycode = event.keyCode ? event.keyCode : event.which;
        if (keycode == "13") {
            event.preventDefault();
            filterHTMLForm();
        }
    });

    $("#tabulator-html-filter-go-Agent").on("click", function () {
        filterHTMLForm();
    });

    $("#tabulator-html-filter-reset-Agent").on("click", function () {
        $("#query-Agent").val("");
        $("#status-Agent").val("1");
        filterHTMLForm();
    });

    $("#tabulator-export-csv").on("click", function () {
        agentTableId.download("csv", "agent-list.csv");
    });

    $("#tabulator-export-xlsx").on("click", function () {
        window.XLSX = xlsx;
        agentTableId.download("xlsx", "agent-list.xlsx", {
            sheetName: "Agent List",
        });
    });

    $("#tabulator-print").on("click", function () {
        agentTableId.print();
    });

    addAgentModalEl.addEventListener("hide.tw.modal", function () {
        const $modal = $("#addAgentModal");
        clearFormErrors($modal);
        document.getElementById("addAgentForm").reset();
        resetStrength($modal);
    });

    editAgentModalEl.addEventListener("hide.tw.modal", function () {
        const $modal = $("#editAgentModal");
        clearFormErrors($modal);
        document.getElementById("editAgentForm").reset();
        $("#editAgentModal input[name='id']").val("0");
        resetStrength($modal);
    });

    $("#agentTableId").on("click", ".delete_btn", function () {
        showConfirm($(this).attr("data-id"), "DELETE", "Do you really want to delete this agent? This process cannot be undone.");
    });

    $("#agentTableId").on("click", ".restore_btn", function () {
        showConfirm($(this).attr("data-id"), "RESTORE", "Do you really want to restore this agent?");
    });

    $("#confirmModal .agreeWith").on("click", function () {
        let $agreeBTN = $(this);
        let recordID = $agreeBTN.attr("data-id");
        let action = $agreeBTN.attr("data-action");

        $("#confirmModal button").attr("disabled", "disabled");

        if (action == "DELETE") {
            axios({
                method: "delete",
                url: route("agent-user.destroy", recordID),
                headers: { "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content") },
            })
                .then((response) => {
                    $("#confirmModal button").removeAttr("disabled");
                    if (response.status == 200) {
                        confirmModal.hide();
                        showSuccess("Done!", "Agent data successfully deleted.");
                    }
                    agentTableId.reload();
                })
                .catch((error) => {
                    $("#confirmModal button").removeAttr("disabled");
                    console.log(error);
                });
        } else if (action == "RESTORE") {
            axios({
                method: "post",
                url: route("agent-user.restore", recordID),
                headers: { "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content") },
            })
                .then((response) => {
                    $("#confirmModal button").removeAttr("disabled");
                    if (response.status == 200) {
                        confirmModal.hide();
                        showSuccess("Success!", "Agent data successfully restored.");
                    }
                    agentTableId.reload();
                })
                .catch((error) => {
                    $("#confirmModal button").removeAttr("disabled");
                    console.log(error);
                });
        }
    });

    $("#agentTableId").on("click", ".edit_btn", function () {
        let editId = $(this).attr("data-id");

        axios({
            method: "get",
            url: route("agent-user.edit", editId),
            headers: { "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content") },
        })
            .then((response) => {
                if (response.status == 200) {
                    let dataset = response.data;
                    let agentUser = dataset.agent_user || dataset.AgentUser || {};

                    $("#editAgentModal input[name='first_name']").val(dataset.first_name || "");
                    $("#editAgentModal input[name='last_name']").val(dataset.last_name || "");
                    $("#editAgentModal input[name='organization']").val(dataset.organization || "");
                    $("#editAgentModal input[name='code']").val(dataset.code || "");
                    $("#editAgentModal input[name='email']").val(agentUser.email || "");
                    $("#editAgentModal input[name='id']").val(editId);
                    $("#editAgentModal [name='is_default']").prop("checked", Number(dataset.is_default || 0) === 1);

                    const verified = agentUser.email_verified_at != null;
                    $("#verificationEmail")
                        .removeClass("is-verified is-unverified")
                        .addClass(verified ? "is-verified" : "is-unverified")
                        .html(`<i data-lucide="${verified ? "check-circle" : "x-circle"}"></i>${verified ? "Verified" : "Unverified"}`);

                    createIcons({
                        icons,
                        "stroke-width": 1.8,
                        nameAttr: "data-lucide",
                    });
                }
            })
            .catch((error) => {
                console.log(error);
            });
    });

    const applyValidationErrors = ($form, errors) => {
        Object.entries(errors).forEach(([key, val]) => {
            $form.find(`[name="${key}"]`).addClass("is-invalid");
            $form.find(`.error-${key}`).html(val);
        });
    };

    $("#editAgentForm").on("submit", function (e) {
        e.preventDefault();

        const form = document.getElementById("editAgentForm");
        const $button = $("#updateAgent");
        const agent = $("#editAgentForm input[name='id']").val();

        clearFormErrors($("#editAgentModal"));
        setButtonBusy($button, true);

        axios({
            method: "post",
            url: route("agent-user.update", agent),
            data: new FormData(form),
            headers: { "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content") },
        })
            .then((response) => {
                setButtonBusy($button, false);
                if (response.status == 200) {
                    editAgentModal.hide();
                    showSuccess("Congratulations!", "Agent data successfully updated.");
                }
                agentTableId.reload();
            })
            .catch((error) => {
                setButtonBusy($button, false);
                if (error.response && error.response.status == 422) {
                    applyValidationErrors($("#editAgentForm"), error.response.data.errors);
                } else {
                    console.log(error);
                }
            });
    });

    $("#addAgentForm").on("submit", function (e) {
        e.preventDefault();

        const form = document.getElementById("addAgentForm");
        const $button = $("#saveAgent");

        clearFormErrors($("#addAgentModal"));
        setButtonBusy($button, true);

        axios({
            method: "post",
            url: route("agent-user.store"),
            data: new FormData(form),
            headers: { "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content") },
        })
            .then((response) => {
                setButtonBusy($button, false);
                if (response.status == 200) {
                    addAgentModal.hide();
                    showSuccess("Congratulations!", "Agent data successfully inserted.");
                }
                agentTableId.reload();
            })
            .catch((error) => {
                setButtonBusy($button, false);
                if (error.response && error.response.status == 422) {
                    applyValidationErrors($("#addAgentForm"), error.response.data.errors);
                } else {
                    console.log(error);
                }
            });
    });

    $(".password").on("keyup", function () {
        paintPasswordStrength(this);
    });
})();
