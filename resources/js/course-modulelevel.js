/*
 * Module Levels list — all behaviour lives in `course-list-table.js`; this
 * file only names the endpoints and labels.
 */

import { initCourseSimpleList } from "./course-list-table";

initCourseSimpleList({
    tableId: "modulelevelTableId",
    noun: "Module Level",
    nounPlural: "Module Levels",
    columnTitle: "Module Level Name",
    routes: {
        list: "modulelevels.list",
        store: "modulelevels.store",
        edit: "modulelevels.edit",
        update: "modulelevels.update",
        // Route name is misspelled in web.php; kept as-is so the URL resolves.
        destroy: "modulelevels.destory",
        restore: "modulelevels.restore",
    },
});
