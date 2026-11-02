<template>
    <!-- Overview: step 1 (upload) and where things stand -->
    <template v-if="view === 'overview'">
        <div class="pagehead">
            <div><span class="eyebrow">Bulk attendance</span><h1>SLC attendance confirmation</h1>
                <p>Upload the SIS Attendance Confirmation Report, match each student by SSN, check SLC’s data against ours, pull the register for the liability period, confirm each student’s code and download the SIS attendance import file.</p></div>
        </div>

        <section class="card" aria-labelledby="att-up">
            <div class="card-h"><h2 id="att-up">Step 1 · Upload from SIS</h2><span class="muted small">SIS → Attendance Worklist → Export → Attendance Confirmation Report</span></div>
            <div class="card-b stack">
                <div :class="'drop' + (over ? ' over' : '')" @dragover.prevent="over = true" @dragleave="over = false" @drop.prevent="onDrop">
                    <div class="stack" style="gap: 4px">
                        <strong>Drop the .txt export here, or choose a file</strong>
                        <span class="muted small">The file is kept with the upload for reference. Nothing is sent to SLC from here.</span>
                    </div>
                    <div class="row">
                        <label class="btn btn-primary" for="att-file" :aria-disabled="store.busy"><Ico name="up" />{{ store.busy && uploading ? 'Uploading…' : 'Choose file' }}</label>
                        <input id="att-file" type="file" accept=".txt,text/plain" hidden @change="onPick">
                    </div>
                </div>
                <div v-if="imp" class="row small" style="gap: 6px 18px">
                    <span><span class="muted">File</span> {{ imp.fileName }}</span>
                    <span><span class="muted">Header</span> <span class="mono">{{ imp.hei }}</span> · {{ imp.heiName }} · report {{ fmtIso(imp.reportDate) }}</span>
                    <span><span class="muted">Term</span> {{ imp.termName }} {{ imp.year }}</span>
                    <span><span class="muted">Students</span> <b class="num">{{ counts.all }}</b> · {{ counts.ssn }} matched by SSN · {{ counts.linked }} matched here</span>
                    <span><span class="muted">Totals</span> {{ imp.totals.map((t) => money(t)).join(' · ') }}</span>
                    <span><span class="muted">Uploaded</span> {{ imp.at }} by {{ imp.by }}</span>
                    <span v-if="imp.errors.length" class="pill bad" :title="imp.errors.join('\n')">{{ plural(imp.errors.length, 'line') }} skipped</span>
                </div>
            </div>
        </section>

        <ol v-if="imp" class="pipeline" aria-label="Where students are">
            <li><button type="button" class="stage t-ok" @click="$emit('go', 'overview')"><span class="s-n">STEP 1</span><span class="s-l">Uploaded</span><span class="s-v">{{ counts.all }}</span><span class="s-m">{{ imp.termName }} {{ imp.year }} · report {{ fmtIso(imp.reportDate) }}</span></button></li>
            <li><button type="button" :class="'stage ' + (counts.unmatched ? 't-warn' : 't-ok')" @click="$emit('go', 'match', counts.unmatched ? 'open' : 'matched')"><span class="s-n">STEP 2</span><span class="s-l">Matched</span><span class="s-v">{{ counts.matched }}</span><span class="s-m">{{ counts.unmatched }} to match · {{ counts.ssn }} by SSN · {{ counts.linked }} matched here</span></button></li>
            <li><button type="button" :class="'stage ' + (counts.data ? 't-bad' : 't-ok')" @click="$emit('go', 'check')"><span class="s-n">STEP 3</span><span class="s-l">Data check</span><span class="s-v">{{ counts.data }}</span><span class="s-m">To sort · {{ counts.clean }} fully matched · {{ noReg }} with no SLC registration for {{ imp.year }}</span></button></li>
            <li><button type="button" :class="'stage ' + (counts.noAttendance ? 't-bad' : 't-ok')" @click="$emit('go', 'register')"><span class="s-n">STEP 4</span><span class="s-l">No attendance</span><span class="s-v">{{ counts.noAttendance }}</span><span class="s-m">Nothing attended in the {{ imp.termName }} term · {{ counts.attending }} attending</span></button></li>
            <li><button type="button" :class="'stage ' + (counts.codeReady ? 't-info' : 't-ok')" @click="$emit('go', 'codes')"><span class="s-n">STEP 5</span><span class="s-l">Codes</span><span class="s-v">{{ counts.codeReady }}</span><span class="s-m">Ready to confirm · {{ counts.codeNoReg }} with no SLC registration · {{ counts.confirmed }} confirmed</span></button></li>
            <li><button type="button" :class="'stage ' + (counts.cocToSubmit ? 't-bad' : 't-ok')" @click="$emit('go', 'cocs')"><span class="s-n">STEP 6</span><span class="s-l">CoCs</span><span class="s-v">{{ counts.cocToSubmit }}</span><span class="s-m">To submit · {{ counts.cocQueued }} queued by codes</span></button></li>
            <li><button type="button" :class="'stage ' + (counts.awaiting ? 't-warn' : 't-ok')" @click="$emit('go', 'batches')"><span class="s-n">STEP 7</span><span class="s-l">Batches</span><span class="s-v">{{ counts.awaiting }}</span><span class="s-m">Awaiting download · {{ batchCount }} {{ batchCount === 1 ? 'batch' : 'batches' }} in all</span></button></li>
        </ol>

        <section class="card"><div class="card-h"><h2>How it will work</h2></div>
            <ol class="plan"><li v-for="([t, d], k) in steps" :key="t"><span class="dot">{{ k + 1 }}</span><div style="flex: 1"><b>{{ t }}</b><div class="muted small">{{ d }}</div></div><span class="pill ok">Ready</span></li></ol>
        </section>
        <section class="card"><div class="card-h"><h2>SIS attendance codes</h2><span class="muted small">From the SLC SIS user guide · CoC from the attendance code settings</span></div>
            <div class="tablebox"><table class="tbl"><thead><tr><th scope="col">Code</th><th scope="col">Meaning</th><th scope="col">CoC</th></tr></thead>
                <tbody><tr v-for="c in codes" :key="c.code"><th scope="row" class="mono">{{ c.code }}</th><td>{{ c.meaning }}</td><td>{{ c.coc ? 'Needs a CoC · ' + c.cocType : '–' }}</td></tr></tbody></table></div>
        </section>
    </template>

    <!-- Steps 3–7 -->
    <AttCheck v-else-if="view === 'check'" />
    <AttRegister v-else-if="view === 'register'" />
    <AttCodes v-else-if="view === 'codes'" @go="(v) => $emit('go', v)" />
    <AttCocs v-else-if="view === 'cocs'" />
    <AttBatches v-else-if="view === 'batches'" />

    <!-- Step 2: match students by SSN -->
    <template v-else>
        <div class="pagehead">
            <div><span class="eyebrow">Step 2</span><h1>Match students by SSN</h1>
                <p>Each line of the attendance report is matched to the student holding its SSN (saved during registration). A line whose SSN is on no student shows the likely students; saving the SSN matches it.</p></div>
            <div class="filters" role="group" aria-label="Show">
                <button type="button" class="chip" :aria-pressed="show === 'open'" @click="$emit('show', 'open')">To match <span class="num">{{ counts.unmatched }}</span></button>
                <button type="button" class="chip" :aria-pressed="show === 'matched'" @click="$emit('show', 'matched')">Matched <span class="num">{{ counts.matched }}</span></button>
            </div>
        </div>
        <div v-if="!imp" class="card empty">Upload the SIS Attendance Confirmation Report on the Overview first.</div>
        <section v-else-if="show === 'open'" class="card" aria-labelledby="att-open-h">
            <div class="card-h"><h2 id="att-open-h">Students to match</h2><span class="muted small">Their SSN is on no student record · {{ strong }} with a strong likely match</span></div>
            <div v-if="!unmatched.length" class="empty">Every student in this upload is matched.</div>
            <div v-else class="tablebox"><table class="tbl" style="min-width: 1080px">
                <thead><tr><th scope="col">Student (as SLC sent)</th><th scope="col">SSN</th><th scope="col">Date of birth</th><th scope="col">SLC course · year</th><th scope="col">Likely student</th><th scope="col"></th></tr></thead>
                <tbody>
                    <template v-for="i in unmatched" :key="i.id">
                        <tr :class="expanded === i.id ? 'is-picked' : ''">
                            <th scope="row">{{ titleCase(i.r.forename + ' ' + i.r.surname) }}<div class="muted small" style="font-weight: 400">{{ i.r.term }} · line {{ i.line }}</div></th>
                            <td class="mono">{{ i.r.ssn }}</td>
                            <td>{{ fmtSlc(i.r.dob) }}</td>
                            <td>{{ i.r.slcCode }} · Year {{ i.r.year }}<div class="muted small">{{ i.r.courseName }}</div></td>
                            <td>
                                <template v-if="i.cands.length">
                                    <div class="att-best">
                                        <span :class="'pill ' + (i.cands[0].pct >= 85 ? 'ok' : i.cands[0].pct >= 60 ? 'warn' : 'muted')">{{ i.cands[0].pct }}%</span>
                                        <span><a class="plain" :href="i.cands[0].s.url" target="_blank" rel="noopener"><b>{{ i.cands[0].s.title }} {{ i.cands[0].s.first }} {{ i.cands[0].s.last }}</b></a> <span class="muted small">{{ i.cands[0].s.id }}</span></span>
                                    </div>
                                    <div class="muted small">{{ i.cands[0].s.course || 'No active course' }}{{ i.cands[0].s.intake ? ' · ' + i.cands[0].s.intake : '' }} · DOB {{ fmtIso(i.cands[0].s.dob) }}</div>
                                    <ul class="chips" style="margin-top: 5px"><li v-for="ch in i.cands[0].chips" :key="ch.label" :class="'pill ' + (ch.st === 'ok' ? 'ok' : ch.st === 'part' ? 'warn' : 'bad')">{{ ch.st === 'ok' ? '✓' : ch.st === 'part' ? '~' : '✗' }} {{ ch.label }}</li></ul>
                                    <div v-if="i.cands.length > 1" class="muted small" style="margin-top: 4px">+ {{ plural(i.cands.length - 1, 'other possible student') }}</div>
                                </template>
                                <span v-else class="pill bad">No likely student found</span>
                            </td>
                            <td><div class="acts">
                                <button v-if="i.cands.length" type="button" :class="'btn btn-sm' + (i.cands[0].pct >= 85 ? ' btn-primary' : '')" :disabled="store.busy" @click="pending = i.id; expanded = null">Save SSN</button>
                                <button type="button" class="btn btn-sm" @click="expanded = expanded === i.id ? null : i.id; pending = null">{{ expanded === i.id ? 'Close' : (i.cands.length ? 'Review' : 'Find student') }}</button>
                            </div></td>
                        </tr>
                        <tr v-if="pending === i.id"><td colspan="6">
                            <div class="confirmrow">
                                <span style="flex: 1 1 260px">Save SSN <b class="mono">{{ i.r.ssn }}</b> to <b>{{ i.cands[0].s.id }} {{ i.cands[0].s.first }} {{ i.cands[0].s.last }}</b>? This updates the student record.</span>
                                <button type="button" class="btn btn-primary btn-sm" :disabled="store.busy" @click="saveBest(i)">Save SSN</button>
                                <button type="button" class="btn btn-sm" @click="pending = null">Cancel</button>
                            </div>
                        </td></tr>
                        <tr v-if="expanded === i.id" class="att-expand"><td colspan="6"><AttFind :i="i" /></td></tr>
                    </template>
                </tbody>
            </table></div>
        </section>
        <section v-else class="card">
            <div class="card-h"><h2>Matched students</h2><span class="muted small">{{ counts.ssn }} by SSN · {{ counts.linked }} matched here · {{ noReg }} with no SLC registration recorded for {{ imp.year }}</span></div>
            <div class="tablebox"><table class="tbl" style="min-width: 1300px">
                <thead><tr><th scope="col">Student</th><th scope="col">SSN</th><th scope="col">SLC course · year</th><th scope="col">Our course · year</th><th scope="col">SLC registration {{ imp.year }}</th><th scope="col" style="min-width: 240px">Attendance</th><th scope="col">Matched</th></tr></thead>
                <tbody><tr v-for="i in matched" :key="i.id">
                    <th scope="row"><a class="plain" :href="i.s.url" target="_blank" rel="noopener">{{ i.s.title }} {{ i.s.first }} {{ i.s.last }}</a><div class="muted small" style="font-weight: 400">{{ i.s.id }}</div></th>
                    <td class="mono">{{ i.r.ssn }}</td>
                    <td>{{ i.r.slcCode }} · Year {{ i.r.year }}<div class="muted small">{{ i.r.courseName }}</div></td>
                    <td>{{ i.s.slcCode || 'No SLC code' }} · {{ i.s.year ? 'Year ' + i.s.year : 'no ' + imp.year + ' year' }}<div class="muted small">{{ i.s.course }}{{ i.s.intake ? ' · ' + i.s.intake : '' }}</div>
                        <span v-if="!i.s.courseOk || !i.s.yearOk" class="pill warn" style="margin-top: 4px">{{ !i.s.courseOk && !i.s.yearOk ? 'Course and year differ' : !i.s.courseOk ? 'Course code differs' : 'Year differs' }}</span></td>
                    <td><span v-if="i.s.registration" class="pill ok">✓ Recorded · {{ fmtIso(i.s.registration.date) }}</span><span v-else class="pill warn">None recorded</span></td>
                    <td><AttCell :att="i.s.att" /></td>
                    <td>{{ i.link.method === 'ssn' ? 'By SSN' : 'SSN saved here' }}<div v-if="i.link.method === 'linked'" class="muted small">{{ i.link.by }} · <button type="button" class="btn-link small" :disabled="store.busy" @click="post('slc.registration.att.unlink', { row_id: i.id })">Unmatch</button></div></td>
                </tr></tbody>
            </table></div>
        </section>
    </template>
</template>

<script>
import { computed, inject, ref } from "vue";
import { PLANNED, fmtIso, fmtSlc, money, plural, titleCase } from "./helpers";
import Ico from "./Ico.vue";
import AttFind from "./AttFind.vue";
import AttCell from "./AttCell.vue";
import AttCheck from "./AttCheck.vue";
import AttRegister from "./AttRegister.vue";
import AttCodes from "./AttCodes.vue";
import AttCocs from "./AttCocs.vue";
import AttBatches from "./AttBatches.vue";

/*
 * Bulk attendance (user, 2 Nov 2026): the Overview with step 1 (upload the SIS Attendance
 * Confirmation Report, AttendanceActions::import) and one tile per step, and step 2 (match students
 * by SSN). Steps 3–7 are their own components.
 */
export default {
    components: { Ico, AttFind, AttCell, AttCheck, AttRegister, AttCodes, AttCocs, AttBatches },
    props: { view: { type: String, default: "overview" }, show: { type: String, default: "open" } },
    emits: ["go", "show"],
    setup() {
        const { store, post } = inject("slr");
        const att = computed(() => store.state.attendance);
        const imp = computed(() => att.value.import);
        const counts = computed(() => att.value.counts);
        const unmatched = computed(() => att.value.rows.filter((i) => !i.s));
        const matched = computed(() => att.value.rows.filter((i) => i.s));
        const noReg = computed(() => matched.value.filter((i) => !i.s.registration).length);
        const codes = computed(() => att.value.codes || []);
        const batchCount = computed(() => (att.value.batches || []).length);
        // To match: Save SSN to the likely student (confirmed in the row), or Review — all candidates and the search
        const strong = computed(() => unmatched.value.filter((i) => i.cands.length && i.cands[0].pct >= 85).length);
        const pending = ref(null);
        const expanded = ref(null);
        async function saveBest(i) {
            const data = await post("slc.registration.att.link", { row_id: i.id, student_id: i.cands[0].s.sid });
            if (data) pending.value = null;
        }
        const over = ref(false);
        const uploading = ref(false);
        async function readFile(f) {
            if (!f || store.busy) return;
            const fd = new FormData();
            fd.append("file", f);
            uploading.value = true;
            await post("slc.registration.att.upload", fd);
            uploading.value = false;
        }
        const onDrop = (e) => { over.value = false; readFile(e.dataTransfer.files[0]); };
        const onPick = (e) => { readFile(e.target.files[0]); e.target.value = ""; };
        return { strong, pending, expanded, saveBest, fmtSlc, titleCase, store, post, imp, counts, unmatched, matched, noReg, over, uploading, onDrop, onPick, steps: PLANNED.attendance.steps, codes, batchCount, fmtIso, money, plural };
    },
};
</script>
