<template>
    <div class="pagehead">
        <div><span class="eyebrow">Step 4</span><h1>Attendance in the liability period</h1>
            <p>The register for each matched student in the term SLC asks about ({{ termLabel }}), from the term’s start to today: sessions held and attended, physical and online, first and last attended. The term’s dates come from the student’s {{ yearName }} course instance, or from their class plans when the instance has none.</p></div>
    </div>

    <section class="kpis" aria-label="Attendance">
        <div class="kpi t-ok"><span class="k-label">Attending</span><span class="k-val">{{ n('ok') }}</span><span class="k-note">Attended recently, 50% or more</span></div>
        <div class="kpi t-warn"><span class="k-label">To check</span><span class="k-val">{{ n('warn') }}</span><span class="k-note">Low attendance or not seen for 14+ days</span></div>
        <div class="kpi t-bad"><span class="k-label">No attendance</span><span class="k-val">{{ n('none') }}</span><span class="k-note">Nothing attended in the term so far</span></div>
        <div class="kpi t-info"><span class="k-label">No term found</span><span class="k-val">{{ n('noterm') }}</span><span class="k-note">No {{ termLabel }} term on the course</span></div>
    </section>

    <div class="row" style="justify-content: space-between">
        <div class="filters" role="group" aria-label="Show students">
            <button v-for="[k, label] in FILTERS" :key="k" type="button" class="chip" :aria-pressed="show === k" @click="show = k">{{ label }} <span class="num">{{ k === 'all' ? rows.length : n(k) }}</span></button>
        </div>
        <span class="muted small">Today {{ fmtIso(today) }}</span>
    </div>

    <div v-if="!imp" class="card empty">Upload the SIS Attendance Confirmation Report on the Overview first.</div>
    <section v-else class="card">
        <div v-if="!items.length" class="empty">No students here.</div>
        <div v-else class="tablebox"><table class="tbl" style="min-width: 1120px">
            <thead><tr><th scope="col">Student</th><th scope="col">Liability period</th><th scope="col" style="min-width: 260px">Attendance</th><th scope="col">First attended</th><th scope="col">Suggested code</th></tr></thead>
            <tbody><tr v-for="i in items" :key="i.id">
                <th scope="row"><a class="plain" :href="i.s.url" target="_blank" rel="noopener">{{ i.s.title }} {{ i.s.first }} {{ i.s.last }}</a>
                    <div class="muted small" style="font-weight: 400">{{ i.s.id }} · {{ i.status || 'No status' }}</div></th>
                <td>
                    <template v-if="i.liab.start"><b>{{ i.liab.termName }}</b> · {{ fmtIso(i.liab.start) }} – {{ fmtIso(i.liab.end) }}
                        <div class="muted small">{{ i.liab.decl }} · {{ i.liab.from === 'instance' ? 'course instance' : 'class plans' }}{{ i.liab.started ? '' : ' · not started yet' }}</div></template>
                    <span v-else class="pill bad">No {{ i.liab.termName }} term found</span>
                </td>
                <td><AttCell :att="i.liab" /></td>
                <td>{{ i.liab.first ? fmtIso(i.liab.first) : '–' }}</td>
                <td>
                    <span v-if="i.code.suggested" :class="'pill ' + (i.code.suggested === 'A' ? 'ok' : 'bad')"><span class="mono">{{ i.code.suggested }}</span></span>
                    <span v-else class="pill muted">Choose</span>
                    <div class="muted small" style="margin-top: 3px; max-width: 260px">{{ i.code.reason }}</div>
                </td>
            </tr></tbody>
        </table></div>
    </section>
</template>

<script>
import { computed, inject, ref } from "vue";
import { fmtIso } from "./helpers";
import AttCell from "./AttCell.vue";

/*
 * Bulk attendance › step 4 (user, 2 Nov 2026): the register in each matched student's liability
 * period (AttendanceState::liability), read-only. The code it suggests is chosen on step 5.
 */
const FILTERS = [["all", "All"], ["ok", "Attending"], ["warn", "To check"], ["none", "No attendance"], ["noterm", "No term"]];

export default {
    components: { AttCell },
    setup() {
        const { store } = inject("slr");
        const att = computed(() => store.state.attendance);
        const imp = computed(() => att.value.import);
        const yearName = computed(() => (imp.value ? imp.value.year : "the year’s"));
        const termLabel = computed(() => (imp.value ? imp.value.termName : "the term"));
        const rows = computed(() => att.value.rows.filter((i) => i.s && i.liab));
        const kind = (i) => (!i.liab.start ? "noterm" : !i.liab.a.attended ? "none" : i.liab.st === "ok" ? "ok" : "warn");
        const n = (k) => rows.value.filter((i) => kind(i) === k).length;
        const show = ref("all");
        const items = computed(() => (show.value === "all" ? rows.value : rows.value.filter((i) => kind(i) === show.value)));
        const today = new Date().toISOString().slice(0, 10);

        return { imp, yearName, termLabel, rows, n, show, items, FILTERS, fmtIso, today };
    },
};
</script>
