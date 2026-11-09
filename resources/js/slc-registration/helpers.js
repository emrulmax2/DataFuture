/*
 * /slc-registration — the prototype's display helpers and labels ("SLC Registration Check.html").
 * The checks themselves run on the server (App\Services\SlcRegistration\Checks); these only format
 * what it sends and build the copy-paste messages.
 */
export const pad = (n) => String(n).padStart(2, "0");
export const slcToIso = (s) => (s && s.length === 8 ? s.slice(4) + "-" + s.slice(2, 4) + "-" + s.slice(0, 2) : "");
export const fmtIso = (iso) => (iso ? iso.slice(8, 10) + "/" + iso.slice(5, 7) + "/" + iso.slice(0, 4) : "–");
export const fmtSlc = (s) => fmtIso(slcToIso(s));
export const money = (n) => "£" + Number(n || 0).toLocaleString("en-GB", { minimumFractionDigits: 2, maximumFractionDigits: 2 });
export const titleCase = (s) => (s || "").toLowerCase().replace(/\s+/g, " ").trim().replace(/(^|[\s\-'])([a-z])/g, (m, a, b) => a + b.toUpperCase());
export const plural = (n, w) => n + " " + w + (n === 1 ? "" : "s");
export const norm = (s) => (s || "").toUpperCase().normalize("NFD").replace(/[̀-ͯ]/g, "").replace(/[^A-Z0-9 ]/g, " ").replace(/\s+/g, " ").trim();

export const FIELD_ORDER = ["tfl", "course", "start", "year", "dob", "first", "last", "title", "gender", "address", "postcode"];
export const ROUTES = {
    update: { label: "Update our record", tone: "info" },
    coc: { label: "Issue CoC", tone: "bad" },
    contact: { label: "Contact student", tone: "warn" },
    accept: { label: "No action", tone: "muted" },
};
/* Our year follows from the course's instances, so a year difference is a CoC or No action (server: Checks::FIELD_ROUTES). */
export const FIELD_ROUTES = {
    title: ["update", "contact", "accept"], first: ["update", "contact", "accept"], last: ["update", "contact", "accept"], gender: ["update", "contact"],
    dob: ["update", "contact"], course: ["coc"], year: ["coc", "accept"], start: ["coc"],
    address: ["update", "contact", "accept"], postcode: ["update", "contact"], tfl: ["coc", "contact"],
};
/* Address › Update our record (server: RegistrationActions::newAddress, user 5 Nov 2026). */
export const ADDRESS_UPDATE_TIP = "Updates the term-time address. The permanent address changes too only when it is the same address as the term-time one.";
/* Data check: fields whose differing words / day-month-year show in the error colour (user, 5 Nov 2026; DiffText.vue). */
export const DIFF = { title: "words", first: "words", last: "words", dob: "date", start: "date", address: "words", postcode: "words" };
/* An earlier decision in a line's history: "Contact student (contacted)". */
export function historyLine(h) {
    const act = h.route === "hold" ? "Hold – student not identified" : (ROUTES[h.route] ? ROUTES[h.route].label : h.route);
    const st = { issued: "CoC submitted in SIS", contacted: "contacted", done: "student updated SLC" }[h.state];
    return act + (st ? " (" + st + ")" : "");
}
/* Bulk attendance › Data check (server: AttendanceChecks::FIELD_ROUTES / COC_TYPE). */
export const ATT_FIELD_ROUTES = {
    first: ["update", "contact", "accept"], last: ["update", "contact", "accept"], dob: ["update", "contact", "accept"],
    ssn: ["accept"], course: ["coc", "accept"], year: ["coc", "accept"], registration: ["accept"],
    fee: ["coc", "accept"], tfl: ["coc", "contact", "accept"],
};
/* Bulk attendance › Data check: how each field's difference is coloured (user, 6 Nov 2026, as on Bulk registration; AttDiff.vue). */
export const ATT_DIFF = { first: "words", last: "words", dob: "date", ssn: "words", course: "words", year: "words", fee: "whole", tfl: "whole" };
export const ATT_COC_TYPE = { course: "Transfer – course", year: "Transfer – course year", fee: "Fee revision", tfl: "Fee revision" };
export const COC_TYPE = { course: "Transfer – course", start: "Transfer – start date", year: "Transfer – course year", tfl: "Fee revision" };

export function contactMessage(key, ch, s) {
    const what = { name: "name", title: "title", first: "first name", last: "last name", gender: "gender", dob: "date of birth", address: "address", postcode: "postcode" }[key];
    if (key === "tfl") {
        return ch.tflZero
            ? "Hi " + s.first + ", Student Finance shows no Tuition Fee Loan for your course. Please log in to your Student Finance account and apply for the Tuition Fee Loan of " + money(s.fee) + "."
            : "Hi " + s.first + ", Student Finance shows a Tuition Fee Loan of " + ch.slc + " but your course fee is " + money(s.fee) + ". Please update your loan amount with Student Finance.";
    }
    return "Hi " + s.first + ", the " + what + " Student Finance holds for you (" + ch.slc + ") does not match our records (" + ch.ours + "). Please update it with Student Finance so we can confirm your registration.";
}

export const MODULES = [["dashboard", "SLC", true], ["registration", "Bulk registration", true], ["attendance", "Bulk attendance", true], ["coc", "Bulk CoC", false], ["payment", "Bulk payment upload", true]];

/* Bulk payment upload (server: PaymentState / PaymentActions). */
export const PAY_STEPS = [
    ["Upload the SLC remittance file", "The .dat SLC sends with each payment run (or a .csv / .txt saved from it). The file is kept with the upload."],
    ["Match each payment", "Student by SSN, their SLC agreement for the year of study and the unpaid instalment it pays. A payment already on the student’s account for that date and amount shows as already recorded, so a file is never recorded twice."],
    ["Sort what doesn’t match", "Find the student for an unknown SSN, choose the agreement when none is found, or skip a payment."],
    ["Record money receipts", "Ticked payments become money receipts on the student’s Accounts tab (Bank, dated the remittance date), each instalment pointing at its receipt."],
];
export const PAY_STATUS = {
    ready: ["ok", "Ready"], look: ["warn", "Needs a look"], error: ["bad", "Can’t record yet"], done: ["muted", "Already recorded"], recorded: ["ok", "Recorded"], skipped: ["muted", "Skipped"],
};

export const PLANNED = {
    attendance: {
        phase: "Phase 2", title: "Bulk attendance confirmation",
        intro: "Confirm attendance for every student at each liability point in one pass, using the register in datafuture, and build the SIS attendance import file.",
        steps: [
            ["Upload the SIS attendance worklist", "Export the attendance worklist for the liability period from SIS and upload it here."],
            ["Match students", "Students are matched by SSN, which is already saved from the registration process."],
            ["Data check", "Name, date of birth, SSN, course code and registration year against our records; course fee and TFL against the SLC registration and its agreement."],
            ["Attendance & codes", "The register for the liability period (sessions held and attended, attendance %, first and last attended) and the code it suggests: A for students attending, negative or disputed codes where the data says so. Staff review and change any code, then confirm it."],
            ["Queue CoCs for negative codes", "A confirmed code that needs a CoC (per the attendance code settings) queues one on the student’s SLC tab for Bulk CoC."],
            ["Batch awaiting download", "Confirmed codes go into a batch you download as the SIS attendance import file."],
        ],
    },
    coc: {
        phase: "Phase 3", title: "Bulk Change of Circumstances",
        intro: "Collect every CoC raised by registration, attendance and student forms in one queue, complete the details, and build the SIS bulk CoC CSV.",
        steps: [
            ["CoCs arrive in one queue", "From bulk registration and bulk attendance, plus course change and discontinuation forms in the student portal."],
            ["Complete the details", "CoC type, effective date and date of last attendance for each student."],
            ["Validate", "Check required fields against SLC’s CoC CSV Bulk Upload Technical Specification."],
            ["Batch awaiting download", "Approved CoCs go into a UTF-8 CSV for SIS → CoC Home."],
            ["Track outcomes", "Record when SLC has processed each CoC so the student can return to registration or attendance."],
        ],
    },
};
export const ATT_CODES = [["A", "In attendance"], ["C", "Course mismatch – CoC"], ["D", "Applied for the new year but not returning"], ["F", "In attendance, fee disputed – CoC"], ["L", "In attendance, liability disputed, we will resolve"], ["N", "In attendance, liability disputed, we will not resolve – CoC"], ["S", "Suspended – CoC"], ["X", "Not in attendance – CoC"]];
export const COC_KINDS = ["Suspension", "Resumption", "Withdrawal", "Transfer", "Repetition", "Credit", "Fee revision"];

/* A row's errors as the prototype derives them from its checks. */
export function derive(i) {
    const errors = (i.checks || []).filter((c) => !c.ok);
    return {
        errors,
        unrouted: errors.filter((c) => !c.route),
        actions: errors.filter((c) => c.route === "coc" || c.route === "contact"),
    };
}
