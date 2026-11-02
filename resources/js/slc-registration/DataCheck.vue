<template>
    <div class="pagehead">
        <div><span class="eyebrow">Stage 2</span><h1>Data check by {{ groupBy === 'issue' ? 'issue' : 'student' }}</h1>
            <p>SLC data against datafuture for {{ plural(linkedCount, 'linked student') }}: title, names, gender, DOB, course code and name, year, start date, address, postcode, SSN and TFL (should equal the course fee). Decide what happens to each error.</p></div>
        <button type="button" class="btn btn-primary" :disabled="!pendingSugg || store.busy" @click="post('slc.registration.suggested', { row_ids: items.map((i) => i.id) })">Apply suggested to all ({{ pendingSugg }})</button>
    </div>

    <section class="kpis" aria-label="Errors by outcome">
        <div class="kpi t-bad"><span class="k-label">Students with errors</span><span class="k-val">{{ withErr.length }}</span><span class="k-note">{{ counts.data }} still to sort · {{ clean }} fully matched</span></div>
        <div class="kpi t-info"><span class="k-label">Update our record</span><span class="k-val">{{ sugCount('update') }}</span><span class="k-note">Suggested or chosen</span></div>
        <div class="kpi t-bad"><span class="k-label">Issue CoC</span><span class="k-val">{{ sugCount('coc') }}</span><span class="k-note">Course, start, year, fee</span></div>
        <div class="kpi t-warn"><span class="k-label">Contact student</span><span class="k-val">{{ sugCount('contact') }}</span><span class="k-note">Student updates SLC</span></div>
    </section>

    <section v-if="fieldSummary.length" class="card" aria-labelledby="by-field">
        <div class="card-h"><h2 id="by-field">Errors by field</h2><span class="muted small">{{ plural(fieldTotal, 'error') }} across {{ plural(shown.length, 'student') }} · choose a field to see only those students</span></div>
        <div class="card-b field-sum" role="group" aria-label="Filter by field">
            <button type="button" class="fsum fsum-all" :aria-pressed="fieldFilter === 'all'" @click="fieldFilter = 'all'"><span class="fsum-l">All fields</span><span class="fsum-n num">{{ shown.length }}</span><span class="fsum-bar" aria-hidden="true"><span style="width: 100%"></span></span><span class="fsum-m">{{ shown.length === 1 ? 'student' : 'students' }} with errors</span></button>
            <button v-for="f in fieldSummary" :key="f.key" type="button" class="fsum" :aria-pressed="fieldFilter === f.key" :title="f.open ? f.open + ' still to decide' : 'All decided'" @click="fieldFilter = fieldFilter === f.key ? 'all' : f.key">
                <span class="fsum-l">{{ f.label }}</span><span class="fsum-n num">{{ f.count }}</span>
                <span class="fsum-bar" aria-hidden="true"><span :style="{ width: Math.max(6, Math.round(f.count / fieldMax * 100)) + '%' }"></span></span>
                <span class="fsum-m">{{ f.open ? f.open + ' to decide' : 'All decided' }}</span>
            </button>
        </div>
    </section>

    <div class="row" style="justify-content: space-between">
        <div class="filters" role="group" aria-label="Show students">
            <button type="button" class="chip" :aria-pressed="show === 'data'" @click="show = 'data'">To sort <span class="num">{{ counts.data }}</span></button>
            <button type="button" class="chip" :aria-pressed="show === 'all'" @click="show = 'all'">All with errors <span class="num">{{ withErr.length }}</span></button>
        </div>
        <div class="row" style="gap: 8px 18px">
            <div class="filters" role="group" aria-label="Filter by outcome">
                <button v-for="[k, l] in outcomeFilters" :key="k" type="button" class="chip" :aria-pressed="routeFilter === k" @click="routeFilter = k">{{ l }}</button>
            </div>
            <div class="row small" style="gap: 8px">
                <span id="reg-group-l" class="muted">Group by</span>
                <div class="filters" role="group" aria-labelledby="reg-group-l">
                    <button type="button" class="chip" :aria-pressed="groupBy === 'student'" @click="groupBy = 'student'">Student</button>
                    <button type="button" class="chip" :aria-pressed="groupBy === 'issue'" @click="groupBy = 'issue'">Issue</button>
                </div>
            </div>
        </div>
    </div>

    <div v-if="items.length === 0" class="card empty">{{ fieldFilter !== 'all' ? 'No students with that error match these filters.' : 'No students to sort here.' }}
        <div v-if="clean > 0" style="margin-top: 10px"><button type="button" class="btn btn-primary" @click="go('precheck')">{{ plural(clean, 'fully matched student') }} – go to pre-confirm</button></div>
    </div>

    <template v-else-if="groupBy === 'issue'">
        <div class="muted small">{{ plural(groups.length, 'issue') }} · {{ plural(groups.reduce((a, g) => a + g.rows.length, 0), 'error') }} · undecided first</div>
        <article v-for="g in groups" :key="g.key" class="scard">
            <div class="scard-h">
                <div class="row" style="flex-wrap: nowrap; min-width: 0">
                    <span :class="'errcount' + (g.open ? '' : ' done')">{{ g.rows.length }}</span>
                    <div style="min-width: 0">
                        <div style="font-weight: 700; font-size: 16px">{{ g.label }}</div>
                        <div class="muted small">{{ plural(g.rows.length, 'student') }}{{ NOTE[g.key] ? ' · ' + NOTE[g.key] : '' }}</div>
                    </div>
                </div>
                <div class="row small">
                    <span v-if="g.open" class="pill bad">{{ g.open }} to decide</span>
                    <span v-else class="pill ok">All decided</span>
                    <button v-if="g.sugg" type="button" class="btn btn-sm btn-primary" :disabled="store.busy" @click="post('slc.registration.suggested', { row_ids: g.rows.map((x) => x.i.id), key: g.key })">Apply suggested ({{ g.sugg }})</button>
                </div>
            </div>
            <div class="tablebox"><table class="tbl" style="min-width: 940px">
                <thead><tr><th scope="col" style="width: 20%">Student</th><th scope="col" style="width: 20%">SLC gives us</th><th scope="col" style="width: 22%">We hold</th><th scope="col">What happens</th></tr></thead>
                <tbody><tr v-for="{ i, ch } in g.rows" :key="i.id">
                    <th scope="row"><a class="plain" :href="i.s.url" target="_blank" rel="noopener">{{ i.s.title }} {{ i.s.first }} {{ i.s.last }}</a>
                        <div class="muted small" style="font-weight: 400"><span class="mono">{{ i.r.ssn }}</span> · {{ i.s.id }}</div></th>
                    <td class="slc">{{ ch.slc }}</td>
                    <td class="ours">{{ ch.ours }}<div v-if="ch.note" class="muted small" style="font-weight: 400">{{ ch.note }}</div></td>
                    <td><RegRoute :i="i" :ch="ch" with-name /></td>
                </tr></tbody>
            </table></div>
        </article>
    </template>

    <article v-for="i in (groupBy === 'student' ? items : [])" :key="i.id" class="scard">
        <div class="scard-h">
            <div class="row" style="flex-wrap: nowrap; min-width: 0">
                <span :class="'errcount' + (i.unrouted.length ? '' : ' done')" :aria-label="plural(i.errors.length, 'error')">{{ i.errors.length }}</span>
                <div style="min-width: 0">
                    <div style="font-weight: 700; font-size: 16px"><a class="plain" :href="i.s.url" target="_blank" rel="noopener">{{ i.s.title }} {{ i.s.first }} {{ i.s.last }}</a></div>
                    <div class="muted small"><span class="mono">{{ i.r.ssn }}</span> · {{ i.s.id }} · {{ i.s.course.name || 'No active course' }} · {{ i.link.method === 'ssn' ? 'matched by SSN' : 'identified by ' + i.link.by }}</div>
                    <RowHistory :entries="i.history" resolved />
                </div>
            </div>
            <div class="row small">
                <span class="muted">{{ i.checks.length - i.errors.length }} of {{ i.checks.length }} fields match</span>
                <span v-if="i.unrouted.length" class="pill bad">{{ i.unrouted.length }} to decide</span>
                <span v-else class="pill ok">All errors sorted</span>
                <span v-if="i.errors.length - i.unrouted.length > 0" class="pill muted">{{ i.errors.length - i.unrouted.length }} sorted</span>
            </div>
        </div>
        <div class="tablebox"><table class="tbl" style="min-width: 860px">
            <thead><tr><th scope="col" style="width: 14%">Field</th><th scope="col" style="width: 22%">SLC gives us</th><th scope="col" style="width: 22%">We hold</th><th scope="col">What happens</th></tr></thead>
            <tbody>
                <tr v-for="ch in sortedErrors(i)" :key="ch.key">
                    <th scope="row">{{ ch.label }}</th>
                    <td class="slc">{{ ch.slc }}</td>
                    <td class="ours">{{ ch.ours }}<div v-if="ch.note" class="muted small" style="font-weight: 400">{{ ch.note }}</div></td>
                    <td><RegRoute :i="i" :ch="ch" /></td>
                </tr>
            </tbody>
        </table></div>
        <div class="scard-f">
            <details class="allf"><summary>View all {{ i.checks.length }} fields</summary>
                <div class="tablebox" style="margin-top: 8px"><table class="tbl" style="min-width: 560px">
                    <thead><tr><th scope="col">Field</th><th scope="col">SLC</th><th scope="col">Ours</th><th scope="col">Result</th></tr></thead>
                    <tbody><tr v-for="c in i.checks" :key="c.key"><th scope="row">{{ c.label }}</th><td>{{ c.slc }}</td><td>{{ c.ours }}</td><td><span :class="'pill ' + (c.covered ? 'muted' : c.ok ? 'ok' : 'bad')">{{ c.covered ? 'In course code CoC' : c.ok ? '✓ Match' : '✗ Error' }}</span></td></tr></tbody>
                </table></div>
            </details>
            <div class="row">
                <button type="button" class="btn btn-sm btn-bad" :disabled="store.busy" @click="post('slc.registration.decide', { row_id: i.id, d: 'notreg' })">Not registered</button>
                <button v-if="suggCount(i) > 0" type="button" class="btn btn-sm btn-primary" :disabled="store.busy" @click="post('slc.registration.suggested', { row_ids: [i.id] })">Apply suggested ({{ suggCount(i) }})</button>
            </div>
        </div>
    </article>
</template>

<script>
import { computed, inject, ref, watch } from "vue";
import { FIELD_ORDER, plural } from "./helpers";
import RowHistory from "./RowHistory.vue";
import RegRoute from "./RegRoute.vue";

/*
 * Bulk registration › Data check: every linked line's errors and what happens to each, grouped by
 * student (the default) or by issue — one card per field with every student who has that error, as
 * on Bulk attendance (user, 2 Nov 2026).
 */
const NOTE = {
    tfl: "TFL should equal the course fee", course: "SLC course code against the student’s course", courseName: "Course name against the student’s course",
    start: "Course start date", year: "Year of study against the course instance", dob: "Check ID documents", first: "Check ID documents",
    last: "Check ID documents", title: "Title on the student record", gender: "Gender on the student record", address: "Address held by SLC against ours",
    postcode: "Postcode held by SLC against ours", ssn: "SSN on the student record",
};

export default {
    components: { RowHistory, RegRoute },
    setup() {
        const { store, list, post, go } = inject("slr");
        const show = ref("data");
        const routeFilter = ref("all");
        const counts = computed(() => store.state.counts);
        const withErr = computed(() => list.value.filter((i) => i.s && i.errors.length && !i.dec));
        const fieldFilter = ref("all");
        // the students the To sort / All with errors chips show; the field summary counts these
        const shown = computed(() => (show.value === "data" ? withErr.value.filter((i) => i.stage === "data") : withErr.value));
        const fieldSummary = computed(() => {
            const by = {};
            shown.value.forEach((i) => i.errors.forEach((c) => {
                const f = (by[c.key] = by[c.key] || { key: c.key, label: c.label, count: 0, open: 0 });
                f.count++;
                if (!c.route) f.open++;
            }));
            return Object.values(by).sort((a, b) => b.count - a.count || FIELD_ORDER.indexOf(a.key) - FIELD_ORDER.indexOf(b.key));
        });
        const fieldTotal = computed(() => fieldSummary.value.reduce((a, f) => a + f.count, 0));
        const fieldMax = computed(() => Math.max(1, ...fieldSummary.value.map((f) => f.count)));
        watch(fieldSummary, (list) => { if (fieldFilter.value !== "all" && !list.some((f) => f.key === fieldFilter.value)) fieldFilter.value = "all"; });
        const items = computed(() => {
            let out = shown.value;
            if (fieldFilter.value !== "all") out = out.filter((i) => i.errors.some((c) => c.key === fieldFilter.value));
            if (routeFilter.value !== "all") out = out.filter((i) => i.errors.some((c) => (c.route || c.sugg) === routeFilter.value));
            const sel = store.selected;
            return [...out].sort((a, b) => (a.id === sel ? -1 : b.id === sel ? 1 : 0) || b.errors.length - a.errors.length);
        });
        const sugCount = (k) => withErr.value.reduce((a, i) => a + i.errors.filter((c) => (c.route || c.sugg) === k).length, 0);
        const pendingSugg = computed(() => items.value.reduce((a, i) => a + i.unrouted.filter((c) => c.sugg).length, 0));
        const clean = computed(() => list.value.filter((i) => i.s && !i.errors.length && !i.dec).length);
        const linkedCount = computed(() => list.value.filter((i) => i.s).length);
        const sortedErrors = (i) => [...i.errors].sort((a, b) => FIELD_ORDER.indexOf(a.key) - FIELD_ORDER.indexOf(b.key));
        const suggCount = (i) => i.unrouted.filter((c) => c.sugg).length;
        const outcomeFilters = [["all", "Any outcome"], ["update", "Update ours"], ["coc", "CoC"], ["contact", "Contact"]];
        // Group by issue: the students (from the same filters) under each field, undecided first
        const groupBy = ref("student");
        const groups = computed(() => fieldSummary.value
            .filter((f) => fieldFilter.value === "all" || f.key === fieldFilter.value)
            .map((f) => {
                const rows = items.value.flatMap((i) => i.errors.filter((c) => c.key === f.key && (routeFilter.value === "all" || (c.route || c.sugg) === routeFilter.value)).map((ch) => ({ i, ch })))
                    .sort((a, b) => (a.ch.route ? 1 : 0) - (b.ch.route ? 1 : 0));
                return { key: f.key, label: f.label, rows, open: rows.filter((x) => !x.ch.route).length, sugg: rows.filter((x) => !x.ch.route && x.ch.sugg).length };
            })
            .filter((g) => g.rows.length));

        return { store, post, go, show, routeFilter, fieldFilter, shown, fieldSummary, fieldTotal, fieldMax, counts, withErr, items, sugCount, pendingSugg, clean, linkedCount, sortedErrors, suggCount, outcomeFilters, groupBy, groups, NOTE, plural };
    },
};
</script>
