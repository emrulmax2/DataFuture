<template>
    <div class="pagehead">
        <div><span class="eyebrow">Clear DB</span><h1>Clear {{ name }} data</h1>
            <p>Deletes this module’s uploads and the work done on them, so you can start again from a fresh file. This can’t be undone.</p></div>
    </div>

    <section class="card" aria-labelledby="clr-h">
        <div class="card-h"><h2 id="clr-h">What will be deleted</h2><span v-if="total !== null" class="muted small">{{ plural(total, 'row') }}</span></div>
        <div v-if="counts === null" class="card-b muted">Counting…</div>
        <div v-else class="tablebox"><table class="tbl">
            <thead><tr><th scope="col">Data</th><th scope="col">Table</th><th scope="col" style="text-align: right">Rows</th></tr></thead>
            <tbody><tr v-for="c in counts" :key="c.table">
                <th scope="row">{{ c.label }}</th><td class="mono muted small">{{ c.table }}</td><td class="num" style="text-align: right">{{ c.count }}</td>
            </tr></tbody>
        </table></div>
    </section>

    <div class="notice"><Ico name="info" /><span><b>Not touched:</b> {{ KEPT[module] }}</span></div>

    <section class="card" aria-labelledby="clr-c">
        <div class="card-h"><h2 id="clr-c">Clear</h2></div>
        <div class="card-b">
            <label for="clr-word" class="small">Type <b>CLEAR</b> to confirm</label>
            <div class="row" style="margin-top: 6px">
                <input id="clr-word" v-model.trim="word" class="input" style="max-width: 200px" autocomplete="off" spellcheck="false">
                <button type="button" class="btn btn-bad" :disabled="word !== 'CLEAR' || !total || store.busy" @click="clear">Clear {{ name }} data</button>
            </div>
            <div v-if="total === 0" class="muted small" style="margin-top: 8px">There is nothing to clear.</div>
        </div>
    </section>
</template>

<script>
import { computed, inject, onMounted, ref, watch } from "vue";
import { plural } from "./helpers";
import Ico from "./Ico.vue";

/*
 * Clear DB (user, 6 Nov 2026; users 1 and 7 only): the last item of each module's sub-menu. Shows
 * what the module's own tables hold and empties them (server: ModuleReset) once CLEAR is typed.
 */
const NAMES = { registration: "Bulk registration", attendance: "Bulk attendance", payment: "Bulk payment upload" };
const KEPT = {
    registration: "student details and addresses, SSNs, SLC registrations and agreements, CoCs and the course mapping. Files already uploaded stay in storage.",
    attendance: "student details, SSNs, SLC registrations and CoCs. Files already uploaded stay in storage.",
    payment: "money receipts, instalments, agreements, SSNs and Reports › Accounts › Payments Upload. Files already uploaded stay in storage.",
};

export default {
    components: { Ico },
    props: { module: { type: String, required: true } },
    setup(props) {
        const { store, post, get } = inject("slr");
        const counts = ref(null);
        const word = ref("");
        const load = async () => {
            const d = await get("slc.registration.clear.counts", { module: props.module });
            counts.value = d ? d.counts : [];
        };
        onMounted(load);
        watch(() => props.module, () => { counts.value = null; word.value = ""; load(); });
        const total = computed(() => (counts.value ? counts.value.reduce((a, c) => a + c.count, 0) : null));
        const name = computed(() => NAMES[props.module] || props.module);
        const clear = async () => {
            const d = await post("slc.registration.clear", { module: props.module, confirm: word.value });
            if (d) {
                word.value = "";
                await load();
            }
        };
        return { store, counts, word, total, name, clear, KEPT, plural };
    },
};
</script>
