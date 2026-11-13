<template>
    <div class="pagehead">
        <div><span class="eyebrow">Step 6</span><h1>Attendance batches</h1>
            <p>Confirmed codes collect in the batch awaiting download, one per course start period (SLC’s field 8). Download it as the SIS attendance import file, upload it in SIS and mark it uploaded. Then open <b>View students</b>, tick the students SIS accepted and use <b>Bulk Attendance &amp; Installment</b> to record their attendance on the SLC tab and, for a fee-paying code, the instalment on the Accounts tab.</p></div>
    </div>
    <section v-if="!awaiting.length" class="card">
        <div class="card-h"><h2>Awaiting download</h2></div>
        <div class="empty">No codes waiting. Confirm codes on step 4 to start a batch.</div>
    </section>
    <section v-for="aw in awaiting" :key="aw.id" class="card">
        <div class="card-h"><h2>Awaiting download · {{ aw.id }} <span class="muted small" style="font-weight: 400">· course start {{ termName(aw.term) }}</span></h2>
            <button type="button" class="btn btn-primary" :disabled="store.busy" @click="download(aw)"><Ico name="down" />Download SIS file ({{ aw.items.length }})</button>
        </div>
        <div class="card-b" style="padding-bottom: 0"><ul class="chips"><li v-for="c in codeCounts(aw)" :key="c.code" class="pill muted"><span class="mono">{{ c.code }}</span> × {{ c.count }}</li></ul></div>
        <div class="tablebox"><table class="tbl" style="min-width: 720px">
            <thead><tr><th scope="col">Student</th><th scope="col">SSN</th><th scope="col">Code</th><th scope="col">Confirmed by</th><th scope="col"></th></tr></thead>
            <tbody><tr v-for="it in aw.items" :key="it.rowId">
                <th scope="row"><a v-if="it.url" class="plain" :href="it.url" target="_blank" rel="noopener">{{ it.name }}</a><template v-else>{{ it.name }}</template><StatusTag :status="it.status" /><div class="muted small" style="font-weight: 400">{{ it.sid }}</div></th>
                <td class="mono">{{ it.ssn }}</td>
                <td><span :class="'pill ' + tone(it.code)"><span class="mono">{{ it.code }}</span> {{ meaning(it.code) }}</span></td>
                <td class="muted">{{ it.by }} · {{ it.at }}</td>
                <td><button type="button" class="btn-link small" :disabled="store.busy" @click="post('slc.registration.att.undo', { row_id: it.rowId })">Remove</button></td>
            </tr></tbody>
        </table></div>
    </section>

    <section class="card">
        <div class="card-h"><h2>All batches</h2></div>
        <div v-if="!batches.length" class="empty">No batches yet.</div>
        <div v-else class="tablebox"><table class="tbl" style="min-width: 820px">
            <thead><tr><th scope="col">Batch</th><th scope="col">Course start</th><th scope="col">Students</th><th scope="col">Status</th><th scope="col">Downloaded</th><th scope="col">Uploaded to SIS</th><th scope="col"></th></tr></thead>
            <tbody><tr v-for="b in [...batches].reverse()" :key="b.id">
                <th scope="row" class="mono">{{ b.id }}</th><td>{{ termName(b.term) }}</td><td class="num">{{ b.items.length }}</td>
                <td><span :class="'pill ' + (b.status === 'awaiting' ? 'warn' : b.status === 'downloaded' ? 'info' : 'ok')">{{ b.status === 'awaiting' ? 'Awaiting download' : b.status === 'downloaded' ? 'Downloaded' : 'Uploaded to SIS' }}</span></td>
                <td class="muted">{{ b.downloadedBy ? b.downloadedBy + ' · ' + b.downloadedAt : '–' }}</td>
                <td class="muted">{{ b.uploadedBy ? b.uploadedBy + ' · ' + b.uploadedAt : '–' }}</td>
                <td><div class="acts">
                    <button type="button" class="btn btn-sm" @click="openStudents(b.id)">View students</button>
                    <button v-if="b.file" type="button" class="btn btn-sm" @click="viewId = b.id">View file</button>
                    <button v-if="b.status === 'downloaded'" type="button" class="btn btn-sm btn-primary" :disabled="store.busy" @click="post('slc.registration.att.batch.uploaded', { code: b.id })">Mark uploaded</button>
                </div></td>
            </tr></tbody>
        </table></div>
    </section>

    <section v-if="sb" ref="panel" class="card" aria-labelledby="ab-h">
        <div class="card-h">
            <div><h2 id="ab-h">Students in {{ sb.id }}</h2><div class="muted small">Course start {{ termName(sb.term) }} · {{ statusLine(sb) }} · {{ recorded }} of {{ sb.items.length }} SLC attendances recorded</div>
                <div v-if="preview" class="ab-totals small">Expected from SLC <b>{{ money(preview.expected) }}</b> for {{ plural(preview.toRecord, 'student') }} to record<template v-if="preview.moved"> · {{ preview.moved }} recorded as the next free term</template><template v-if="preview.problem"> · {{ preview.problem }} can’t be recorded</template><template v-if="preview.recorded"> · instalments recorded <b>{{ money(preview.recorded) }}</b></template></div></div>
            <div class="row">
                <!-- Bulk Attendance & Installment (user, 13 Nov 2026): the ticked students' SLC attendance on their SLC tab -->
                <template v-if="picked.length">
                    <span class="muted small">{{ plural(picked.length, 'student') }} selected</span>
                    <!-- (user, 13 Nov 2026) SIS's Import Results: lines it rejected go back to step 4 to be sent again -->
                    <button type="button" class="btn" :disabled="store.busy" :aria-expanded="rejecting ? 'true' : 'false'" @click="rejecting = !rejecting">Rejected by SIS ({{ picked.length }})</button>
                    <button type="button" class="btn btn-primary" :disabled="store.busy" @click="bulkAttendance">Bulk Attendance & Installment ({{ picked.length }})</button>
                </template>
                <button type="button" class="btn btn-sm" @click="closeStudents">Close</button>
            </div>
        </div>
        <div v-if="rejecting && picked.length" class="card-b" style="padding-bottom: 0"><div class="confirmrow">
            <span style="flex: 1 1 100%">Record {{ plural(picked.length, 'ticked student') }} as rejected by SIS (its Attendance Confirmation Import Results). They leave this batch and go back to step 4 with their code, to be confirmed into a new batch and sent again; this batch’s file stays as it was sent.</span>
            <label class="sr-only" for="sis-reason">SIS’s reason</label>
            <input id="sis-reason" v-model="rejectReason" class="input" style="flex: 1 1 320px" maxlength="255" placeholder="SIS’s reason, e.g. Confirmation has already been submitted">
            <button type="button" class="btn btn-sm btn-primary" :disabled="store.busy" @click="markRejected">Back to step 4 ({{ picked.length }})</button>
            <button type="button" class="btn btn-sm" @click="rejecting = false">Cancel</button>
        </div></div>
        <div v-if="sb.status !== 'uploaded'" class="card-b" style="padding-bottom: 0"><div class="notice">
            <Ico name="info" /><span>{{ sb.status === 'awaiting' ? 'Download the SIS file and upload it in SIS first.' : 'Once the file is in SIS, mark the batch uploaded.' }} Then tick the students SIS accepted and use Bulk Attendance & Installment to record their attendance on the SLC tab and, for a fee-paying code, the instalment on the Accounts tab. Students SIS rejected (its Import Results): tick them and use Rejected by SIS to send them again in a new batch.</span>
        </div></div>
        <!-- each line's attendance (SLC tab) and instalment (Accounts tab): to be recorded, or recorded (user, 13 Nov 2026) -->
        <div class="tablebox"><table class="tbl ab-tbl" style="min-width: 1180px">
            <thead><tr>
                <th scope="col" style="width: 44px"><input class="pick" type="checkbox" :checked="allPicked" :indeterminate.prop="somePicked" :disabled="!canPick || !pickable.length" aria-label="Select all students not yet recorded" @change="toggleAll($event.target.checked)"></th>
                <th scope="col">Student</th><th scope="col">Code</th><th scope="col">Attendance · SLC tab</th><th scope="col">Instalment · Accounts</th><th scope="col"></th>
            </tr></thead>
            <tbody><tr v-for="it in sb.items" :key="it.rowId" :class="picked.includes(it.rowId) ? 'is-picked' : ''">
                <td><input v-model="picked" class="pick" type="checkbox" :value="it.rowId" :disabled="!canPick || !isPickable(it)" :aria-label="'Select ' + it.name"></td>
                <th scope="row"><a v-if="it.url" class="plain" :href="it.url" target="_blank" rel="noopener">{{ it.name }}</a><template v-else>{{ it.name }}</template><StatusTag :status="it.status" />
                    <div class="muted small" style="font-weight: 400">{{ it.sid }} · <span class="mono">{{ it.ssn }}</span></div></th>
                <td><span :class="'pill ' + tone(it.code)"><span class="mono">{{ it.code }}</span> {{ meaning(it.code) }}</span><div class="muted small" style="margin-top: 3px">{{ it.by }} · {{ it.at }}</div></td>
                <td>
                    <template v-if="it.rejected">
                        <span class="pill bad">Rejected by SIS</span>
                        <div class="ab-note">{{ it.rejected.reason || 'No reason noted' }}<span class="muted"> · {{ it.rejected.by }} · {{ it.rejected.at }}</span></div>
                        <div v-if="it.rejected.resent" class="ab-note ok">Sent again in {{ it.rejected.resent }}</div>
                        <div v-else class="muted small">Back on step 4 to confirm again · <button type="button" class="btn-link small" :disabled="store.busy" @click="post('slc.registration.att.batch.rejected.undo', { row_id: it.rowId })">Undo</button></div>
                    </template>
                    <span v-else-if="!pv(it)" class="muted small">Loading…</span>
                    <template v-else>
                        <span :class="'pill ' + attState(it).tone">{{ attState(it).text }}</span>
                        <dl v-if="pv(it).attendance" class="ab-kv">
                            <dt>Registration</dt><dd>{{ pv(it).attendance.registrationId ? '#' + pv(it).attendance.registrationId + ' · Year ' + pv(it).attendance.registrationYear : 'None (never attended)' }}</dd>
                            <dt>Confirmation</dt><dd>{{ pv(it).attendance.date ? fmtIso(pv(it).attendance.date) : 'The day the batch is marked uploaded' }}</dd>
                            <dt>Term</dt><dd>{{ pv(it).attendance.term }} · Term {{ pv(it).attendance.sessionTerm }}</dd>
                            <dt>Code</dt><dd class="mono">{{ pv(it).attendance.code }}</dd>
                        </dl>
                        <div v-if="pv(it).problem" class="ab-note bad">Can’t be recorded: {{ pv(it).problem }}</div>
                        <!-- (user, 13 Nov 2026) no overwriting: the terms already on the SLC tab are passed over for the next free one -->
                        <template v-else-if="pv(it).taken && pv(it).taken.length">
                            <div v-for="t in pv(it).taken" :key="t.sessionTerm" class="ab-note warn"><a class="ab-link" :href="it.slcUrl" target="_blank" rel="noopener">Term {{ t.sessionTerm }}{{ t.term ? ' · ' + t.term : '' }} already on the SLC tab: #{{ t.attendance.id }} · {{ t.attendance.code || '–' }} · {{ fmtIso(t.attendance.date) }}{{ t.attendance.by ? ' · ' + t.attendance.by : '' }} ↗</a><template v-if="t.installment"> · <a class="ab-link" :href="it.accountsUrl" target="_blank" rel="noopener">instalment #{{ t.installment.id }} {{ money(t.installment.amount) }}{{ t.installment.receiptId ? ' (paid)' : '' }} ↗</a></template></div>
                            <div v-if="pv(it).attendance" class="ab-note ok">→ {{ pv(it).recorded ? 'Recorded as' : 'Will be recorded as' }} a new row for Term {{ pv(it).attendance.sessionTerm }} · {{ pv(it).attendance.term }}</div>
                        </template>
                        <div v-if="pv(it).recorded && it.record" class="muted small">{{ it.record.by }}{{ it.record.by ? ' · ' : '' }}{{ it.record.at }}{{ recordedNote(it.record.status) }}</div>
                    </template>
                </td>
                <td>
                    <template v-if="!it.rejected && pv(it)">
                        <template v-if="pv(it).installment">
                            <div class="ab-amt">{{ money(pv(it).installment.amount) }}<span v-if="pv(it).installment.share" class="muted small"> · {{ pv(it).installment.share }}% of TFL {{ money(pv(it).tfl) }}</span><span v-if="pv(it).recorded" class="muted small"> · #{{ pv(it).installment.id }}</span></div>
                            <dl class="ab-kv">
                                <dt>Agreement</dt><dd :class="pv(it).installment.agreementId ? '' : 'ab-bad'">{{ pv(it).installment.agreementId ? '#' + pv(it).installment.agreementId + ' · Year ' + pv(it).installment.agreementYear : 'None for the year' }}</dd>
                                <dt>Date</dt><dd>{{ pv(it).installment.date ? fmtIso(pv(it).installment.date) : 'The day the batch is marked uploaded' }}</dd>
                                <dt>Term</dt><dd>{{ pv(it).installment.term }} · Term {{ pv(it).installment.sessionTerm }}</dd>
                            </dl>
                        </template>
                        <span v-else class="muted small">{{ pv(it).noInstallment || '–' }}</span>
                    </template>
                </td>
                <td><div class="acts"><a v-if="it.slcUrl" class="btn-link small" :href="it.slcUrl" target="_blank" rel="noopener">SLC tab ↗</a><a v-if="it.accountsUrl" class="btn-link small" :href="it.accountsUrl" target="_blank" rel="noopener">Accounts ↗</a></div></td>
            </tr></tbody>
        </table></div>
    </section>

    <section v-if="shown && shown.file" class="card">
        <div class="card-h"><h2 class="mono">{{ shown.file.name }}</h2>
            <div class="row">
                <button type="button" class="btn btn-sm" :disabled="store.busy" @click="download(shown)"><Ico name="down" />Download again</button>
                <button type="button" class="btn btn-sm" :disabled="store.busy" :aria-expanded="regenFor === shown.id ? 'true' : 'false'" @click="regenFor = regenFor === shown.id ? null : shown.id">Regenerate file</button>
                <button type="button" class="btn btn-sm" :disabled="!texts[shown.id]" @click="copy(texts[shown.id])">{{ copied || 'Copy text' }}</button>
            </div>
        </div>
        <div class="card-b stack">
            <!-- Regenerate (user, 13 Nov 2026, as Bulk registration's batches): build the file again, save it, download it -->
            <div v-if="regenFor === shown.id" class="confirmrow">
                <span style="flex: 1 1 100%">Build <b class="mono">{{ shown.file.name }}</b> again from the batch’s {{ plural(shown.items.length, 'student') }}, save it and download it. SIS refuses a file whose content it has already imported – to send it again, use today’s date.</span>
                <template v-if="shown.file.date && shown.file.date !== today">
                    <button type="button" class="btn btn-sm" :disabled="store.busy" @click="regenerate(shown, 'original')">Keep {{ fmtIso(shown.file.date) }}</button>
                    <button type="button" class="btn btn-sm btn-primary" :disabled="store.busy" @click="regenerate(shown, 'today')">Use today ({{ fmtIso(today) }})</button>
                </template>
                <button v-else type="button" class="btn btn-sm btn-primary" :disabled="store.busy" @click="regenerate(shown, 'today')">Regenerate ({{ fmtIso(today) }})</button>
                <button type="button" class="btn btn-sm" @click="regenFor = null">Cancel</button>
            </div>
            <span v-if="msg" class="muted">{{ msg }}</span>
            <pre class="file" aria-label="File preview">{{ texts[shown.id] || 'Loading…' }}</pre>
            <div v-if="rebuilt[shown.id]" class="notice"><Ico name="info" /><span>The saved copy of this file couldn’t be read here, so it was built again from the batch’s lines with its original date – the same file.</span></div>
            <!-- SLC's Attendance and Student Information Import & Export Files technical specification v9.0 (user, 13 Nov 2026) -->
            <div class="notice"><Ico name="info" /><span>Built to SLC’s technical specification (v9.0, Appendix B): a header of HEI code, HEI name, file date, number of students and the Tuition Fee Loan, Tuition Fee Grant and Student Pays totals of these lines; then each student’s line exactly as SIS exported it, with the last field set to the confirmed code. No footer, one line per student, UTF-8.</span></div>
            <div class="notice"><Ico name="info" /><span>In SIS: <b>Attendance Home › Import Attendance</b>, choose academic year <b>{{ shown.year || 'of this report' }}</b> (the file doesn’t carry it), then <b>Import and Submit</b> – Import and Save only saves the codes and releases no payment. SLC checks the file offline and posts results under Attendance Confirmation Import Results within 24 hours. A code goes to the student’s earliest outstanding liability period. SIS refuses a file whose content it has already imported.</span></div>
        </div>
    </section>
</template>

<script>
import { computed, inject, nextTick, reactive, ref, watch } from "vue";
import Ico from "./Ico.vue";
import StatusTag from "./StatusTag.vue";
import { fmtIso, money, plural } from "./helpers";

/*
 * Bulk attendance › step 6 (step 7 until 6 Nov 2026; user, 2 Nov 2026): the batches of confirmed codes, downloaded as the SIS
 * attendance import file (AttendanceActions::download / fileText / markUploaded), as Bulk
 * registration's Batches.
 */
const TERMS = { AUT: "Autumn", WIN: "Winter", SPR: "Spring", SUM: "Summer" };

export default {
    components: { StatusTag, Ico },
    setup() {
        const { store, post, get } = inject("slr");
        const att = computed(() => store.state.attendance);
        const batches = computed(() => att.value.batches || []);
        const awaiting = computed(() => batches.value.filter((b) => b.status === "awaiting"));
        const codes = computed(() => att.value.codes || []);
        const meaning = (c) => (codes.value.find((x) => x.code === c) || {}).meaning || "";
        const tone = (c) => (c === "A" ? "ok" : (codes.value.find((x) => x.code === c) || {}).coc ? "bad" : "warn");
        const termName = (t) => TERMS[t] || t || "";
        const codeCounts = (b) => {
            const n = {};
            b.items.forEach((it) => { n[it.code] = (n[it.code] || 0) + 1; });
            return Object.keys(n).sort().map((code) => ({ code, count: n[code] }));
        };
        const statusLine = (b) => (b.status === "uploaded" ? "Uploaded to SIS · " + b.uploadedBy + " " + b.uploadedAt : b.status === "downloaded" ? "Downloaded · " + b.downloadedBy + " " + b.downloadedAt + " · not yet marked uploaded" : "Awaiting download");

        const msg = ref("");
        const copied = ref("");
        const viewId = ref(null);
        const texts = reactive({});
        const rebuilt = reactive({}); // the saved copy couldn't be read, so the file shown was built again
        const shown = computed(() => batches.value.find((b) => b.id === viewId.value) || [...batches.value].reverse().find((b) => b.file));
        function save(file) {
            try {
                const blob = new Blob([file.text], { type: "text/plain;charset=utf-8" });
                const a = document.createElement("a");
                a.href = URL.createObjectURL(blob);
                a.download = file.name;
                document.body.appendChild(a);
                a.click();
                setTimeout(() => { URL.revokeObjectURL(a.href); a.remove(); }, 0);
                msg.value = "Saved " + file.name + ".";
            } catch (e) {
                msg.value = "Download isn’t available here. Use Copy text instead.";
            }
        }
        async function download(b) {
            msg.value = "";
            const data = await post("slc.registration.att.batch.download", { code: b.id });
            if (data && data.file) { texts[b.id] = data.file.text; rebuilt[b.id] = !!data.file.rebuilt; viewId.value = b.id; save(data.file); }
        }
        const regenFor = ref(null); // the batch whose Regenerate choice is open
        const today = new Date().toISOString().slice(0, 10);
        async function regenerate(b, date) {
            msg.value = "";
            const data = await post("slc.registration.att.batch.regenerate", { code: b.id, date });
            if (data && data.file) { texts[b.id] = data.file.text; rebuilt[b.id] = false; regenFor.value = null; save(data.file); }
        }
        function copy(text) {
            try { navigator.clipboard.writeText(text).then(() => { copied.value = "Copied"; }, () => { copied.value = "Select the text below and copy it"; }); } catch (e) { copied.value = "Select the text below and copy it"; }
        }
        watch(shown, async (b) => {
            copied.value = "";
            if (b && b.file && !texts[b.id]) {
                const data = await get("slc.registration.att.batch.file", { code: b.id });
                if (data) { texts[b.id] = data.text; rebuilt[b.id] = !!data.rebuilt; }
            }
        }, { immediate: true });

        /* View students; Bulk Attendance & Installment on the ticked (user, 13 Nov 2026), once the batch is uploaded to SIS */
        const studentsOf = ref(null);
        const panel = ref(null);
        const picked = ref([]);
        const sb = computed(() => batches.value.find((b) => b.id === studentsOf.value) || null);
        const canPick = computed(() => !!sb.value && sb.value.status === "uploaded");
        const isPickable = (it) => !(it.record && it.record.status) && !it.rejected; // recorded or rejected: can't be ticked again
        const pickable = computed(() => (sb.value ? sb.value.items.filter(isPickable) : []));
        const allPicked = computed(() => pickable.value.length > 0 && pickable.value.every((it) => picked.value.includes(it.rowId)));
        const somePicked = computed(() => picked.value.length > 0 && !allPicked.value);
        const recorded = computed(() => (sb.value ? sb.value.items.filter((it) => it.record && it.record.status).length : 0));
        const toggleAll = (on) => { picked.value = on ? pickable.value.map((it) => it.rowId) : []; };
        // each line's attendance and instalment, to be recorded or recorded (AttendanceActions::batchPreview)
        const previews = reactive({});
        const preview = computed(() => (sb.value ? previews[sb.value.id] || null : null));
        const pv = (it) => (preview.value ? preview.value.rows[it.rowId] || null : null);
        async function loadPreview(code) {
            const data = await get("slc.registration.att.batch.preview", { code });
            if (data && data.rows) previews[code] = data;
        }
        watch(() => (sb.value ? sb.value.id + "|" + sb.value.status + "|" + sb.value.items.map((it) => it.rowId + ":" + ((it.record && it.record.status) || "")).join(",") : ""),
            (key) => { if (key && sb.value) loadPreview(sb.value.id); }, { immediate: true });
        const attState = (it) => {
            const p = pv(it);
            if (p.recorded) return { tone: "ok", text: (p.status === "overwritten" ? "✓ Recorded – overwritten" : "✓ Recorded") + (p.attendance ? " #" + p.attendance.id : "") };
            if (p.problem) return { tone: "bad", text: "Can’t be recorded" };
            return { tone: "info", text: p.moved ? "To record – Term " + p.attendance.sessionTerm : "To record" };
        };
        async function openStudents(code) {
            studentsOf.value = code;
            picked.value = [];
            await nextTick();
            if (panel.value) panel.value.scrollIntoView({ behavior: "smooth", block: "start" });
        }
        const closeStudents = () => { studentsOf.value = null; picked.value = []; };
        const recordedNote = (st) => (st === "overwritten" ? " · records there overwritten" : st === "completed" ? " · kept what was there, added the missing part" : "");
        // (user, 13 Nov 2026) always new rows: a term already on the SLC tab is passed over for the next free one
        // Rejected by SIS (user, 13 Nov 2026): the ticked lines back to step 4, with SIS's reason
        const rejecting = ref(false);
        const rejectReason = ref("");
        async function markRejected() {
            const data = await post("slc.registration.att.batch.rejected", { code: sb.value.id, row_ids: picked.value, reason: rejectReason.value });
            if (data) { picked.value = []; rejecting.value = false; rejectReason.value = ""; }
        }
        async function bulkAttendance() {
            const data = await post("slc.registration.att.batch.attendance", { code: sb.value.id, row_ids: picked.value });
            if (data) picked.value = [];
        }
        // a line that left the batch or got recorded is no longer ticked
        watch(sb, (b) => { if (!b) return; picked.value = picked.value.filter((id) => b.items.some((it) => it.rowId === id && isPickable(it))); });

        return { store, post, batches, awaiting, meaning, tone, termName, codeCounts, statusLine, msg, copied, viewId, texts, rebuilt, shown, download, copy, regenFor, today, regenerate, studentsOf, panel, sb, openStudents,
            picked, canPick, isPickable, pickable, allPicked, somePicked, recorded, toggleAll, preview, pv, attState, money, closeStudents, recordedNote, bulkAttendance, rejecting, rejectReason, markRejected, plural, fmtIso };
    },
};
</script>
