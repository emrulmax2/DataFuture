import xlsx from "xlsx";
import { createIcons, icons } from "lucide";

("use strict");
/* Visa / Passport expiry — card list (server-rendered). This file replaces the
   old Tabulator table: it only wires the Print button and CSV/XLSX export,
   working off the dataset embedded by the blade (window.__expiryExport). */
(function () {
    if (!document.getElementById("visaExpiryCards") && !document.getElementById("passportExpiryCards")) {
        return;
    }

    // Draw the lucide icons used inside the server-rendered cards/toolbar.
    createIcons({ icons, "stroke-width": 1.5, nameAttr: "data-lucide" });

    var exportData = window.__expiryExport || [];
    var fileName = window.__expiryName || "expiry-report";
    var sheetName = window.__expirySheet || "Report";

    var printBtn = document.getElementById("list-print");
    if (printBtn) {
        printBtn.addEventListener("click", function () {
            window.print();
        });
    }

    function downloadSheet(ext) {
        if (!exportData.length) {
            return;
        }
        window.XLSX = xlsx;
        var ws = xlsx.utils.json_to_sheet(exportData);
        var wb = xlsx.utils.book_new();
        xlsx.utils.book_append_sheet(wb, ws, sheetName);
        xlsx.writeFile(wb, fileName + "." + ext);
    }

    var csvBtn = document.getElementById("list-export-csv");
    if (csvBtn) {
        csvBtn.addEventListener("click", function () {
            downloadSheet("csv");
        });
    }

    var xlsxBtn = document.getElementById("list-export-xlsx");
    if (xlsxBtn) {
        xlsxBtn.addEventListener("click", function () {
            downloadSheet("xlsx");
        });
    }
})();
