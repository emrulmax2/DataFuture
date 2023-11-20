import dayjs from "dayjs";
import Litepicker from "litepicker";

(function () {
    "use strict";

    // Litepicker
    $(".datepicker").each(function () {
        let options = {
            autoApply: true,
            singleMode: false,
            numberOfColumns: 2,
            numberOfMonths: 2,
            showWeekNumbers: true,
            format: "DD-MM-YYYY",
            dropdowns: {
                minYear: 1900,
                maxYear: 2050,
                months: true,
                years: true,
            },
        };

        if ($(this).data("single-mode")) {
            options.singleMode = true;
            options.numberOfColumns = 1;
            options.numberOfMonths = 1;
        }

        if ($(this).data("format")) {
            options.format = $(this).data("format");
        }
        if ($(this).data("show-current")==true) {
            if (!$(this).val()) {
                let date = dayjs().format(options.format);
                date += !options.singleMode
                    ? " - " + dayjs().add(1, "month").format(options.format)
                    : "";
                $(this).val(date);
            }
        }

        new Litepicker({
            element: this,
            ...options,
        });
    });
})();
