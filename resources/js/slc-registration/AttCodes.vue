<template>
    <div class="pagehead">
        <div><span class="eyebrow">Step 4</span><h1>Attendance &amp; codes</h1>
            <p>The register for each matched student in the term SLC asks about ({{ termLabel }}), from the term’s start to today, and the code it suggests from their status, the register and the Data check. The term’s dates come from the student’s {{ yearName }} course instance, or from their class plans when the instance has none. Change any code, then confirm: confirmed codes go into the batch awaiting download (step 6), and a code that needs a CoC queues one on the student’s SLC tab (step 5). A code can only be confirmed once the student’s SLC registration for {{ yearName }} is recorded.</p></div>
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
        <div class="card-h"><h2 id="att-by-att">Attendance in the {{ termLabel }} term</h2><span class="muted small">Register from the term’s start to today, {{ fmtIso(today) }} · choose one to see only those students</span></div>
        <div class="card-b field-sum" role="group" aria-label="Filter by attendance">
            <button type="button" class="fsum fsum-all" :aria-pressed="attFilter === 'all'" @click="attFilter = 'all'"><span class="fsum-l">All students</span><span class="fsum-n num">{{ rows.length }}</span><span class="fsum-bar" aria-hidden="true"><span style="width: 100%"></span></span><span class="fsum-m">matched students</span></button>
            <button v-for="k in KINDS" :key="k.key" type="button" :class="'fsum t-' + k.tone" :aria-pressed="attFilter === k.key" @click="attFilter = attFilter === k.key ? 'all' : k.key">
                <span class="fsum-l">{{ k.label }}</span><span class="fsum-n num">{{ kindCount(k.key) }}</span>
                <span class="fsum-bar" aria-hidden="true"><span :style="{ width: (kindCount(k.key) ? Math.max(6, Math.round(kindCount(k.key) / rows.length * 100)) : 0) + '%' }"></span></span>
                <span class="fsum-m">{{ k.key === 'noterm' ? 'No ' + termLabel + ' term on the course' : k.note }}</span>
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
                        <div class="small" style="margin-top: 4px"><b>{{ i.liab.termName }}</b> {{ fmtIso(i.liab.start) }} – {{ fmtIso(i.liab.end) }} <span class="muted">· first attended {{ i.liab.first ? fmtIso(i.liab.first) : '–' }}</span></div>
                        <div class="muted small">{{ i.liab.decl }} · {{ i.liab.from === 'instance' ? 'course instance' : 'class plans' }}{{ i.liab.started ? '' : ' · not started yet' }}</div>
                    </template>
                    <span v-else class="pill bad">No {{ i.liab ? i.liab.termName : termLabel }} term found</span>
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
                                <option v-for="c in codes" :key="c.code" :value="c.code">{{ c.code }} – {{ c.meaning }}</option>
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
                    <div v-if="i.codeStage === 'noreg'" class="muted small" style="margin-top: 4px">Record the {{ yearName }} SLC registration to send a code{{ i.dataOpen ? ' · ' + plural(i.dataOpen, 'Data check difference') + ' to sort too' : '' }}</div>
                    <div v-else-if="!i.confirm && (i.code.chosen || i.code.coc)" class="muted small" style="margin-top: 4px">
                        <template v-if="i.code.chosen">Chosen by {{ i.code.chosenBy }} · {{ i.code.chosenAt }}</template>
                        <template v-if="i.code.coc">{{ i.code.chosen ? ' · ' : '' }}Queues a {{ cocType(i.code.final) }} CoC</template>
                    </div>
                </td>
            </tr></tbody>
        </table></div>
    </section>
</template>

<script>
import { computed, inject, ref, watch } from "vue";
import { fmtIso, plural } from "./helpers";
import AttCell from "./AttCell.vue";
import StatusTag from "./StatusTag.vue";

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
    components: { StatusTag, AttCell },
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
        const items = computed(() => rows.value.filter((i) => (show.value === "all" || i.codeStage === show.value)
            && (codeFilter.value === "all" || sent(i) === codeFilter.value)
            && (attFilter.value === "all" || kind(i) === attFilter.value)));
        const narrowed = computed(() => show.value !== "all" || codeFilter.value !== "all" || attFilter.value !== "all");
        const showAll = () => { show.value = "all"; codeFilter.value = "all"; attFilter.value = "all"; };
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
        const confirm = async (ids) => { const data = await post("slc.registration.att.confirm", { row_ids: ids }); if (data) picked.value = picked.value.filter((id) => !ids.includes(id)); };

        return { store, post, imp, yearName, termLabel, codes, meaning, cocType, tone, rows, by, kindCount, KINDS, show, codeFilter, attFilter, items, narrowed, showAll, codeSummary, codeMax, review, cocCount, today,
            picked, pickable, allPicked, toggleAll, setCode, confirm, FILTERS, fmtIso, plural };
    },
};
</script>
