<template>
    <div class="stack" style="gap: 8px">
        <div class="muted small">SLC sent <b>{{ titleCase(i.r.forename + ' ' + i.r.surname) }}</b> · SSN <span class="mono">{{ i.r.ssn }}</span> · DOB {{ fmtIso(i.r.dob) }} · {{ i.r.slcCode }} {{ i.r.courseName }}. No student holds this SSN.</div>
        <div class="stack" style="gap: 6px">
            <label class="lbl" :for="'payq-' + i.id">Search students</label>
            <input :id="'payq-' + i.id" v-model="q" class="input" type="search" placeholder="Name, student ID, postcode or DOB (yyyy-mm-dd)">
        </div>
        <div v-for="s in results" :key="s.sid" class="stack" style="gap: 8px; padding-top: 8px; border-top: 1px solid var(--line-soft)">
            <div class="row" style="justify-content: space-between">
                <span><b><a class="plain" :href="s.url" target="_blank" rel="noopener">{{ s.title }} {{ s.first }} {{ s.last }}</a></b><StatusTag :status="s.status" /> <span class="muted">· {{ s.id }} · DOB {{ fmtIso(s.dob) }} · {{ s.postcode || '–' }}</span></span>
                <span v-if="s.ssn && s.ssn.toUpperCase() !== i.r.ssn" class="pill muted">Has SSN <span class="mono">{{ s.ssn }}</span></span>
                <button v-else type="button" class="btn btn-sm" @click="pending = s.sid">This is the student</button>
            </div>
            <div v-if="pending === s.sid" class="confirmrow">
                <span style="flex: 1 1 260px">Match SSN <b class="mono">{{ i.r.ssn }}</b> to <b>{{ s.id }} {{ s.first }} {{ s.last }}</b>?{{ s.ssn ? '' : ' The SSN is saved to their record.' }} Every payment in this upload with that SSN goes to them.</span>
                <button type="button" class="btn btn-primary btn-sm" :disabled="store.busy" @click="link(s.sid)">Match</button>
                <button type="button" class="btn btn-sm" @click="pending = null">Cancel</button>
            </div>
        </div>
        <div v-if="q.trim().length >= 2 && searched && !results.length" class="muted">No students match “{{ q }}”.</div>
    </div>
</template>

<script>
import { inject, ref, watch } from "vue";
import { fmtIso, titleCase } from "./helpers";
import StatusTag from "./StatusTag.vue";

/* Bulk payment upload: a payment whose SSN is on no student — search all students, match (PaymentActions::link). */
export default {
    components: { StatusTag },
    props: { i: { type: Object, required: true } },
    emits: ["done"],
    setup(props, { emit }) {
        const { store, post, get } = inject("slr");
        const q = ref(props.i.r.surname || "");
        const results = ref([]);
        const searched = ref(false);
        const pending = ref(null);
        let t = null;
        const search = () => {
            clearTimeout(t);
            const term = q.value.trim();
            if (term.length < 2) { results.value = []; searched.value = false; return; }
            t = setTimeout(async () => {
                const data = await get("slc.registration.students", { q: term });
                if (data && q.value.trim() === term) { results.value = data.students || []; searched.value = true; }
            }, 250);
        };
        watch(q, search, { immediate: true });
        const link = async (sid) => { const data = await post("slc.registration.pay.link", { row_id: props.i.id, student_id: sid }); if (data) emit("done"); };

        return { store, q, results, searched, pending, link, fmtIso, titleCase };
    },
};
</script>
