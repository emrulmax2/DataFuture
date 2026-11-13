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
            <button v-for="f in boxSummary" :key="f.key" type="button" class="fsum" :aria-pressed="fieldFilter === f.key" :title="(f.parts ? f.parts.map((x) => x.label + ' ' + x.count).join(', ') + ' · ' : '') + (f.open ? f.open + ' still to decide' : 'All decided')" @click="fieldFilter = fieldFilter === f.key ? 'all' : f.key">
                <span class="fsum-l">{{ f.label }}</span><span class="fsum-n num">{{ f.count }}</span>
                <span class="fsum-bar" aria-hidden="true"><span :style="{ width: Math.max(6, Math.round(f.count / fieldMax * 100)) + '%' }"></span></span>
                <span v-if="f.parts" class="fsum-m fsum-parts">{{ f.parts.map((x) => x.short + ' ' + x.count).join(' · ') }}</span>
                <span class="fsum-m">{{ f.open ? f.open + ' to decide' : 'All decided' }}</span>
            </button>
        </div>
    </section>

    <StepSearch v-if="withErr.length" v-model="q" id="search-att-check" :shown="items.length" :total="filtered.length" />
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
    <div v-else-if="!items.length" class="card empty">{{ q.trim() && filtered.length ? 'No students on this step match “' + q.trim() + '”.' : fieldFilter !== 'all' ? 'No students with that difference match these filters.' : 'No students to sort here.' }}</div>
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
                    <th scope="row"><a class="plain" :href="i.s.url" target="_blank" rel="noopener">{{ i.s.title }} {{ i.s.first }} {{ i.s.last }}</a><StatusTag :status="i.s.status" />
                        <div class="muted small" style="font-weight: 400"><span class="mono">{{ i.r.ssn }}</span> · {{ i.s.id }}</div></th>
                    <td class="slc"><AttDiff :ch="ch" side="slc" /></td>
                    <td class="ours"><AttDiff :ch="ch" side="ours" /><div v-if="ch.note" class="muted small" style="font-weight: 400">{{ ch.note }}</div></td>
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
                    <div style="font-weight: 700; font-size: 16px"><a class="plain" :href="i.s.url" target="_blank" rel="noopener">{{ i.s.title }} {{ i.s.first }} {{ i.s.last }}</a><StatusTag :status="i.s.status" /></div>
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
            <tbody>
                <template v-for="r in cardRows(i)" :key="r.key">
                    <tr v-if="!r.parts">
                        <th scope="row">{{ r.ch.label }}</th>
                        <td class="slc"><AttDiff :ch="r.ch" side="slc" /></td>
                        <td class="ours"><AttDiff :ch="r.ch" side="ours" /><div v-if="r.ch.note" class="muted small" style="font-weight: 400">{{ r.ch.note }}</div></td>
                        <td><AttRoute :i="i" :ch="r.ch" /></td>
                    </tr>
                    <!-- Name row: first and last name as one (user, 6 Nov 2026), the whole name on both sides, one action for both differences -->
                    <tr v-else class="grp-row">
                        <th scope="row">{{ r.label }}<div v-if="r.parts.length > 1" class="muted small" style="font-weight: 400">{{ r.parts.length }} differences</div></th>
                        <td class="slc"><BoxValue :segs="r.full.slc" /></td>
                        <td class="ours">
                            <BoxValue :segs="r.full.ours" />
                            <div class="small nm-what">{{ r.mismatch }}</div>
                            <div v-for="n in r.notes" :key="n" class="muted small" style="font-weight: 400; margin-top: 4px">{{ n }}</div></td>
                        <td>
                            <AttRoute v-if="r.parts.length === 1" :i="i" :ch="r.parts[0]" />
                            <template v-else-if="r.compact">
                                <div v-if="r.decided" class="acts">
                                    <span :class="'pill ' + ROUTES[r.decided].tone">{{ ROUTES[r.decided].label }}{{ r.decided === 'coc' ? ' · ' + (ATT_COC_TYPE[r.parts[0].key] || '') : '' }}</span>
                                    <span class="muted small">all {{ r.parts.length }}</span>
                                    <button v-if="r.decided === 'contact'" type="button" class="btn-link small" @click="copy(i.id, contactMessage('name', r.msg, i.s))">{{ copied === i.id ? 'Copied' : 'Copy message' }}</button>
                                    <button type="button" class="btn-link small" :disabled="store.busy" @click="post('slc.registration.att.routes.clear', { row_id: i.id, keys: r.parts.map((p) => p.key) })">Change</button>
                                </div>
                                <div v-else class="routes" role="group" :aria-label="'Action for ' + r.label + ' – all ' + r.parts.length + ' differences'">
                                    <button v-for="k in r.common" :key="k" type="button" :class="'route' + (r.sugg === k ? ' sugg' : '')" :disabled="store.busy" @click="post('slc.registration.att.routes', { row_id: i.id, keys: r.parts.map((p) => p.key), route: k })">
                                        {{ ROUTES[k].label }}<span v-if="r.sugg === k" class="tag">Suggested</span>
                                    </button>
                                </div>
                                <div class="muted small" style="margin-top: 6px">Applies to {{ r.parts.map((p) => p.label.toLowerCase()).join(' and ') }} · <button type="button" class="btn-link small" @click="toggleEach(i, r)">Decide each separately</button></div>
                            </template>
                            <div v-else class="grp-each">
                                <div v-for="p in r.parts" :key="p.key" class="grp-each-row"><span class="grp-each-l">{{ p.label }}</span><div><AttRoute :i="i" :ch="p" /></div></div>
                                <button v-if="r.canTogether" type="button" class="btn-link small" @click="toggleEach(i, r)">Decide together</button>
                            </div>
                        </td>
                    </tr>
                </template>
            </tbody>
        </table></div>
        <div class="scard-f">
            <details class="allf"><summary>View all {{ i.checks.length }} fields</summary>
                <div class="tablebox" style="margin-top: 8px"><table class="tbl" style="min-width: 560px">
                    <thead><tr><th scope="col">Field</th><th scope="col">SLC</th><th scope="col">Ours</th><th scope="col">Result</th></tr></thead>
                    <tbody><tr v-for="c in i.checks" :key="c.key"><th scope="row">{{ c.label }}</th><td><AttDiff :ch="c" side="slc" /></td><td><AttDiff :ch="c" side="ours" /></td><td><span :class="'pill ' + (c.ok ? 'ok' : 'bad')">{{ c.ok ? '✓ Match' : '✗ Difference' }}</span></td></tr></tbody>
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
import { ATT_COC_TYPE, ATT_FIELD_ROUTES, ROUTES, contactMessage, matchesSearch, plural, searchText } from "./helpers";
import StepSearch from "./StepSearch.vue";
import AttRoute from "./AttRoute.vue";
import AttDiff from "./AttDiff.vue";
import BoxValue from "./BoxValue.vue";
import StatusTag from "./StatusTag.vue";

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
// first and last name share one "Name" box and one row on the student's card (user, 6 Nov 2026, as on Bulk registration)
const BOX = { first: "name", last: "name" };
const BOX_LABEL = { name: "Name" };
const NAME_KEYS = ["first", "last"];
const SHORT = { first: "First", last: "Last" };
// a name part as the Name row shows it: hyphens as spaces (display only; Update still writes SLC's value)
const nameText = (v) => String(v ?? "").replace(/-/g, " ").replace(/\s+/g, " ").trim();
const partOf = (i, k) => i.checks.find((c) => c.key === k) || {};
const andList = (xs) => (xs.length < 2 ? xs.join("") : xs.slice(0, -1).join(", ") + " and " + xs[xs.length - 1]);
const upFirst = (t) => t.charAt(0).toUpperCase() + t.slice(1);

export default {
    components: { StatusTag, AttRoute, AttDiff, BoxValue, StepSearch },
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
        const boxOf = (key) => BOX[key] || key;
        const filtered = computed(() => (fieldFilter.value === "all" ? shown.value : shown.value.filter((i) => errors(i).some((c) => boxOf(c.key) === fieldFilter.value))));
        // this step's search (user, 13 Nov 2026), after the chips and the field filter
        const q = ref("");
        const items = computed(() => (q.value.trim() ? filtered.value.filter((i) => matchesSearch(q.value, searchText(i))) : filtered.value));
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
        // the boxes: first and last name are one "Name" box (the students with either), the rest one per field
        const boxSummary = computed(() => {
            const out = fieldSummary.value.filter((f) => !BOX[f.key]);
            Object.entries(BOX_LABEL).forEach(([key, label]) => {
                const parts = fieldSummary.value.filter((f) => BOX[f.key] === key);
                if (!parts.length) return;
                const has = (i, undecided) => errors(i).some((c) => BOX[c.key] === key && (!undecided || !c.route));
                out.push({
                    key, label, count: shown.value.filter((i) => has(i)).length, open: shown.value.filter((i) => has(i, true)).length,
                    parts: parts.sort((a, b) => NAME_KEYS.indexOf(a.key) - NAME_KEYS.indexOf(b.key)).map((f) => ({ key: f.key, label: f.label, short: SHORT[f.key] || f.label, count: f.count })),
                });
            });
            const order = (f) => ORDER.indexOf(f.parts ? f.parts[0].key : f.key);
            return out.sort((a, b) => b.count - a.count || order(a) - order(b));
        });
        const fieldMax = computed(() => Math.max(1, ...boxSummary.value.map((f) => f.count)));
        watch(boxSummary, (list) => { if (fieldFilter.value !== "all" && !list.some((f) => f.key === fieldFilter.value)) fieldFilter.value = "all"; });
        /*
         * A student's rows: any first or last name difference is one Name row with the whole name on both
         * sides (differing words in the error colour). Two differences: one action for both
         * (AttendanceActions::setRoutes, all or none), or "Decide each separately" — which opens by itself
         * when the two were decided differently.
         */
        const eachOpen = ref({});
        const toggleEach = (i, r) => { eachOpen.value = { ...eachOpen.value, [i.id + "-" + r.key]: !eachOpen.value[i.id + "-" + r.key] }; };
        const cardRows = (i) => {
            const out = [];
            const list = errors(i);
            let named = false;
            list.forEach((ch) => {
                if (!BOX[ch.key]) { out.push({ key: ch.key, ch }); return; }
                if (named) return;
                named = true;
                const parts = list.filter((c) => BOX[c.key] === "name").sort((a, b) => NAME_KEYS.indexOf(a.key) - NAME_KEYS.indexOf(b.key));
                const common = parts.reduce((acc, p) => acc.filter((k) => (ATT_FIELD_ROUTES[p.key] || []).includes(k)), [...(ATT_FIELD_ROUTES[parts[0].key] || [])]);
                const undecided = parts.every((p) => !p.route);
                const routes = [...new Set(parts.map((p) => p.route))];
                const decided = !undecided && routes.length === 1 && routes[0] ? routes[0] : null;
                const suggs = [...new Set(parts.map((p) => p.sugg))];
                const canTogether = undecided && common.length > 0;
                const bad = new Set(parts.map((p) => p.key));
                const val = (k, s) => { const v = String(partOf(i, k)[s] ?? "").trim(); return v === "–" ? "" : v; };
                const side = (s, o) => NAME_KEYS.map((k) => ({ k, v: nameText(val(k, s)), o: nameText(val(k, o)), bad: bad.has(k) })).filter((g) => g.v);
                out.push({
                    key: "name", label: BOX_LABEL.name, parts, common, decided, canTogether,
                    sugg: suggs.length === 1 && suggs[0] && common.includes(suggs[0]) ? suggs[0] : null,
                    notes: [...new Set(parts.map((p) => p.note).filter(Boolean))],
                    compact: (canTogether || !!decided) && !eachOpen.value[i.id + "-name"],
                    full: { slc: side("slc", "ours"), ours: side("ours", "slc") },
                    mismatch: upFirst(andList(parts.map((p) => p.label.toLowerCase()))) + (parts.length > 1 ? " don’t match" : " doesn’t match"),
                    // the Copy message of a "Contact student" for both: the whole name each side holds
                    msg: { slc: NAME_KEYS.map((k) => val(k, "slc")).filter(Boolean).join(" "), ours: NAME_KEYS.map((k) => val(k, "ours")).filter(Boolean).join(" ") },
                });
            });
            return out;
        };
        const copied = ref(null);
        const copy = (id, text) => { try { navigator.clipboard.writeText(text).then(() => { copied.value = id; }, () => { copied.value = null; }); } catch (e) { copied.value = null; } };
        const noReg = computed(() => att.value.rows.filter((i) => i.s && !i.s.registration).length);
        const chosen = (k) => withErr.value.reduce((a, i) => a + errors(i).filter((c) => (c.route || c.sugg) === k).length, 0);
        const pendingSugg = computed(() => items.value.reduce((a, i) => a + sugg(i), 0));
        // Group by issue: the students (from the same filters) under each field, undecided first
        const groupBy = ref("student");
        const groups = computed(() => fieldSummary.value
            .filter((f) => fieldFilter.value === "all" || boxOf(f.key) === fieldFilter.value)
            .map((f) => {
                const rows = items.value.flatMap((i) => errors(i).filter((c) => c.key === f.key).map((ch) => ({ i, ch })))
                    .sort((a, b) => (a.ch.route ? 1 : 0) - (b.ch.route ? 1 : 0));
                return { key: f.key, label: f.label, rows, open: rows.filter((x) => !x.ch.route).length, sugg: rows.filter((x) => !x.ch.route && x.ch.sugg).length };
            })
            .filter((g) => g.rows.length));

        return { q, filtered, store, post, imp, counts, yearName, show, fieldFilter, errors, unrouted, sugg, withErr, shown, items, fieldSummary, boxSummary, fieldTotal, fieldMax, noReg, chosen, pendingSugg, groupBy, groups, NOTE, plural,
            cardRows, toggleEach, copied, copy, contactMessage, ROUTES, ATT_COC_TYPE };
    },
};
</script>
