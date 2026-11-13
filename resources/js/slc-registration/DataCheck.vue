<template>
    <div class="pagehead">
        <div><span class="eyebrow">Stage 2</span><h1>Data check by {{ groupBy === 'issue' ? 'issue' : 'student' }}</h1>
            <p>SLC data against datafuture for {{ plural(linkedCount, 'linked student') }}: title, names, gender, DOB, course code, year, start date, address, postcode, SSN and TFL (should equal the course fee). Decide what happens to each error.</p></div>
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
            <button v-for="f in boxSummary" :key="f.key" type="button" class="fsum" :aria-pressed="fieldFilter === f.key" :title="(f.parts ? f.parts.map((x) => x.label + ' ' + x.count).join(', ') + ' · ' : '') + (f.open ? f.open + ' still to decide' : 'All decided')" @click="fieldFilter = fieldFilter === f.key ? 'all' : f.key">
                <span class="fsum-l">{{ f.label }}</span><span class="fsum-n num">{{ f.count }}</span>
                <span class="fsum-bar" aria-hidden="true"><span :style="{ width: Math.max(6, Math.round(f.count / fieldMax * 100)) + '%' }"></span></span>
                <span v-if="f.parts" class="fsum-m fsum-parts">{{ f.parts.map((x) => x.short + ' ' + x.count).join(' · ') }}</span>
                <span class="fsum-m">{{ f.open ? f.open + ' to decide' : 'All decided' }}</span>
            </button>
        </div>
    </section>

    <StepSearch v-if="withErr.length" v-model="q" id="search-data" :shown="items.length" :total="filtered.length" />
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

    <div v-if="items.length === 0" class="card empty">{{ q.trim() && filtered.length ? 'No students on this step match “' + q.trim() + '”.' : fieldFilter !== 'all' ? 'No students with that error match these filters.' : 'No students to sort here.' }}
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
                    <th scope="row"><a class="plain" :href="i.s.url" target="_blank" rel="noopener">{{ i.s.title }} {{ i.s.first }} {{ i.s.last }}</a><StatusTag :status="i.s.status" />
                        <div class="muted small" style="font-weight: 400"><span class="mono">{{ i.r.ssn }}</span> · {{ i.s.id }}</div></th>
                    <td class="slc"><DiffText :text="ch.slc" :other="ch.ours" :kind="DIFF[ch.key]" :active="!!DIFF[ch.key]" /></td>
                    <td class="ours"><DiffText :text="ch.ours" :other="ch.slc" :kind="DIFF[ch.key]" :active="!!DIFF[ch.key]" /><div v-if="ch.note" class="muted small" style="font-weight: 400">{{ ch.note }}</div>
                        <UploadsLink v-if="BOX[ch.key] === 'addr'" :i="i" /><UploadsLink v-else-if="ID_KEYS.includes(ch.key)" :i="i" what="id" /></td>
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
                    <div style="font-weight: 700; font-size: 16px"><a class="plain" :href="i.s.url" target="_blank" rel="noopener">{{ i.s.title }} {{ i.s.first }} {{ i.s.last }}</a><StatusTag :status="i.s.status" /></div>
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
                <template v-for="r in cardRows(i)" :key="r.key">
                    <tr v-if="!r.parts">
                        <th scope="row">{{ r.ch.label }}</th>
                        <td class="slc"><DiffText :text="r.ch.slc" :other="r.ch.ours" :kind="DIFF[r.ch.key]" :active="!!DIFF[r.ch.key]" /></td>
                        <td class="ours"><DiffText :text="r.ch.ours" :other="r.ch.slc" :kind="DIFF[r.ch.key]" :active="!!DIFF[r.ch.key]" /><div v-if="r.ch.note" class="muted small" style="font-weight: 400">{{ r.ch.note }}</div>
                            <UploadsLink v-if="ID_KEYS.includes(r.ch.key)" :i="i" what="id" /></td>
                        <td><RegRoute :i="i" :ch="r.ch" /></td>
                    </tr>
                    <!-- Name / Address / Course row: the whole value of the group, one action for all its differences -->
                    <tr v-else class="grp-row">
                        <th scope="row">{{ r.label }}<div v-if="r.parts.length > 1" class="muted small" style="font-weight: 400">{{ r.parts.length }} differences</div></th>
                        <td class="slc"><BoxValue :segs="r.full.slc" :stack="r.full.stack" /></td>
                        <td class="ours">
                            <BoxValue :segs="r.full.ours" :stack="r.full.stack" />
                            <div class="small nm-what">{{ r.mismatch }}</div>
                            <div v-for="n in r.notes" :key="n" class="muted small" style="font-weight: 400; margin-top: 4px">{{ n }}</div>
                            <UploadsLink v-if="r.key === 'addr'" :i="i" /><UploadsLink v-else-if="r.parts.some((p) => ID_KEYS.includes(p.key))" :i="i" what="id" /></td>
                        <td>
                            <RegRoute v-if="r.parts.length === 1" :i="i" :ch="r.parts[0]" />
                            <template v-else-if="r.compact">
                                <div v-if="r.decided" class="acts">
                                    <span :class="'pill ' + ROUTES[r.decided].tone">{{ ROUTES[r.decided].label }}{{ r.decided === 'coc' ? ' · ' + (COC_TYPE[r.parts[0].key] || '') : '' }}</span>
                                    <span class="muted small">all {{ r.parts.length }}</span>
                                    <button type="button" class="btn-link small" :disabled="store.busy" @click="post('slc.registration.routes.clear', { row_id: i.id, keys: r.parts.map((p) => p.key) })">Change</button>
                                </div>
                                <div v-else class="routes" role="group" :aria-label="'Action for ' + r.label + ' – all ' + r.parts.length + ' differences'">
                                    <button v-for="k in r.common" :key="k" type="button" :class="'route' + (r.sugg === k ? ' sugg' : '')" :title="k === 'update' && r.key === 'addr' ? ADDRESS_UPDATE_TIP : undefined" :disabled="store.busy" @click="post('slc.registration.routes', { row_id: i.id, keys: r.parts.map((p) => p.key), route: k })">
                                        {{ ROUTES[k].label }}<span v-if="r.sugg === k" class="tag">Suggested</span>
                                    </button>
                                </div>
                                <div class="muted small" style="margin-top: 6px">Applies to {{ r.parts.map((p) => p.label.toLowerCase()).join(', ') }} · <button type="button" class="btn-link small" @click="toggleEach(i, r)">Decide each separately</button></div>
                                <RowHistory v-for="p in r.parts" :key="'h' + p.key" :entries="i.history" :field="p.key" />
                            </template>
                            <div v-else class="grp-each">
                                <div v-for="p in r.parts" :key="p.key" class="grp-each-row"><span class="grp-each-l">{{ p.label }}</span><div><RegRoute :i="i" :ch="p" /></div></div>
                                <button v-if="r.canTogether" type="button" class="btn-link small" @click="toggleEach(i, r)">Decide together</button>
                            </div>
                        </td>
                    </tr>
                </template>
            </tbody>
        </table></div>
        <div class="scard-f">
            <details class="allf"><summary>View all {{ i.checks.length }} fields</summary>
                <div style="margin-top: 8px"><AllFields :checks="i.checks" /></div>
            </details>
            <div class="row">
                <button type="button" class="btn btn-sm btn-bad" :disabled="store.busy" @click="post('slc.registration.decide', { row_id: i.id, d: 'notreg' })">Not registered</button>
                <button v-if="suggCount(i) > 0" type="button" class="btn btn-sm btn-primary" :disabled="store.busy" @click="post('slc.registration.suggested', { row_ids: [i.id] })">Apply suggested ({{ suggCount(i) }})</button>
            </div>
        </div>
    </article>

    <div class="notice"><Ico name="info" /><span>No action is remembered. On a later upload, the same difference for the same student (SLC sends the same value and we hold the same value) is set to No action by itself, so you don’t check it again. Change forgets it.</span></div>
</template>

<script>
import { computed, inject, ref, watch } from "vue";
import { ADDRESS_UPDATE_TIP, COC_TYPE, DIFF, FIELD_ORDER, FIELD_ROUTES, ROUTES, matchesSearch, plural, searchText } from "./helpers";
import StepSearch from "./StepSearch.vue";
import Ico from "./Ico.vue";
import AllFields from "./AllFields.vue";
import RowHistory from "./RowHistory.vue";
import RegRoute from "./RegRoute.vue";
import DiffText from "./DiffText.vue";
import BoxValue from "./BoxValue.vue";
import UploadsLink from "./UploadsLink.vue";
import StatusTag from "./StatusTag.vue";

/*
 * Bulk registration › Data check: every linked line's errors and what happens to each, grouped by
 * student (the default) or by issue — one card per field with every student who has that error, as
 * on Bulk attendance (user, 2 Nov 2026).
 */
// Errors by field: these fields share one box (user, 5 Nov 2026: name; address and postcode)
const BOX = { title: "name", first: "name", last: "name", address: "addr", postcode: "addr", course: "crs" };
const BOX_LABEL = { name: "Name", addr: "Address", crs: "Course" };
const PART_ORDER = ["title", "first", "last", "address", "postcode", "course"];
const NAME_KEYS = ["title", "first", "last"];
// differences checked against the student's proof of ID (a link to their documents; user, 6 Nov 2026)
const ID_KEYS = ["first", "last", "dob"];
const BOX_PARTS = { name: NAME_KEYS, addr: ["address", "postcode"], crs: ["courseName", "intake", "course"] };
const courseMap = (i) => (i.checks.find((c) => c.key === "course") || {}).map || null;
const PRE = { course: () => "Code", intake: (i, s) => (s === "ours" ? "Intake" : courseMap(i) ? "Mapped to" : "") };
// after the intake: our venue (not compared — SLC's code can sit on several venues)
const TAIL = { intake: (i, s) => (s === "ours" && i.s?.course?.venue ? " · " + i.s.course.venue : "") };
// one side's value of a Name / Address / Course part (names with hyphens as spaces; no value is left out)
const fullVal = (i, k, s) => {
    if (k === "courseName") return String((s === "slc" ? i.r.courseName : i.s?.course?.name) || "").trim();
    if (k === "course") return String((s === "slc" ? i.r.slcCode : i.s?.course?.slcCode) || "–").trim();
    // the intake as the student's Course Details page shows it; for SLC, the intake its code is mapped to
    if (k === "intake") return String(s === "ours" ? i.s?.course?.intake || "" : courseMap(i)?.intake || "Not in Course mapping").trim();
    const v = String((i.checks.find((c) => c.key === k) || {})[s] ?? "").trim();
    if (v === "–") return "";
    return NAME_KEYS.includes(k) ? nameText(v) : v;
};
const nameText = (v) => String(v ?? "").replace(/-/g, " ").replace(/\s+/g, " ").trim();
const andList = (xs) => (xs.length < 2 ? xs.join("") : xs.slice(0, -1).join(", ") + " and " + xs[xs.length - 1]);
const upFirst = (t) => t.charAt(0).toUpperCase() + t.slice(1);
const SHORT = { title: "Title", first: "First", last: "Last", address: "Address", postcode: "Postcode", course: "Code" };
const NOTE = {
    tfl: "TFL should equal the course fee", course: "SLC course code against the student’s course",
    start: "Course start date", year: "Year of study against the course instance", dob: "Check ID documents", first: "Check ID documents",
    last: "Check ID documents", title: "Title on the student record", gender: "Gender on the student record", address: "Address held by SLC against ours",
    postcode: "Postcode held by SLC against ours", ssn: "SSN on the student record",
};

export default {
    components: { StatusTag, Ico, AllFields, RowHistory, RegRoute, DiffText, BoxValue, UploadsLink, StepSearch },
    setup() {
        const { store, list, post, go } = inject("slr");
        const show = ref("data");
        const routeFilter = ref("all");
        const counts = computed(() => store.state.counts);
        const withErr = computed(() => list.value.filter((i) => i.s && i.errors.length && !i.dec));
        const fieldFilter = ref("all");
        // the students the To sort / All with errors chips show; the field summary counts these
        const shown = computed(() => (show.value === "data" ? withErr.value.filter((i) => i.stage === "data") : withErr.value));
        // each field's errors (Group by issue has a card per field)
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
        // the boxes: title, first and last name are one "Name" box, address and postcode one "Address" box,
        // the course code the "Course" box (user, 5 Nov 2026) — the students with any of them
        const boxSummary = computed(() => {
            const out = fieldSummary.value.filter((f) => !BOX[f.key]);
            Object.entries(BOX_LABEL).forEach(([key, label]) => {
                const parts = fieldSummary.value.filter((f) => BOX[f.key] === key);
                if (!parts.length) return;
                const has = (i, undecided) => i.errors.some((c) => BOX[c.key] === key && (!undecided || !c.route));
                out.push({
                    key, label, count: shown.value.filter((i) => has(i)).length, open: shown.value.filter((i) => has(i, true)).length,
                    parts: parts.sort((a, b) => PART_ORDER.indexOf(a.key) - PART_ORDER.indexOf(b.key)).map((f) => ({ key: f.key, label: f.label, short: SHORT[f.key] || f.label, count: f.count })),
                });
            });
            const order = (f) => FIELD_ORDER.indexOf(f.parts ? f.parts[0].key : f.key);
            return out.sort((a, b) => b.count - a.count || order(a) - order(b));
        });
        const fieldMax = computed(() => Math.max(1, ...boxSummary.value.map((f) => f.count)));
        const boxOf = (key) => BOX[key] || key;
        watch(boxSummary, (list) => { if (fieldFilter.value !== "all" && !list.some((f) => f.key === fieldFilter.value)) fieldFilter.value = "all"; });
        // this step's search (user, 13 Nov 2026): after the chips and filters, by name, reg no., email, SLC name or SSN
        const q = ref("");
        const filtered = computed(() => {
            let out = shown.value;
            if (fieldFilter.value !== "all") out = out.filter((i) => i.errors.some((c) => boxOf(c.key) === fieldFilter.value));
            if (routeFilter.value !== "all") out = out.filter((i) => i.errors.some((c) => (c.route || c.sugg) === routeFilter.value));
            return out;
        });
        const items = computed(() => {
            const out = q.value.trim() ? filtered.value.filter((i) => matchesSearch(q.value, searchText(i))) : filtered.value;
            const sel = store.selected;
            // fewest issues first (user, 5 Nov 2026); the student just opened from elsewhere stays on top
            return [...out].sort((a, b) => (a.id === sel ? -1 : b.id === sel ? 1 : 0) || a.errors.length - b.errors.length || a.line - b.line);
        });
        const sugCount = (k) => withErr.value.reduce((a, i) => a + i.errors.filter((c) => (c.route || c.sugg) === k).length, 0);
        const pendingSugg = computed(() => items.value.reduce((a, i) => a + i.unrouted.filter((c) => c.sugg).length, 0));
        const clean = computed(() => list.value.filter((i) => i.s && !i.errors.length && !i.dec).length);
        const linkedCount = computed(() => list.value.filter((i) => i.s).length);
        const sortedErrors = (i) => [...i.errors].sort((a, b) => FIELD_ORDER.indexOf(a.key) - FIELD_ORDER.indexOf(b.key));
        /*
         * A student's rows: two or more differences of one group (Name, Address, Course — the boxes above)
         * become one row. Together: the actions every part allows, one click for all (RegistrationActions::
         * setRoutes, all or none). Each separately: the usual buttons per part — opened by "Decide each
         * separately", or by itself when the parts were decided differently or have something carried over.
         */
        const eachOpen = ref({});
        const toggleEach = (i, r) => { eachOpen.value = { ...eachOpen.value, [i.id + '-' + r.key]: !eachOpen.value[i.id + '-' + r.key] }; };
        const cardRows = (i) => {
            const out = [];
            const seen = {};
            sortedErrors(i).forEach((ch) => {
                const box = BOX[ch.key];
                const same = box ? i.errors.filter((c) => BOX[c.key] === box) : [];
                if (!box) { out.push({ key: ch.key, ch }); return; }
                if (seen[box]) return;
                seen[box] = true;
                const parts = same.sort((a, b) => PART_ORDER.indexOf(a.key) - PART_ORDER.indexOf(b.key));
                const common = parts.reduce((acc, p) => acc.filter((k) => (FIELD_ROUTES[p.key] || []).includes(k)), [...(FIELD_ROUTES[parts[0].key] || [])]);
                const undecided = parts.every((p) => !p.route);
                const routes = [...new Set(parts.map((p) => p.route))];
                const decided = !undecided && routes.length === 1 && routes[0] && parts.every((p) => !p.state && !p.carried) ? routes[0] : null;
                const suggs = [...new Set(parts.map((p) => p.sugg))];
                const canTogether = undecided && common.length > 0;
                const row = {
                    key: box, label: BOX_LABEL[box], parts, common, decided, canTogether,
                    sugg: suggs.length === 1 && suggs[0] && common.includes(suggs[0]) ? suggs[0] : null,
                    notes: [...new Set(parts.map((p) => p.note).filter(Boolean))],
                    compact: (canTogether || !!decided) && !eachOpen.value[i.id + '-' + box],
                };
                // Name, Address and Course (user, 5 Nov 2026): the whole value on both sides — title and name,
                // address and postcode, course name, intake (· our venue) and code — what differs in the error colour (course
                // names aren't coloured: SLC truncates them) and named under ours
                const bad = new Set(parts.map((p) => p.key));
                const side = (s, o) => BOX_PARTS[box].map((k) => ({
                    k, v: fullVal(i, k, s), o: fullVal(i, k, o), pre: PRE[k] ? PRE[k](i, s) : "", tail: TAIL[k] ? TAIL[k](i, s) : "",
                    bad: k === "intake" ? bad.has("course") : bad.has(k), // the course name is shown, never compared
                })).filter((g) => g.v);
                row.full = { slc: side("slc", "ours"), ours: side("ours", "slc"), stack: box === "crs" };
                row.mismatch = upFirst(andList(parts.map((p) => p.label.toLowerCase()))) + (parts.length > 1 ? " don’t match" : " doesn’t match");
                out.push(row);
            });
            return out;
        };
        const suggCount = (i) => i.unrouted.filter((c) => c.sugg).length;
        const outcomeFilters = [["all", "Any outcome"], ["update", "Update ours"], ["coc", "CoC"], ["contact", "Contact"]];
        // Group by issue: the students (from the same filters) under each field, undecided first
        const groupBy = ref("student");
        const groups = computed(() => fieldSummary.value
            .filter((f) => fieldFilter.value === "all" || boxOf(f.key) === fieldFilter.value)
            .map((f) => {
                const rows = items.value.flatMap((i) => i.errors.filter((c) => c.key === f.key && (routeFilter.value === "all" || (c.route || c.sugg) === routeFilter.value)).map((ch) => ({ i, ch })))
                    .sort((a, b) => (a.ch.route ? 1 : 0) - (b.ch.route ? 1 : 0));
                return { key: f.key, label: f.label, rows, open: rows.filter((x) => !x.ch.route).length, sugg: rows.filter((x) => !x.ch.route && x.ch.sugg).length };
            })
            .filter((g) => g.rows.length));

        return { q, filtered, store, post, go, show, routeFilter, fieldFilter, shown, fieldSummary, boxSummary, fieldTotal, fieldMax, counts, withErr, items, sugCount, pendingSugg, clean, linkedCount, sortedErrors, suggCount, outcomeFilters, groupBy, groups, NOTE, cardRows, toggleEach, ROUTES, COC_TYPE, DIFF, ADDRESS_UPDATE_TIP, BOX, ID_KEYS, plural };
    },
};
</script>
