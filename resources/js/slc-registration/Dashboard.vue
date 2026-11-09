<template>
    <div class="pagehead">
        <div><span class="eyebrow">SLC</span><h1>SLC dashboard</h1>
            <p>Where bulk registration, attendance, Change of Circumstances and payment uploads stand for {{ d.year ? d.year.name : 'this academic year' }}, and SLC records that need checking.</p></div>
        <span v-if="d.year" class="pill info">Academic year {{ d.year.name }}</span>
    </div>

    <!-- the processes -->
    <section class="dash-procs" aria-label="SLC processes">
        <article class="card dash-proc">
            <div class="card-h"><h2>Bulk registration</h2><span class="pill ok">Live</span></div>
            <div class="card-b stack">
                <div class="muted small">{{ reg.upload ? 'Current upload: ' + reg.upload.fileName + ' · ' + reg.upload.at + ' by ' + reg.upload.by : 'No SIS file uploaded yet.' }}</div>
                <ul class="dash-steps">
                    <li v-for="[k, n, label, v, tone] in steps" :key="k"><button type="button" @click="openRegistration(k)">
                        <span class="dash-step-n">{{ n }}</span><span class="dash-step-l">{{ label }}</span><span :class="'dash-step-v num t-' + tone">{{ v }}</span>
                    </button></li>
                </ul>
            </div>
            <div class="scard-f"><span class="muted small">{{ reg.counts.all }} students in the upload</span><button type="button" class="btn btn-sm btn-primary" @click="openRegistration('dashboard')">Open bulk registration</button></div>
        </article>

        <article class="card dash-proc">
            <div class="card-h"><h2>Bulk attendance</h2><span class="pill info">All 6 steps live</span></div>
            <div class="card-b stack">
                <div class="muted small">{{ att.import ? 'Current upload: ' + att.import.fileName + ' · ' + att.import.termName + ' ' + att.import.year + ' · ' + att.import.at + ' by ' + att.import.by : 'No SIS attendance file uploaded yet.' }}</div>
                <ul v-if="att.import" class="dash-steps">
                    <li><button type="button" @click="openAttendance('overview')"><span class="dash-step-n">1</span><span class="dash-step-l">Uploaded</span><span class="dash-step-v num t-info">{{ att.counts.all }}</span></button></li>
                    <li><button type="button" @click="openAttendance('match', att.counts.unmatched ? 'open' : 'matched')"><span class="dash-step-n">2</span><span class="dash-step-l">Still to match</span><span :class="'dash-step-v num ' + (att.counts.unmatched ? 't-warn' : 't-ok')">{{ att.counts.unmatched }}</span></button></li>
                    <li><button type="button" @click="openAttendance('check')"><span class="dash-step-n">3</span><span class="dash-step-l">Data check – to sort</span><span :class="'dash-step-v num ' + (att.counts.data ? 't-bad' : 't-ok')">{{ att.counts.data }}</span></button></li>
                    <!-- step 4 is Attendance & codes (one step since 6 Nov 2026): codes ready, with the students not attending -->
                    <li><button type="button" @click="openAttendance('codes')"><span class="dash-step-n">4</span><span class="dash-step-l">Codes ready to confirm<span :class="att.counts.noAttendance ? 't-bad' : 'muted'"> · {{ att.counts.noAttendance }} with no attendance in the term</span></span><span :class="'dash-step-v num ' + (att.counts.codeReady ? 't-info' : 't-ok')">{{ att.counts.codeReady }}</span></button></li>
                    <li><button type="button" @click="openAttendance('cocs')"><span class="dash-step-n">5</span><span class="dash-step-l">CoCs to submit</span><span :class="'dash-step-v num ' + (att.counts.cocToSubmit ? 't-bad' : 't-ok')">{{ att.counts.cocToSubmit }}</span></button></li>
                    <li><button type="button" @click="openAttendance('batches')"><span class="dash-step-n">6</span><span class="dash-step-l">Awaiting download</span><span :class="'dash-step-v num ' + (att.counts.awaiting ? 't-warn' : 't-ok')">{{ att.counts.awaiting }}</span></button></li>
                </ul>
                <div class="dash-fig"><span class="dash-fig-v num">{{ slc.attendances }}</span><span class="dash-fig-l">SLC attendances confirmed in {{ yearName }} · {{ slc.attendanceCoc }} with a code that needs a CoC</span></div>
            </div>
            <div class="scard-f"><span class="muted small">{{ att.counts.confirmed }} codes confirmed in this upload</span><button type="button" class="btn btn-sm btn-primary" @click="openModule('attendance')">Open bulk attendance</button></div>
        </article>

        <article class="card dash-proc">
            <div class="card-h"><h2>Bulk CoC</h2><span class="pill muted">Planned</span></div>
            <div class="card-b stack">
                <div class="dash-fig"><span :class="'dash-fig-v num' + (slc.cocsOpen ? ' t-warn' : '')">{{ slc.cocsOpen }}</span><span class="dash-fig-l">CoCs not yet submitted in SIS ({{ yearName }})</span></div>
                <div class="dash-fig"><span class="dash-fig-v num">{{ slc.cocsSubmitted }}</span><span class="dash-fig-l">submitted in SIS · {{ slc.cocsFromCheck }} raised from the registration check</span></div>
            </div>
            <div class="scard-f"><span class="muted small">{{ cocItems.length }} CoC{{ cocItems.length === 1 ? '' : 's' }} to issue from this upload</span><button type="button" class="btn btn-sm" @click="openModule('coc')">View the plan</button></div>
        </article>

        <article class="card dash-proc">
            <div class="card-h"><h2>Bulk payment upload</h2><span class="pill ok">Live</span></div>
            <div class="card-b stack">
                <div class="muted small">{{ pay.upload ? 'Current upload: ' + pay.upload.fileName + ' · remittance ' + fmtIso(pay.upload.date) + ' · ' + pay.upload.at + ' by ' + pay.upload.by : 'No remittance file uploaded yet.' }}</div>
                <ul v-if="pay.upload" class="dash-steps">
                    <li><button type="button" @click="openPayments('check', 'all')"><span class="dash-step-n">1</span><span class="dash-step-l">Payments uploaded</span><span class="dash-step-v num t-info">{{ pay.counts.all }}</span></button></li>
                    <li><button type="button" @click="openPayments('check', 'ready')"><span class="dash-step-n">2</span><span class="dash-step-l">Ready to record</span><span :class="'dash-step-v num ' + (pay.counts.ready ? 't-info' : 't-ok')">{{ pay.counts.ready }}</span></button></li>
                    <li><button type="button" @click="openPayments('check', 'look')"><span class="dash-step-n">3</span><span class="dash-step-l">Need a look</span><span :class="'dash-step-v num ' + (pay.counts.look ? 't-warn' : 't-ok')">{{ pay.counts.look }}</span></button></li>
                    <li><button type="button" @click="openPayments('check', 'error')"><span class="dash-step-n">4</span><span class="dash-step-l">Can’t record yet</span><span :class="'dash-step-v num ' + (pay.counts.error ? 't-bad' : 't-ok')">{{ pay.counts.error }}</span></button></li>
                    <li><button type="button" @click="openPayments('check', 'recorded')"><span class="dash-step-n">5</span><span class="dash-step-l">Recorded here</span><span class="dash-step-v num t-ok">{{ pay.counts.recorded }}</span></button></li>
                </ul>
            </div>
            <div class="scard-f"><span class="muted small">{{ pay.upload ? money(pay.counts.sum.recorded) + ' recorded of ' + money(pay.counts.sum.all) : '' }}</span><button type="button" class="btn btn-sm btn-primary" @click="openPayments('overview')">Open payment upload</button></div>
        </article>
    </section>

    <!-- the year's figures -->
    <section class="kpis" aria-label="SLC figures">
        <div class="kpi t-info"><span class="k-label">SLC registrations</span><span class="k-val">{{ slc.registrations }}</span><span class="k-note">{{ byStatus || 'None yet' }} · {{ yearName }}</span></div>
        <div class="kpi t-info"><span class="k-label">Agreements</span><span class="k-val">{{ slc.agreements }}</span><span class="k-note">On {{ yearName }} course instances</span></div>
        <div class="kpi t-ok"><span class="k-label">Recorded in bulk</span><span class="k-val">{{ reg.recorded }}</span><span class="k-note">{{ reg.toRecord }} uploaded, still to record</span></div>
        <div class="kpi"><span class="k-label">Batches</span><span class="k-val">{{ reg.batches.awaiting + reg.batches.downloaded + reg.batches.uploaded }}</span><span class="k-note">{{ reg.batches.awaitingStudents }} awaiting download · {{ reg.batches.downloaded }} downloaded · {{ reg.batches.uploaded }} uploaded</span></div>
        <a v-if="d.duplicates" :class="'kpi kpi-link ' + (d.duplicates.count ? 't-bad' : 't-ok')" :href="d.duplicates.url" target="_blank" rel="noopener">
            <span class="k-label">Duplicate agreements</span><span class="k-val">{{ d.duplicates.count }}</span><span class="k-note">Students with 2+ agreements for one year, still to review{{ d.duplicates.hidden ? ' · ' + d.duplicates.hidden + ' reviewed & hidden' : '' }} · open the list ↗</span>
        </a>
    </section>
</template>

<script>
import { computed, inject } from "vue";
import { fmtIso, money } from "./helpers";

/*
 * The Dashboard tab (user, 2 Nov 2026): the three processes with their figures and a way into each,
 * and the year's SLC figures; the Duplicate agreements box opens its list on its own page
 * (/slc-registration/multiple-agreements). RegistrationState::dashboard().
 */
export default {
    setup() {
        const { store, go, cocItems } = inject("slr");
        const d = computed(() => store.state.dashboard);
        const reg = computed(() => d.value.registration);
        const slc = computed(() => d.value.slc);
        const yearName = computed(() => (d.value.year ? d.value.year.name : "this year"));
        const byStatus = computed(() => slc.value.byStatus.map((x) => x.n + " " + x.name).join(" · "));
        const steps = computed(() => {
            const c = reg.value.counts;
            const b = reg.value.batches;
            return [
                ["identify", "1", "Identify", c.identify, c.identify ? "warn" : "ok"],
                ["data", "2", "Data check", c.data, c.data ? "bad" : "ok"],
                ["actions", "3", "Actions – on hold", c.action + c.held, c.action + c.held ? "warn" : "ok"],
                ["precheck", "4", "Pre-confirm", c.precheck, "info"],
                ["batches", "5", "Batches – awaiting / to record", b.awaitingStudents + " / " + reg.value.toRecord, "ok"],
            ];
        });
        const openRegistration = (view) => { store.module = "registration"; go(view); };
        const openModule = (m) => { store.module = m; window.scrollTo(0, 0); };
        const att = computed(() => store.state.attendance);
        // the attendance sub-page is App's state: ask it through the module switch + a custom event
        const openAttendance = (view, show) => { store.module = "attendance"; window.dispatchEvent(new CustomEvent("slr-att-go", { detail: { view, show } })); window.scrollTo(0, 0); };


        const pay = computed(() => store.state.payments);
        const openPayments = (view, show) => { store.module = "payment"; window.dispatchEvent(new CustomEvent("slr-pay-go", { detail: { view, show } })); window.scrollTo(0, 0); };

        return { d, reg, slc, att, pay, yearName, byStatus, steps, openRegistration, openModule, openAttendance, openPayments, cocItems, fmtIso, money };
    },
};
</script>
