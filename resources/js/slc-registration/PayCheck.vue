<template>
    <div class="pagehead">
        <div><span class="eyebrow">Step 2</span><h1>Check &amp; record payments</h1>
            <p>Each payment in the {{ up ? fmtIso(up.date) : '' }} remittance matched to the student, their SLC agreement and the instalment it pays. Tick the ones to record: they become money receipts on the students’ Accounts tabs. Payments already on an account are never recorded twice.</p></div>
        <button type="button" class="btn btn-primary" :disabled="!picked.length || store.busy" @click="ask(picked)">Record selected ({{ picked.length }}{{ picked.length ? ' · ' + money(pickedSum) : '' }})</button>
    </div>

    <div v-if="up && !up.newest" class="notice warn"><span style="flex: 1">You’re working on an earlier upload: <b>{{ up.fileName }}</b> (remittance {{ fmtIso(up.date) }}).</span><button type="button" class="btn btn-sm" :disabled="store.busy" @click="post('slc.registration.pay.open', { id: newestId })">Open the newest</button></div>

    <section class="kpis" aria-label="Payments">
        <div class="kpi t-ok"><span class="k-label">Ready</span><span class="k-val">{{ c.ready }}</span><span class="k-note">{{ money(c.sum.ready) }} · everything matches</span></div>
        <div class="kpi t-warn"><span class="k-label">Needs a look</span><span class="k-val">{{ c.look }}</span><span class="k-note">{{ money(c.sum.look) }} · recordable once checked</span></div>
        <div class="kpi t-bad"><span class="k-label">Can’t record yet</span><span class="k-val">{{ c.error }}</span><span class="k-note">No student or no agreement found</span></div>
        <div class="kpi t-info"><span class="k-label">Recorded here</span><span class="k-val">{{ c.recorded }}</span><span class="k-note">{{ money(c.sum.recorded) }}</span></div>
        <div class="kpi"><span class="k-label">Already recorded</span><span class="k-val">{{ c.done }}</span><span class="k-note">On the account before this upload · {{ c.skipped }} skipped</span></div>
    </section>

    <section v-if="issueSummary.length && (show === 'look' || show === 'all')" class="card" aria-labelledby="pay-by-issue">
        <div class="card-h"><h2 id="pay-by-issue">What needs a look</h2><span class="muted small">Choose one to see only those payments</span></div>
        <div class="card-b field-sum" role="group" aria-label="Filter by issue">
            <button type="button" class="fsum fsum-all" :aria-pressed="issue === 'all'" @click="issue = 'all'"><span class="fsum-l">All</span><span class="fsum-n num">{{ c.look }}</span><span class="fsum-bar" aria-hidden="true"><span style="width: 100%"></span></span><span class="fsum-m">payments to look at</span></button>
            <button v-for="f in issueSummary" :key="f.key" type="button" class="fsum" :aria-pressed="issue === f.key" @click="issue = issue === f.key ? 'all' : f.key">
                <span class="fsum-l">{{ ISSUE[f.key] || f.key }}</span><span class="fsum-n num">{{ f.count }}</span>
                <span class="fsum-bar" aria-hidden="true"><span :style="{ width: Math.max(6, Math.round(f.count / issueMax * 100)) + '%' }"></span></span>
                <span class="fsum-m">{{ ISSUE_NOTE[f.key] || '' }}</span>
            </button>
        </div>
    </section>

    <section v-if="errorSummary.length && (show === 'error' || show === 'all')" class="card" aria-labelledby="pay-by-error">
        <div class="card-h"><h2 id="pay-by-error">Why payments can’t be recorded yet</h2><span class="muted small">The same checks as Reports › Accounts › Payments Upload · choose one to see only those</span></div>
        <div class="card-b field-sum" role="group" aria-label="Filter by reason">
            <button type="button" class="fsum fsum-all" :aria-pressed="whyFilter === 'all'" @click="whyFilter = 'all'"><span class="fsum-l">All</span><span class="fsum-n num">{{ c.error }}</span><span class="fsum-bar" aria-hidden="true"><span style="width: 100%"></span></span><span class="fsum-m">can’t be recorded yet</span></button>
            <button v-for="f in errorSummary" :key="f.key" type="button" class="fsum" :aria-pressed="whyFilter === f.key" @click="whyFilter = whyFilter === f.key ? 'all' : f.key">
                <span class="fsum-l">{{ ERROR[f.key] || f.key }}</span><span class="fsum-n num">{{ f.count }}</span>
                <span class="fsum-bar" aria-hidden="true"><span :style="{ width: Math.max(6, Math.round(f.count / errorMax * 100)) + '%' }"></span></span>
                <span class="fsum-m">{{ ERROR_NOTE[f.key] || '' }}</span>
            </button>
        </div>
    </section>

    <div class="row" style="justify-content: space-between">
        <div class="filters" role="group" aria-label="Show payments">
            <button v-for="[k, label] in FILTERS" :key="k" type="button" class="chip" :aria-pressed="show === k" @click="show = k">{{ label }} <span class="num">{{ k === 'all' ? c.all : c[k] }}</span></button>
        </div>
        <div class="row small" style="gap: 8px">
            <label class="sr-only" for="pay-q">Search payments</label>
            <input id="pay-q" v-model="q" class="input pay-search" type="search" placeholder="Name, SSN or student ID">
        </div>
    </div>

    <div v-if="!up" class="card empty">Upload the SLC remittance file on the Overview first.</div>
    <section v-else class="card">
        <div v-if="!items.length" class="empty">{{ q ? 'No payments match “' + q + '”.' : 'No payments here.' }}</div>
        <div v-else class="tablebox"><table class="tbl pay-tbl">
            <colgroup><col style="width: 40px"><col style="width: 21%"><col style="width: 18%"><col style="width: 11%"><col style="width: 21%"><col></colgroup>
            <thead><tr>
                <th scope="col"><input class="pick" type="checkbox" :checked="allPicked" :indeterminate.prop="somePicked" :disabled="!pickable.length" aria-label="Select every payment that can be recorded in this list" @change="toggleAll($event.target.checked)"></th>
                <th scope="col">Student</th><th scope="col">SLC course · year</th><th scope="col">Payment</th><th scope="col">Matched to</th><th scope="col">Status</th>
            </tr></thead>
            <tbody>
                <template v-for="i in shown" :key="i.id">
                    <tr :class="picked.includes(i.id) ? 'is-picked' : ''">
                        <td><input v-model="picked" class="pick" type="checkbox" :value="i.id" :disabled="!canRecord(i)" :aria-label="'Select payment line ' + i.line"></td>
                        <th scope="row">
                            <template v-if="i.s">
                                <a class="plain" :href="i.s.accountsUrl" target="_blank" rel="noopener">{{ i.s.title }} {{ i.s.first }} {{ i.s.last }}</a>
                                <div class="muted small" style="font-weight: 400">{{ i.s.id }} · <span class="mono">{{ i.r.ssn }}</span></div>
                                <div v-if="i.link.method === 'linked'" class="muted small" style="font-weight: 400">Matched here by {{ i.link.by }}<template v-if="!i.receipt"> · <button type="button" class="btn-link small" :disabled="store.busy" @click="post('slc.registration.pay.unlink', { row_id: i.id })">Unmatch</button></template></div>
                            </template>
                            <template v-else>
                                {{ titleCase(i.r.forename + ' ' + i.r.surname) }}
                                <div class="muted small" style="font-weight: 400"><span class="mono">{{ i.r.ssn }}</span> · DOB {{ fmtIso(i.r.dob) }}</div>
                            </template>
                        </th>
                        <td>{{ i.r.slcCode }} · Year {{ i.r.year }}<div class="muted small">{{ i.r.courseName }}</div></td>
                        <td><b class="num">{{ money(Math.abs(i.r.amount)) }}</b><div><span v-if="i.r.amount < 0" class="pill warn">Refund</span><span v-else :class="'small ' + (i.issues.some((x) => x.key === 'term') ? 'term-diff' : 'muted')">{{ i.r.termName }} ({{ i.r.term }})</span></div>
                            <div v-if="i.r.date && up && i.r.date !== up.date" class="muted small">Remittance {{ fmtIso(i.r.date) }}</div>
                            <span v-if="i.fromReports" class="pill muted" title="Was waiting in Reports › Accounts › Payments Upload">From Reports</span></td>
                        <td class="small">
                            <template v-if="i.status === 'done'">Receipt <span class="mono">{{ i.done.invoice }}</span><div class="muted">{{ i.done.bulk ? 'Bulk upload' : 'Entered' }} by {{ i.done.by || '–' }}{{ i.done.at ? ' · ' + i.done.at : '' }}</div></template>
                            <template v-else-if="i.agreement">
                                Agreement <span class="mono">#{{ i.agreement.id }}</span> · Year {{ i.agreement.year }} · {{ i.agreement.code || '–' }}
                                <div class="muted">Total {{ money(i.agreement.total) }}<template v-if="i.agreement.date"> · {{ fmtIso(i.agreement.date) }}</template></div>
                                <div v-if="i.inst">Instalment {{ fmtIso(i.inst.date) }} · {{ money(i.inst.amount) }}<div class="muted">{{ i.inst.decl || 'No term declaration' }}{{ i.inst.term ? ' (' + i.inst.term + ')' : '' }}</div></div>
                                <div v-else-if="i.r.amount > 0 && i.status !== 'recorded'" class="muted">No instalment linked</div>
                                <div v-if="i.choice" class="muted">Chosen by {{ i.choice.by }}<template v-if="!i.receipt"> · <button type="button" class="btn-link small" :disabled="store.busy" @click="post('slc.registration.pay.agreement', { row_id: i.id, agreement_id: null })">Use the matched one</button></template></div>
                            </template>
                            <span v-else class="muted">–</span>
                        </td>
                        <td>
                            <span :class="'pill ' + PAY_STATUS[i.status][0]">{{ PAY_STATUS[i.status][1] }}</span>
                            <ul v-if="i.status === 'look' && i.issues.length" class="pay-issues small"><li v-for="x in i.issues" :key="x.key">{{ x.text }}</li></ul>
                            <div v-if="i.status === 'error'" class="small" style="color: var(--bad); margin-top: 4px">{{ i.error }}</div>
                            <div v-if="i.status === 'error' && i.holder" class="small" style="margin-top: 4px"><a class="plain" :href="i.holder.url" target="_blank" rel="noopener">{{ i.holder.id }} {{ i.holder.name }} ↗</a> · DOB {{ fmtIso(i.holder.dob) }}</div>
                            <div v-if="confirming === i.id" class="confirmrow" style="margin-top: 8px">
                                <span style="flex: 1 1 200px">Confirm <b>{{ i.holder.id }} {{ i.holder.name }}</b> is the student? Their date of birth stays as it is; the difference is kept on the payment.</span>
                                <button type="button" class="btn btn-primary btn-sm" :disabled="store.busy" @click="confirmHolder(i)">Confirm</button>
                                <button type="button" class="btn btn-sm" @click="confirming = null">Cancel</button>
                            </div>
                            <div v-if="i.status === 'recorded'" class="muted small" style="margin-top: 4px">Receipt <span class="mono">{{ i.receipt.invoice }}</span> · {{ i.receipt.by }} {{ i.receipt.at }}{{ i.receipt.forced ? ' · forced entry' : '' }}<span v-if="i.receipt.deleted" class="pill bad" style="margin-left: 4px">Receipt since deleted</span></div>
                            <div v-if="i.status === 'skipped'" class="muted small" style="margin-top: 4px">{{ i.skip.reason ? '“' + i.skip.reason + '” · ' : '' }}{{ i.skip.by }} {{ i.skip.at }}</div>
                            <div class="code-row" style="margin-top: 6px">
                                <template v-if="['ready', 'look', 'error'].includes(i.status) && i.s && i.choices.length && (i.status !== 'ready')">
                                    <label class="sr-only" :for="'agr-' + i.id">Agreement for line {{ i.line }}</label>
                                    <select :id="'agr-' + i.id" class="input code-sel" :value="i.choice ? i.agreement.id : ''" :disabled="store.busy" @change="choose(i, $event.target.value)">
                                        <option value="">{{ i.agreement && !i.choice ? 'Matched · #' + i.agreement.id : 'Choose agreement' }}</option>
                                        <option v-for="a in i.choices" :key="a.id" :value="a.id">#{{ a.id }} · Year {{ a.year }} · {{ a.code || '–' }} · {{ money(a.total) }}</option>
                                    </select>
                                </template>
                                <button v-if="canRecord(i)" type="button" class="btn btn-sm btn-primary" :disabled="store.busy" @click="ask([i.id])">Record</button>
                                <button v-if="!i.s && i.holder && i.status === 'error'" type="button" class="btn btn-sm" @click="confirming = confirming === i.id ? null : i.id">This is the student</button>
                                <button v-if="!i.s && !i.holder && i.status === 'error'" type="button" class="btn btn-sm" @click="finding = finding === i.id ? null : i.id">{{ finding === i.id ? 'Close' : 'Find student' }}</button>
                                <a v-if="i.s && i.status === 'error' && !i.choices.length" class="btn btn-sm" :href="i.s.accountsUrl" target="_blank" rel="noopener">Accounts tab ↗</a>
                                <button v-if="['ready', 'look', 'error'].includes(i.status)" type="button" class="btn-link small" @click="skipping = skipping === i.id ? null : i.id; reason = ''">Skip</button>
                                <button v-if="i.status === 'skipped'" type="button" class="btn-link small" :disabled="store.busy" @click="post('slc.registration.pay.unskip', { row_id: i.id })">Reopen</button>
                            </div>
                            <div v-if="skipping === i.id" class="confirmrow" style="margin-top: 8px">
                                <label class="sr-only" :for="'skip-' + i.id">Reason</label>
                                <input :id="'skip-' + i.id" v-model="reason" class="input" style="flex: 1 1 160px; min-height: 34px" type="text" maxlength="190" placeholder="Reason (optional)">
                                <button type="button" class="btn btn-sm" :disabled="store.busy" @click="skip(i)">Skip payment</button>
                            </div>
                        </td>
                    </tr>
                    <tr v-if="finding === i.id" class="att-expand"><td colspan="6"><PayFind :i="i" @done="finding = null" /></td></tr>
                </template>
            </tbody>
        </table></div>
        <div v-if="items.length > limit" class="card-b" style="text-align: center"><button type="button" class="btn" @click="limit += 150">Show {{ Math.min(150, items.length - limit) }} more of {{ items.length - limit }}</button></div>
    </section>

    <div v-if="confirmIds" class="mx-back" @click.self="confirmIds = null">
        <div class="mx-box" role="dialog" aria-modal="true" aria-labelledby="pay-mx-h">
            <div class="mx-h"><h2 id="pay-mx-h">Record {{ plural(confirmItems.length, 'payment') }}?</h2></div>
            <div class="mx-b">
                <p>{{ confirmItems.length === 1 ? 'It becomes a money receipt' : 'They become money receipts' }} on the student’s Accounts tab: Bank, dated {{ confirmDates.length === 1 ? fmtIso(confirmDates[0]) : 'their remittance dates' }}, {{ money(confirmSum) }} in all.</p>
                <p v-if="confirmFromReports" class="muted">{{ plural(confirmFromReports, 'payment') }} came from Reports › Accounts › Payments Upload and will leave its waiting list.</p>
                <p v-if="confirmLook" class="muted">{{ plural(confirmLook, 'payment') }} needed a look ({{ confirmLook === 1 ? 'it is' : 'they are' }} recorded as matched). {{ confirmForced ? plural(confirmForced, 'payment') + ' use an agreement you chose and are saved as forced entries.' : '' }}</p>
            </div>
            <div class="mx-f">
                <button type="button" class="btn" @click="confirmIds = null">Cancel</button>
                <button ref="mxGo" type="button" class="btn btn-primary" :disabled="store.busy" @click="record">Record {{ plural(confirmItems.length, 'payment') }}</button>
            </div>
        </div>
    </div>
</template>

<script>
import { computed, inject, nextTick, onBeforeUnmount, ref, watch } from "vue";
import { PAY_STATUS, fmtIso, money, plural, titleCase } from "./helpers";
import PayFind from "./PayFind.vue";

/*
 * Bulk payment upload › Check & record (user, 2 Nov 2026): every payment line as PaymentState
 * matched it; find the student, choose the agreement or skip; Record (one or the ticked ones, after
 * a confirm) → PaymentActions::record. Fixed columns, no horizontal scrollbar on a desktop.
 */
const FILTERS = [["ready", "Ready"], ["look", "Needs a look"], ["error", "Can’t record yet"], ["recorded", "Recorded here"], ["done", "Already recorded"], ["skipped", "Skipped"], ["all", "All"]];
const ISSUE = { term: "Term differs", paid: "Instalments already paid", amount: "Instalment amount", dob: "Date of birth", code: "Agreement course code", course: "Course (forced entry)", multi: "Several agreements", inst: "No instalments (forced)" };
const ISSUE_NOTE = { term: "SLC’s term isn’t the instalment’s", paid: "No unpaid instalment on the agreement", amount: "SLC paid a different amount", dob: "Staff confirmed the student", code: "Differs from SLC’s code", course: "Not the active course", multi: "For the same year", inst: "Linked to the agreement only" };
const ERROR = { nostudent: "Student not found", dob: "Date of birth differs", course: "Course does not match", agreement: "No agreement", inst: "Instalment not found" };
const ERROR_NOTE = { nostudent: "No student has the SSN", dob: "SSN matches, date of birth doesn’t", course: "Not the student’s active course", agreement: "For the year, on the active course", inst: "The agreement has no instalments" };

export default {
    components: { PayFind },
    props: { initial: { type: String, default: "ready" } },
    emits: ["go"],
    setup(props) {
        const { store, post } = inject("slr");
        const pay = computed(() => store.state.payments);
        const up = computed(() => pay.value.upload);
        const c = computed(() => pay.value.counts);
        const rows = computed(() => pay.value.rows);
        const newestId = computed(() => (pay.value.uploads[0] || {}).id);
        const pick = (want) => (want && want !== "ready" ? want : c.value.ready ? "ready" : c.value.look ? "look" : c.value.error ? "error" : "all");
        const show = ref(pick(props.initial));
        watch(() => props.initial, (v) => { show.value = pick(v); });
        const q = ref("");
        const issue = ref("all");
        const whyFilter = ref("all"); // Why payments can't be recorded yet (not the Skip reason below)
        const limit = ref(150);
        const norm = (s) => (s || "").toLowerCase();
        const items = computed(() => {
            let list = show.value === "all" ? rows.value : rows.value.filter((i) => i.status === show.value);
            if (issue.value !== "all" && (show.value === "look" || show.value === "all")) list = list.filter((i) => i.status === "look" && i.issues.some((x) => x.key === issue.value));
            if (whyFilter.value !== "all" && (show.value === "error" || show.value === "all")) list = list.filter((i) => i.status === "error" && i.errorKey === whyFilter.value);
            const t = norm(q.value.trim());
            if (t) list = list.filter((i) => [i.r.ssn, i.r.forename + " " + i.r.surname, i.s && i.s.first + " " + i.s.last, i.s && i.s.id].some((v) => norm(v).includes(t)));
            return list;
        });
        watch([show, q, issue, whyFilter], () => { limit.value = 150; });
        const shown = computed(() => items.value.slice(0, limit.value));
        const issueSummary = computed(() => {
            const n = {};
            rows.value.forEach((i) => { if (i.status === "look") i.issues.forEach((x) => { n[x.key] = (n[x.key] || 0) + 1; }); });
            return Object.entries(n).map(([key, count]) => ({ key, count })).sort((a, b) => b.count - a.count);
        });
        const issueMax = computed(() => Math.max(1, ...issueSummary.value.map((f) => f.count)));
        const errorSummary = computed(() => {
            const n = {};
            rows.value.forEach((i) => { if (i.status === "error" && i.errorKey) n[i.errorKey] = (n[i.errorKey] || 0) + 1; });
            return Object.entries(n).map(([key, count]) => ({ key, count })).sort((a, b) => b.count - a.count);
        });
        const errorMax = computed(() => Math.max(1, ...errorSummary.value.map((f) => f.count)));
        const confirming = ref(null);
        const confirmHolder = async (i) => { const data = await post("slc.registration.pay.link", { row_id: i.id, student_id: i.holder.sid }); if (data) confirming.value = null; };

        const canRecord = (i) => (i.status === "ready" || i.status === "look") && !!i.agreement;
        const picked = ref([]);
        const pickable = computed(() => items.value.filter(canRecord));
        const allPicked = computed(() => pickable.value.length > 0 && pickable.value.every((i) => picked.value.includes(i.id)));
        const somePicked = computed(() => picked.value.length > 0 && !allPicked.value);
        const toggleAll = (on) => {
            const ids = pickable.value.map((i) => i.id);
            picked.value = on ? [...new Set([...picked.value, ...ids])] : picked.value.filter((id) => !ids.includes(id));
        };
        const byId = computed(() => Object.fromEntries(rows.value.map((i) => [i.id, i])));
        const pickedSum = computed(() => picked.value.reduce((a, id) => a + (byId.value[id] ? byId.value[id].r.amount : 0), 0));
        watch(rows, (list) => { picked.value = picked.value.filter((id) => list.some((i) => i.id === id && canRecord(i))); });

        // Record: always asked first (money receipts)
        const confirmIds = ref(null);
        const mxGo = ref(null);
        const confirmItems = computed(() => (confirmIds.value || []).map((id) => byId.value[id]).filter((i) => i && canRecord(i)));
        const confirmSum = computed(() => confirmItems.value.reduce((a, i) => a + i.r.amount, 0));
        const confirmLook = computed(() => confirmItems.value.filter((i) => i.status === "look").length);
        const confirmForced = computed(() => confirmItems.value.filter((i) => i.choice).length);
        const confirmDates = computed(() => [...new Set(confirmItems.value.map((i) => i.r.date || (up.value && up.value.date)))]);
        const confirmFromReports = computed(() => confirmItems.value.filter((i) => i.fromReports).length);
        async function ask(ids) { confirmIds.value = [...ids]; await nextTick(); if (mxGo.value) mxGo.value.focus(); }
        async function record() {
            const ids = confirmItems.value.map((i) => i.id);
            const data = await post("slc.registration.pay.record", { row_ids: ids });
            confirmIds.value = null;
            if (data) picked.value = picked.value.filter((id) => !ids.includes(id));
        }
        const onKey = (e) => { if (e.key === "Escape" && confirmIds.value) confirmIds.value = null; };
        document.addEventListener("keydown", onKey);
        onBeforeUnmount(() => document.removeEventListener("keydown", onKey));

        const choose = (i, v) => post("slc.registration.pay.agreement", { row_id: i.id, agreement_id: v ? Number(v) : null });
        const finding = ref(null);
        const skipping = ref(null);
        const reason = ref("");
        const skip = async (i) => { const data = await post("slc.registration.pay.skip", { row_ids: [i.id], reason: reason.value }); if (data) { skipping.value = null; reason.value = ""; } };

        return { store, post, up, newestId, c, show, q, issue, whyFilter, limit, items, shown, issueSummary, issueMax, errorSummary, errorMax, confirming, confirmHolder, ERROR, ERROR_NOTE, canRecord, picked, pickable, allPicked, somePicked, toggleAll, pickedSum,
            confirmIds, mxGo, confirmItems, confirmSum, confirmLook, confirmForced, confirmDates, confirmFromReports, ask, record, choose, finding, skipping, reason, skip,
            FILTERS, ISSUE, ISSUE_NOTE, PAY_STATUS, fmtIso, money, plural, titleCase };
    },
};
</script>
