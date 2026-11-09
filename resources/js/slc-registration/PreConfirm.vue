<template>
    <div class="pagehead">
        <div><span class="eyebrow">Stage 4</span><h1>Pre-confirm checks</h1>
            <p>These students match SLC on every field. The system has pulled their awarding body registration and attendance. When you’re happy, confirm registration Yes and they go into the batch awaiting download.</p></div>
        <button type="button" class="btn btn-primary" :disabled="!clear.length || store.busy" @click="confirm(clear.map((i) => i.id))">Confirm Yes for all clear ({{ clear.length }})</button>
    </div>
    <div v-if="items.length === 0" class="card empty">No students are ready for pre-confirm checks.</div>
    <section v-else class="card">
        <div class="pc-bar"><span class="muted small">{{ plural(items.length, 'student') }}</span><button type="button" class="btn-link small" :aria-pressed="allOpen ? 'true' : 'false'" @click="toggleAll">{{ allOpen ? 'Hide all fields' : 'View all fields for every student' }}</button></div>
        <div class="tablebox"><table class="tbl" style="min-width: 1020px">
        <thead><tr><th scope="col">Student</th><th scope="col">Course</th><th scope="col">Awarding body registration</th><th scope="col">Attendance</th><th scope="col">Decision</th></tr></thead>
        <tbody><template v-for="i in items" :key="i.id"><tr :class="open[i.id] ? 'has-allf' : undefined">
            <th scope="row"><a class="plain" :href="i.s.url" target="_blank" rel="noopener">{{ i.s.title }} {{ i.s.first }} {{ i.s.last }}</a><StatusTag :status="i.s.status" /><div class="mono muted small" style="font-weight: 400">{{ i.r.ssn }} · {{ i.s.id }}</div><span class="pill ok" style="margin-top: 4px"><Ico name="check" />{{ i.checks.filter((c) => c.ok).length }} of {{ i.checks.length }} fields match{{ i.errors.length ? ' · ' + i.errors.length + ' accepted' : '' }}</span><RowHistory :entries="i.history" />
                <div><button type="button" class="btn-link small allf-toggle" :aria-expanded="open[i.id] ? 'true' : 'false'" @click="toggle(i.id)">{{ open[i.id] ? 'Hide fields' : 'View all ' + i.checks.length + ' fields' }}</button></div></th>
            <td>{{ i.s.course.name }}<div class="muted small">{{ i.s.course.intake }} · Year {{ i.s.year }}</div>
                <div v-if="i.s.registration" class="muted small" style="margin-top: 4px">SLC registration already recorded (Year {{ i.s.registration.year }}{{ i.s.registration.date ? ', ' + fmtIso(i.s.registration.date) : '' }})</div></td>
            <td>
                <span :class="'pill ' + (i.ab.st === 'ok' ? 'ok' : 'bad')">{{ i.ab.st === 'ok' ? '✓ ' : '✗ ' }}{{ i.ab.text }}</span>
                <div v-if="i.ab.ab" class="small" style="margin-top: 4px">{{ i.ab.ab.qual }}<div class="muted">Reg. <span class="mono">{{ i.ab.ab.regNo }}</span> · {{ fmtIso(i.ab.ab.regDate) }}</div></div>
                <div v-else class="muted small" style="margin-top: 4px">Expected: {{ i.ab.expected }}</div>
            </td>
            <td>
                <AttCell :att="i.att" />
                <label v-if="i.att.st === 'warn'" class="check-line" style="margin-top: 6px"><input :id="'ack-' + i.id" type="checkbox" :checked="i.attChecked" :disabled="store.busy" @change="post('slc.registration.attendance', { row_id: i.id, checked: $event.target.checked })"><span>I’ve checked attendance – student is still studying</span></label>
            </td>
            <td><div class="acts">
                <button type="button" class="btn btn-sm btn-primary" :disabled="!canConfirm(i) || store.busy" @click="confirm([i.id])">Confirm Yes</button>
                <button v-if="i.ab.st !== 'ok'" type="button" class="btn btn-sm" :disabled="store.busy" @click="post('slc.registration.decide', { row_id: i.id, d: 'hold', reason: 'awarding body registration to fix' })">Hold – fix registration</button>
                <button type="button" class="btn btn-sm btn-bad" :disabled="store.busy" @click="post('slc.registration.decide', { row_id: i.id, d: 'notreg' })">Not registered</button>
            </div></td>
        </tr>
        <!-- every field, as on the Data check card (user, 5 Nov 2026) -->
        <tr v-if="open[i.id]" class="allf-row"><td colspan="5"><AllFields :checks="i.checks" /></td></tr>
        </template></tbody>
    </table></div></section>
    <div class="notice"><Ico name="info" /><span>Awarding body registration comes from the student’s awarding body details on their current course. Attendance is from the register since the start of the student’s current year of study.</span></div>
</template>

<script>
import { computed, inject, ref } from "vue";
import { fmtIso, plural } from "./helpers";
import Ico from "./Ico.vue";
import RowHistory from "./RowHistory.vue";
import AttCell from "./AttCell.vue";
import AllFields from "./AllFields.vue";
import StatusTag from "./StatusTag.vue";

export default {
    components: { StatusTag, Ico, RowHistory, AttCell, AllFields },
    setup() {
        const { store, list, post } = inject("slr");
        const items = computed(() => list.value.filter((i) => i.stage === "precheck"));
        const clear = computed(() => items.value.filter((i) => i.ab.st === "ok" && i.att.st === "ok"));
        const canConfirm = (i) => i.ab.st === "ok" && (i.att.st === "ok" || (i.att.st === "warn" && i.attChecked));
        const confirm = (ids) => post("slc.registration.confirm", { row_ids: ids, checked: items.value.filter((i) => ids.includes(i.id) && i.attChecked).map((i) => i.id) });
        // each student's "View all fields" (open by line id)
        const open = ref({});
        const toggle = (id) => { open.value = { ...open.value, [id]: !open.value[id] }; };
        const allOpen = computed(() => items.value.length > 0 && items.value.every((i) => open.value[i.id]));
        const toggleAll = () => { const to = !allOpen.value; open.value = Object.fromEntries(items.value.map((i) => [i.id, to])); };
        return { store, post, items, clear, canConfirm, confirm, fmtIso, plural, open, toggle, allOpen, toggleAll };
    },
};
</script>
