import xlsx from "xlsx";
import { createIcons, icons } from "lucide";
import Tabulator from "tabulator-tables";
import TomSelect from "tom-select";

("use strict");

/* Escape anything that reaches a formatter as raw HTML — course titles and
   status names are free text entered elsewhere in the system. */
function sdrEscape(value) {
    return $("<div/>").text(value == null ? "" : value).html();
}

/* Status names are user-maintained, so the pill colour is keyed off the words
   that actually appear rather than an id we cannot rely on. Anything
   unrecognised falls back to the neutral pill. */
function sdrStatusPill(status) {
    let tone = "";
    const name = (status || "").toLowerCase();

    if (name.includes("active") || name.includes("continu") || name.includes("complet")) {
        tone = " sdr-pill--green";
    } else if (name.includes("withdraw") || name.includes("cancel") || name.includes("terminat")) {
        tone = " sdr-pill--red";
    } else if (name.includes("defer") || name.includes("suspend") || name.includes("hold") || name.includes("break")) {
        tone = " sdr-pill--gold";
    } else if (name.includes("interrupt") || name.includes("transfer") || name.includes("await")) {
        tone = " sdr-pill--blue";
    }

    return status ? '<span class="sdr-pill' + tone + '">' + sdrEscape(status) + "</span>" : "";
}

/* Money arrives pre-formatted ("£1,234.56"), so the numeric test only has to
   tell a real balance from a zero one to decide whether to shout in red. */
function sdrIsZeroMoney(value) {
    const amount = parseFloat(String(value == null ? "" : value).replace(/[^0-9.-]/g, ""));
    return !amount;
}

/* The filter line above the table — it should describe what the user actually
   asked for, so it is read back off the selects rather than the response. */
function sdrSelectedLabels(selector) {
    return $(selector + " option:selected").map(function () {
        return $(this).text().trim();
    }).get().filter(function (label) {
        return label !== "" && label !== "Please Select";
    });
}

function sdrRenderResultMeta() {
    const semesters = sdrSelectedLabels("#due_semester_id");
    const courses = sdrSelectedLabels("#due_course_id");
    const statuses = sdrSelectedLabels("#due_status_id");

    const summarise = function (labels, all) {
        if (labels.length === 0) return all;
        if (labels.length <= 2) return labels.join(", ");
        return labels.length + " " + all.replace("all ", "");
    };

    const parts = [summarise(semesters, "all intakes"), summarise(courses, "all courses")];
    if (statuses.length > 0) parts.push(summarise(statuses, ""));

    $("[data-sdr-result-meta]").text(parts.join(" · "));
}

/* Tiles describe the whole filtered set. The controller only sends `summary`
   with the first page — paging cannot change a filter-level total — so later
   pages simply leave the tiles as they are. */
function sdrRenderSummary(summary) {
    if (!summary) return;

    const values = {
        students: summary.students,
        claim: summary.claim_total,
        received: summary.received_total,
        due: summary.due_total,
    };

    $.each(values, function (key, value) {
        $('[data-sdr-stat="' + key + '"]').removeClass("is-loading").text(value == null ? "—" : value);
    });
}

/* Tabulator's own footer only carries the pager, so the record range is
   injected alongside it after each render. */
function sdrRenderFooterSummary(table, total) {
    const $footer = $(table.element).find(".tabulator-footer");
    if (!$footer.length) return;

    let $summary = $footer.find(".sdr-tablesummary");
    if (!$summary.length) {
        $summary = $('<div class="sdr-tablesummary"></div>').prependTo($footer);
    }

    if (!total) {
        $summary.html("No records");
        return;
    }

    const size = table.getPageSize() || total;
    const page = table.getPage() || 1;
    const from = (page - 1) * size + 1;
    const to = Math.min(page * size, total);

    $summary.html("Showing <b>" + from + "–" + to + "</b> of <b>" + total + "</b>");
}

var studentDueReportList = (function () {
    /* Held across renders so the footer range survives a redraw, which fires
       renderComplete without a fresh ajax response. */
    var _total = 0;

    var _tableGen = function () {
        // Setup Tabulator
        let semester_ids = $("#due_semester_id").val() != "" ? $("#due_semester_id").val() : "";
        let course_ids = $("#due_semester_id").val() != "" ? $("#due_course_id").val() : "";
        let status_ids = $("#due_status_id").val() != "" ? $("#due_status_id").val() : "";
        let due_date = $("#due_date").val() != "" ? $("#due_date").val() : "";

        let tableContent = new Tabulator("#studentDueReportList", {
            ajaxURL: route("report.student.due.list"),
            ajaxParams: { semester_ids: semester_ids, course_ids: course_ids, status_ids: status_ids, due_date: due_date },
            ajaxFiltering: true,
            ajaxSorting: true,
            printAsHtml: true,
            printStyled: true,
            pagination: "remote",
            paginationSize: 50,
            paginationSizeSelector: [true, 50, 100, 200, 300, 400],
            layout: "fitColumns",
            responsiveLayout: "collapse",
            placeholder: "No matching records found",
            columns: [
                // {
                //     title: "#SL",
                //     field: "sl",
                //     width: "60",
                //     headerSort: false,
                // },
                {
                    title: "Student ID",
                    field: "student_id",
                    headerHozAlign: "left",
                    headerSort: false,
                    formatter(cell, formatterParams) {
                        return '<span class="sdr-id">' + sdrEscape(cell.getData().student_id) + "</span>";
                    },
                },
                {
                    title: "Course",
                    field: "course",
                    headerHozAlign: "left",
                    headerSort: false,
                    cssClass: "sdr-col-wrap",
                    formatter(cell, formatterParams) {
                        return '<span class="sdr-course">' + sdrEscape(cell.getData().course) + "</span>";
                    },
                },
                {
                    title: "Intake",
                    field: "semester",
                    headerHozAlign: "left",
                    headerSort: false,
                },
                /*{
                    title: "Start Date",
                    field: "start_date",
                    headerHozAlign: "left",
                },
                {
                    title: "End Date",
                    field: "end_date",
                    headerHozAlign: "left",
                },*/
                {
                    title: "Status",
                    field: "status",
                    headerHozAlign: "left",
                    headerSort: false,
                    cssClass: "sdr-col-pill",
                    formatter(cell, formatterParams) {
                        return sdrStatusPill(cell.getData().status);
                    },
                },
                {
                    title: "Agreements",
                    field: "no_of_agreement",
                    headerHozAlign: "left",
                    headerSort: false,
                    formatter(cell, formatterParams) {
                        /* "with dues / total" — the second figure is context for
                           the first, so it is deliberately the quieter one. */
                        return '<span class="sdr-agreements">' + sdrEscape(cell.getData().no_of_agreement) +
                            '<span class="sdr-agreements__all">/' + sdrEscape(cell.getData().no_of_agreement_all) + "</span></span>";
                    },
                },
                {
                    title: "Claim Total",
                    field: "claim_total",
                    headerHozAlign: "right",
                    headerSort: false,
                    cssClass: "sdr-col-num",
                },
                {
                    title: "Received",
                    field: "received_total",
                    headerHozAlign: "right",
                    headerSort: false,
                    cssClass: "sdr-col-num",
                    formatter(cell, formatterParams) {
                        const value = cell.getData().received_total;
                        return sdrIsZeroMoney(value)
                            ? sdrEscape(value)
                            : '<span class="sdr-money--received">' + sdrEscape(value) + "</span>";
                    },
                },
                {
                    title: "Due",
                    field: "due",
                    headerHozAlign: "right",
                    headerSort: false,
                    cssClass: "sdr-col-num",
                    formatter(cell, formatterParams) {
                        const value = cell.getData().due;
                        return sdrIsZeroMoney(value)
                            ? sdrEscape(value)
                            : '<span class="sdr-money--due">' + sdrEscape(value) + "</span>";
                    },
                },
                {
                    title: "Due Date",
                    field: "due_date",
                    headerHozAlign: "left",
                    headerSort: false,
                    cssClass: "sdr-col-wrap",
                    formatter(cell, formatterParams) {
                        return '<span class="sdr-dates">' + sdrEscape(cell.getData().due_date) + "</span>";
                    },
                },
            ],
            /* Extra payload rides along with the paged rows: `total` feeds the
               record count and footer range, `summary` the tiles (first page
               only). Tabulator still needs the response handed straight back. */
            ajaxResponse(url, params, response) {
                _total = response.total || 0;
                $("[data-sdr-result-count]").text(_total === 1 ? "1 record" : _total + " records");
                sdrRenderSummary(response.summary);
                return response;
            },
            renderComplete() {
                createIcons({
                    icons,
                    "stroke-width": 1.5,
                    nameAttr: "data-lucide",
                });
                const columnLists = this.getColumns();
                if (columnLists.length > 0) {
                    const lastColumn = columnLists[columnLists.length - 1];
                    const currentWidth = lastColumn.getWidth();
                    lastColumn.setWidth(currentWidth - 1);
                }
                sdrRenderFooterSummary(this, _total);
            },
            rowClick:function(e, row){
                window.open(row.getData().url, '_blank');
            }
        });

        // Redraw table onresize
        window.addEventListener("resize", () => {
            tableContent.redraw();
            createIcons({
                icons,
                "stroke-width": 1.5,
                nameAttr: "data-lucide",
            });
        });

    };
    return {
        init: function () {
            _tableGen();
        },
    };
})();


(function(){
    if ($("#studentDueReportList").length) {
        studentDueReportList.init();
        sdrRenderResultMeta();

        // Filter function
        function filterDueReportHTMLForm() {
            /* Blank the tiles while the new set is fetched — leaving the
               previous totals up next to a fresh filter would be misread. */
            $("[data-sdr-stat]").addClass("is-loading").text("—");
            sdrRenderResultMeta();
            studentDueReportList.init();
        }

        // On click go button
        $("#accDueSubmitBtn").on("click", function (event) {
            filterDueReportHTMLForm();
        });
    }


    let dueTomOptions = {
        plugins: {
            dropdown_input: {}
        },
        placeholder: 'Search Here...',
        //persist: false,
        create: false,
        allowEmptyOption: true,
        onDelete: function (values) {
            return confirm( values.length > 1 ? "Are you sure you want to remove these " + values.length + " items?" : 'Are you sure you want to remove "' +values[0] +'"?' );
        },
    };

    let dueTomOptionsMul = {
        ...dueTomOptions,
        plugins: {
            ...dueTomOptions.plugins,
            remove_button: {
                title: "Remove this item",
            },
        }
    };

    var due_semester_id = new TomSelect('#due_semester_id', dueTomOptionsMul);
    var due_course_id = new TomSelect('#due_course_id', dueTomOptionsMul);
        due_course_id.clear(true);
        due_course_id.clearOptions();
        due_course_id.disable;
    var due_status_id = new TomSelect('#due_status_id', dueTomOptionsMul);
        due_status_id.clear(true);
        due_status_id.clearOptions();
        due_status_id.disable;

    $('#due_semester_id').on('change', function(){
        var $theSemester = $(this);
        var theSemesters = $theSemester.val();

        if(theSemesters.length > 0){
            axios({
                method: "post",
                url: route('reports.account.due.get.course.status'),
                data: {theSemesters : theSemesters},
                headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
            }).then(response => {
                if (response.status == 200) {
                    var courses = response.data.courses;
                    var statuses = response.data.statuses;
                    due_course_id.enable();
                    $.each(courses, function(index, row) {
                        due_course_id.addOption({
                            value: row.id,
                            text: row.name,
                        });
                    });
                    due_course_id.refreshOptions();

                    due_status_id.enable();
                    $.each(statuses, function(index, row) {
                        due_status_id.addOption({
                            value: row.id,
                            text: row.name,
                        });
                    });
                    due_status_id.refreshOptions();
                }

            }).catch(error => {
                if (error.response) {
                    due_course_id.clear(true);
                    due_course_id.clearOptions();
                    due_course_id.disable();

                    due_status_id.clear(true);
                    due_status_id.clearOptions();
                    due_status_id.disable();
                }

            });
        }else{
            due_course_id.clear(true);
            due_course_id.clearOptions();
            due_course_id.disable();

            due_status_id.clear(true);
            due_status_id.clearOptions();
            due_status_id.disable();
        }
    });
    
    $('#due_course_id').on('change', function(){
        var $theSemester = $('#due_semester_id');
        var theSemesters = $theSemester.val();
        var $theCourse = $(this);
        var theCourses = $theCourse.val();

        if(theCourses.length > 0){
            axios({
                method: "post",
                url: route('reports.account.due.get.statuses'),
                data: {theSemesters : theSemesters, theCourses : theCourses},
                headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
            }).then(response => {
                if (response.status == 200) {
                    var statuses = response.data.statuses;
                    due_status_id.enable();
                    $.each(statuses, function(index, row) {
                        due_status_id.addOption({
                            value: row.id,
                            text: row.name,
                        });
                    });
                    due_status_id.refreshOptions();
                }

            }).catch(error => {
                if (error.response) {
                    due_status_id.clear(true);
                    due_status_id.clearOptions();
                    due_status_id.disable();
                }

            });
        }else{
            due_status_id.clear(true);
            due_status_id.clearOptions();
            due_status_id.disable();
        }
    });

    $(document).on('click', '#downloadXl', function(e){
        e.preventDefault();
        var $theBtn = $(this);
        var $theLoader = $theBtn.find('.theLoader');

        let semester_ids = $("#due_semester_id").val() != "" ? $("#due_semester_id").val() : "";
        let course_ids = $("#due_course_id").val() != "" ? $("#due_course_id").val() : "";
        let statuses = $("#due_status_id").val() != "" ? $("#due_status_id").val() : "";

        $theBtn.addClass('disabled');
        $theLoader.fadeIn();

        axios({
            method: "post",
            url: route("report.student.due.xl.download"),
            params:{ semester_ids: semester_ids, course_ids: course_ids, statuses: statuses },
            headers: {
                "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
            },
            responseType: 'blob',
        }).then((response) => {
            $theBtn.removeClass('disabled');
            $theLoader.fadeOut();

            const url = window.URL.createObjectURL(new Blob([response.data]));
            const link = document.createElement('a');
            link.href = url;
            link.setAttribute('download', 'Student_due_reports.xlsx'); 
            document.body.appendChild(link);
            link.click();
            link.remove();
        }).catch((error) => {
            $theBtn.removeClass('disabled');
            $theLoader.fadeOut();
            console.log(error);
        });
    })
})()