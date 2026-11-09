<template>
    <div class="pagehead">
        <div><span class="eyebrow">Step 5</span><h1>CoCs queued by attendance codes</h1>
            <p>A confirmed code that needs a CoC ({{ cocCodes }} under the attendance code settings) queues one on the student’s SLC tab, linked to their {{ yearName }} SLC registration, for Bulk CoC to pick up. Until Bulk CoC is live, submit each one in SIS and mark it done here.</p></div>
    </div>

    <section class="kpis" aria-label="CoCs">
        <div class="kpi t-bad"><span class="k-label">To submit</span><span class="k-val">{{ open.length }}</span><span class="k-note">Queued, not yet done in SIS</span></div>
        <div class="kpi t-ok"><span class="k-label">Done in SIS</span><span class="k-val">{{ done.length }}</span><span class="k-note">Marked actioned</span></div>
        <div v-for="t in byType" :key="t.type" class="kpi t-info"><span class="k-label">{{ t.type }}</span><span class="k-val">{{ t.count }}</span><span class="k-note">Code {{ t.codes.join(', ') }}</span></div>
    </section>

    <div class="row">
        <div class="filters" role="group" aria-label="Show CoCs">
            <button type="button" class="chip" :aria-pressed="show === 'open'" @click="show = 'open'">To submit <span class="num">{{ open.length }}</span></button>
            <button type="button" class="chip" :aria-pressed="show === 'done'" @click="show = 'done'">Done <span class="num">{{ done.length }}</span></button>
            <button type="button" class="chip" :aria-pressed="show === 'all'" @click="show = 'all'">All <span class="num">{{ all.length }}</span></button>
        </div>
    </div>

    <div v-if="!imp" class="card empty">Upload the SIS Attendance Confirmation Report on the Overview first.</div>
    <section v-else class="card">
        <div v-if="!items.length" class="empty">{{ all.length ? 'No CoCs here.' : 'No CoCs queued yet. Confirming a code that needs one (step 4) queues it here.' }}</div>
        <div v-else class="tablebox"><table class="tbl" style="min-width: 1080px">
            <thead><tr><th scope="col">Student</th><th scope="col">Code</th><th scope="col">CoC type</th><th scope="col">Liability period</th><th scope="col">Confirmed</th><th scope="col">State</th><th scope="col"></th></tr></thead>
            <tbody><tr v-for="i in items" :key="i.id">
                <th scope="row"><a class="plain" :href="i.s.url" target="_blank" rel="noopener">{{ i.s.title }} {{ i.s.first }} {{ i.s.last }}</a><StatusTag :status="i.s.status" />
                    <div class="muted small" style="font-weight: 400">{{ i.s.id }} · <span class="mono">{{ i.r.ssn }}</span></div></th>
                <td><span class="pill bad"><span class="mono">{{ i.confirm.code }}</span></span><div class="muted small" style="margin-top: 3px">{{ meaning(i.confirm.code) }}</div></td>
                <td>{{ i.coc.type }}</td>
                <td class="small"><template v-if="i.liab && i.liab.start">{{ i.liab.termName }} {{ fmtIso(i.liab.start) }} – {{ fmtIso(i.liab.end) }}<div class="muted">{{ i.liab.a.attended }} of {{ i.liab.a.held }} attended · last {{ i.liab.last ? fmtIso(i.liab.last) : 'never' }}</div></template><span v-else class="muted">No term found</span></td>
                <td class="muted small">{{ i.confirm.by }} · {{ i.confirm.at }}<div>Batch <span class="mono">{{ i.confirm.batchId }}</span></div></td>
                <td><span :class="'pill ' + (i.coc.actioned ? 'ok' : 'warn')">{{ i.coc.actioned ? '✓ Done in SIS' : 'Queued' }}</span></td>
                <td><div class="acts">
                    <a class="btn btn-sm" :href="i.s.slcUrl" target="_blank" rel="noopener">SLC tab ↗</a>
                    <button v-if="!i.coc.actioned" type="button" class="btn btn-sm btn-primary" :disabled="store.busy" @click="post('slc.registration.att.coc', { row_id: i.id, done: true })">Mark done in SIS</button>
                    <button v-else type="button" class="btn-link small" :disabled="store.busy" @click="post('slc.registration.att.coc', { row_id: i.id, done: false })">Reopen</button>
                </div></td>
            </tr></tbody>
        </table></div>
    </section>
</template>

<script>
import { computed, inject, ref } from "vue";
import { fmtIso } from "./helpers";
import StatusTag from "./StatusTag.vue";

/*
 * Bulk attendance › step 5 (step 6 until 6 Nov 2026; user, 2 Nov 2026): the CoCs that confirmed codes queued on the students'
 * SLC tab (AttendanceActions::confirm), and "Mark done in SIS" (slc_cocs.actioned, ::cocDone).
 */
export default {
    components: { StatusTag },
    setup() {
        const { store, post } = inject("slr");
        const att = computed(() => store.state.attendance);
        const imp = computed(() => att.value.import);
        const yearName = computed(() => (imp.value ? imp.value.year : "the year’s"));
        const codes = computed(() => att.value.codes || []);
        const cocCodes = computed(() => codes.value.filter((c) => c.coc).map((c) => c.code).join(", "));
        const meaning = (c) => (codes.value.find((x) => x.code === c) || {}).meaning || "";
        const all = computed(() => att.value.rows.filter((i) => i.s && i.confirm && i.coc));
        const open = computed(() => all.value.filter((i) => !i.coc.actioned));
        const done = computed(() => all.value.filter((i) => i.coc.actioned));
        const show = ref("open");
        const items = computed(() => (show.value === "open" ? open.value : show.value === "done" ? done.value : all.value));
        const byType = computed(() => {
            const t = {};
            all.value.forEach((i) => { const x = (t[i.coc.type] = t[i.coc.type] || { type: i.coc.type, count: 0, codes: [] }); x.count++; if (!x.codes.includes(i.confirm.code)) x.codes.push(i.confirm.code); });
            return Object.values(t);
        });

        return { store, post, imp, yearName, cocCodes, meaning, all, open, done, show, items, byType, fmtIso };
    },
};
</script>
