<template>
    <div class="pagehead">
        <div><span class="eyebrow">Step 4</span><h1>Attendance &amp; codes</h1>
            <p>The register for each matched student in their liability period – the first of Term 1–3 of their {{ yearName }} year not yet confirmed on the SLC tab when SIS issued this report{{ imp && imp.reportDate ? ' (' + fmtIso(imp.reportDate) + ')' : '' }}, as SIS applies a code to the earliest outstanding period – from the term’s start to today, and the code it suggests from their status, the register and the Data check. The term comes from the student’s course instance, or from their class plans for the year when the instance has none. Change any code, then confirm: confirmed codes go into the batch awaiting download (step 6), and a code that needs a CoC queues one on the student’s SLC tab (step 5). A code can only be confirmed once the student’s SLC registration for {{ yearName }} is recorded.</p></div>
        <button type="button" class="btn btn-primary" :disabled="!picked.length || store.busy" @click="confirm(picked)">Confirm selected ({{ picked.length }})</button>
    </div>

    <section class="kpis" aria-label="Codes">
        <div class="kpi t-ok"><span class="k-label">Ready to confirm</span><span class="k-val">{{ by('ready').length }}</span><span class="k-note">{{ review }} flagged for a look</span></div>
        <div class="kpi t-bad"><span class="k-label">No SLC registration</span><span class="k-val">{{ by('noreg').length }}</span><span class="k-note">None recorded for {{ yearName }} – can’t confirm</span></div>
        <div class="kpi t-warn"><span class="k-label">Needs a code</span><span class="k-val">{{ by('nocode').length }}</span><span class="k-note">No suggestion – choose one</span></div>
        <div class="kpi t-bad"><span class="k-label">Held by the Data check</span><span class="k-val">{{ by('blocked').length }}</span><span class="k-note">Differences still to sort (step 3)</span></div>
        <div class="kpi t-info"><span class="k-label">Confirmed</span><span class="k-val">{{ by('confirmed').length }}</span><span class="k-note">In a batch · {{ cocCount }} with a CoC</span></div>
    </section>

    <!-- the register in the liability period (step 4 before 6 Nov 2026): its counts, which filter the list -->
    <section v-if="imp && rows.length" class="card" aria-labelledby="att-by-att">
        <div class="card-h"><h2 id="att-by-att">Attendance in the liability period</h2><span class="muted small">Register from the term’s start to today, {{ fmtIso(today) }} · choose one to see only those students</span></div>
        <div class="card-b field-sum" role="group" aria-label="Filter by attendance">
            <button type="button" class="fsum fsum-all" :aria-pressed="attFilter === 'all'" @click="attFilter = 'all'"><span class="fsum-l">All students</span><span class="fsum-n num">{{ rows.length }}</span><span class="fsum-bar" aria-hidden="true"><span style="width: 100%"></span></span><span class="fsum-m">matched students</span></button>
            <button v-for="k in KINDS" :key="k.key" type="button" :class="'fsum t-' + k.tone" :aria-pressed="attFilter === k.key" @click="attFilter = attFilter === k.key ? 'all' : k.key">
                <span class="fsum-l">{{ k.label }}</span><span class="fsum-n num">{{ kindCount(k.key) }}</span>
                <span class="fsum-bar" aria-hidden="true"><span :style="{ width: (kindCount(k.key) ? Math.max(6, Math.round(kindCount(k.key) / rows.length * 100)) : 0) + '%' }"></span></span>
                <span class="fsum-m">{{ k.key === 'noterm' ? 'No term for the period on the course' : k.note }}</span>
            </button>
        </div>
    </section>

    <section v-if="codeSummary.length" class="card" aria-labelledby="att-by-code">
        <div class="card-h"><h2 id="att-by-code">By code</h2><span class="muted small">Code each student will be sent with · choose one to see only those students</span></div>
        <div class="card-b field-sum" role="group" aria-label="Filter by code">
            <button type="button" class="fsum fsum-all" :aria-pressed="codeFilter === 'all'" @click="codeFilter = 'all'"><span class="fsum-l">All codes</span><span class="fsum-n num">{{ rows.length }}</span><span class="fsum-bar" aria-hidden="true"><span style="width: 100%"></span></span><span class="fsum-m">matched students</span></button>
            <button v-for="c in codeSummary" :key="c.code" type="button" class="fsum" :title="c.code + ' – ' + c.meaning" :aria-pressed="codeFilter === c.code" @click="codeFilter = codeFilter === c.code ? 'all' : c.code">
                <span class="fsum-l"><span class="mono">{{ c.code }}</span> · {{ c.meaning }}</span><span class="fsum-n num">{{ c.count }}</span>
                <span class="fsum-bar" aria-hidden="true"><span :style="{ width: Math.max(6, Math.round(c.count / codeMax * 100)) + '%' }"></span></span>
                <span class="fsum-m">{{ c.coc ? 'Needs a CoC · ' + c.cocType : 'No CoC' }}</span>
            </button>
        </div>
    </section>

    <!-- (user, 13 Nov 2026) the period already on the SLC tab, or what would stop the SLC tab record at the final step -->
    <div v-if="onTab.length || blockedLater.length || inSis.length" class="notice warn" style="margin-bottom: 12px"><Ico name="info" /><span>
        <template v-if="inSis.length"><b>{{ plural(inSis.length, 'student') }}</b> already {{ inSis.length === 1 ? 'has a code' : 'have codes' }} in SIS for the period (the report’s column 28: saved or submitted) – confirming asks about them first.<br v-if="onTab.length || blockedLater.length"></template>
        <template v-if="onTab.length"><b>{{ plural(onTab.length, 'student') }}</b> already {{ onTab.length === 1 ? 'has' : 'have' }} their liability period on the SLC tab, entered by hand after SIS issued this report. If it was confirmed in SIS too, SIS refuses a second confirmation – confirming asks about them first.
            <button type="button" class="btn-link small" :aria-pressed="special === 'tab'" @click="special = special === 'tab' ? '' : 'tab'">{{ special === 'tab' ? 'Show all' : 'Show them' }}</button></template>
        <template v-if="blockedLater.length"><br v-if="onTab.length"><b>{{ plural(blockedLater.length, 'student') }}</b> can be sent to SIS but not recorded on the SLC tab afterwards (Bulk Attendance &amp; Installment) – the reason is on their row.
            <button type="button" class="btn-link small" :aria-pressed="special === 'block'" @click="special = special === 'block' ? '' : 'block'">{{ special === 'block' ? 'Show all' : 'Show them' }}</button></template>
    </span></div>

    <StepSearch v-if="rows.length" v-model="q" id="search-att-codes" :shown="items.length" :total="unsearched.length" />
    <div class="row" style="justify-content: space-between">
        <div class="filters" role="group" aria-label="Show students">
            <button v-for="[k, label] in FILTERS" :key="k" type="button" class="chip" :aria-pressed="show === k" @click="show = k">{{ label }} <span class="num">{{ k === 'all' ? rows.length : by(k).length }}</span></button>
        </div>
        <span class="muted small">
            <template v-if="narrowed">{{ plural(items.length, 'student') }} shown · <button type="button" class="btn-link small" @click="showAll">Show all</button></template>
            <template v-if="narrowed && picked.length"> · </template>
            <template v-if="picked.length">{{ plural(picked.length, 'student') }} selected · <button type="button" class="btn-link small" @click="picked = []">Clear</button></template>
        </span>
    </div>

    <div v-if="!imp" class="card empty">Upload the SIS Attendance Confirmation Report on the Overview first.</div>
    <section v-else class="card">
        <div v-if="!items.length" class="empty">{{ narrowed ? 'No students match these filters.' : 'No students here.' }}
            <div v-if="narrowed" style="margin-top: 10px"><button type="button" class="btn btn-sm" @click="showAll">Show all students</button></div>
        </div>
        <div v-else class="tablebox"><table class="tbl codes-tbl">
            <colgroup><col style="width: 44px"><col style="width: 19%"><col style="width: 29%"><col style="width: 22%"><col></colgroup>
            <thead><tr>
                <th scope="col"><input class="pick" type="checkbox" :checked="allPicked" :indeterminate.prop="picked.length > 0 && !allPicked" :disabled="!pickable.length" aria-label="Select every student ready to confirm in this list" @change="toggleAll($event.target.checked)"></th>
                <th scope="col">Student</th><th scope="col">Attendance in the liability period</th><th scope="col">Suggested</th><th scope="col">Code to send</th>
            </tr></thead>
            <tbody><tr v-for="i in items" :key="i.id" :class="picked.includes(i.id) ? 'is-picked' : ''">
                <td><input v-model="picked" class="pick" type="checkbox" :value="i.id" :disabled="i.codeStage !== 'ready'" :aria-label="'Select ' + i.s.first + ' ' + i.s.last"></td>
                <th scope="row"><a class="plain" :href="i.s.url" target="_blank" rel="noopener">{{ i.s.title }} {{ i.s.first }} {{ i.s.last }}</a><StatusTag :status="i.s.status" />
                    <div class="muted small" style="font-weight: 400">{{ i.s.id }} · {{ i.status || 'No status' }}</div></th>
                <td>
                    <template v-if="i.liab && i.liab.start">
                        <AttCell :att="i.liab" />
                        <div class="small" style="margin-top: 4px"><b>{{ i.liab.sessionTerm ? 'Term ' + i.liab.sessionTerm + ' · ' : '' }}{{ i.liab.termName }}</b> {{ fmtIso(i.liab.start) }} – {{ fmtIso(i.liab.end) }} <span class="muted">· first attended {{ i.liab.first ? fmtIso(i.liab.first) : '–' }}</span></div>
                        <div class="muted small">{{ i.liab.decl }} · {{ i.liab.from === 'instance' ? 'course instance' : 'class plans' }}{{ i.liab.started ? '' : ' · not started yet' }}</div>
                    </template>
                    <span v-else class="pill bad">No term found for the period</span>
                    <!-- the records link to the student's SLC tab and Accounts tab (user, 13 Nov 2026) -->
                    <div v-if="i.slcTab && i.slcTab.attendance" :class="'ab-note ' + (i.slcTab.recorded ? '' : 'warn')">
                        <a class="ab-link" :href="i.s.slcUrl" target="_blank" rel="noopener" :title="'Open the SLC tab of ' + i.s.first + ' ' + i.s.last">{{ i.slcTab.recorded ? 'Recorded on the SLC tab' : 'Already on the SLC tab' }}: #{{ i.slcTab.attendance.id }} · {{ i.slcTab.attendance.code || '–' }} · {{ fmtIso(i.slcTab.attendance.date) }}{{ i.slcTab.attendance.by ? ' · ' + i.slcTab.attendance.by : '' }} ↗</a><template v-if="i.slcTab.installment"> · <a class="ab-link" :href="i.s.accountsUrl" target="_blank" rel="noopener" :title="'Open the Accounts tab of ' + i.s.first + ' ' + i.s.last">instalment #{{ i.slcTab.installment.id }} {{ money(i.slcTab.installment.amount) }}{{ i.slcTab.installment.receiptId ? ' (paid)' : '' }} ↗</a></template>
                    </div>
                    <div v-if="i.sisCode && !i.confirm" class="ab-note warn">SIS already has {{ i.sisCode }} for this period (saved or submitted) – if submitted, SIS refuses a second one</div>
                    <div v-if="i.sisRejected" class="ab-note bad">Rejected by SIS in {{ i.sisRejected.batchId }} ({{ i.sisRejected.code }}){{ i.sisRejected.reason ? ': ' + i.sisRejected.reason : '' }} · {{ i.sisRejected.at }}{{ i.confirm ? '' : ' – confirm again to send it in a new batch' }}</div>
                    <div v-if="i.notReg" class="ab-note warn">Not Registered with SLC for {{ yearName }} ({{ i.notReg.status }}, Year {{ i.notReg.year }}): SIS takes only X or S</div>
                    <div v-else-if="i.slcTab && i.slcTab.installment && !i.slcTab.attendance" class="ab-note warn"><a class="ab-link" :href="i.s.accountsUrl" target="_blank" rel="noopener">Already on Accounts: instalment #{{ i.slcTab.installment.id }} · {{ money(i.slcTab.installment.amount) }} · {{ fmtIso(i.slcTab.installment.date) }}{{ i.slcTab.installment.receiptId ? ' (paid)' : '' }} ↗</a></div>
                </td>
                <td>
                    <span v-if="i.code.suggested" :class="'pill ' + tone(i.code.suggested)"><span class="mono">{{ i.code.suggested }}</span> {{ meaning(i.code.suggested) }}</span>
                    <span v-else class="pill muted">No suggestion</span>
                    <span v-if="i.code.review && i.code.suggested" class="pill warn" style="margin-left: 4px">Check</span>
                    <div class="muted small" style="margin-top: 3px">{{ i.code.reason }}</div>
                </td>
                <td>
                    <template v-if="i.confirm">
                        <span :class="'pill ' + tone(i.confirm.code)"><span class="mono">{{ i.confirm.code }}</span> {{ meaning(i.confirm.code) }}</span>
                        <div v-if="i.coc" class="muted small" style="margin-top: 3px">{{ i.coc.type }} CoC {{ i.coc.actioned ? 'done' : 'queued' }}</div>
                    </template>
                    <div class="code-row" :style="i.confirm ? 'margin-top: 6px' : ''">
                        <template v-if="!i.confirm && i.codeStage !== 'noreg'">
                            <label class="sr-only" :for="'code-' + i.id">Code for {{ i.s.first }} {{ i.s.last }}</label>
                            <select :id="'code-' + i.id" class="input code-sel" :value="i.code.chosen || ''" :disabled="store.busy" @change="setCode(i, $event.target.value)">
                                <option value="">{{ i.code.suggested ? 'Suggested · ' + i.code.suggested : 'Choose a code' }}</option>
                                <option v-for="c in codes.filter((x) => x.importable !== false && (!i.notReg || ['X', 'S'].includes(x.code)))" :key="c.code" :value="c.code">{{ c.code }} – {{ c.meaning }}</option>
                            </select>
                        </template>
                        <template v-if="i.codeStage === 'confirmed'">
                            <span class="muted small">{{ i.confirm.earlier ? 'Earlier batch' : 'In' }} <span class="mono">{{ i.confirm.batchId }}</span> · {{ i.confirm.by }} {{ i.confirm.at }}</span>
                            <button v-if="i.confirm.batchStatus === 'awaiting' && !i.confirm.earlier" type="button" class="btn-link small" :disabled="store.busy" @click="post('slc.registration.att.undo', { row_id: i.id })">Remove</button>
                        </template>
                        <button v-else-if="i.codeStage === 'ready'" type="button" class="btn btn-sm btn-primary" :disabled="store.busy" @click="confirm([i.id])">Confirm {{ i.code.final }}</button>
                        <template v-else-if="i.codeStage === 'noreg'">
                            <span class="pill bad">No SLC registration</span>
                            <a class="btn btn-sm" :href="i.s.slcUrl" target="_blank" rel="noopener">SLC tab ↗</a>
                        </template>
                        <button v-else-if="i.codeStage === 'blocked'" type="button" class="btn btn-sm" @click="$emit('go', 'check')">Sort Data check</button>
                        <span v-else class="muted small">Choose a code</span>
                    </div>
                    <div v-if="i.recordBlock && i.codeStage !== 'noreg'" class="ab-note bad">Can’t be recorded on the SLC tab later: {{ i.recordBlock }}</div>
                    <div v-if="i.codeStage === 'noreg'" class="muted small" style="margin-top: 4px">Record the {{ yearName }} SLC registration to send a code{{ i.dataOpen ? ' · ' + plural(i.dataOpen, 'Data check difference') + ' to sort too' : '' }}</div>
                    <div v-else-if="!i.confirm && (i.code.chosen || i.code.coc)" class="muted small" style="margin-top: 4px">
                        <template v-if="i.code.chosen">Chosen by {{ i.code.chosenBy }} · {{ i.code.chosenAt }}</template>
                        <template v-if="i.code.coc">{{ i.code.chosen ? ' · ' : '' }}Queues a {{ cocType(i.code.final) }} CoC</template>
                    </div>
                    <!-- (user, 13 Nov 2026) the Session Term and Attendance Term the SLC tab attendance (and instalment) is recorded with -->
                    <div v-if="i.recordTerm && i.codeStage !== 'noreg'" class="term-box">
                        <div class="term-h muted small">On the SLC tab as</div>
                        <div class="term-row">
                            <label class="term-sel"><span class="sr-only">Session Term for {{ i.s.first }} {{ i.s.last }}</span>
                                <select class="input code-sel" :value="i.recordTerm.chosen.sessionTerm || ''" :disabled="store.busy || i.recordTerm.recorded" @change="setTerm(i, 'session', $event.target.value)">
                                    <option value="">{{ i.recordTerm.suggested.sessionTerm ? 'Suggested · Term ' + i.recordTerm.suggested.sessionTerm : 'Session Term' }}</option>
                                    <option v-for="n in SESSIONS" :key="n" :value="n">Term {{ n }}{{ i.recordTerm.taken[n] ? ' – on the SLC tab' : '' }}</option>
                                </select></label>
                            <label class="term-sel term-sel-wide"><span class="sr-only">Attendance Term for {{ i.s.first }} {{ i.s.last }}</span>
                                <select class="input code-sel" :value="i.recordTerm.chosen.termDeclarationId || ''" :disabled="store.busy || i.recordTerm.recorded" @change="setTerm(i, 'term', $event.target.value)">
                                    <option value="">{{ i.recordTerm.suggested.term ? 'Suggested · ' + i.recordTerm.suggested.term : 'Attendance Term' }}</option>
                                    <optgroup v-if="i.recordTerm.options.length" label="Course instance">
                                        <option v-for="o in i.recordTerm.options" :key="o.id" :value="o.id">{{ o.name }} · Term {{ o.sessionTerm }}{{ o.taken ? ' – on the SLC tab' : '' }}</option>
                                    </optgroup>
                                    <optgroup label="All terms">
                                        <option v-for="t in otherTerms(i)" :key="t.id" :value="t.id">{{ t.name }}</option>
                                    </optgroup>
                                </select></label>
                        </div>
                        <div v-if="i.recordTerm.recorded" class="ab-note ok"><a class="ab-link" :href="i.s.slcUrl" target="_blank" rel="noopener">Recorded on the SLC tab as Term {{ i.recordTerm.final.sessionTerm }} · {{ i.recordTerm.final.term }} (#{{ i.recordTerm.attendanceId }}) ↗</a></div>
                        <div v-else-if="i.recordTerm.problem" class="ab-note bad">{{ i.recordTerm.problem }}</div>
                        <div v-else class="muted small">Term {{ i.recordTerm.final.sessionTerm }} · {{ i.recordTerm.final.term }}<template v-if="i.recordTerm.chosen.sessionTerm || i.recordTerm.chosen.termDeclarationId"> · chosen by {{ i.recordTerm.by }} · {{ i.recordTerm.at }}</template><template v-else-if="Object.keys(i.recordTerm.taken).length"> · Term {{ Object.keys(i.recordTerm.taken).join(', ') }} already on the SLC tab</template></div>
                    </div>
                </td>
            </tr></tbody>
        </table></div>
    </section>

    <!-- (user, 13 Nov 2026) ticked students whose period is already on the SLC tab: send anyway, one by one -->
    <div v-if="dlg" class="mx-back" @click.self="dlg = null">
        <div class="mx-box ab-mx" role="dialog" aria-modal="true" aria-labelledby="cmx-h">
            <div class="mx-h"><h2 id="cmx-h">{{ dlg.existing.length === 1 ? '1 student may already be' : dlg.existing.length + ' students may already be' }} confirmed for this period</h2></div>
            <div class="mx-b">
                <p>Their attendance for the period was entered on the SLC tab after SIS issued this report, or SIS already has a code for it (the report’s column 28) – most likely confirmed in SIS already. If it was submitted, SIS refuses the line (“confirmation has already been submitted”); a code only saved in SIS is submitted by the file. Tick the ones to send to SIS anyway; the others are left out of the batch.</p>
                <div class="tablebox"><table class="tbl ab-tbl">
                    <thead><tr>
                        <th scope="col" style="width: 44px"><input class="pick" type="checkbox" :checked="dlg.existing.length > 0 && include.length === dlg.existing.length" :indeterminate.prop="include.length > 0 && include.length < dlg.existing.length" aria-label="Send all anyway" @change="include = $event.target.checked ? dlg.existing.map((e) => e.rowId) : []"></th>
                        <th scope="col">Student</th><th scope="col">Period</th><th scope="col">Already there</th><th scope="col">Code to send</th>
                    </tr></thead>
                    <tbody><tr v-for="e in dlg.existing" :key="e.rowId" :class="include.includes(e.rowId) ? 'is-picked' : ''">
                        <td><input v-model="include" class="pick" type="checkbox" :value="e.rowId" :aria-label="'Send ' + e.name + ' anyway'"></td>
                        <th scope="row">{{ e.name }}<StatusTag :status="e.status" /><div class="muted small" style="font-weight: 400">{{ e.sid }}</div>
                            <div :class="'ab-note ' + (include.includes(e.rowId) ? '' : 'warn')">{{ include.includes(e.rowId) ? 'Sent to SIS anyway' : 'Left out of the batch' }}</div></th>
                        <td>{{ e.sessionTerm ? 'Term ' + e.sessionTerm + ' · ' : '' }}{{ e.term || '–' }}</td>
                        <td><div v-if="e.sisCode">In SIS: <b class="mono">{{ e.sisCode }}</b> (report column 28)</div>
                            <template v-if="e.onTab && e.onTab.attendance"><a class="ab-link" :href="e.slcUrl" target="_blank" rel="noopener">SLC tab #{{ e.onTab.attendance.id }} · {{ e.onTab.attendance.code || '–' }} · {{ fmtIso(e.onTab.attendance.date) }} ↗</a><div v-if="e.onTab.attendance.by" class="muted small">{{ e.onTab.attendance.by }}</div></template>
                            <div v-if="e.onTab && e.onTab.installment" class="small"><a class="ab-link" :href="e.accountsUrl" target="_blank" rel="noopener">Instalment #{{ e.onTab.installment.id }} · {{ money(e.onTab.installment.amount) }}{{ e.onTab.installment.receiptId ? ' (paid)' : '' }} ↗</a></div></td>
                        <td><span :class="'pill ' + tone(e.code)"><span class="mono">{{ e.code }}</span> {{ meaning(e.code) }}</span></td>
                    </tr></tbody>
                </table></div>
                <p v-if="dlg.others > 0" class="muted small">{{ plural(dlg.others, 'other ticked student') }} will be confirmed as usual.</p>
            </div>
            <div class="mx-f">
                <button type="button" class="btn" @click="dlg = null">Cancel</button>
                <button ref="dlgGo" type="button" class="btn btn-primary" :disabled="store.busy || !(dlg.others + include.length)" @click="confirm(dlg.ids, true)">{{ dlg.others + include.length ? 'Confirm ' + plural(dlg.others + include.length, 'student') : 'Nothing to confirm' }}</button>
            </div>
        </div>
    </div>
</template>

<script>
import { computed, inject, nextTick, onBeforeUnmount, ref, watch } from "vue";
import { fmtIso, matchesSearch, money, plural, searchText } from "./helpers";
import Ico from "./Ico.vue";
import AttCell from "./AttCell.vue";
import StatusTag from "./StatusTag.vue";
import StepSearch from "./StepSearch.vue";

/*
 * Bulk attendance › step 4, Attendance & codes (user, 6 Nov 2026: the Attendance and Codes steps as
 * one). For each matched student: the register in their liability period (AttendanceState::liability —
 * the term, its dates and where they come from, sessions, physical / online, first and last attended),
 * the code it suggests (AttendanceCodes::suggest) or the one staff choose (AttendanceActions::setCode),
 * and Confirm, which puts it in the batch awaiting download and queues the CoC a code needs (::confirm).
 * The attendance counts (Attending, To check, No attendance, No term found) filter the list, as do the
 * code and the code's stage.
 */
const FILTERS = [["ready", "Ready"], ["noreg", "No SLC registration"], ["nocode", "Needs a code"], ["blocked", "Held by Data check"], ["confirmed", "Confirmed"], ["all", "All"]];
const KINDS = [
    { key: "ok", label: "Attending", note: "Attended recently, 50% or more", tone: "ok" },
    { key: "warn", label: "To check", note: "Low attendance or not seen for 14+ days", tone: "warn" },
    { key: "none", label: "No attendance", note: "Nothing attended in the term so far", tone: "bad" },
    { key: "noterm", label: "No term found", note: "", tone: "info" },
];
// a student's attendance in the term, as the Attendance step sorted them
const kind = (i) => (!i.liab || !i.liab.start ? "noterm" : !i.liab.a.attended ? "none" : i.liab.st === "ok" ? "ok" : "warn");

export default {
    components: { StatusTag, AttCell, StepSearch, Ico },
    emits: ["go"],
    setup() {
        const { store, post } = inject("slr");
        const att = computed(() => store.state.attendance);
        const imp = computed(() => att.value.import);
        const yearName = computed(() => (imp.value ? imp.value.year : "the year"));
        const termLabel = computed(() => (imp.value ? imp.value.termName : "the"));
        const codes = computed(() => att.value.codes || []);
        const codeOf = computed(() => Object.fromEntries(codes.value.map((c) => [c.code, c])));
        const meaning = (c) => (codeOf.value[c] ? codeOf.value[c].meaning : "");
        const cocType = (c) => (codeOf.value[c] ? codeOf.value[c].cocType : "");
        const tone = (c) => (c === "A" ? "ok" : codeOf.value[c] && codeOf.value[c].coc ? "bad" : "warn");
        const rows = computed(() => att.value.rows.filter((i) => i.s && i.code));
        const by = (k) => rows.value.filter((i) => i.codeStage === k);
        const kindCount = (k) => rows.value.filter((i) => kind(i) === k).length;
        const sent = (i) => (i.confirm ? i.confirm.code : i.code.final);
        const show = ref(by("ready").length ? "ready" : "all");
        const codeFilter = ref("all");
        const attFilter = ref("all");
        // (user, 13 Nov 2026) the period already on the SLC tab (by hand), or the SLC tab record blocked later
        const isOnTab = (i) => !!(i.slcTab && i.slcTab.attendance && !i.slcTab.recorded);
        const onTab = computed(() => rows.value.filter(isOnTab));
        const inSis = computed(() => rows.value.filter((i) => i.sisCode && !i.confirm)); // the report's column 28
        const blockedLater = computed(() => rows.value.filter((i) => i.recordBlock && i.codeStage !== "noreg"));
        const special = ref(""); // "tab" | "block": only those students
        const unsearched = computed(() => rows.value.filter((i) => (show.value === "all" || i.codeStage === show.value)
            && (codeFilter.value === "all" || sent(i) === codeFilter.value)
            && (attFilter.value === "all" || kind(i) === attFilter.value)
            && (special.value !== "tab" || isOnTab(i)) && (special.value !== "block" || (i.recordBlock && i.codeStage !== "noreg"))));
        // this step's search (user, 13 Nov 2026), on top of the filters; "Confirm selected" acts on the students shown
        const q = ref("");
        const items = computed(() => (q.value.trim() ? unsearched.value.filter((i) => matchesSearch(q.value, searchText(i))) : unsearched.value));
        const narrowed = computed(() => show.value !== "all" || codeFilter.value !== "all" || attFilter.value !== "all" || q.value.trim() !== "" || special.value !== "");
        const showAll = () => { show.value = "all"; codeFilter.value = "all"; attFilter.value = "all"; q.value = ""; special.value = ""; };
        watch(special, (v) => { if (v) show.value = "all"; });
        const codeSummary = computed(() => {
            const n = {};
            rows.value.forEach((i) => { const c = sent(i); if (c) n[c] = (n[c] || 0) + 1; });
            return codes.value.filter((c) => n[c.code]).map((c) => ({ ...c, count: n[c.code] }));
        });
        const codeMax = computed(() => Math.max(1, ...codeSummary.value.map((c) => c.count)));
        watch(codeSummary, (list) => { if (codeFilter.value !== "all" && !list.some((c) => c.code === codeFilter.value)) codeFilter.value = "all"; });
        const review = computed(() => by("ready").filter((i) => i.code.review).length);
        const cocCount = computed(() => by("confirmed").filter((i) => i.coc).length);
        const today = new Date().toISOString().slice(0, 10);

        const picked = ref([]);
        const pickable = computed(() => items.value.filter((i) => i.codeStage === "ready"));
        const allPicked = computed(() => pickable.value.length > 0 && pickable.value.every((i) => picked.value.includes(i.id)));
        const toggleAll = (on) => {
            const ids = pickable.value.map((i) => i.id);
            picked.value = on ? [...new Set([...picked.value, ...ids])] : picked.value.filter((id) => !ids.includes(id));
        };
        // a line that's no longer ready (confirmed, reopened) drops out of the selection
        watch(rows, (list) => { picked.value = picked.value.filter((id) => list.some((i) => i.id === id && i.codeStage === "ready")); });

        const setCode = (i, code) => post("slc.registration.att.code", { row_id: i.id, code: code || null });
        // (user, 13 Nov 2026) the SLC tab Session Term / Attendance Term: a Session Term lets the Attendance Term follow it
        // (the instance's term with it); an instance term lets the Session Term follow; another term keeps the Session Term
        const SESSIONS = [1, 2, 3, 4];
        const allTerms = computed(() => att.value.termDeclarations || []);
        const otherTerms = (i) => { const own = new Set(i.recordTerm.options.map((o) => o.id)); return allTerms.value.filter((t) => !own.has(t.id)); };
        const setTerm = (i, which, value) => {
            const v = value ? Number(value) : null;
            const ownTerm = which === "term" && v && i.recordTerm.options.some((o) => o.id === v);
            const body = which === "session" ? { session_term: v, term_declaration_id: null }
                : { session_term: ownTerm || !v ? null : i.recordTerm.chosen.sessionTerm, term_declaration_id: v };
            return post("slc.registration.att.term", { row_id: i.id, ...body });
        };
        // students whose period is already on the SLC tab: a dialog asks which to send anyway (409 from the server)
        const dlg = ref(null);
        const dlgGo = ref(null);
        const include = ref([]);
        const confirm = async (ids, answered = false) => {
            const data = await post("slc.registration.att.confirm", answered ? { row_ids: ids, confirmed: true, include_ids: include.value } : { row_ids: ids });
            if (data && data.conflict) {
                dlg.value = { ...data.conflict, ids };
                include.value = [];
                await nextTick();
                if (dlgGo.value) dlgGo.value.focus();
                return;
            }
            if (data) { picked.value = picked.value.filter((id) => !ids.includes(id)); dlg.value = null; include.value = []; }
        };
        const onKey = (e) => { if (e.key === "Escape" && dlg.value) dlg.value = null; };
        document.addEventListener("keydown", onKey);
        onBeforeUnmount(() => document.removeEventListener("keydown", onKey));

        return { q, unsearched, store, post, imp, yearName, termLabel, codes, meaning, cocType, tone, rows, by, kindCount, KINDS, show, codeFilter, attFilter, items, narrowed, showAll, codeSummary, codeMax, review, cocCount, today,
            picked, pickable, allPicked, toggleAll, setCode, confirm, FILTERS, fmtIso, plural, money, SESSIONS, otherTerms, setTerm, onTab, inSis, blockedLater, special, dlg, dlgGo, include };
    },
};
</script>
