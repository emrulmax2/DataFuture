<template>
    <div class="pagehead">
        <div><span class="eyebrow">{{ m.phase }}</span><h1>{{ m.title }}</h1><p>{{ m.intro }}</p></div>
        <span class="pill muted">Planned – not built yet</span>
    </div>
    <section class="card"><div class="card-h"><h2>How it will work</h2></div>
        <ol class="plan"><li v-for="([t, d], k) in m.steps" :key="t"><span class="dot">{{ k + 1 }}</span><div><b>{{ t }}</b><div class="muted small">{{ d }}</div></div></li></ol>
    </section>
    <section v-if="module === 'attendance'" class="card"><div class="card-h"><h2>SIS attendance codes</h2><span class="muted small">From the SLC SIS user guide</span></div>
        <div class="tablebox"><table class="tbl"><thead><tr><th scope="col">Code</th><th scope="col">Meaning</th></tr></thead>
            <tbody><tr v-for="[c, t] in ATT_CODES" :key="c"><th scope="row" class="mono">{{ c }}</th><td>{{ t }}</td></tr></tbody></table></div>
    </section>
    <template v-if="module === 'coc'">
        <section class="card"><div class="card-h"><h2>Already queued from bulk registration</h2><button type="button" class="btn-link" @click="$emit('open-actions')">Manage in registration → Actions</button></div>
            <div v-if="cocItems.length === 0" class="empty">No CoCs raised from registration yet.</div>
            <div v-else class="tablebox"><table class="tbl" style="min-width: 640px">
                <thead><tr><th scope="col">Student</th><th scope="col">CoC type</th><th scope="col">Reason</th><th scope="col">Status</th></tr></thead>
                <tbody><tr v-for="{ i, ch } in cocItems" :key="i.id + ch.key"><th scope="row">{{ i.s.first }} {{ i.s.last }}<StatusTag :status="i.s.status" /><div class="mono muted small" style="font-weight: 400">{{ i.r.ssn }}</div></th><td>{{ COC_TYPE[ch.key] }}</td><td>{{ ch.label }}: SLC {{ ch.slc }} · ours {{ ch.ours }}</td><td><span :class="'pill ' + (ch.state ? 'ok' : 'warn')">{{ ch.state ? 'Submitted in SIS' : 'To issue' }}</span></td></tr></tbody>
            </table></div>
        </section>
        <section class="card"><div class="card-h"><h2>Already queued from bulk attendance</h2><button type="button" class="btn-link" @click="openAttCocs">Manage in attendance → CoCs</button></div>
            <div v-if="attCocs.length === 0" class="empty">No CoCs queued by attendance codes yet.</div>
            <div v-else class="tablebox"><table class="tbl" style="min-width: 640px">
                <thead><tr><th scope="col">Student</th><th scope="col">CoC type</th><th scope="col">Code</th><th scope="col">Status</th></tr></thead>
                <tbody><tr v-for="i in attCocs" :key="i.id"><th scope="row">{{ i.s.first }} {{ i.s.last }}<StatusTag :status="i.s.status" /><div class="mono muted small" style="font-weight: 400">{{ i.r.ssn }}</div></th><td>{{ i.coc.type }}</td><td><span class="mono">{{ i.confirm.code }}</span> · {{ i.code.reason }}</td><td><span :class="'pill ' + (i.coc.actioned ? 'ok' : 'warn')">{{ i.coc.actioned ? 'Done in SIS' : 'To submit' }}</span></td></tr></tbody>
            </table></div>
        </section>
        <section class="card"><div class="card-h"><h2>CoC types in the bulk CSV</h2><span class="muted small">Undergraduate only</span></div>
            <div class="card-b row"><span v-for="k in COC_KINDS" :key="k" class="pill info">{{ k }}</span></div>
        </section>
    </template>
</template>

<script>
import { computed, inject } from "vue";
import { ATT_CODES, COC_KINDS, COC_TYPE, PLANNED } from "./helpers";
import StatusTag from "./StatusTag.vue";

export default {
    components: { StatusTag },
    props: { module: { type: String, required: true } },
    emits: ["open-actions"],
    setup(props) {
        const { store, cocItems } = inject("slr");
        const m = computed(() => PLANNED[props.module]);
        const attCocs = computed(() => ((store.state.attendance && store.state.attendance.rows) || []).filter((i) => i.s && i.confirm && i.coc));
        const openAttCocs = () => { store.module = "attendance"; window.dispatchEvent(new CustomEvent("slr-att-go", { detail: { view: "cocs" } })); window.scrollTo(0, 0); };
        return { m, cocItems, attCocs, openAttCocs, ATT_CODES, COC_KINDS, COC_TYPE };
    },
};
</script>
