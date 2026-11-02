<template>
    <div class="pagehead">
        <div><span class="eyebrow">Step 3</span><h1>Data check by {{ groupBy === 'issue' ? 'issue' : 'student' }}</h1>
            <p>SLC’s attendance line against datafuture for {{ plural(counts.matched, 'matched student') }}: name, date of birth, SSN, course code and registration year; whether the SLC registration for {{ yearName }} is recorded; and, when it is, the course fee and TFL against its agreement. Decide what happens to each difference.</p></div>
        <button type="button" class="btn btn-primary" :disabled="!pendingSugg || store.busy" @click="post('slc.registration.att.suggested', { row_ids: items.map((i) => i.id) })">Apply suggested to all ({{ pendingSugg }})</button>
    </div>

    <section class="kpis" aria-label="Differences">
        <div class="kpi t-bad"><span class="k-label">Students with differences</span><span class="k-val">{{ withErr.length }}</span><span class="k-note">{{ counts.data }} still to sort · {{ counts.clean }} fully matched</span></div>
        <div class="kpi t-warn"><span class="k-label">No SLC registration</span><span class="k-val">{{ noReg }}</span><span class="k-note">None recorded for {{ yearName }}</span></div>
        <div class="kpi t-info"><span class="k-label">Update our record</span><span class="k-val">{{ chosen('update') }}</span><span class="k-note">Name or date of birth</span></div>
        <div class="kpi t-bad"><span class="k-label">Issue CoC</span><span class="k-val">{{ chosen('coc') }}</span><span class="k-note">Course, year, fee, TFL</span></div>
        <div class="kpi t-warn"><span class="k-label">Contact student</span><span class="k-val">{{ chosen('contact') }}</span><span class="k-note">Student updates SLC</span></div>
    </section>

    <section v-if="fieldSummary.length" class="card" aria-labelledby="att-by-field">
        <div class="card-h"><h2 id="att-by-field">Differences by field</h2><span class="muted small">{{ plural(fieldTotal, 'difference') }} across {{ plural(shown.length, 'student') }} · choose a field to see only those students</span></div>
        <div class="card-b field-sum" role="group" aria-label="Filter by field">
            <button type="button" class="fsum fsum-all" :aria-pressed="fieldFilter === 'all'" @click="fieldFilter = 'all'"><span class="fsum-l">All fields</span><span class="fsum-n num">{{ shown.length }}</span><span class="fsum-bar" aria-hidden="true"><span style="width: 100%"></span></span><span class="fsum-m">students with differences</span></button>
            <button v-for="f in fieldSummary" :key="f.key" type="button" class="fsum" :aria-pressed="fieldFilter === f.key" @click="fieldFilter = fieldFilter === f.key ? 'all' : f.key">
                <span class="fsum-l">{{ f.label }}</span><span class="fsum-n num">{{ f.count }}</span>
                <span class="fsum-bar" aria-hidden="true"><span :style="{ width: Math.max(6, Math.round(f.count / fieldMax * 100)) + '%' }"></span></span>
                <span class="fsum-m">{{ f.open ? f.open + ' to decide' : 'All decided' }}</span>
            </button>
        </div>
    </section>

    <div class="row" style="justify-content: space-between">
        <div class="filters" role="group" aria-label="Show students">
            <button type="button" class="chip" :aria-pressed="show === 'data'" @click="show = 'data'">To sort <span class="num">{{ counts.data }}</span></button>
            <button type="button" class="chip" :aria-pressed="show === 'all'" @click="show = 'all'">All with differences <span class="num">{{ withErr.length }}</span></button>
        </div>
        <div class="row small" style="gap: 8px">
            <span id="att-group-l" class="muted">Group by</span>
            <div class="filters" role="group" aria-labelledby="att-group-l">
                <button type="button" class="chip" :aria-pressed="groupBy === 'student'" @click="groupBy = 'student'">Student</button>
                <button type="button" class="chip" :aria-pressed="groupBy === 'issue'" @click="groupBy = 'issue'">Issue</button>
            </div>
        </div>
    </div>

    <div v-if="!imp" class="card empty">Upload the SIS Attendance Confirmation Report on the Overview first.</div>
    <div v-else-if="!items.length" class="card empty">{{ fieldFilter !== 'all' ? 'No students with that difference match these filters.' : 'No students to sort here.' }}</div>
    <div v-else-if="groupBy === 'issue'" class="muted small">{{ plural(groups.length, 'issue') }} · {{ plural(groups.reduce((a, g) => a + g.rows.length, 0), 'difference') }} · undecided first</div>

    <template v-if="groupBy === 'issue'">
        <article v-for="g in groups" :key="g.key" class="scard">
            <div class="scard-h">
                <div class="row" style="flex-wrap: nowrap; min-width: 0">
                    <span :class="'errcount' + (g.open ? '' : ' done')">{{ g.rows.length }}</span>
                    <div style="min-width: 0">
                        <div style="font-weight: 700; font-size: 16px">{{ g.label }}</div>
                        <div class="muted small">{{ plural(g.rows.length, 'student') }} · {{ NOTE[g.key] || '' }}</div>
                    </div>
                </div>
                <div class="row small">
                    <span v-if="g.open" class="pill bad">{{ g.open }} to decide</span>
                    <span v-else class="pill ok">All decided</span>
                    <button v-if="g.sugg" type="button" class="btn btn-sm btn-primary" :disabled="store.busy" @click="post('slc.registration.att.suggested', { row_ids: g.rows.map((x) => x.i.id), key: g.key })">Apply suggested ({{ g.sugg }})</button>
                </div>
            </div>
            <div class="tablebox"><table class="tbl" style="min-width: 940px">
                <thead><tr><th scope="col" style="width: 20%">Student</th><th scope="col" style="width: 20%">SLC gives us</th><th scope="col" style="width: 24%">We hold</th><th scope="col">What happens</th></tr></thead>
                <tbody><tr v-for="{ i, ch } in g.rows" :key="i.id">
                    <th scope="row"><a class="plain" :href="i.s.url" target="_blank" rel="noopener">{{ i.s.title }} {{ i.s.first }} {{ i.s.last }}</a>
                        <div class="muted small" style="font-weight: 400"><span class="mono">{{ i.r.ssn }}</span> · {{ i.s.id }}</div></th>
                    <td class="slc">{{ ch.slc }}</td>
                    <td class="ours">{{ ch.ours }}<div v-if="ch.note" class="muted small" style="font-weight: 400">{{ ch.note }}</div></td>
                    <td><AttRoute :i="i" :ch="ch" with-name /></td>
                </tr></tbody>
            </table></div>
        </article>
    </template>

    <article v-for="i in (groupBy === 'student' ? items : [])" :key="i.id" class="scard">
        <div class="scard-h">
            <div class="row" style="flex-wrap: nowrap; min-width: 0">
                <span :class="'errcount' + (unrouted(i).length ? '' : ' done')">{{ errors(i).length }}</span>
                <div style="min-width: 0">
                    <div style="font-weight: 700; font-size: 16px"><a class="plain" :href="i.s.url" target="_blank" rel="noopener">{{ i.s.title }} {{ i.s.first }} {{ i.s.last }}</a></div>
                    <div class="muted small"><span class="mono">{{ i.r.ssn }}</span> · {{ i.s.id }} · {{ i.s.course || 'No active course' }} · {{ i.link.method === 'ssn' ? 'matched by SSN' : 'SSN saved here' }}</div>
                </div>
            </div>
            <div class="row small">
                <span class="muted">{{ i.checks.length - errors(i).length }} of {{ i.checks.length }} fields match</span>
                <span v-if="unrouted(i).length" class="pill bad">{{ unrouted(i).length }} to decide</span>
                <span v-else class="pill ok">All differences sorted</span>
            </div>
        </div>
        <div class="tablebox"><table class="tbl" style="min-width: 860px">
            <thead><tr><th scope="col" style="width: 14%">Field</th><th scope="col" style="width: 22%">SLC gives us</th><th scope="col" style="width: 24%">We hold</th><th scope="col">What happens</th></tr></thead>
            <tbody><tr v-for="ch in errors(i)" :key="ch.key">
                <th scope="row">{{ ch.label }}</th>
                <td class="slc">{{ ch.slc }}</td>
                <td class="ours">{{ ch.ours }}<div v-if="ch.note" class="muted small" style="font-weight: 400">{{ ch.note }}</div></td>
                <td><AttRoute :i="i" :ch="ch" /></td>
            </tr></tbody>
        </table></div>
        <div class="scard-f">
            <details class="allf"><summary>View all {{ i.checks.length }} fields</summary>
                <div class="tablebox" style="margin-top: 8px"><table class="tbl" style="min-width: 560px">
                    <thead><tr><th scope="col">Field</th><th scope="col">SLC</th><th scope="col">Ours</th><th scope="col">Result</th></tr></thead>
                    <tbody><tr v-for="c in i.checks" :key="c.key"><th scope="row">{{ c.label }}</th><td>{{ c.slc }}</td><td>{{ c.ours }}</td><td><span :class="'pill ' + (c.ok ? 'ok' : 'bad')">{{ c.ok ? '✓ Match' : '✗ Difference' }}</span></td></tr></tbody>
                </table></div>
            </details>
            <div class="row">
                <button v-if="sugg(i) > 0" type="button" class="btn btn-sm btn-primary" :disabled="store.busy" @click="post('slc.registration.att.suggested', { row_ids: [i.id] })">Apply suggested ({{ sugg(i) }})</button>
            </div>
        </div>
    </article>
</template>

<script>
import { computed, inject, ref, watch } from "vue";
import { plural } from "./helpers";
import AttRoute from "./AttRoute.vue";

/*
 * Bulk attendance › Data check (user, 2 Nov 2026): AttendanceChecks on every matched line, with the
 * same decisions as Bulk registration's data check (AttendanceActions::setRoute / clearRoute /
 * applySuggested). Grouped by student (the default) or by issue: one card per field with every
 * student who has that difference (user, 2 Nov 2026).
 */
const ORDER = ["registration", "tfl", "fee", "course", "year", "dob", "first", "last", "ssn"];
const NOTE = {
    registration: "No SLC registration recorded for the year", tfl: "TFL against the agreement total", fee: "Course fee against the agreement",
    course: "SLC course code against the student’s course", year: "Year of study against the registration or course", dob: "Check ID documents",
    first: "Check ID documents", last: "Check ID documents", ssn: "SSN on the student record",
};

export default {
    components: { AttRoute },
    setup() {
        const { store, post } = inject("slr");
        const att = computed(() => store.state.attendance);
        const imp = computed(() => att.value.import);
        const counts = computed(() => att.value.counts);
        const yearName = computed(() => (imp.value ? imp.value.year : "the year"));
        const show = ref("data");
        const fieldFilter = ref("all");
        const errors = (i) => i.checks.filter((c) => !c.ok).sort((a, b) => ORDER.indexOf(a.key) - ORDER.indexOf(b.key));
        const unrouted = (i) => errors(i).filter((c) => !c.route);
        const sugg = (i) => unrouted(i).filter((c) => c.sugg).length;
        const withErr = computed(() => att.value.rows.filter((i) => i.s && errors(i).length));
        const shown = computed(() => (show.value === "data" ? withErr.value.filter((i) => i.stage === "data") : withErr.value));
        const items = computed(() => (fieldFilter.value === "all" ? shown.value : shown.value.filter((i) => errors(i).some((c) => c.key === fieldFilter.value))));
        const fieldSummary = computed(() => {
            const by = {};
            shown.value.forEach((i) => errors(i).forEach((c) => {
                const f = (by[c.key] = by[c.key] || { key: c.key, label: c.label, count: 0, open: 0 });
                f.count++;
                if (!c.route) f.open++;
            }));
            return Object.values(by).sort((a, b) => b.count - a.count || ORDER.indexOf(a.key) - ORDER.indexOf(b.key));
        });
        const fieldTotal = computed(() => fieldSummary.value.reduce((a, f) => a + f.count, 0));
        const fieldMax = computed(() => Math.max(1, ...fieldSummary.value.map((f) => f.count)));
        watch(fieldSummary, (list) => { if (fieldFilter.value !== "all" && !list.some((f) => f.key === fieldFilter.value)) fieldFilter.value = "all"; });
        const noReg = computed(() => att.value.rows.filter((i) => i.s && !i.s.registration).length);
        const chosen = (k) => withErr.value.reduce((a, i) => a + errors(i).filter((c) => (c.route || c.sugg) === k).length, 0);
        const pendingSugg = computed(() => items.value.reduce((a, i) => a + sugg(i), 0));
        // Group by issue: the students (from the same filters) under each field, undecided first
        const groupBy = ref("student");
        const groups = computed(() => fieldSummary.value
            .filter((f) => fieldFilter.value === "all" || f.key === fieldFilter.value)
            .map((f) => {
                const rows = items.value.flatMap((i) => errors(i).filter((c) => c.key === f.key).map((ch) => ({ i, ch })))
                    .sort((a, b) => (a.ch.route ? 1 : 0) - (b.ch.route ? 1 : 0));
                return { key: f.key, label: f.label, rows, open: rows.filter((x) => !x.ch.route).length, sugg: rows.filter((x) => !x.ch.route && x.ch.sugg).length };
            })
            .filter((g) => g.rows.length));

        return { store, post, imp, counts, yearName, show, fieldFilter, errors, unrouted, sugg, withErr, shown, items, fieldSummary, fieldTotal, fieldMax, noReg, chosen, pendingSugg, groupBy, groups, NOTE, plural };
    },
};
</script>
