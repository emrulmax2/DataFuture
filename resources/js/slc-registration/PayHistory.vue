<template>
    <section class="card" aria-labelledby="pay-hist-h">
        <div class="card-h">
            <div><h2 id="pay-hist-h">All payment uploads, by remittance date</h2>
                <div class="muted small">Every remittance recorded in bulk, here or in Reports › Accounts › Payments Upload. Reports kept neither the file nor the upload, so its uploads are rebuilt from the money receipts it made.</div></div>
            <div class="filters" role="group" aria-label="Year">
                <button v-for="y in years" :key="y" type="button" class="chip" :aria-pressed="year === y" @click="year = y">{{ y === 'all' ? 'All years' : y }} <span class="num">{{ y === 'all' ? dates.length : dates.filter((d) => d.date.startsWith(y)).length }}</span></button>
            </div>
        </div>
        <div v-if="loading" class="empty">Loading…</div>
        <div v-else-if="!dates.length" class="empty">No payments recorded in bulk yet.</div>
        <div v-else class="tablebox"><table class="tbl pay-hist">
            <colgroup><col style="width: 13%"><col style="width: 9%"><col style="width: 13%"><col style="width: 20%"><col style="width: 17%"><col><col style="width: 90px"></colgroup>
            <thead><tr><th scope="col">Remittance date</th><th scope="col">Payments</th><th scope="col">Total</th><th scope="col">Entered by</th><th scope="col">Entered</th><th scope="col">Checks</th><th scope="col"></th></tr></thead>
            <tbody><tr v-for="d in shownDates" :key="d.date" :class="openDate === d.date ? 'is-picked' : ''">
                <th scope="row">{{ fmtIso(d.date) }}</th>
                <td class="num">{{ d.n }}</td>
                <td class="num">{{ money(d.total) }}</td>
                <td class="small">{{ d.by.join(', ') || '–' }}</td>
                <td class="small muted">{{ d.first === d.last ? d.first : d.first + ' – ' + d.last }}</td>
                <td><div class="acts">
                    <span v-if="d.doubles" class="pill bad" title="The same student, amount and type more than once on this date">{{ plural(d.doubles, 'possible double') }}</span>
                    <span v-if="d.waiting" class="pill warn">{{ d.waiting }} waiting in Reports</span>
                    <span v-if="d.refunds" class="pill muted">{{ plural(d.refunds, 'refund') }}</span>
                    <span v-if="d.forced" class="pill muted">{{ d.forced }} forced</span>
                    <span v-if="d.uploadsHere" class="pill info">Uploaded here</span>
                    <span v-if="!d.doubles && !d.waiting && !d.refunds && !d.forced && !d.uploadsHere" class="muted small">–</span>
                </div></td>
                <td><button type="button" class="btn btn-sm" @click="open(d.date)">{{ openDate === d.date ? 'Close' : 'View' }}</button></td>
            </tr></tbody>
        </table></div>
        <div v-if="filteredDates.length > dateLimit" class="card-b" style="text-align: center"><button type="button" class="btn" @click="dateLimit += 50">Show {{ Math.min(50, filteredDates.length - dateLimit) }} more of {{ filteredDates.length - dateLimit }}</button></div>
    </section>

    <section v-if="openDate" ref="panel" class="card" aria-labelledby="pay-day-h">
        <div class="card-h">
            <div><h2 id="pay-day-h">Remittance {{ fmtIso(openDate) }}</h2><div class="muted small">{{ day ? plural(day.receipts.length, 'money receipt') + ' recorded in bulk · ' + money(dayTotal) : 'Loading…' }}</div></div>
            <div class="row small" style="gap: 8px">
                <label class="sr-only" for="pay-day-q">Search receipts</label>
                <input id="pay-day-q" v-model="q" class="input pay-search" type="search" placeholder="Name, SSN, student ID or invoice">
                <button type="button" class="btn btn-sm" @click="openDate = null">Close</button>
            </div>
        </div>
        <template v-if="day">
            <div v-if="day.waiting.length" class="card-b" style="padding-bottom: 0">
                <div class="notice warn"><Ico name="info" /><span style="flex: 1">{{ plural(day.waiting.length, 'payment') }} of this remittance {{ day.waiting.length === 1 ? 'is' : 'are' }} still waiting in Reports › Accounts › Payments Upload: {{ day.waiting.map((w) => w.name + ' (' + w.ssn + ', ' + money(w.amount) + ') – ' + w.error).join('; ') }}</span><a class="btn btn-sm" :href="legacyUrl" target="_blank" rel="noopener">Open ↗</a></div>
            </div>
            <div class="card-b" style="padding-bottom: 0">
                <div class="filters" role="group" aria-label="Show receipts">
                    <button v-for="[k, label] in DAY_FILTERS" :key="k" type="button" class="chip" :aria-pressed="dayShow === k" @click="dayShow = k">{{ label }} <span class="num">{{ dayCount(k) }}</span></button>
                </div>
            </div>
            <div v-if="!dayItems.length" class="empty">No receipts here.</div>
            <div v-else class="tablebox"><table class="tbl pay-hist">
                <colgroup><col style="width: 22%"><col style="width: 12%"><col style="width: 10%"><col style="width: 15%"><col style="width: 14%"><col style="width: 15%"><col></colgroup>
                <thead><tr><th scope="col">Student</th><th scope="col">Invoice</th><th scope="col">Amount</th><th scope="col">Agreement</th><th scope="col">Instalment</th><th scope="col">Entered</th><th scope="col">Checks</th></tr></thead>
                <tbody><tr v-for="m in dayShown" :key="m.id">
                    <th scope="row"><template v-if="m.s"><a class="plain" :href="m.s.accountsUrl" target="_blank" rel="noopener">{{ m.s.name }}</a><div class="muted small" style="font-weight: 400">{{ m.s.id }} · <span class="mono">{{ m.s.ssn || '–' }}</span></div></template><span v-else class="muted">Student not found</span></th>
                    <td class="mono">{{ m.invoice }}</td>
                    <td class="num"><b>{{ money(m.amount) }}</b><div v-if="m.refund"><span class="pill warn">Refund</span></div></td>
                    <td class="small">{{ m.agreement ? '#' + m.agreement.id + ' · Year ' + (m.agreement.year ?? '–') + ' · ' + (m.agreement.code || '–') : '–' }}</td>
                    <td class="small">{{ m.inst ? fmtIso(m.inst.date) + ' · ' + money(m.inst.amount) : '–' }}</td>
                    <td class="small muted">{{ m.by }}<div>{{ m.at }}</div></td>
                    <td><div class="acts">
                        <span v-if="m.double" class="pill bad" title="The same student, amount and type more than once on this date">Recorded {{ m.double }}×</span>
                        <span v-if="m.inst && Math.abs(m.inst.amount - m.amount) >= 0.005" class="pill warn">Instalment amount differs</span>
                        <span v-if="!m.inst && !m.refund" class="pill muted">No instalment</span>
                        <span v-if="m.forced" class="pill muted">Forced</span>
                        <span :class="'pill ' + (m.source === 'here' ? 'info' : 'muted')">{{ m.source === 'here' ? 'This tab' : 'Reports' }}</span>
                    </div></td>
                </tr></tbody>
            </table></div>
            <div v-if="dayItems.length > dayLimit" class="card-b" style="text-align: center"><button type="button" class="btn" @click="dayLimit += 150">Show {{ Math.min(150, dayItems.length - dayLimit) }} more of {{ dayItems.length - dayLimit }}</button></div>
        </template>
    </section>
</template>

<script>
import { computed, inject, nextTick, onMounted, ref, watch } from "vue";
import { fmtIso, money, plural } from "./helpers";
import Ico from "./Ico.vue";

/*
 * Bulk payment upload › Uploads › "All payment uploads" (user, 2 Nov 2026: show old data too): every
 * remittance recorded in bulk — here or in Reports › Accounts › Payments Upload — by remittance date
 * (PaymentState::history), and View: that date's receipts (::historyDate). Read only.
 */
const DAY_FILTERS = [["all", "All"], ["double", "Possible doubles"], ["noinst", "No instalment"], ["amount", "Instalment amount differs"], ["forced", "Forced"], ["refund", "Refunds"]];

export default {
    components: { Ico },
    props: { legacyUrl: { type: String, default: "" } },
    setup() {
        const { get } = inject("slr");
        const dates = ref([]);
        const loading = ref(true);
        onMounted(async () => { const data = await get("slc.registration.pay.history"); dates.value = (data && data.dates) || []; loading.value = false; });
        const years = computed(() => ["all", ...new Set(dates.value.map((d) => d.date.slice(0, 4)))]);
        const year = ref("all");
        const dateLimit = ref(50);
        const filteredDates = computed(() => (year.value === "all" ? dates.value : dates.value.filter((d) => d.date.startsWith(year.value))));
        const shownDates = computed(() => filteredDates.value.slice(0, dateLimit.value));
        watch(year, () => { dateLimit.value = 50; });

        const openDate = ref(null);
        const day = ref(null);
        const panel = ref(null);
        const q = ref("");
        const dayShow = ref("all");
        const dayLimit = ref(150);
        async function open(date) {
            if (openDate.value === date) { openDate.value = null; return; }
            openDate.value = date;
            day.value = null;
            q.value = "";
            dayShow.value = "all";
            dayLimit.value = 150;
            await nextTick();
            if (panel.value) panel.value.scrollIntoView({ behavior: "smooth", block: "start" });
            const data = await get("slc.registration.pay.history.date", { date });
            if (openDate.value === date) day.value = data;
        }
        const is = {
            all: () => true, double: (m) => m.double > 0, noinst: (m) => !m.inst && !m.refund, forced: (m) => m.forced, refund: (m) => m.refund,
            amount: (m) => m.inst && Math.abs(m.inst.amount - m.amount) >= 0.005,
        };
        const dayCount = (k) => (day.value ? day.value.receipts.filter(is[k]).length : 0);
        const norm = (s) => (s || "").toString().toLowerCase();
        const dayItems = computed(() => {
            if (!day.value) return [];
            const t = norm(q.value.trim());
            return day.value.receipts.filter(is[dayShow.value]).filter((m) => !t || [m.invoice, m.s && m.s.name, m.s && m.s.id, m.s && m.s.ssn].some((v) => norm(v).includes(t)));
        });
        watch([q, dayShow], () => { dayLimit.value = 150; });
        const dayShown = computed(() => dayItems.value.slice(0, dayLimit.value));
        const dayTotal = computed(() => (day.value ? day.value.receipts.reduce((a, m) => a + (m.refund ? -m.amount : m.amount), 0) : 0));

        return { dates, loading, years, year, dateLimit, filteredDates, shownDates, openDate, day, panel, q, dayShow, dayLimit, open, dayCount, dayItems, dayShown, dayTotal, DAY_FILTERS, fmtIso, money, plural };
    },
};
</script>
