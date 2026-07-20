import IMask from 'imask';
import xlsx from "xlsx";
import { createIcons, icons } from "lucide";
import { createElement, Plus,Minus } from 'lucide';
import Tabulator from "tabulator-tables";
import TomSelect from "tom-select";
import Dropzone from "dropzone";
import Toastify from "toastify-js";

import ClassicEditor from "@ckeditor/ckeditor5-build-decoupled-document";

("use strict");

const tmDateIcons = {
    eye: '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7-10-7-10-7z"></path><circle cx="12" cy="12" r="3"></circle></svg>',
    feed: '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><path d="M9 11l3 3L22 4"></path><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"></path></svg>',
    end: '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="9"></circle><path d="M15 9l-6 6M9 9l6 6"></path></svg>',
};

function tmDateStatusBadge(status) {
    const statuses = {
        Scheduled: { className: "is-scheduled", label: "Scheduled" },
        Ongoing: { className: "is-live", label: "In Progress" },
        Completed: { className: "is-completed", label: "Completed" },
        Canceled: { className: "is-canceled", label: "Canceled" },
        Cancelled: { className: "is-canceled", label: "Canceled" },
    };
    const option = statuses[status] || { className: "is-unknown", label: "Unknown" };

    return '<span class="tm-date-status ' + option.className + '">' + option.label + '</span>';
}

function tmDateRoomFormatter(cell) {
    return '<span class="tm-date-room"><strong>' + cell.getData().venue + '</strong><small>' + cell.getData().room + '</small></span>';
}

function tmDateTimeFormatter(cell) {
    return '<span class="tm-date-time">' + cell.getData().start_time + ' &mdash; ' + cell.getData().end_time + '</span>';
}

function tmDateActionLink(href, className, icon, label) {
    return '<a href="' + href + '" class="tm-date-action ' + className + '">' + icon + label + '</a>';
}

function tmDateActionButton(attributes, className, icon, label) {
    return '<button type="button" ' + attributes + ' class="tm-date-action ' + className + '">' + icon + label + '</button>';
}

function tmDateActionsFormatter(cell) {
    let btn = '';
    let attendanceInformation = cell.getData().attendance_information;
    let personal_tutor_id = cell.getData().personal_tutor_id;
    let tutor_id = cell.getData().tutor_id;
    let class_type = cell.getData().class_type;
    let the_id = ((class_type == 'Tutorial' || class_type == 'Seminar') && personal_tutor_id > 0 ? personal_tutor_id : tutor_id);

    if(cell.getData().time_passed == 1 && cell.getData().attendance_information == null){
        btn += tmDateActionLink(route('attendance.create', cell.getData().id), 'is-feed', tmDateIcons.feed, 'Feed Attendance');
    }else{
        if(cell.getData().status == 'Scheduled'){
            btn = '<span class="tm-date-action is-muted">N/A</span>';
        }else if(cell.getData().status == 'Canceled' || cell.getData().status == 'Cancelled'){
            btn = '<span class="tm-date-action is-canceled">' + tmDateIcons.end + 'Canceled</span>';
        }else if(cell.getData().status == 'Unknown'){
            btn = '<span class="tm-date-action is-muted">Unknown</span>';
        }else{
            if(cell.getData().status == 'Ongoing' && cell.getData().feed_given == 0 && the_id > 0){
                btn += tmDateActionLink(route('tutor-dashboard.attendance', [the_id, cell.getData().id, 2]), 'is-feed', tmDateIcons.feed, 'Feed Attendance');
            }
            if(cell.getData().status == 'Ongoing' && cell.getData().feed_given == 1){
                btn += tmDateActionButton(
                    'data-tw-toggle="modal" data-attendanceinfo="' + (attendanceInformation ? attendanceInformation.id : '') + '" data-id="' + cell.getData().id + '" data-tw-target="#endClassModal"',
                    'endClassBtns is-end',
                    tmDateIcons.end,
                    'End Class'
                );
            }
            if(cell.getData().status == 'Completed' && the_id > 0){
                btn += tmDateActionLink(route('tutor-dashboard.attendance', [the_id, cell.getData().id, 2]), 'is-view', tmDateIcons.eye, 'View Feed');
            }
        }
    }

    return '<span class="tm-date-actions">' + btn + '</span>';
}

const tmParticipantIcons = {
    sun: '<svg class="is-day" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="4"></circle><path d="M12 2v2"></path><path d="M12 20v2"></path><path d="m4.93 4.93 1.41 1.41"></path><path d="m17.66 17.66 1.41 1.41"></path><path d="M2 12h2"></path><path d="M20 12h2"></path><path d="m6.34 17.66-1.41 1.41"></path><path d="m19.07 4.93-1.41 1.41"></path></svg>',
    sunset: '<svg class="is-evening" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><path d="M12 10V2"></path><path d="m4.93 10.93 1.41 1.41"></path><path d="M2 18h2"></path><path d="M20 18h2"></path><path d="m19.07 10.93-1.41 1.41"></path><path d="M22 22H2"></path><path d="m16 6-4 4-4-4"></path><path d="M16 18a4 4 0 0 0-8 0"></path></svg>',
    accessibility: '<svg class="is-disability" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><circle cx="16" cy="4" r="1"></circle><path d="m18 19 1-7-6 1"></path><path d="m5 8 3-3 5.5 3-2 3"></path><path d="M4.24 14.24a4 4 0 0 0 5.52 5.52"></path><path d="M13.76 19.76a4 4 0 0 0 0-5.52"></path></svg>',
};

function tmParticipantEscape(value) {
    return String(value == null ? '' : value).replace(/[&<>"']/g, function (char) {
        return {
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#39;',
        }[char];
    });
}

function tmParticipantInitials(data) {
    const first = String(data.first_name || '').trim();
    const last = String(data.last_name || '').trim();
    const fallback = String(data.registration_no || 'LC').trim();
    const firstInitial = (first || fallback).charAt(0);
    const lastInitial = (last || fallback.charAt(1) || firstInitial).charAt(0);

    return (firstInitial + lastInitial).toUpperCase();
}

function tmParticipantAvatarStyle(seed) {
    const colors = ['#7a4fa3', '#137a70', '#2f8f5b', '#c94f7c', '#b5602f', '#2f5fa1', '#a13f6b', '#4a7a2f', '#b3261e', '#0d7c73'];
    let hash = 0;

    String(seed || 'student').split('').forEach(function (char) {
        hash = ((hash * 31) + char.charCodeAt(0)) >>> 0;
    });

    return 'background:' + colors[hash % colors.length] + ';';
}

function tmParticipantRegFormatter(cell) {
    const data = cell.getData();
    const regNo = tmParticipantEscape(data.registration_no);
    const initials = tmParticipantInitials(data);
    const avatarStyle = tmParticipantAvatarStyle((data.first_name || '') + (data.last_name || '') + regNo);

    return '<span class="tm-participant-reg">' +
        '<span class="tm-participant-avatar" style="' + avatarStyle + '">' + initials + '</span>' +
        '<span class="tm-participant-regno">' + regNo + '</span>' +
        '</span>' +
        '<input type="hidden" class="student_ids" name="student_ids[]" value="' + tmParticipantEscape(data.student_id) + '"/>';
}

function tmParticipantModeFormatter(cell) {
    const data = cell.getData();
    const modeIcon = parseInt(data.evening_and_weekend, 10) === 1 ? tmParticipantIcons.sunset : tmParticipantIcons.sun;
    const disabilityIcon = parseInt(data.disability, 10) === 1 ? tmParticipantIcons.accessibility : '';

    return '<span class="tm-participant-mode">' + modeIcon + disabilityIcon + '</span>';
}

function tmParticipantStatusFormatter(cell) {
    const label = tmParticipantEscape(cell.getValue());
    const normalised = label.toLowerCase();
    let className = 'is-enrolled';

    if (!label) {
        className = 'is-muted';
    } else if (normalised.includes('suspend') || normalised.includes('withdraw') || normalised.includes('termin') || normalised.includes('intermit') || normalised.includes('archive') || normalised.includes('drop')) {
        className = 'is-danger';
    } else if (normalised.includes('pending') || normalised.includes('defer') || normalised.includes('hold')) {
        className = 'is-warning';
    }

    return '<span class="tm-participant-status ' + className + '">' + (label || 'Unknown') + '</span>';
}

function tmAssessmentDateFormatter(cell) {
    const value = String(cell.getValue() == null ? '' : cell.getValue()).trim();

    if (!value) {
        return '<span class="tm-assessment-date is-empty">&mdash;</span>';
    }

    return '<span class="tm-assessment-date">' + tmParticipantEscape(value) + '</span>';
}

var classPlanDateListsTutorTable = (function () {
    var _tableGen = function () {
        // Setup Tabulator
        let planid = $('#classPlanDateListsTutorTable').attr('data-planid');
        let dates = $("#dates-PD").val() != "" ? $("#dates-PD").val() : "";
        let statusu = $("#status-PD").val() != "" ? $("#status-PD").val() : "";
        
        let tableContent = new Tabulator("#classPlanDateListsTutorTable", {
            ajaxURL: route("plan.dates.list"),
            ajaxParams: { planid: planid, dates: dates, status: statusu },
            ajaxFiltering: true,
            ajaxSorting: true,
            printAsHtml: true,
            printStyled: true,
            pagination: "remote",
            paginationSize: 20,
            paginationSizeSelector: [true, 10, 20, 30, 40],
            layout: "fitColumns",
            responsiveLayout: "collapse",
            placeholder: "No matching records found",
            columns: [
                {
                    title: "#",
                    field: "sl",
                    
                    headerSort: false,
                    width: 74,
                },
                {
                    title: "DATE",
                    field: "date",
                    headerHozAlign: "left",
                    minWidth: 220,
                    widthGrow: 13,
                    formatter(cell) {
                        return '<span class="tm-date-primary">' + cell.getValue() + '</span>';
                    },
                },
                {
                    title: "ROOM",
                    field: "room",
                    vertAlign: "middle",
                    headerHozAlign: "left",
                    hozAlign: "left",
                    minWidth: 220,
                    widthGrow: 13,
                    formatter(cell) {
                        return tmDateRoomFormatter(cell);
                    },
                },
                {
                    title: "TIME",
                    field: "time",
                    vertAlign: "middle",
                    headerHozAlign: "left",
                    hozAlign: "left",
                    minWidth: 170,
                    widthGrow: 10,
                    formatter(cell) {
                        return tmDateTimeFormatter(cell);
                    },
                },
                {
                    title: "STATUS",
                    field: "status",
                    minWidth: 150,
                    widthGrow: 9,
                    vertAlign: "middle",
                    hozAlign: "left",
                    headerSort: false,
                    headerHozAlign: "left",
                    formatter(cell) {
                        return tmDateStatusBadge(cell.getData().status);
                    },
                },
                {
                    title: "ACTIONS",
                    minWidth: 220,
                    field: "actions",
                    responsive: 1,
                    hozAlign: "right",
                    vertAlign: "middle",
                    headerHozAlign: "right",
                    headerSort: false,
                    print: false,
                    download: false,
                    widthGrow: 27,
                    formatter(cell) {
                        return tmDateActionsFormatter(cell);
                    },
                },
            ],
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
            },
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

        // Export
        $("#tabulator-export-csv").on("click", function (event) {
            tableContent.download("csv", "data.csv");
        });

        $("#tabulator-export-json").on("click", function (event) {
            tableContent.download("json", "data.json");
        });

        $("#tabulator-export-xlsx").on("click", function (event) {
            window.XLSX = xlsx;
            tableContent.download("xlsx", "data.xlsx", {
                sheetName: "Plan Date List Details",
            });
        });

        $("#tabulator-export-html").on("click", function (event) {
            tableContent.download("html", "data.html", {
                style: true,
            });
        });

        // Print
        $("#tabulator-print").on("click", function (event) {
            tableContent.print();
        });
    };
    return {
        init: function () {
            _tableGen();
        },
    };
})();

var classPlanDateListsTutorialTable = (function () {
    var _tableGen = function () {
        // Setup Tabulator
        let planid = $('#classPlanDateListsTutorialTable').attr('data-tutorialid');
        let dates = $("#dates-TPD").val() != "" ? $("#dates-TPD").val() : "";
        let statusu = $("#status-TPD").val() != "" ? $("#status-TPD").val() : "";
        
        let tableContent = new Tabulator("#classPlanDateListsTutorialTable", {
            ajaxURL: route("plan.dates.list"),
            ajaxParams: { planid: planid, dates: dates, status: statusu },
            ajaxFiltering: true,
            ajaxSorting: true,
            printAsHtml: true,
            printStyled: true,
            pagination: "remote",
            paginationSize: 20,
            paginationSizeSelector: [true, 10, 20, 30, 40],
            layout: "fitColumns",
            responsiveLayout: "collapse",
            placeholder: "No matching records found",
            columns: [
                {
                    title: "#",
                    field: "sl",
                    
                    headerSort: false,
                    width: 74,
                },
                {
                    title: "DATE",
                    field: "date",
                    headerHozAlign: "left",
                    minWidth: 220,
                    widthGrow: 13,
                    formatter(cell) {
                        return '<span class="tm-date-primary">' + cell.getValue() + '</span>';
                    },
                },
                {
                    title: "ROOM",
                    field: "room",
                    vertAlign: "middle",
                    headerHozAlign: "left",
                    hozAlign: "left",
                    minWidth: 220,
                    widthGrow: 13,
                    formatter(cell) {
                        return tmDateRoomFormatter(cell);
                    },
                },
                {
                    title: "TIME",
                    field: "time",
                    vertAlign: "middle",
                    headerHozAlign: "left",
                    hozAlign: "left",
                    minWidth: 170,
                    widthGrow: 10,
                    formatter(cell) {
                        return tmDateTimeFormatter(cell);
                    },
                },
                {
                    title: "STATUS",
                    field: "status",
                    minWidth: 150,
                    widthGrow: 9,
                    vertAlign: "middle",
                    hozAlign: "left",
                    headerSort: false,
                    headerHozAlign: "left",
                    formatter(cell) {
                        return tmDateStatusBadge(cell.getData().status);
                    },
                },
                {
                    title: "ACTIONS",
                    minWidth: 220,
                    field: "actions",
                    responsive: 1,
                    hozAlign: "right",
                    vertAlign: "middle",
                    headerHozAlign: "right",
                    headerSort: false,
                    print: false,
                    download: false,
                    widthGrow: 27,
                    formatter(cell) {
                        return tmDateActionsFormatter(cell);
                    },
                },
            ],
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
            },
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

        // Export
        $("#tabulator-export-csv-TPD").on("click", function (event) {
            tableContent.download("csv", "data.csv");
        });

        $("#tabulator-export-json-TPD").on("click", function (event) {
            tableContent.download("json", "data.json");
        });

        $("#tabulator-export-xlsx-TPD").on("click", function (event) {
            window.XLSX = xlsx;
            tableContent.download("xlsx", "data.xlsx", {
                sheetName: "Plan Date List Details",
            });
        });

        $("#tabulator-export-html-TPD").on("click", function (event) {
            tableContent.download("html", "data.html", {
                style: true,
            });
        });

        // Print
        $("#tabulator-print-TPD").on("click", function (event) {
            tableContent.print();
        });
    };
    return {
        init: function () {
            _tableGen();
        },
    };
})();


var classParticipantsTutorTable = (function () {
    var _tableGen = function () {
        // Setup Tabulator
        let planid = $('#classParticipantsTutorTable').attr('data-planid');
        let statusu = $("#status-PT").val() != "" ? $("#status-PT").val() : "";
        
        let tableContent = new Tabulator("#classParticipantsTutorTable", {
            ajaxURL: route("plan-participant.list"),
            ajaxParams: { planid: planid, status: statusu },
            ajaxFiltering: true,
            ajaxSorting: true,
            printAsHtml: true,
            printStyled: true,
            pagination: "remote",
            paginationSize: 10,
            paginationSizeSelector: [true, 5, 10, 20, 30, 40],
            layout: "fitColumns",
            responsiveLayout: "collapse",
            placeholder: "No matching records found",
            columns: [
                {
                    title: "#",
                    field: "sl",
                    
                    headerSort: false,
                    width: "180",
                },
                
                {
                    title: "PHOTO",
                    minWidth: 200,
                    field: "images",
                    headerHozAlign: "center",
                    hozAlign: "center",
                    vertAlign: "middle",
                    print: false,
                    download: false,
                    formatter(cell, formatterParams) {
                        return `<div class="flex lg:justify-center">
                            <div class="intro-x w-10 h-10 image-fit">
                                <img  class="rounded-full" src="${
                                    cell.getData().images
                                }">
                            </div>
                        </div>`;
                    },
                },
                {
                    title: "NAME",
                    field: "name",
                    vertAlign: "middle",
                    headerHozAlign: "center",
                    hozAlign:  "center",
                    width: 200,
                    formatter(cell, formatterParams) {
                        return `<div>
                            <div class="font-medium whitespace-nowrap">${
                                cell.getData().name
                            }</div>
                            <div class="text-slate-500 text-xs whitespace-nowrap">${
                                cell.getData().type
                            }</div>
                        </div>`;
                    },
                },
                
            ],
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
            },
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

        // Export
        $("#tabulator-export-csv").on("click", function (event) {
            tableContent.download("csv", "data.csv");
        });

        $("#tabulator-export-json").on("click", function (event) {
            tableContent.download("json", "data.json");
        });

        $("#tabulator-export-xlsx").on("click", function (event) {
            window.XLSX = xlsx;
            tableContent.download("xlsx", "data.xlsx", {
                sheetName: "Plan Date List Details",
            });
        });

        $("#tabulator-export-html").on("click", function (event) {
            tableContent.download("html", "data.html", {
                style: true,
            });
        });

        // Print
        $("#tabulator-print").on("click", function (event) {
            tableContent.print();
        });
    };
    return {
        init: function () {
            _tableGen();
        },
    };
})();
// classStudentListTutorModuleTable

var classStudentListTutorModuleTable = (function () {
    // Tabulator 4.9 has no paginationCounter option, so the footer count is rendered by hand
    // from the total_rows the endpoint reports alongside the paged data.
    var _totalRows = 0;

    var _renderCounter = function (table) {
        let paginator = table.element.querySelector(".tabulator-footer .tabulator-paginator");
        if (!paginator) {
            return;
        }

        let counter = paginator.querySelector(".tm-participant-counter");
        if (!counter) {
            counter = document.createElement("span");
            counter.className = "tm-participant-counter";
            paginator.insertBefore(counter, paginator.querySelector(".tabulator-page"));
        }

        let size = table.getPageSize();
        let page = table.getPage() || 1;
        let noun = _totalRows === 1 ? "student" : "students";

        if (!_totalRows) {
            counter.textContent = "No students";
        } else if (size === true || size >= _totalRows) {
            counter.textContent = "Showing all " + _totalRows + " " + noun;
        } else {
            let from = (page - 1) * size + 1;
            let to = Math.min(page * size, _totalRows);
            counter.textContent = "Showing " + from + "–" + to + " of " + _totalRows + " " + noun;
        }
    };

    var _tableGen = function () {
        // Setup Tabulator
        let planid = $('#classStudentListTutorModuleTable').attr('data-planid');
        let statusu = $("#status-CLTML").val() != "" ? $("#status-CLTML").val() : "";

        $('.tm-selected-count').text(0);
        $('#actionButtonWrap').hide();

        let tableContent = new Tabulator("#classStudentListTutorModuleTable", {
            ajaxURL: route("student-assign.list"),
            ajaxParams: { planid: planid, status: statusu },
            ajaxFiltering: true,
            ajaxSorting: true,
            printAsHtml: true,
            printStyled: true,
            pagination: "remote",
            paginationSize: 50,
            paginationSizeSelector: [true, 20, 50, 100],
            layout: "fitColumns",
            responsiveLayout: false,
            placeholder: "No matching records found",
            selectable:true,
            columns: [
                {
                    formatter: "rowSelection", 
                    titleFormatter: "rowSelection", 
                    hozAlign: "center",
                    headerHozAlign: "center",
                    width: 70,
                    cssClass: "tm-participant-select-cell",
                    headerSort: false, 
                    download: false,
                    cellClick: function(e, cell) {
                        cell.getRow().toggleSelect();
                    }
                },
                {
                    title: "S/N",
                    formatter: function(cell) {
                        return '<span class="tm-participant-sn">' + (cell.getRow().getPosition(true) + 1) + '</span>';
                    },
                    hozAlign: "left",
                    headerHozAlign: "left",
                    width: 56,
                    cssClass: "tm-participant-sn-cell",
                    headerSort: false,
                    download: false
                },
                {
                    title: "Reg. No",
                    field: "registration_no",
                    headerHozAlign: "left",
                    minWidth: 280,
                    widthGrow: 16,
                    cssClass: "tm-participant-reg-cell",
                    formatter(cell) {
                        return tmParticipantRegFormatter(cell);
                    },
                },
                {
                    title: "",
                    field: "evening_and_weekend",
                    headerHozAlign: "center",
                    hozAlign: "center",
                    width: 54,
                    cssClass: "tm-participant-mode-cell",
                    headerSort: false,
                    formatter(cell) {
                        return tmParticipantModeFormatter(cell);
                    },
                },
                {
                    title: "First Name",
                    field: "first_name",
                    headerHozAlign: "left",
                    minWidth: 180,
                    widthGrow: 14,
                    formatter(cell) {
                        return '<span class="tm-participant-name">' + tmParticipantEscape(cell.getValue()) + '</span>';
                    },
                },
                {
                    title: "Last Name",
                    field: "last_name",
                    headerHozAlign: "left",
                    minWidth: 180,
                    widthGrow: 14,
                    formatter(cell) {
                        return '<span class="tm-participant-name">' + tmParticipantEscape(cell.getValue()) + '</span>';
                    },
                },
                {
                    title: "Status",
                    field: "status_id",
                    headerHozAlign: "left",
                    minWidth: 170,
                    widthGrow: 10,
                    formatter(cell) {
                        return tmParticipantStatusFormatter(cell);
                    },
                }
                
            ],
            ajaxResponse(url, params, response) {
                _totalRows = response.total_rows > 0 ? response.total_rows : 0;
                return response;
            },
            renderComplete() {
                createIcons({
                    icons,
                    "stroke-width": 1.5,
                    nameAttr: "data-lucide",
                });
                _renderCounter(this);
            },
            rowSelectionChanged:function(data, rows){
                $('.tm-selected-count').text(rows.length);
                if(rows.length > 0){
                    $('#actionButtonWrap').stop(true, true).css('display', 'flex').hide().fadeIn(120);
                }else{
                    $('#actionButtonWrap').stop(true, true).fadeOut(120);
                }
            },
            selectableCheck:function(row){
                return row.getData().student_id > 0; //allow selection of rows where the age is greater than 18
            },
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

        $("#clearClassStudentSelection").off("click.tmParticipants").on("click.tmParticipants", function () {
            tableContent.deselectRow();
        });

        // The Participants tab has no Print/Export buttons of its own — it exports server-side via
        // #exportStudentList. The old -CLTML print/export handlers here only ever matched the
        // Assessments tab's buttons, which is why printing there printed this table too.
    };
    return {
        init: function () {
            _tableGen();
        },
    };
})();
var classPlanAssessmentModuleTable = (function () {
    var _tableGen = function () {
        // Setup Tabulator
        let planid = $('#classPlanAssessmentModuleTable').attr('data-planid');
        let statusu = $("#status-ASMT").val() != "" ? $("#status-ASMT").val() : "";
        
        let tableContent = new Tabulator("#classPlanAssessmentModuleTable", {
            ajaxURL: route("assessment.plan.list"),
            ajaxParams: { planid: planid, status: statusu },
            ajaxFiltering: true,
            ajaxSorting: true,
            printAsHtml: true,
            printStyled: true,
            pagination: "remote",
            paginationSize: 10,
            paginationSizeSelector: [true, 5, 10, 20, 30, 40],
            layout: "fitColumns",
            responsiveLayout: false,
            placeholder: "No matching records found",
            columns: [
                {
                    title: "#",
                    field: "sl",
                    headerHozAlign: "left",
                    hozAlign: "left",
                    headerSort: false,
                    width: 56,
                    cssClass: "tm-assessment-sn-cell",
                    formatter(cell) {
                        return '<span class="tm-participant-sn">' + tmParticipantEscape(cell.getValue()) + '</span>';
                    },
                },
                {
                    title: "Assessment Name",
                    field: "name",
                    headerHozAlign: "left",
                    minWidth: 220,
                    widthGrow: 14,
                    formatter(cell) {
                        return '<span class="tm-assessment-name">' + tmParticipantEscape(cell.getValue()) + '</span>';
                    },
                },
                {
                    title: "Publish Date",
                    field: "published_at",
                    headerHozAlign: "left",
                    minWidth: 180,
                    widthGrow: 10,
                    formatter(cell) {
                        return tmAssessmentDateFormatter(cell);
                    },
                },
                {
                    title: "Resubmission Date",
                    field: "resubmission_at",
                    headerHozAlign: "left",
                    minWidth: 180,
                    widthGrow: 10,
                    formatter(cell) {
                        return tmAssessmentDateFormatter(cell);
                    },
                },
                {
                    title: "Actions",
                    field: "id",
                    headerSort: false,
                    hozAlign: "right",
                    headerHozAlign: "right",
                    download: false,
                    width: 190,
                    cssClass: "tm-assessment-actions-cell",
                    formatter(cell) {
                        let row = cell.getData();
                        let btns = '<div class="tm-row-actions">';

                        if (row.deleted_at == null) {
                            if (row.resultFound == 1) {
                                btns += '<a href="' + route('result.downloadresult-excel', row.id) + '" data-id="' + row.id + '" title="Download result" class="downloadresult_btn tm-row-action is-download"><i data-lucide="download-cloud" class="w-4 h-4"></i></a>';
                            } else {
                                btns += '<a href="' + route('result.download-excel', row.id) + '" data-id="' + row.id + '" title="Download template" class="download_btn tm-row-action is-download"><i data-lucide="file-down" class="w-4 h-4"></i></a>';
                            }

                            btns += '<a href="javascript:void(0);" data-tw-toggle="modal" data-tw-target="#resultImportModal" data-id="' + row.id + '" title="Import result" class="uploadresult_btn tm-row-action is-upload"><i data-lucide="upload-cloud" class="w-4 h-4"></i></a>';
                            btns += '<a href="' + route('result.index', row.id) + '" data-id="' + row.id + '" title="Edit" class="edit_btn tm-row-action is-edit"><i data-lucide="pencil" class="w-4 h-4"></i></a>';
                            btns += '<button type="button" data-id="' + row.id + '" title="Delete" class="delete_btn tm-row-action is-delete"><i data-lucide="trash-2" class="w-4 h-4"></i></button>';
                        } else {
                            btns += '<button type="button" data-id="' + row.id + '" title="Restore" class="restore_btn tm-row-action is-restore"><i data-lucide="rotate-cw" class="w-4 h-4"></i></button>';
                        }

                        return btns + '</div>';
                    },
                },
            ],
            renderComplete() {
                createIcons({
                    icons,
                    "stroke-width": 1.5,
                    nameAttr: "data-lucide",
                });

                $(".uploadresult_btn").off('click.tmAssessment').on('click.tmAssessment', function () {
                    let id = $(this).attr('data-id');
                    $("input[name='assessment_plan_id']").val(id);
                });
            },
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

        // Export
        $("#tabulator-export-csv-ASMT").on("click", function (event) {
            tableContent.download("csv", "assessments.csv");
        });

        $("#tabulator-export-xlsx-ASMT").on("click", function (event) {
            window.XLSX = xlsx;
            tableContent.download("xlsx", "assessments.xlsx", {
                sheetName: "Module Assessments",
            });
        });

        // Print
        $("#tabulator-print-ASMT").on("click", function (event) {
            tableContent.print();
        });
    };
    return {
        init: function () {
            _tableGen();
        },
    };
})();

(function(){
    // This IIFE binds every plan/result handler below, so a modal that is absent
    // from the current page must not throw here - that would leave the whole page
    // without handlers. Pages that lack a modal simply get a no-op for it.
    const noopModal = { show(){}, hide(){}, toggle(){} };
    const modalFor = (selector) => {
        const el = document.querySelector(selector);
        return el ? tailwind.Modal.getOrCreateInstance(el) : noopModal;
    };

    const succModal = modalFor("#successModal");
    const confModal = modalFor("#confirmModal");
    const warningModal = modalFor("#warningModal");
    const endClassModal = modalFor("#endClassModal");

    
    let confModalDelTitle = 'Are you sure?';
    if ($("#classParticipantsTutorTable").length) {
        // Init Table
        classParticipantsTutorTable.init();

        // Filter function
        function filterHTMLForm() {
            classParticipantsTutorTable.init();
        }

        // On submit filter form
        $("#tabulatorFilterForm-PT")[0].addEventListener(
            "keypress",
            function (event) {
                let keycode = event.keyCode ? event.keyCode : event.which;
                if (keycode == "13") {
                    event.preventDefault();
                    filterHTMLForm();
                }
            }
        );
        // On click go button
        $("#tabulator-html-filter-go-PT").on("click", function (event) {
            filterHTMLForm();
        });

        // On reset filter form
        $("#tabulator-html-filter-reset-PT").on("click", function (event) {
            
            $("#status-PT").val("1");
            filterHTMLForm();
        });
    }

    if($("#confirmModalPlanTask").length > 0) {
        const confirmModalPlanTask = tailwind.Modal.getOrCreateInstance(document.querySelector("#confirmModalPlanTask"));
        let confirmModalPlanTaskTitle = 'Are you sure?';
        let confirmModalPlanTaskDescription = 'Do you really want to re-assign the module related documents.';
        const confirmModalPlanTaskEL = document.getElementById('confirmModalPlanTask');
        confirmModalPlanTaskEL.addEventListener('hidden.tw.modal', function(event){
            $('#confirmModalPlanTask .agreeWithPlanTask').attr('data-id', '0');
            $('#confirmModalPlanTask .agreeWithPlanTask').attr('data-action', 'none');
        });
        document.getElementById('confirmModalPlanTask').addEventListener('shown.tw.modal', function(event){
            $('#confirmModalPlanTask .title').html(confirmModalPlanTaskTitle);
            $('#confirmModalPlanTask .description').html(confirmModalPlanTaskDescription);
            let id = $(".callModalPlanTask").data('planid');
            $('#confirmModalPlanTask .agreeWithPlanTask').attr('data-id', id);
            $('#confirmModalPlanTask .agreeWithPlanTask').attr('data-action', 'update');
        });
        
        $(".agreeWithPlanTask").on('click',function(e){
            let $agreeBTN = $(this);
            let recordID = $agreeBTN.attr('data-id');
            
            $('#confirmModalPlanTask button').attr('disabled', 'disabled');
            
            e.preventDefault();
            let planid = recordID;
            axios({
                method: "post",
                url: route('plan-module-task.auto.sync',planid),
                
                headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
            }).then(response => {
                if (response.status == 200) {
                    succModal.show();
                    confirmModalPlanTask.hide()
                    document.getElementById('successModal').addEventListener('shown.tw.modal', function(event){
                        $('#successModal .successModalTitle').html('Congratulations!');
                        $('#successModal .successModalDesc').html('Modules Assignment data successfully generated.');
                    });

                }
                
                setTimeout(function(){
                    succModal.hide();
                    window.location.reload();
                }, 2000);
            }).catch(error => {
                confirmModalPlanTask.hide();
                console.log('error');
            });
        });
    }

    if ($("#classPlanDateListsTutorTable").length) {
        // Init Table
        classPlanDateListsTutorTable.init();

        // Filter function
        function filterHTMLForm() {
            classPlanDateListsTutorTable.init();
        }

        // On submit filter form
        $("#tabulatorFilterForm-PD")[0].addEventListener(
            "keypress",
            function (event) {
                let keycode = event.keyCode ? event.keyCode : event.which;
                if (keycode == "13") {
                    event.preventDefault();
                    filterHTMLForm();
                }
            }
        );

        // On click go button
        $("#tabulator-html-filter-go-PD").on("click", function (event) {
            filterHTMLForm();
        });

        // On reset filter form
        $("#tabulator-html-filter-reset-PD").on("click", function (event) {
            $("#dates-PD").val("");
            $("#status-PD").val("1");
            filterHTMLForm();
        });

        /*End Class Btn*/
        $('#classPlanDateListsTutorTable').on('click', '.endClassBtns', function(e){
            e.preventDefault();
            var $theBtn = $(this);
            var infoId = $theBtn.attr('data-attendanceinfo');
            var plandDateId = $theBtn.attr('data-id');

            $('#endClassModal [name="plan_date_list_id"]').val(plandDateId);
        });

        $('#endClassModalForm').on('submit', function(e){
            e.preventDefault();
            let $form = $(this);
            const form = document.getElementById('endClassModalForm');

            $('#endClassModalForm').find('input').removeClass('border-danger')
            $('#endClassModalForm').find('.acc__input-error').html('')

            document.querySelector('#endClassSave').setAttribute('disabled', 'disabled');
            document.querySelector('#endClassSave svg').style.cssText = 'display: inline-block;';

            let url = $form.find("input[name=url]").val();
            let form_data = new FormData(form);

            axios({
                method: "post",
                url: url,
                data: form_data,
                headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
            }).then(response => {
                document.querySelector('#endClassSave').removeAttribute('disabled');
                document.querySelector('#endClassSave svg').style.cssText = 'display: none;';
                
                if (response.status == 200) {
                    endClassModal.hide();

                    succModal.show();
                    document.getElementById('successModal').addEventListener('shown.tw.modal', function(event){
                        $('#successModal .successModalTitle').html('WOW!');
                        $('#successModal .successModalDesc').html('Class successfully ended.');
                    });

                    setTimeout(() => {
                        succModal.hide();
                        window.location.reload();
                    }, 1000);
                }
                
            }).catch(error => {
                document.querySelector('#endClassSave').removeAttribute('disabled');
                document.querySelector('#endClassSave svg').style.cssText = 'display: none;';
                if(error.response){
                    endClassModal.hide();
                    if(error.response.status == 422 || error.response.status == 322){   
                        warningModal.show();
                        document.getElementById('warningModal').addEventListener('shown.tw.modal', function(event){
                            $('#warningModal .warningModalTitle').html('Oops!');
                            $('#warningModal .warningModalDesc').html(error.response.data.data);
                        });

                        setTimeout(() => {
                            warningModal.hide();
                        }, 2000);
                    }else{
                        console.log('error');
                    }
                }
            });
        });
        /*End Class Btn*/
    }

    if ($("#classPlanDateListsTutorialTable").length) {
        // Init Table
        classPlanDateListsTutorialTable.init();

        // Filter function
        function tutorialFilterHTMLForm() {
            classPlanDateListsTutorialTable.init();
        }

        // On submit filter form
        $("#tabulatorFilterForm-TPD")[0].addEventListener(
            "keypress",
            function (event) {
                let keycode = event.keyCode ? event.keyCode : event.which;
                if (keycode == "13") {
                    event.preventDefault();
                    tutorialFilterHTMLForm();
                }
            }
        );

        // On click go button
        $("#tabulator-html-filter-go-TPD").on("click", function (event) {
            tutorialFilterHTMLForm();
        });

        // On reset filter form
        $("#tabulator-html-filter-reset-TPD").on("click", function (event) {
            $("#dates-TPD").val("");
            $("#status-TPD").val("1");
            tutorialFilterHTMLForm();
        });

        /*End Class Btn*/
        $('#classPlanDateListsTutorialTable').on('click', '.endClassBtns', function(e){
            e.preventDefault();
            var $theBtn = $(this);
            var infoId = $theBtn.attr('data-attendanceinfo');
            var plandDateId = $theBtn.attr('data-id');

            $('#endClassModal [name="plan_date_list_id"]').val(plandDateId);
        });
        /*End Class Btn*/
    }

    /* Start End Class Form Submission */
    $('#endClassModalForm').on('submit', function(e){
        e.preventDefault();
        let $form = $(this);
        const form = document.getElementById('endClassModalForm');

        $('#endClassModalForm').find('input').removeClass('border-danger')
        $('#endClassModalForm').find('.acc__input-error').html('')

        document.querySelector('#endClassSave').setAttribute('disabled', 'disabled');
        document.querySelector('#endClassSave svg').style.cssText = 'display: inline-block;';

        let url = $form.find("input[name=url]").val();
        let form_data = new FormData(form);

        axios({
            method: "post",
            url: url,
            data: form_data,
            headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
        }).then(response => {
            document.querySelector('#endClassSave').removeAttribute('disabled');
            document.querySelector('#endClassSave svg').style.cssText = 'display: none;';
            
            if (response.status == 200) {
                endClassModal.hide();

                succModal.show();
                document.getElementById('successModal').addEventListener('shown.tw.modal', function(event){
                    $('#successModal .successModalTitle').html('WOW!');
                    $('#successModal .successModalDesc').html('Class successfully ended.');
                });

                setTimeout(() => {
                    succModal.hide();
                    window.location.reload();
                }, 1000);
            }
            
        }).catch(error => {
            document.querySelector('#endClassSave').removeAttribute('disabled');
            document.querySelector('#endClassSave svg').style.cssText = 'display: none;';
            if(error.response){
                endClassModal.hide();
                if(error.response.status == 422 || error.response.status == 322){   
                    warningModal.show();
                    document.getElementById('warningModal').addEventListener('shown.tw.modal', function(event){
                        $('#warningModal .warningModalTitle').html('Oops!');
                        $('#warningModal .warningModalDesc').html(error.response.data.data);
                    });

                    setTimeout(() => {
                        warningModal.hide();
                    }, 2000);
                }else{
                    console.log('error');
                }
            }
        });
    });
    /* End End Class Form Submission */

    if ($("#classStudentListTutorModuleTable").length) {
        // Init Table
        classStudentListTutorModuleTable.init();

        // Filter function
        function filterHTMLFormCLTML() {
            classStudentListTutorModuleTable.init();
        }

        // On click go button
        $("#tabulator-html-filter-go-CLTML").on("click", function (event) {
            filterHTMLFormCLTML();
        });

        // On reset filter form
        $("#tabulator-html-filter-reset-CLTML").on("click", function (event) {
            $("#dates-CLTML").val("");
            $("#status-CLTML").val("1");
            filterHTMLFormCLTML();
        });

        const successModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#successModal"));
        const confirmModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#confirmModal"));
        const warningModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#warningModal"));
        const sendBulkSmsModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#sendBulkSmsModal"));
        const sendBulkMailModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#sendBulkMailModal"));

        let tomOptions = {
            plugins: {
                dropdown_input: {}
            },
            placeholder: 'Search Here...',
            dropdownParent: 'body',
            dropdownClass: 'ts-dropdown lcc-tom-float',
            //persist: false,
            create: false,
            allowEmptyOption: true,
            //maxItems: null,
            onDelete: function (values) {
                return confirm( values.length > 1 ? "Are you sure you want to remove these " + values.length + " items?" : 'Are you sure you want to remove "' +values[0] +'"?' );
            },
        };
        let sms_template_id = new TomSelect('#sms_template_id', tomOptions);
        //let email_template_id = new TomSelect('#email_template_id', tomOptions);
    
        let mailEditor;
        if($("#mailEditor").length > 0){
            const el = document.getElementById('mailEditor');
            ClassicEditor.create(el).then((editor) => {
                mailEditor = editor;
                $(el).closest(".editor").find(".document-editor__toolbar").append(editor.ui.view.toolbar.element);
            }).catch((error) => {
                console.error(error);
            });
        }

        const sendBulkSmsModalEl = document.getElementById('sendBulkSmsModal')
        sendBulkSmsModalEl.addEventListener('hide.tw.modal', function(event) {
            $('#sendBulkSmsModal .acc__input-error').html('');
            $('#sendBulkSmsModal .modal-body input, #sendBulkSmsModal .modal-body textarea').val('');
            $('#sendBulkSmsModal .sms_countr').html('160 / 1');
            $('#sendBulkSmsModal input[name="student_ids"]').val('');
            sms_template_id.clear(true);
        });

        const sendBulkMailModalEl = document.getElementById('sendBulkMailModal')
        sendBulkMailModalEl.addEventListener('hide.tw.modal', function(event) {
            $('#sendBulkMailModal .acc__input-error').html('');
            $('#sendBulkMailModal .modal-body input#sendMailsDocument').val('');
            $('#sendBulkMailModal .modal-body input').val('');
            $('#sendBulkMailModal .sendMailsDocumentNames').html('').fadeOut();
            $('#sendBulkMailModal input[name="student_ids"]').val('');

            mailEditor.setData('');
            //email_template_id.clear(true);
        });


        /* Export Student List Start */
        $('#exportStudentList').on('click', function(e){
            var $theBtn = $(this);
            var plan_id = $theBtn.attr('data-planid');
            var filename = $theBtn.attr('data-filename');

            var ids = [];
            $('#classStudentListTutorModuleTable').find('.tabulator-row.tabulator-selected').each(function(){
                var $row = $(this);
                ids.push($row.find('.student_ids').val());
            });

            $('#actionButtonWrap button').attr('disabled', 'disabled');
            $theBtn.find('.loaders').fadeIn();

            if(ids.length > 0){
                axios({
                    method: "post",
                    url: route('student.assign.export'),
                    data: {plan_id : plan_id, ids : ids},
                    headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
                    responseType: 'blob',
                }).then(response => {
                    $('#actionButtonWrap button').removeAttr('disabled');
                    $theBtn.find('.loaders').fadeOut();
    
                    if (response.status == 200) {
                        const url = window.URL.createObjectURL(new Blob([response.data]));
                        const link = document.createElement('a');
                        link.href = url;
                        link.setAttribute('download', filename);
                        document.body.appendChild(link);
                        link.click();
                        link.remove();
                    }
                }).catch(error => {
                    $('#actionButtonWrap button').removeAttr('disabled');
                    $theBtn.find('.loaders').fadeOut();
                    if (error.response) {
                        console.log('error');
                    }
                });
            }else{
                $('#actionButtonWrap button').removeAttr('disabled');
                $theBtn.find('.loaders').fadeOut();

                warningModal.show();
                document.getElementById("warningModal").addEventListener("shown.tw.modal", function (event) {
                    $("#warningModal .warningModalTitle").html("Error Found!");
                    $("#warningModal .warningModalDesc").html('Selected students not foudn. Please select some students first or contact with the site administrator.');
                });
            }
        });
        /* Export Student List End */

        /* Bulk SMS Start */
        $('.sendBulkSmsBtn').on('click', function(e){
            var $btn = $(this);
            var ids = [];
            
            $('#classStudentListTutorModuleTable').find('.tabulator-row.tabulator-selected').each(function(){
                var $row = $(this);
                ids.push($row.find('.student_ids').val());
            });

            if(ids.length > 0){
                sendBulkSmsModal.show();
                document.getElementById("sendBulkSmsModal").addEventListener("shown.tw.modal", function (event) {
                    $('#sendBulkSmsModal [name="student_ids"]').val(ids.join(','));
                });
            }else{
                warningModal.show();
                document.getElementById("warningModal").addEventListener("shown.tw.modal", function (event) {
                    $("#warningModal .warningModalTitle").html("Error Found!");
                    $("#warningModal .warningModalDesc").html('Selected students not foudn. Please select some students first or contact with the site administrator.');
                });
            }
        });

        $('#smsTextArea').on('keyup', function(){
            var maxlength = ($(this).attr('maxlength') > 0 && $(this).attr('maxlength') != '' ? $(this).attr('maxlength') : 0);
            var chars = this.value.length,
                messages = Math.ceil(chars / 160),
                remaining = messages * 160 - (chars % (messages * 160) || messages * 160);
            if(chars > 0){
                if(chars >= maxlength && maxlength > 0){
                    $('#sendBulkSmsModal .modal-content .smsWarning').remove();
                    $('#sendBulkSmsModal .modal-content').prepend('<div class="alert smsWarning alert-danger-soft show flex items-center mb-0" role="alert"><i data-lucide="alert-triangle" class="w-6 h-6 mr-2"></i>Opps! Your maximum character limit exceeded. Please make the text short or contact with administrator.</div>').fadeIn();
                }else{
                    $('#sendBulkSmsModal .modal-content .smsWarning').remove();
                }
                $('#sendBulkSmsModal .sms_countr').html(remaining +' / '+messages);
            }else{
                $('#sendBulkSmsModal .sms_countr').html('160 / 1');
                $('#sendBulkSmsModal .modal-content .smsWarning').remove();
            }
        });

        $('#sendBulkSmsForm #sms_template_id').on('change', function(){
            var smsTemplateId = $(this).val();
            if(smsTemplateId != ''){
                axios({
                    method: "post",
                    url: route('bulk.communication.get.sms.template'),
                    data: {smsTemplateId : smsTemplateId},
                    headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
                }).then(response => {
                    if (response.status == 200) {
                        $('#sendBulkSmsForm #smsTextArea').val(response.data.row.description ? response.data.row.description : '').trigger('keyup');
                    }
                }).catch(error => {
                    if (error.response) {
                        console.log('error');
                    }
                })
            }else{
                $('#sendBulkSmsForm #smsTextArea').val('');
                $('#sendBulkSmsForm .sms_countr').html('160 / 1');
            }
        });

        $('#sendBulkSmsForm').on('submit', function(e){
            e.preventDefault();
            const form = document.getElementById('sendBulkSmsForm');
        
            document.querySelector('#sendSMSBtn').setAttribute('disabled', 'disabled');
            document.querySelector("#sendSMSBtn svg").style.cssText ="display: inline-block;";

            let form_data = new FormData(form);
            axios({
                method: "post",
                url: route('bulk.communication.send.sms'),
                data: form_data,
                headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
            }).then(response => {
                document.querySelector('#sendSMSBtn').removeAttribute('disabled');
                document.querySelector("#sendSMSBtn svg").style.cssText = "display: none;";

                if (response.status == 200) {
                    sendBulkSmsModal.hide();

                    successModal.show(); 
                    document.getElementById("successModal").addEventListener("shown.tw.modal", function (event) {
                        $("#successModal .successModalTitle").html('Congratulation!');
                        $("#successModal .successModalDesc").html(response.data.message);
                    });  
                    
                    setTimeout(function(){
                        successModal.hide();
                    }, 5000);
                }
            }).catch(error => {
                document.querySelector('#sendSMSBtn').removeAttribute('disabled');
                document.querySelector("#sendSMSBtn svg").style.cssText = "display: none;";
                if (error.response) {
                    if (error.response.status == 422) {
                        for (const [key, val] of Object.entries(error.response.data.errors)) {
                            $(`#sendBulkSmsForm .${key}`).addClass('border-danger');
                            $(`#sendBulkSmsForm  .error-${key}`).html(val);
                        }
                    } else if(error.response.status == 412){
                        warningModal.show(); 
                        document.getElementById("warningModal").addEventListener("shown.tw.modal", function (event) {
                            $("#warningModal .warningModalTitle").html('Oops!');
                            $("#warningModal .warningModalDesc").html(error.response.data.message);
                        });
                    
                        setTimeout(function(){
                            warningModal.hide();
                        }, 5000);
                    }else {
                        console.log('error');
                    }
                }
            });
        });
        /* Bulk SMS End */

        /* Bulk Email Start */
        $('.sendBulkMailBtn').on('click', function(e){
            var $btn = $(this);
            var ids = [];
            
            $('#classStudentListTutorModuleTable').find('.tabulator-row.tabulator-selected').each(function(){
                var $row = $(this);
                ids.push($row.find('.student_ids').val());
            });

            if(ids.length > 0){
                sendBulkMailModal.show();
                document.getElementById("sendBulkMailModal").addEventListener("shown.tw.modal", function (event) {
                    $('#sendBulkMailModal [name="student_ids"]').val(ids.join(','));
                });
            }else{
                warningModal.show();
                document.getElementById("warningModal").addEventListener("shown.tw.modal", function (event) {
                    $("#warningModal .warningModalTitle").html("Error Found!");
                    $("#warningModal .warningModalDesc").html('Selected students not foudn. Please select some students first or contact with the site administrator.');
                });
            }
        });

        $('#sendBulkMailForm #sendMailsDocument').on('change', function(){
            var inputs = document.getElementById('sendMailsDocument');
            var html = '';
            for (var i = 0; i < inputs.files.length; ++i) {
                var name = inputs.files.item(i).name;
                html += '<div class="mb-1 text-primary font-medium flex justify-start items-center"><i data-lucide="disc" class="w-3 h3 mr-2"></i>'+name+'</div>';
            }

            $('#sendBulkMailForm .sendMailsDocumentNames').fadeIn().html(html);
            createIcons({
                icons,
                "stroke-width": 1.5,
                nameAttr: "data-lucide",
            });
        });

        /*$('#sendBulkMailForm [name="email_template_id"]').on('change', function(){
            var emailTemplateID = $(this).val();
            if(emailTemplateID != ''){
                axios({
                    method: "post",
                    url: route('bulk.communication.get.mail.template'),
                    data: {emailTemplateID : emailTemplateID},
                    headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
                }).then(response => {
                    if (response.status == 200) {
                        if(response.data.row.description){
                            mailEditor.setData(response.data.row.description);
                        }else{
                            mailEditor.setData('');
                        }
                    }
                }).catch(error => {
                    if (error.response) {
                        console.log('error');
                    }
                });
            }else{
                mailEditor.setData('');
            }
        });*/

        $('#sendBulkMailForm').on('submit', function(e){
            e.preventDefault();
            const form = document.getElementById('sendBulkMailForm');
        
            document.querySelector('#sendEmailBtn').setAttribute('disabled', 'disabled');
            document.querySelector("#sendEmailBtn svg").style.cssText ="display: inline-block;";

            let form_data = new FormData(form);
            form_data.append('file', $('#sendBulkMailForm input#sendMailsDocument')[0].files[0]); 
            form_data.append("body", mailEditor.getData());
            axios({
                method: "post",
                url: route('bulk.communication.send.group.email'),
                data: form_data,
                headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
            }).then(response => {
                document.querySelector('#sendEmailBtn').removeAttribute('disabled');
                document.querySelector("#sendEmailBtn svg").style.cssText = "display: none;";

                if (response.status == 200) {
                    sendBulkMailModal.hide();

                    successModal.show(); 
                    document.getElementById("successModal").addEventListener("shown.tw.modal", function (event) {
                        $("#successModal .successModalTitle").html("Congratulation!" );
                        $("#successModal .successModalDesc").html(response.data.message);
                    });  
                    
                    setTimeout(function(){
                        successModal.hide();
                    }, 2000);
                }
            }).catch(error => {
                document.querySelector('#sendEmailBtn').removeAttribute('disabled');
                document.querySelector("#sendEmailBtn svg").style.cssText = "display: none;";
                if (error.response) {
                    if (error.response.status == 422) {
                        for (const [key, val] of Object.entries(error.response.data.errors)) {
                            $(`#sendBulkMailForm .${key}`).addClass('border-danger');
                            $(`#sendBulkMailForm  .error-${key}`).html(val);
                        }
                    } else if(error.response.status == 412){
                        warningModal.show(); 
                        document.getElementById("warningModal").addEventListener("shown.tw.modal", function (event) {
                            $("#warningModal .warningModalTitle").html('Oops!');
                            $("#warningModal .warningModalDesc").html(error.response.data.message);
                        });
                    
                        setTimeout(function(){
                            warningModal.hide();
                        }, 5000);
                    } else {
                        console.log('error');
                    }
                }
            });
        });
        /* Bulk Email End */

    }
    /* End Tabulator */

    if ($("#classPlanAssessmentModuleTable").length) {

        const addModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#addAssessmentModal"));
        const resultImportModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#resultImportModal"));
        // Dropzone
        let dzErrors = false;
        Dropzone.autoDiscover = false;
        Dropzone.options.bankholidayImportForm = {
            autoProcessQueue: false,
            maxFiles: 1,
            maxFilesize: 50,
            parallelUploads: 1,
            acceptedFiles: ".xls,.xlsx,.xlsm,.xltx,.xltm",
            addRemoveLinks: true,
            //thumbnailWidth: 100,
            //thumbnailHeight: 100,
        };

        let options = {
            accept: (file, done) => {
                console.log("Uploaded");
                done();
            },
        };


        var drzn12 = new Dropzone('#bankholidayImportForm', options);

        drzn12.on("maxfilesexceeded", (file) => {
            $('#resultImportModal .modal-content .uploadError').remove();
            $('#resultImportModal .modal-content').prepend('<div class="alert uploadError alert-danger-soft show flex items-start mb-0" role="alert"><i data-lucide="alert-octagon" class="w-6 h-6 mr-2"></i> Oops! Can not upload more than 10 files at a time.</div>');
            drzn12.removeFile(file);
            setTimeout(function(){
                $('#resultImportModal .modal-content .uploadError').remove();
            }, 2000)
        });

        drzn12.on("error", function(file, response){
            dzErrors = true;
        });

        drzn12.on("success", function(file, response){
            //console.log(response);
            return file.previewElement.classList.add("dz-success");
        });

        drzn12.on("complete", function(file) {
            //drzn1.removeFile(file);
        }); 

        drzn12.on('queuecomplete', function(){
            $('#saveImportResult').removeAttr('disabled');
            document.querySelector("#saveImportResult svg").style.cssText ="display: none;";

            if(!dzErrors){
                drzn12.removeAllFiles();

                $('#resultImportModal .modal-content .uploadError').remove();
                $('#resultImportModal .modal-content').prepend('<div class="alert uploadError alert-success-soft show flex items-start mb-0" role="alert"><i data-lucide="alert-octagon" class="w-6 h-6 mr-2"></i> WOW! Student photo successfully uploaded.</div>');
                createIcons({
                    icons,
                    "stroke-width": 1.5,
                    nameAttr: "data-lucide",
                });

                setTimeout(function(){
                    $('#resultImportModal .modal-content .uploadError').remove();
                    window.location.reload();
                }, 2000);
            }else{
                $('#resultImportModal .modal-content .uploadError').remove();
                $('#resultImportModal .modal-content').prepend('<div class="alert uploadError alert-danger-soft show flex items-start mb-0" role="alert"><i data-lucide="alert-octagon" class="w-6 h-6 mr-2"></i> Oops! Something went wrong. Please try later.</div>');
                createIcons({
                    icons,
                    "stroke-width": 1.5,
                    nameAttr: "data-lucide",
                });
                
                setTimeout(function(){
                    $('#resultImportModal .modal-content .uploadError').remove();
                }, 2000);
            }
        })

        $('#saveImportResult').on('click', function(e){
            e.preventDefault();
        
            document.querySelector('#saveImportResult').setAttribute('disabled', 'disabled');
            document.querySelector("#saveImportResult svg").style.cssText ="display: inline-block;";
            
            drzn12.processQueue();
            
        });
        // Init Table
        classPlanAssessmentModuleTable.init();

        // Filter function
        function filterHTMLFormASMT() {
            classPlanAssessmentModuleTable.init();
        }

        // On submit filter form
        $("#tabulatorFilterForm-ASMT")[0].addEventListener(
            "keypress",
            function (event) {
                let keycode = event.keyCode ? event.keyCode : event.which;
                if (keycode == "13") {
                    event.preventDefault();
                    filterHTMLFormASMT();
                }
            }
        );

        // On click go button
        $("#tabulator-html-filter-go-ASMT").on("click", function (event) {
            filterHTMLFormASMT();
        });

        // On reset filter form
        $("#tabulator-html-filter-reset-ASMT").on("click", function (event) {
            $("#status-ASMT").val("1");
            filterHTMLFormASMT();
        });

        const confirmModalEl = document.getElementById('confirmModal')
        confirmModalEl.addEventListener('hidden.tw.modal', function(event){
            $('#confirmModal .agreeWith').attr('data-id', '0');
            $('#confirmModal .agreeWith').attr('data-action', 'none');
        });
        // Delete Course
        $('#classPlanAssessmentModuleTable').on('click', '.delete_btn', function() {
            let $statusBTN = $(this);
            let rowID = $statusBTN.attr('data-id');
            let url = $statusBTN.attr('data-url');

            confModal.show();
            document.getElementById('confirmModal').addEventListener('shown.tw.modal', function(event){
                $('#confirmModal .confModTitle').html(confModalDelTitle);
                $('#confirmModal .confModDesc').html('Do you really want to delete these record? If yes, the please click on agree btn.');
                $('#confirmModal .agreeWith').attr('data-id', rowID);
                $('#confirmModal .agreeWith').attr('data-action', 'DELETE');
            });
        });

        // Restore Course
        $('#classPlanAssessmentModuleTable').on('click', '.restore_btn', function() {
            const confModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#confirmModal"));
            document.getElementById('confirmModal').addEventListener('hidden.tw.modal', function(event){
                $('#confirmModal .agreeWith').attr('data-id', '0');
                $('#confirmModal .agreeWith').attr('data-action', 'none');
            });
            let $statusBTN = $(this);
            let courseID = $statusBTN.attr('data-id');

            confModal.show();
            document.getElementById('confirmModal').addEventListener('shown.tw.modal', function(event){
                $('#confirmModal .confModTitle').html(confModalDelTitle);
                $('#confirmModal .confModDesc').html('Want to restore this Academic year from the trash? Please click on agree to continue.');
                $('#confirmModal .agreeWith').attr('data-id', courseID);
                $('#confirmModal .agreeWith').attr('data-action', 'RESTORE');
            });
        });

        

        $('#saveModuleAssesment').on('submit', function(e){
            e.preventDefault();
            const form = document.getElementById('saveModuleAssesment');
        
            document.querySelector('#save').setAttribute('disabled', 'disabled');
            document.querySelector("#save svg").style.cssText ="display: inline-block;";

            let form_data = new FormData(form);
            axios({
                method: "post",
                url: route('plan-assessment.store'),
                data: form_data,
                headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
            }).then(response => {
                document.querySelector('#save').removeAttribute('disabled');
                document.querySelector("#save svg").style.cssText = "display: none;";
                
                if (response.status == 200) {
                    document.querySelector('#save').removeAttribute('disabled');
                    document.querySelector("#save svg").style.cssText = "display: none;";
                    addModal.hide();

                    succModal.show();
                    document.getElementById("successModal").addEventListener("shown.tw.modal", function (event) {
                        $("#successModal .successModalTitle").html("Congratulations!");
                        $("#successModal .successModalDesc").html('Academic years data successfully inserted.');
                    });         
                }
                classPlanAssessmentModuleTable.init();
            }).catch(error => {
                document.querySelector('#save').removeAttribute('disabled');
                document.querySelector("#save svg").style.cssText = "display: none;";
                
                if (error.response) {
                    if (error.response.status == 422) {
                        for (const [key, val] of Object.entries(error.response.data.errors)) {
                            $(`#addForm .${key}`).addClass('border-danger')
                            $(`#addForm  .error-${key}`).html(val)
                        }
                    } else {
                        console.log('error');
                    }
                }
            });
        });

        $('#resultImportModal').on('click','#saveImportholiday',function(e) {
            e.preventDefault();
            $('.dropzone').get(0).dropzone.processQueue();
            resultImportModal.hide();

            succModal.show();   
            //setTimeout(function() { succModal.hide(); }, 3000);
            
        });

    }
    
    $(".readd-currentresult").on('click',function(e) {
        //e.preventDefault();
        let tthis = $(this);
        var row = tthis.closest('tr');
        let grade = row.find('select[name="grade_id[]"]').val();
        let student_id = row.find('input[name="student_id[]"]').val();
        let plan_id = row.find('input[name="plan_id[]"]').val();
        let created_by = row.find('input[name="updated_by[]"]').val();
        let assessmentPlan = tthis.data('assessmentplan');
        tthis.attr('disabled', 'disabled');
        $("svg",tthis).eq(1).css('display','inline-block');
        axios({
            method: "post",
            url: route('result.resubmit'),
            data: { grade_id:grade, assessment_plan_id: assessmentPlan, student_id: student_id, plan_id:plan_id, created_by:created_by },
            headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
        }).then(response => {
            let totalAttempt = response.data.data.count;
            let results = response.data.data.results;
            tthis.removeAttr('disabled');
            $("svg",tthis).eq(1).css('display','none');

            if (response.status == 200) {
                row.find('input[name="id[]"]').val(response.data.data.id);
                
                row.find('a.attempt-count').html(totalAttempt);

                Toastify({
                    node: $("#success-notification-content")
                        .clone()
                        .removeClass("hidden")[0],
                    duration: -1,
                    newWindow: true,
                    close: true,
                    gravity: "top",
                    position: "right",
                    stopOnFocus: true,
                }).showToast();     
            }

        }).catch(error => {
            tthis.removeAttr('disabled');
            $("svg",tthis).eq(1).css('display','none');
            if (error.response) {
                if (error.response.status == 422) {
                    Toastify({
                        node: $("#error-notification-content")
                            .clone()
                            .removeClass("hidden")[0],
                        duration: -1,
                        newWindow: true,
                        close: true,
                        gravity: "top",
                        position: "right",
                        stopOnFocus: true,
                    }).showToast(); 
                }
            }
        });
        
    })

    $('.show-attempted').on('click',function(){
        let tthis = $(this);
        
        let assessment_plan_id = tthis.data('assessmentplan');
        let student_id = tthis.data('student_id');
        let totalAttemptHeader = $("h2#totalAttemptHeader");

        $("svg",totalAttemptHeader).css('display','inline-block');
        tthis.siblings("svg").css('display','inline-block');

        axios({
            method: "get",
            url: route("result.show.assessment", [assessment_plan_id,student_id]),
            headers: {
                "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
            },
        }).then((response) => {
            if (response.status == 200) {

                $("svg",totalAttemptHeader).css('display','none');
                tthis.siblings("svg").css('display','none');

                let results = response.data.data.results;
                let html =`<table id="resultListByStudent" data-tw-merge class="w-full text-left">
                    <thead data-tw-merge>
                        <tr data-tw-merge class="[&:hover_td]:bg-slate-100 [&:hover_td]:dark:bg-darkmode-300 [&:hover_td]:dark:bg-opacity-50">
                            <th data-tw-merge class="font-medium px-5 py-3 border-b-2 dark:border-darkmode-300 border-l border-r border-t whitespace-nowrap">
                                #
                            </th>
                            <th data-tw-merge class="font-medium px-5 py-3 border-b-2 dark:border-darkmode-300 border-l border-r border-t whitespace-nowrap">
                                Grade
                            </th>
                            <th data-tw-merge class="font-medium px-5 py-3 border-b-2 dark:border-darkmode-300 border-l border-r border-t whitespace-nowrap">
                                Published at
                            </th>
                            <th data-tw-merge class="font-medium px-5 py-3 border-b-2 dark:border-darkmode-300 border-l border-r border-t whitespace-nowrap">
                                Action
                            </th>
                        </tr>
                    </thead>
                <tbody>`;
                $.each(results,function(i, item){

                    let iCount = i+1;
                    html+=`<tr id="${item.id}" data-tw-merge class="[&:hover_td]:bg-slate-100 [&:hover_td]:dark:bg-darkmode-300 [&:hover_td]:dark:bg-opacity-50">
                            <td data-tw-merge class="px-5 py-3 border-b dark:border-darkmode-300 border-l border-r border-t">
                                ${ iCount }
                            </td>
                            <td data-tw-merge class="px-5 py-3 border-b dark:border-darkmode-300 border-l border-r border-t">
                            ${ item.grade.code } - ${item.grade.name}
                            </td>
                            <td data-tw-merge class="px-5 py-3 border-b dark:border-darkmode-300 border-l border-r border-t">
                            ${ item.published_at  }
                            </td>
                            <td data-tw-merge class="px-5 py-3 border-b dark:border-darkmode-300 border-l border-r border-t">
                                <a href="javascript:;" data-url="${route('result.destroy', item.id)}" data-id="${item.id}" data-action="DELETE" class="delete-result btn btn-danger text-white"><i class="w-4 h-4" data-lucide="trash"></i></a>
                            </td>
                        </tr>`
                    
                })
                html+=`</tbody>
                </table>`;
                
                $('#attemedModal .modal-body').html(html);
                createIcons({
                    icons,
                    "stroke-width": 1.5,
                    nameAttr: "data-lucide",
                });
                $('.delete-result').on('click',function(){
            
                        let $statusBTN = $(this);
                        let rowID = $statusBTN.attr('data-id');
                        let url = $statusBTN.attr('data-url');
                        let confModalDelTitle ="Do you want to delete all?";
                        confModal.show();
                        document.getElementById('confirmModal').addEventListener('shown.tw.modal', function(event){
                            $('#confirmModal .confModTitle').html(confModalDelTitle);
                            $('#confirmModal .confModDesc').html('Do you really want to delete this record? If yes, then please click on agree button. This will be permanent.');
                            $('#confirmModal .agreeWith').attr('data-id', rowID);
                            $('#confirmModal .agreeWith').attr('data-action', 'DELETE');
                            $('#confirmModal .agreeWith').attr('data-url', url);
                        });
                
                })
            }
        })
        .catch((error) => {
            console.log(error);
        });
        //let results= {};

    })
    // Delete Course
    $('.delete_all_result').on('click', function(){
        //const confModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#confirmModal"));
        let confModalDelTitle ="Do you want to delete all?";
        
        let assessmentPlan = $(this).data('assessmentplan');
        confModal.show();
        document.getElementById('confirmModal').addEventListener('shown.tw.modal', function(event){
            $('#confirmModal .confModTitle').html(confModalDelTitle);
            $('#confirmModal .confModDesc').html('Want to delete all results. This will be permanent.');
            $('#confirmModal .agreeWith').attr('data-id', "all");
            $('#confirmModal .agreeWith').attr('data-assessmentPlan', assessmentPlan);
            $('#confirmModal .agreeWith').attr('data-action', 'DELETE');
        });
    });

    
    $(".update-currentresult").on('click',function(e) {
        //e.preventDefault();
        let tthis = $(this);
        var row = tthis.closest('tr');
        let grade = row.find('select[name="grade_id[]"]').val();
        let result = tthis.data('id');
        tthis.attr('disabled', 'disabled');
        $("svg",tthis).eq(1).css('display','inline-block');
        axios({
            method: "PATCH",
            url: route('result.update',result),
            data: { grade_id:grade },
            headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
        }).then(response => {

            tthis.removeAttr('disabled');
            $("svg",tthis).eq(1).css('display','none');

            if (response.status == 200) {
                Toastify({
                    node: $("#success-notification-content")
                        .clone()
                        .removeClass("hidden")[0],
                    duration: -1,
                    newWindow: true,
                    close: true,
                    gravity: "top",
                    position: "right",
                    stopOnFocus: true,
                }).showToast();     
            }

        }).catch(error => {
            tthis.removeAttr('disabled');
            $("svg",tthis).eq(1).css('display','none');
            if (error.response) {
                if (error.response.status == 422) {
                    Toastify({
                        node: $("#error-notification-content")
                            .clone()
                            .removeClass("hidden")[0],
                        duration: -1,
                        newWindow: true,
                        close: true,
                        gravity: "top",
                        position: "right",
                        stopOnFocus: true,
                    }).showToast(); 
                }
            }
        });
        
    })

    $("input[name='upload_type_select']").on('click',function(e) {
        //e.preventDefault();
        let tthis = $(this);
        let type = tthis.val();
        $('input[name="upload_type"]').val(type);
    })
    //
    // Confirm Modal Action
    $('#confirmModal .agreeWith').on('click', function(){
        let $agreeBTN = $(this);
        let recordID = $agreeBTN.attr('data-id');
        let action = $agreeBTN.attr('data-action');
        let url = $agreeBTN.attr('data-url');
        if(recordID!="all" && (url=="" || url==undefined)) {
            $('#confirmModal button').attr('disabled', 'disabled');
            if(action == 'DELETE'){
                axios({
                    method: 'delete',
                    url: route('plan-assessment.destroy', recordID),
                    headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
                }).then(response => {
                    if (response.status == 200) {
                        $('#confirmModal button').removeAttr('disabled');
                        confModal.hide();

                        succModal.show();
                        document.getElementById('successModal').addEventListener('shown.tw.modal', function(event){
                            $('#successModal .successModalTitle').html('Done!');
                            $('#successModal .successModalDesc').html('Assessment successfully deleted!');
                        });
                    }
                    classPlanAssessmentModuleTable.init();
                }).catch(error =>{
                    console.log(error)
                });
            } else if(action == 'RESTORE'){
                axios({
                    method: 'post',
                    url: route('plan-assessment.restore', recordID),
                    headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
                }).then(response => {
                    if (response.status == 200) {
                        $('#confirmModal button').removeAttr('disabled');
                        confModal.hide();

                        succModal.show();
                        document.getElementById('successModal').addEventListener('shown.tw.modal', function(event){
                            $('#successModal .successModalTitle').html('Success!');
                            $('#successModal .successModalDesc').html('Assessment Data Successfully Restored!');
                        });
                    }
                    classPlanAssessmentModuleTable.init();
                }).catch(error =>{
                    console.log(error)
                });
            }
        } else if(recordID=="all") { 
            $('#confirmModal button').attr('disabled', 'disabled');
            if(action == 'DELETE'){
                let assessmentPlan = $agreeBTN.attr('data-assessmentPlan');
                axios({
                    method: 'delete',
                    url: route('result.all.delete', assessmentPlan),
                    headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
                }).then(response => {
                    if (response.status == 200) {
                        $('#confirmModal button').removeAttr('disabled');
                        confModal.hide();

                        succModal.show();
                        document.getElementById('successModal').addEventListener('shown.tw.modal', function(event){
                            $('#successModal .successModalTitle').html('Done!');
                            $('#successModal .successModalDesc').html('Assessment successfully deleted!');
                        });
                    }
                    location.reload();
                }).catch(error =>{
                    console.log(error)
                });
            }
        }else { 
            $('#confirmModal button').attr('disabled', 'disabled');
            if(action == 'DELETE'){

                axios({
                    method: 'delete',
                    url: url,
                    headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
                }).then(response => {
                    if (response.status == 200) {
                        $('#confirmModal button').removeAttr('disabled');
                        confModal.hide();

                        succModal.show();
                        document.getElementById('successModal').addEventListener('shown.tw.modal', function(event){
                            $('#successModal .successModalTitle').html('Done!');
                            $('#successModal .successModalDesc').html('Assessment successfully deleted!');
                        });
                    }
                    location.reload();
                }).catch(error =>{
                    console.log(error)
                });
            }

        }
    })
    
    $('#resultBulkInsert').on('submit', function(e){
        e.preventDefault();
        const form = document.getElementById('resultBulkInsert');

        let tthisSubmitButton =$('#insertAllResult');
        tthisSubmitButton.attr('disabled', 'disabled');
        $("svg",tthisSubmitButton).eq(1).css('display','inline-block');
        

        let url = $("#resultBulkInsert input[name=url]").val();

        let form_data = new FormData(form);

        axios({
            method: "post",
            url: url,
            data: form_data,
            headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
        }).then(response => {

            tthisSubmitButton.removeAttr('disabled');
            $("svg",tthisSubmitButton).eq(1).css('display','none');

            if (response.status == 200) {
                

                succModal.show();
                document.getElementById("successModal").addEventListener("shown.tw.modal", function (event) {
                    $("#successModal .successModalTitle").html("Congratulations!");
                    $("#successModal .successModalDesc").html('Course creation data successfully inserted.');
                });                
                    
            }
            location.reload()
        }).catch(error => {

            tthisSubmitButton.removeAttr('disabled');
            $("svg",tthisSubmitButton).eq(1).css('display','none');

            if (error.response) {
                if (error.response.status == 422) {
                    for (const [key, val] of Object.entries(error.response.data.errors)) {
                        
                        if(key == "grade_id[]") {
                            //$(`#resultBulkInsert .grade_id`).addClass('border-danger')
                            //$(`#resultBulkInsert  .error-grade_id`).html(val)
                            $('select[name="grade_id[]"]').each(function(){
                                let tthis = $(this);
                                let getValue = tthis.val()
                                if(getValue=="") {
                                    $(`.grade_id`,tthis).addClass('border-danger')
                                    tthis.siblings('div.error-grade_id').html(val)
                                } else {
                                    $(`.grade_id`,tthis).removeClass('border-danger')
                                    
                                    tthis.siblings('div.error-grade_id').html("")
                                }
                            });
                        }
                    }
                } else {
                    console.log('error');
                }
            }
        });
    });
    /* Start Dropzone */
    if($("#addStudentPhotoModal").length > 0){
        let dzErrors = false;
        Dropzone.autoDiscover = false;
        Dropzone.options.addStudentPhotoForm = {
            autoProcessQueue: false,
            maxFiles: 1,
            maxFilesize: 5,
            parallelUploads: 1,
            acceptedFiles: ".jpeg,.jpg,.png,.gif,.pdf",
            addRemoveLinks: true,
            //thumbnailWidth: 100,
            //thumbnailHeight: 100,
        };

        let options = {
            accept: (file, done) => {
                console.log("Uploaded");
                done();
            },
        };


        var drzn1 = new Dropzone('#addStudentPhotoForm', options);

        drzn1.on("maxfilesexceeded", (file) => {
            $('#addStudentPhotoModal .modal-content .uploadError').remove();
            $('#addStudentPhotoModal .modal-content').prepend('<div class="alert uploadError alert-danger-soft show flex items-start mb-0" role="alert"><i data-lucide="alert-octagon" class="w-6 h-6 mr-2"></i> Oops! Can not upload more than 10 files at a time.</div>');
            drzn1.removeFile(file);
            setTimeout(function(){
                $('#addStudentPhotoModal .modal-content .uploadError').remove();
            }, 2000)
        });

        drzn1.on("error", function(file, response){
            dzErrors = true;
        });

        drzn1.on("success", function(file, response){
            //console.log(response);
            return file.previewElement.classList.add("dz-success");
        });

        drzn1.on("complete", function(file) {
            //drzn1.removeFile(file);
        }); 

        drzn1.on('queuecomplete', function(){
            $('#uploadStudentPhotoBtn').removeAttr('disabled');
            document.querySelector("#uploadStudentPhotoBtn svg").style.cssText ="display: none;";

            if(!dzErrors){
                drzn1.removeAllFiles();

                $('#addStudentPhotoModal .modal-content .uploadError').remove();
                $('#addStudentPhotoModal .modal-content').prepend('<div class="alert uploadError alert-success-soft show flex items-start mb-0" role="alert"><i data-lucide="alert-octagon" class="w-6 h-6 mr-2"></i> WOW! Student photo successfully uploaded.</div>');
                createIcons({
                    icons,
                    "stroke-width": 1.5,
                    nameAttr: "data-lucide",
                });

                setTimeout(function(){
                    $('#addStudentPhotoModal .modal-content .uploadError').remove();
                    window.location.reload();
                }, 2000);
            }else{
                $('#addStudentPhotoModal .modal-content .uploadError').remove();
                $('#addStudentPhotoModal .modal-content').prepend('<div class="alert uploadError alert-danger-soft show flex items-start mb-0" role="alert"><i data-lucide="alert-octagon" class="w-6 h-6 mr-2"></i> Oops! Something went wrong. Please try later.</div>');
                createIcons({
                    icons,
                    "stroke-width": 1.5,
                    nameAttr: "data-lucide",
                });
                
                setTimeout(function(){
                    $('#addStudentPhotoModal .modal-content .uploadError').remove();
                }, 2000);
            }
        })

        $('#uploadStudentPhotoBtn').on('click', function(e){
            e.preventDefault();
        
            document.querySelector('#uploadStudentPhotoBtn').setAttribute('disabled', 'disabled');
            document.querySelector("#uploadStudentPhotoBtn span").style.cssText ="display: inline-block;";
            
            drzn1.processQueue();
            
        });
        $('.task-upload__Button').on('click', function(e){
            let tthis = $(this);
            let planTaskId = tthis.data('plantaskid');
            $("input[name='plan_task_id']").val(planTaskId);

        });
        
    }
    /* End Dropzone */

    /**
     * Accordian Button (+/-) Works
     */
        $('.accordion-button').on('click',function(){
            let tthis = $(this)

            const plusIcon = createElement(Plus); // Returns HTMLElement (svg)
            const minusIcon = createElement(Minus); // Returns HTMLElement (svg)
            //console.log(plusIcon)
            // set custom attributes with browser native functions
            
            plusIcon.classList.add('w-4');
            plusIcon.classList.add('h-4');
            minusIcon.classList.add('w-4');
            minusIcon.classList.add('h-4');
            $("div.accordian-lucide").html("")
            $("div.accordian-lucide").append(plusIcon)
            // Append HTMLElement in webpage
            const myApp = document.getElementById('app');
            if(tthis.hasClass("collapsed")) {
                
                //create minus sign
                tthis.children("div.accordian-lucide").html("");
                tthis.children("div.accordian-lucide").append(minusIcon)
                
                
            } else {
                //create plus sign
                tthis.children("div.accordian-lucide").html("");
                tthis.children("div.accordian-lucide").append(plusIcon)
            }   

            
        })
    /**
     * Accordian Button Finished
     */
    const activityModalCP = tailwind.Modal.getOrCreateInstance(document.querySelector("#addActivityModal"));

    $('.activity-call').on('click', function(e){
        e.preventDefault();
        let tthis = $(this)
        let planDateListId = tthis.data('plandataid');
        let isModuleOnly = tthis.data('mandatory');
        tthis.children('span').css('display', 'inline-block');
        tthis.attr('disabled', 'disabled');
        let data ={
            page: 1,
            size: 100,
            status:1,
        }

        axios({
            method: 'get',
            url: route('elearning.list', data),
            headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
        }).then(response => {
            if (response.status == 200) {
                tthis.removeAttr('disabled');
                tthis.children('span').css('display', 'none');
                
                const LearningData = response.data.data;
                
                let html = '';
                for (let i=0; i<LearningData.length; i++) {
                    let data =[planDateListId,LearningData[i].id
                    ]
                    if(LearningData[i].active==1 ) {
                      html += `<a href="${
                        route('tutor_module_activity.create',data)
                      }" data-tw-toggle="modal" data-tw-target="#add-item-modal" class="intro-y block col-span-12 sm:col-span-4 2xl:col-span-3">
                                 <div class="box rounded-md p-3 relative zoom-in">
                                     <div class="flex-none relative block before:block before:w-full before:pt-[100%]">
                                         <div class="absolute top-0 left-0 w-full h-full image-fit">
                                             <img alt="London Churchill College" class="rounded-md" src="${
                                                LearningData[i].logo_url
                                             }">
                                         </div>
                                     </div>
                                     <div class="block font-medium text-center truncate mt-3">${
                                        LearningData[i].name
                                     }</div>
                                </div>
                             </a>`
                    }
                }

                $("#activit-contentlist").html(html)

                if(html!="") {
                    activityModalCP.show();
                }
            }
        }).catch(error =>{
            errorModal.show();
                document.getElementById("errorModal").addEventListener("shown.tw.modal", function (event) {
                    $("#errorModal .title").html("Token Mismatch!" );
                    $("#errorModal .descrtiption").html('Please reload');
                }); 
            location.reload();
        });
        
    });

    
    $('.module-call').on('click', function(e){
        e.preventDefault();
        let tthis = $(this)
        let planDateListId = tthis.data('plandataid');
     
        tthis.children('span').css('display', 'inline-block');
        tthis.attr('disabled', 'disabled');
        let data ={
            page: 1,
            size: 100,
            status:1,
        }

        axios({
            method: 'get',
            url: route('elearning.list', data),
            headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
        }).then(response => {
            if (response.status == 200) {
                tthis.removeAttr('disabled');
                tthis.children('span').css('display', 'none');
                
                const LearningData = response.data.data;
                
                let html = '';
                for (let i=0; i<LearningData.length; i++) {
                    let data =[planDateListId,LearningData[i].id
                    ]
                    if(LearningData[i].active==1 ) {
                      html += `<a href="${
                        route('plan-module-task.create',data)
                      }" data-tw-toggle="modal" data-tw-target="#add-item-modal" class="intro-y block col-span-12 sm:col-span-4 2xl:col-span-3">
                                 <div class="box rounded-md p-3 relative zoom-in">
                                     <div class="flex-none relative block before:block before:w-full before:pt-[100%]">
                                         <div class="absolute top-0 left-0 w-full h-full image-fit">
                                             <img alt="London Churchill College" class="rounded-md" src="${
                                                LearningData[i].logo_url
                                             }">
                                         </div>
                                     </div>
                                     <div class="block font-medium text-center truncate mt-3">${
                                        LearningData[i].name
                                     }</div>
                                </div>
                             </a>`
                    }
                }

                $("#activit-contentlist").html(html)

                if(html!="") {
                    activityModalCP.show();
                }
            }
        }).catch(error =>{
            errorModal.show();
                document.getElementById("errorModal").addEventListener("shown.tw.modal", function (event) {
                    $("#errorModal .title").html("Token Mismatch!" );
                    $("#errorModal .descrtiption").html('Please reload');
                }); 
            location.reload();
        });
        
    });
    /* Profile Menu End */
})();
