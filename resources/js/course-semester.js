/*
 * Semesters list — all behaviour lives in `course-list-table.js`; this file
 * only names the endpoints and labels.
 */

import { initCourseSimpleList } from "./course-list-table";

initCourseSimpleList({
    tableId: "semesterTableId",
    noun: "Semester",
    nounPlural: "Semesters",
    columnTitle: "Semester Name",
    routes: {
        list: "semester.list",
        store: "semester.store",
        edit: "semester.edit",
        update: "semester.update",
        // Route name is misspelled in web.php; kept as-is so the URL resolves.
        destroy: "semester.destory",
        restore: "semester.restore",
    },
});
