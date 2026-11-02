<template>
    <div class="pagehead">
        <div><span class="eyebrow">Stage 3</span><h1>Actions</h1>
            <p>Students with a CoC or student contact stay on hold and are not confirmed in this round. Once SLC reassesses them, they come back on the next SIS export.</p></div>
    </div>
    <div class="filters" role="tablist" aria-label="Action lists">
        <button v-for="[k, l, n] in tabs" :key="k" type="button" role="tab" class="chip" :aria-selected="tab === k" :aria-pressed="tab === k" @click="tab = k">{{ l }} <span class="num">{{ n }}</span></button>
    </div>

    <section v-if="tab === 'coc'" class="card">
        <div class="card-h"><h2>Change of Circumstances to raise in SIS</h2><span class="muted small">SIS → CoC Home → Create CoC, or bulk CSV upload</span></div>
        <div v-if="cocItems.length === 0" class="empty">No CoCs to issue.</div>
        <div v-else class="tablebox"><table class="tbl" style="min-width: 900px">
            <thead><tr><th scope="col">Student</th><th scope="col">CoC type</th><th scope="col">SLC gives us</th><th scope="col">We hold</th><th scope="col">Status</th></tr></thead>
            <tbody><tr v-for="{ i, ch } in cocItems" :key="i.id + ch.key">
                <th scope="row">{{ i.s.first }} {{ i.s.last }}<div class="mono muted small" style="font-weight: 400">{{ i.r.ssn }}</div></th>
                <td><b>{{ COC_TYPE[ch.key] }}</b><div class="muted small">{{ ch.label }}</div></td>
                <td class="slc">{{ ch.slc }}</td><td class="ours">{{ ch.ours }}</td>
                <td><div class="acts">
                    <span v-if="ch.state" class="pill ok">CoC submitted · {{ ch.state.by }} {{ ch.state.at }}</span>
                    <button v-else type="button" class="btn btn-sm btn-primary" :disabled="store.busy" @click="mark(i, ch, 'issued')">Mark submitted in SIS</button>
                    <button type="button" class="btn-link small" :disabled="store.busy" @click="clear(i, ch)">Change</button>
                </div><div v-if="ch.carried" class="carried small">Carried over from an earlier upload</div></td>
            </tr></tbody>
        </table></div>
    </section>

    <section v-if="tab === 'contact'" class="card">
        <div class="card-h"><h2>Students to contact</h2><span class="muted small">{{ copied === 'fail' ? 'Copy isn’t available here – select the message text instead' : 'Copy the message into an email or text' }}</span></div>
        <div v-if="contactItems.length === 0" class="empty">No students to contact.</div>
        <div v-else class="tablebox"><table class="tbl" style="min-width: 960px">
            <thead><tr><th scope="col">Student</th><th scope="col">Field</th><th scope="col">Message</th><th scope="col">Status</th></tr></thead>
            <tbody><tr v-for="{ i, ch } in contactItems" :key="i.id + ch.key">
                <th scope="row">{{ i.s.first }} {{ i.s.last }}<div class="mono muted small" style="font-weight: 400">{{ i.r.ssn }} · {{ i.s.id }}</div></th>
                <td><b>{{ ch.label }}</b><div class="small">SLC: {{ ch.slc }}</div><div class="small muted">Ours: {{ ch.ours }}</div></td>
                <td><div class="msg">{{ contactMessage(ch.key, ch, i.s) }}</div><button type="button" class="btn-link small" @click="copy(i.id + ch.key, contactMessage(ch.key, ch, i.s))">{{ copied === i.id + ch.key ? 'Copied' : 'Copy message' }}</button></td>
                <td><div class="acts">
                    <button v-if="!ch.state" type="button" class="btn btn-sm btn-primary" :disabled="store.busy" @click="mark(i, ch, 'contacted')">Mark contacted</button>
                    <template v-if="ch.state && ch.state.state === 'contacted'"><span class="pill warn">Contacted · {{ ch.state.at }}</span><button type="button" class="btn btn-sm" :disabled="store.busy" @click="mark(i, ch, 'done')">Student updated SLC</button></template>
                    <span v-if="ch.state && ch.state.state === 'done'" class="pill ok">Updated with SLC · {{ ch.state.at }}</span>
                    <button type="button" class="btn-link small" :disabled="store.busy" @click="clear(i, ch)">Change</button>
                </div><div v-if="ch.carried" class="carried small">Carried over from an earlier upload</div></td>
            </tr></tbody>
        </table></div>
    </section>

    <section v-if="tab === 'holds'" class="card">
        <div class="card-h"><h2>Other holds</h2><span class="muted small">Awarding body registration, student not identified</span></div>
        <div v-if="holds.length === 0" class="empty">No other holds.</div>
        <div v-else class="tablebox"><table class="tbl" style="min-width: 700px">
            <thead><tr><th scope="col">Student</th><th scope="col">Reason</th><th scope="col">By</th><th scope="col"></th></tr></thead>
            <tbody><tr v-for="i in holds" :key="i.id">
                <th scope="row">{{ i.s ? i.s.first + ' ' + i.s.last : titleCase(i.r.first + ' ' + i.r.last) }}<div class="mono muted small" style="font-weight: 400">{{ i.r.ssn }}</div></th>
                <td>{{ i.dec.reason }}</td><td class="muted">{{ i.dec.by }} · {{ i.dec.at }}<div v-if="i.dec.carried" class="carried small">Carried over from an earlier upload</div></td>
                <td><button type="button" class="btn btn-sm" :disabled="store.busy" @click="post('slc.registration.undo', { row_id: i.id })">Resolved – release</button></td>
            </tr></tbody>
        </table></div>
    </section>

    <section v-if="tab === 'updates'" class="card">
        <div class="card-h"><h2>Changes made to our records</h2><span class="muted small">Taken from SLC data</span></div>
        <div v-if="updates.length === 0" class="empty">No records updated yet.</div>
        <div v-else class="tablebox"><table class="tbl" style="min-width: 760px">
            <thead><tr><th scope="col">Student</th><th scope="col">Field</th><th scope="col">Was</th><th scope="col">Now</th><th scope="col">By</th></tr></thead>
            <tbody><tr v-for="(u, k) in updates" :key="k"><th scope="row">{{ u.name }}<div class="mono muted small" style="font-weight: 400">{{ u.sid }}</div></th><td>{{ u.field }}</td><td class="ours">{{ u.from }}</td><td class="slc">{{ u.to }}</td><td class="muted">{{ u.by }} · {{ u.at }}</td></tr></tbody>
        </table></div>
    </section>
</template>

<script>
import { computed, inject, ref } from "vue";
import { COC_TYPE, contactMessage, titleCase } from "./helpers";

export default {
    setup() {
        const { store, list, post, cocItems, contactItems } = inject("slr");
        const tab = ref("coc");
        const copied = ref("");
        const holds = computed(() => list.value.filter((i) => i.stage === "held"));
        const updates = computed(() => store.state.updates);
        const tabs = computed(() => [["coc", "CoC to issue", cocItems.value.length], ["contact", "Contact student", contactItems.value.length], ["holds", "Other holds", holds.value.length], ["updates", "Our record updated", updates.value.length]]);
        const copy = (k, text) => {
            try { navigator.clipboard.writeText(text).then(() => { copied.value = k; }, () => { copied.value = "fail"; }); } catch (e) { copied.value = "fail"; }
        };
        const mark = (i, ch, state) => post("slc.registration.action", { row_id: i.id, key: ch.key, state });
        const clear = (i, ch) => post("slc.registration.route.clear", { row_id: i.id, key: ch.key });
        return { store, post, tab, tabs, copied, holds, updates, cocItems, contactItems, copy, mark, clear, contactMessage, COC_TYPE, titleCase };
    },
};
</script>
