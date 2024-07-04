import dayjs from "dayjs";
import Litepicker from "litepicker";

let dateOption = {
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
const from_date = new Litepicker({
    element: document.getElementById('from_date'),
    ...dateOption
});
const to_date = new Litepicker({
    element: document.getElementById('to_date'),
    ...dateOption
});