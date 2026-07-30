import xlsx from "xlsx";
import { createIcons, icons } from "lucide";
import Tabulator from "tabulator-tables";
import TomSelect from "tom-select";

("use strict");

const escapeHtml = (value) => {
    const div = document.createElement("div");
    div.textContent = value == null ? "" : String(value);
    return div.innerHTML;
};

const avatarPalette = ["#0b6b66", "#6d4bb0", "#2f6ea5", "#2b8754", "#c65a2a", "#9f2945"];

const avatarColor = (value) => {
    const source = String(value || "applicant");
    let hash = 0;

    for (let index = 0; index < source.length; index += 1) {
        hash = source.charCodeAt(index) + ((hash << 5) - hash);
    }

    return avatarPalette[Math.abs(hash) % avatarPalette.length];
};

const fallbackInitials = (name) => {
    const clean = String(name || "Applicant").replace(/^(Mr|Miss|Mrs|Ms)\.?\s+/i, "").trim();
    const words = clean.split(/\s+/).filter(Boolean);
    const first = words[0]?.slice(0, 1) || "A";
    const second = (words[1] || words[0] || "P").slice(0, 1);

    return `${first}${second}`.toUpperCase();
};

const isRealPhoto = (value) => {
    const photo = String(value || "");

    return photo !== "" && photo.slice(0, 5) !== "data:";
};

const renderApplicantAvatar = (row) => {
    const initials = escapeHtml(row.initials || fallbackInitials(row.name));
    const photo = String(row.photo_url || "");

    if (isRealPhoto(photo)) {
        return `
            <span class="agm-profile-applicant-avatar" style="background:${avatarColor(row.name || row.id)}">
                <img src="${escapeHtml(photo)}" alt="${escapeHtml(row.name || "Applicant")}">
            </span>
        `;
    }

    return `<span class="agm-profile-applicant-avatar" style="background:${avatarColor(row.name || row.id)}">${initials}</span>`;
};

const chipTone = (value, fallback = "slate") => {
    const text = String(value || "").toLowerCase();
    if (/completed|active|enrolled|approved|accepted|success|passed/.test(text)) return "green";
    if (/withdrawn|refused|discarded|rejected|cancel|fail|inactive/.test(text)) return "red";
    if (/progress|pending|incomplete|review|waiting/.test(text)) return "gold";

    return fallback;
};

const renderStatusChip = (value, tone = null, withIcon = false) => {
    const label = String(value || "").trim();
    if (label === "") {
        return `<span class="agm-agent-empty-mark">&mdash;</span>`;
    }

    const safeTone = tone || chipTone(label);
    const icon = withIcon && safeTone === "green"
        ? '<i data-lucide="check"></i>'
        : withIcon && safeTone === "red"
            ? '<i data-lucide="x"></i>'
            : '<b></b>';

    return `<span class="agm-profile-status-chip is-${safeTone}">${icon}${escapeHtml(label)}</span>`;
};

var applicantApplicantionList = (function () {
    let tableContent;
    let totalRows = 0;
    let resizeBound = false;
    const tableSelector = "#applicantApplicantionList";

    const getAgentId = () => $('#addressForm input[name="id"]').val();

    const getParams = () => ({
        refno: ($("#application_no").val() || "").trim(),
        email: ($("#applicantEmail").val() || "").trim(),
        phone: ($("#applicantPhone").val() || "").trim(),
        semesters: $("#semesters").val() || [],
        statuses: $("#statuses").val() || [],
        courses: $("#courses").val() || [],
        agents: $("#agents").val() || [],
        querystr: ($("#query-CNTR").val() || "").trim(),
    });

    const syncFooter = () => {
        window.requestAnimationFrame(() => {
            const tableElement = document.querySelector(tableSelector);
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

    var _tableGen = function () {
        const id = getAgentId();
        const listUrl = route("agent-user.query.list", id);

        if (tableContent) {
            tableContent.setData(listUrl, getParams());
            syncFooter();
            return;
        }

        tableContent = new Tabulator(tableSelector, {
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
                    field: "application_no",
                    width: 92,
                    minWidth: 86,
                    formatter(cell) {
                        return `<span class="agm-agent-id">${escapeHtml(cell.getValue())}</span>`;
                    },
                },
                {
                    title: "Name",
                    field: "name",
                    headerSort: false,
                    headerHozAlign: "left",
                    minWidth: 190,
                    widthGrow: 2,
                    formatter(cell) {
                        const row = cell.getData();

                        return `
                            <div class="agm-profile-applicant">
                                ${renderApplicantAvatar(row)}
                                <strong>${escapeHtml(row.name)}</strong>
                            </div>
                        `;
                    },
                },
                {
                    title: "DOB",
                    field: "dob",
                    headerSort: false,
                    headerHozAlign: "left",
                    width: 100,
                    formatter(cell) {
                        return `<span class="agm-profile-muted">${escapeHtml(cell.getValue())}</span>`;
                    },
                },
                {
                    title: "Gender",
                    field: "gender",
                    headerSort:false,
                    headerHozAlign: "left",
                    width: 76,
                    formatter(cell) {
                        return `<span class="agm-profile-muted">${escapeHtml(cell.getValue())}</span>`;
                    },
                },
                {
                    title: "Course",
                    field: "course",
                    headerSort: false,
                    headerHozAlign: "left",
                    minWidth: 180,
                    widthGrow: 2,
                    formatter(cell) {
                        return `<span class="agm-profile-course" title="${escapeHtml(cell.getValue())}">${escapeHtml(cell.getValue())}</span>`;
                    },
                },
                {
                    title: "Submission Date",
                    field: "submission_date",
                    headerHozAlign: "left",
                    width: 118,
                    formatter(cell) {
                        return `<span class="agm-profile-muted">${escapeHtml(cell.getValue())}</span>`;
                    },
                },
                {
                    title: "RF code",
                    field: "referral_code",
                    headerHozAlign: "left",
                    width: 92,
                    formatter(cell) {
                        const value = String(cell.getValue() || "").trim();
                        return value === "" ? `<span class="agm-agent-empty-mark">&mdash;</span>` : `<span class="agm-agent-code">${escapeHtml(value)}</span>`;
                    },
                },
                {
                    title: "Status",
                    field: "status",
                    headerSort: false,
                    headerHozAlign: "left",
                    width: 116,
                    formatter(cell) {
                        return renderStatusChip(cell.getValue(), chipTone(cell.getValue(), "gold"), false);
                    },
                },
                
                {
                    title: "Current Status",
                    field: "current_status",
                    headerSort: false,
                    headerHozAlign: "left",
                    width: 188,
                    formatter(cell) {
                        return renderStatusChip(cell.getValue(), null, true);
                    },
                },
                {
                    title: "Actions",
                    field: "id",
                    headerSort: false,
                    hozAlign: "right",
                    headerHozAlign: "right",
                    width: 88,
                    minWidth: 84,
                    download: false,
                    formatter(cell) {
                        if (cell.getData().submission_date == '') {
                            return `<span class="agm-agent-empty-mark">&mdash;</span>`;
                        }

                        return `
                            <span class="agm-agent-actions">
                                <a href="${route('admission.show', cell.getData().id)}" class="agm-agent-action agm-agent-action--view" title="View applicant">
                                    <i data-lucide="eye"></i>
                                </a>
                            </span>
                        `;
                    },
                },
            ],
            ajaxResponse: function (url, params, response) {
                totalRows = Number(response.all_rows || 0);
                syncFooter();

                return response;
            },
            dataLoaded() {
                syncFooter();
            },
            pageLoaded() {
                syncFooter();
            },
            renderComplete() {
                createIcons({
                    icons,
                    "stroke-width": 1.8,
                    nameAttr: "data-lucide",
                });
                syncFooter();
            }
        });

        if (!resizeBound) {
            resizeBound = true;
            window.addEventListener("resize", () => {
                tableContent?.redraw();
                createIcons({
                    icons,
                    "stroke-width": 1.8,
                    nameAttr: "data-lucide",
                });
                syncFooter();
            });
        }
    };
    return {
        init: function () {
            _tableGen();
        },
    };
})();
function checkPasswordStrength(password) {
    // Initialize variables
    let strength = 0;
    let tips = "";
    //let lowUpperCase = document.querySelector(".low-upper-case i");

    //let number = document.querySelector(".one-number i");
    //let specialChar = document.querySelector(".one-special-char i");
    //let eightChar = document.querySelector(".eight-character i");

    //If password contains both lower and uppercase characters
    if (password.match(/([a-z].*[A-Z])|([A-Z].*[a-z])/)) {
        strength += 1;
        //lowUpperCase.classList.remove('fa-circle');
        //lowUpperCase.classList.add('fa-check');
    } else {
        //lowUpperCase.classList.add('fa-circle');
        //lowUpperCase.classList.remove('fa-check');
    }
    //If it has numbers and characters
    if (password.match(/([0-9])/)) {
        strength += 1;
        //number.classList.remove('fa-circle');
        //number.classList.add('fa-check');
    } else {
        //number.classList.add('fa-circle');
        //number.classList.remove('fa-check');
    }
    //If it has one special character
    if (password.match(/([!,%,&,@,#,$,^,*,?,_,~])/)) {
        strength += 1;
        //specialChar.classList.remove('fa-circle');
        //specialChar.classList.add('fa-check');
    } else {
        //specialChar.classList.add('fa-circle');
        //specialChar.classList.remove('fa-check');
    }
    //If password is greater than 7
    if (password.length > 7) {
        strength += 1;
        //eightChar.classList.remove('fa-circle');
        //eightChar.classList.add('fa-check');
    } else {
        //eightChar.classList.add('fa-circle');
        //eightChar.classList.remove('fa-check');   
    }
   
    // Return results
    if (strength < 2) {
        return strength;
    } else if (strength === 2) {
        return strength;
    } else if (strength === 3) {
        return strength;
    } else {
        return strength;
    }
}
(function () {

    if($('#applicantApplicantionList').length > 0){
        applicantApplicantionList.init();
        let tomOptions = {
            plugins: {
                dropdown_input: {}
            },
            placeholder: '',
            dropdownParent: 'body',
            dropdownClass: 'ts-dropdown lcc-tom-float',
            persist: false,
            create: false,
            allowEmptyOption: true,
            onDelete: function (values) {
                return confirm( values.length > 1 ? "Are you sure you want to remove these " + values.length + " items?" : 'Are you sure you want to remove "' +values[0] +'"?' );
            },
        };

        let tomOptionsMul = {
            dropdownParent: 'body',
            dropdownClass: 'ts-dropdown lcc-tom-float',
            
            ...tomOptions,
            plugins: {
                ...tomOptions.plugins,
                remove_button: {
                    title: "Remove this item",
                },
            }
        };

        
        var semesters = new TomSelect('#semesters', tomOptionsMul);
        var courses = new TomSelect('#courses', tomOptionsMul);

        var statuses = new TomSelect('#statuses', tomOptionsMul);
        var agents = new TomSelect('#agents', tomOptionsMul);
        const filterSelects = [semesters, courses, statuses, agents];

            // Filter function
            function filterHTMLForm() {
                applicantApplicantionList.init();
            }
            // On click go button
            $("#studentGroupSearchSubmitBtn").on("click", function (event) {
                filterHTMLForm();
            });
            $("#studentGroupSearchResetBtn").on("click", function () {
                $("#application_no, #applicantEmail, #applicantPhone, #query-CNTR").val("");
                filterSelects.forEach((select) => select.clear(true));
                filterHTMLForm();
            });

    }
    const succModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#successModal"));
    const editContactModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#editContactModal"));
    // if($('#agentTableId').length > 0){
    //     // Init Table
    //     agentTableId.init();
    //     //const succModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#successModal"));
    //     //const confirmModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#confirmModal"));

    // }
    /*Resend Verification Modal*/
    if($('#resendverification-staff').length > 0) {

        $("#resendverification-staff").on("submit", function (e) {
            e.preventDefault();
            let editId = $('#resendverification-staff input[name="id"]').val();

            const form = document.getElementById("resendverification-staff");

            document.querySelector('#resend-mail-agent').setAttribute('disabled', 'disabled');
            document.querySelector('#resend-mail-agent .theSend').style.cssText = 'display: none;';
            document.querySelector('#resend-mail-agent .theLoading').style.cssText = 'display: inline-block;';

            let form_data = new FormData(form);

            axios({
                method: "post",
                url:  route('agent.verification.send.from.staff', editId),
                data: form_data,
                headers: {
                    "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
                },
            }).then((response) => {
                if (response.status == 200) {
                    document.querySelector("#resend-mail-agent").removeAttribute("disabled");
                    document.querySelector("#resend-mail-agent svg.theLoading").style.cssText = "display: none;";
                    document.querySelector("#resend-mail-agent svg.theSend").style.cssText = "display: inline-block;";
                    succModal.show();
                    
                    $("#successModal .successModalTitle").html("Email Sent!");
                    $("#successModal .successModalDesc").html('Verification email successfully sent.');
                    
                    location.reload();
                }
            }).catch((error) => {
                document.querySelector("#resend-mail-agent").removeAttribute("disabled");
                document.querySelector("#resend-mail-agent svg.theLoading").style.cssText = "display: none;";
                document.querySelector("#resend-mail-agent svg.theSend").style.cssText = "display: inline-block;";
                if (error.response) {
                    if (error.response.status == 422) {
                        for (const [key, val] of Object.entries(error.response.data.errors)) {
                            $(`#editForm .${key}`).addClass('border-danger')
                            $(`#editForm  .error-${key}`).html(val)
                        }
                    }else if (error.response.status == 304) {
                        let message = error.response.statusText;
                        succModal.show();
                        document.getElementById("successModal").addEventListener("shown.tw.modal", function (event) {
                            
                            $("#successModal .successModalTitle").html("Oops!");
                            $("#successModal .successModalDesc").html(message);
                        });
                    } else {
                        console.log("error");
                    }
                }
            });
        });
    }
})()
